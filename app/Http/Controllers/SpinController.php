<?php

namespace App\Http\Controllers;

use App\Events\PrizeWon;
use App\Models\EventSetting;
use App\Models\Prize;
use App\Models\SpinLog;
use App\Models\SystemSetting;
use App\Models\Visitor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SpinController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Public Endpoints
    // ─────────────────────────────────────────────────────────────────────────

     /**
      * Execute the spin.
      *
      * Priority order:
      *   1. God Mode override (force_next_prize_id) — single-use, cleared on consume
      *   2. Daily-quota-filtered weighted random
      *   3. Zonk special handling (no stock decrement, auto-claimed, NO-CLAIM code)
      *
      * POST /api/spin/execute
      */
     public function execute(Request $request)
     {
          $visitorId = $request->session()->get('pending_visitor_id');

          if (! $visitorId) {
               return response()->json(['error' => 'No active session. Please register first.'], 403);
          }

          $visitor = Visitor::findOrFail($visitorId);

          if ($visitor->hasSpunToday()) {
               $request->session()->forget(['pending_visitor_id', 'pending_visitor_name']);
               return response()->json(['error' => 'Already spun today.'], 409);
          }

          try {
               $result = DB::transaction(function () use ($visitor) {

                    // ── Step 1: Load all active prizes + event settings ──────────────────
                    $allPrizes    = Prize::active()->orderBy('id')->get();
                    $eventSetting = EventSetting::current();

                    if ($allPrizes->isEmpty()) {
                         throw new \RuntimeException('No active prizes configured.');
                    }

                    // ── Step 2: God Mode check (atomic read-then-clear) ───────────────
                    $forcedPrizeId = SystemSetting::takeOnce('force_next_prize_id');
                    $isGodMode     = false;

                    if ($forcedPrizeId) {
                         $lockedPrize = Prize::where('id', $forcedPrizeId)
                              ->active()
                              ->lockForUpdate()
                              ->first();

                         if ($lockedPrize) {
                              $isGodMode = true;
                         }
                         // If the forced prize somehow no longer exists/is inactive, fall through
                    }

                    // ── Step 3: Normal weighted-random path ───────────────────────────
                    if (! $isGodMode) {
                         $pool = $this->getAvailablePrizesForToday($allPrizes, $eventSetting);

                         if ($pool->isEmpty()) {
                              throw new \RuntimeException('All prizes are out of stock or quota for today.');
                         }

                         $winner = $this->weightedRandom($pool);

                         // Pessimistic lock on the selected prize row
                         $lockedPrize = Prize::where('id', $winner->id)
                              ->where(function ($q) {
                                   $q->where('is_infinite', true)->orWhere('stock', '>', 0);
                              })
                              ->lockForUpdate()
                              ->first();

                         if (! $lockedPrize) {
                              throw new \RuntimeException('Selected prize just became unavailable. Please try again.');
                         }
                    }

                    // ── Step 4: Zonk / Coba Lagi special path ────────────────────────
                    $isZonk    = $lockedPrize->is_infinite;
                    $claimCode = $isZonk ? 'NO-CLAIM' : SpinLog::generateClaimCode();

                    if (! $isZonk) {
                         $lockedPrize->decrement('stock');
                    }

                    $spinLog = SpinLog::create([
                         'visitor_id' => $visitor->id,
                         'prize_id'   => $lockedPrize->id,
                         'claim_code' => $claimCode,
                         'is_claimed' => $isZonk,  // Zonk auto-claimed
                    ]);

                    $prizeIndex = $allPrizes->search(fn(Prize $p) => $p->id === $lockedPrize->id);

                    return [
                         'prize'      => $lockedPrize,
                         'spinLog'    => $spinLog,
                         'prizeIndex' => (int) $prizeIndex,
                         'claimCode'  => $claimCode,
                         'isZonk'     => $isZonk,
                         'isGodMode'  => $isGodMode,
                    ];
               });
          } catch (\RuntimeException $e) {
               return response()->json(['error' => $e->getMessage()], 422);
          }

          // Broadcast to TV display outside transaction (no locks held during I/O)
          broadcast(new PrizeWon(
               prizeIndex: $result['prizeIndex'],
               prizeName: $result['prize']->name,
               visitorName: $visitor->name,
               claimCode: $result['claimCode'],
               isZonk: $result['isZonk'],
          ));

          $request->session()->forget(['pending_visitor_id', 'pending_visitor_name']);

          return response()->json([
               'success'    => true,
               'prizeName'  => $result['prize']->name,
               'claimCode'  => $result['claimCode'],
               'prizeIndex' => $result['prizeIndex'],
               'isZonk'     => $result['isZonk'],
          ]);
     }

     /**
      * Return all active prizes (for TV wheel rendering).
      * GET /api/prizes
      */
     public function prizes()
     {
          $prizes = Prize::active()
               ->orderBy('id')
               ->get(['id', 'name', 'bg_color', 'stock', 'is_infinite']);

          return response()->json($prizes);
     }

    // ─────────────────────────────────────────────────────────────────────────
    // Core Logic Helpers
    // ─────────────────────────────────────────────────────────────────────────

     /**
      * Filter prizes to those still available today.
      *
      * Effective daily limit resolution order:
      *   1. prize.daily_limit (explicit manual override)
      *   2. floor(prize.initial_stock / event.total_days)  (auto-calc from event settings)
      *   3. null → no daily quota limit
      *
      * @param  Collection<Prize>  $allPrizes
      * @param  EventSetting       $event
      * @return Collection<Prize>
      */
     private function getAvailablePrizesForToday(Collection $allPrizes, EventSetting $event): Collection
     {
          // Single bulk query: count today's wins per prize_id
          $todayCounts = SpinLog::selectRaw('prize_id, COUNT(*) as won_today')
               ->whereDate('created_at', Carbon::today())
               ->groupBy('prize_id')
               ->pluck('won_today', 'prize_id');

          return $allPrizes->filter(function (Prize $prize) use ($todayCounts, $event) {
               // Must have physical stock (or be infinite)
               if (! $prize->is_infinite && $prize->stock <= 0) {
                    return false;
               }

               // Resolve effective daily limit (manual override → auto-calc → no limit)
               $effectiveLimit = $event->effectiveDailyLimit($prize);

               if ($effectiveLimit !== null) {
                    $wonToday = $todayCounts->get($prize->id, 0);
                    if ($wonToday >= $effectiveLimit) {
                         return false;  // Daily quota exhausted → weight drops to 0
                    }
               }

               return true;
          });
     }

     /**
      * Weighted random selection.
      * Each prize's `probability` field is its relative weight.
      */
     private function weightedRandom(Collection $prizes): Prize
     {
          $totalWeight = $prizes->sum('probability');

          if ($totalWeight <= 0) {
               return $prizes->random();
          }

          $random     = random_int(1, $totalWeight);
          $cumulative = 0;

          foreach ($prizes as $prize) {
               $cumulative += $prize->probability;
               if ($random <= $cumulative) {
                    return $prize;
               }
          }

          return $prizes->last();
     }
}

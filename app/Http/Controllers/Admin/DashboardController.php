<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventSetting;
use App\Models\Prize;
use App\Models\SpinLog;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
     // ─────────────────────────────────────────────────────────────────────────
     // Dashboard
     // ─────────────────────────────────────────────────────────────────────────

     public function index()
     {
          $recentLogs = SpinLog::with(['visitor', 'prize'])
               ->latest()
               ->take(50)
               ->get();

          $totalSpins   = SpinLog::count();
          $totalWinners = SpinLog::distinct('visitor_id')->count('visitor_id');
          $todaySpins   = SpinLog::whereDate('created_at', today())->count();
          $prizeStats   = Prize::withCount([
               'spinLogs',
               'spinLogs as today_count' => fn($q) => $q->whereDate('created_at', today()),
          ])->active()->orderBy('id')->get();

          $godModePrize  = SystemSetting::get('force_next_prize_id');
          $eventSetting  = EventSetting::current();

          return view('admin.dashboard', compact(
               'recentLogs',
               'totalSpins',
               'totalWinners',
               'todaySpins',
               'prizeStats',
               'godModePrize',
               'eventSetting',
          ));
     }

    // ─────────────────────────────────────────────────────────────────────────
    // God Mode
    // ─────────────────────────────────────────────────────────────────────────

     /**
      * Set (or clear) the one-shot forced prize.
      * POST /admin/god-mode
      */
     public function setGodMode(Request $request)
     {
          $request->validate([
               'prize_id' => ['nullable', 'integer', 'exists:prizes,id'],
          ]);

          $prizeId = $request->input('prize_id');

          SystemSetting::set('force_next_prize_id', $prizeId ?: null);

          $message = $prizeId
               ? 'God Mode activated! The next spin will be forced to the selected prize.'
               : 'God Mode cleared.';

          return response()->json(['success' => true, 'message' => $message]);
     }

    // ─────────────────────────────────────────────────────────────────────────
    // Probability Simulator
    // ─────────────────────────────────────────────────────────────────────────

     /**
      * Run N in-memory simulated spins. Zero DB mutations, zero spin logs.
      * Returns percentage breakdown per prize.
      * GET /admin/simulate-spin?count=1000
      */
     public function simulateSpin(Request $request)
     {
          $request->validate([
               'count' => ['integer', 'min:1', 'max:100000'],
          ]);

          $count = (int) $request->input('count', 1000);

          // Fetch the live prize pool (same logic as real spin, but using today's quota context)
          $allPrizes = Prize::active()->orderBy('id')->get();

          if ($allPrizes->isEmpty()) {
               return response()->json(['error' => 'No active prizes configured.'], 422);
          }

          // Build available pool respecting daily quota (uses same resolution as real spin)
          $todayCounts = SpinLog::selectRaw('prize_id, COUNT(*) as won_today')
               ->whereDate('created_at', Carbon::today())
               ->groupBy('prize_id')
               ->pluck('won_today', 'prize_id');

          $event = EventSetting::current();

          $pool = $allPrizes->filter(function (Prize $prize) use ($todayCounts, $event) {
               if (! $prize->is_infinite && $prize->stock <= 0) return false;
               $limit = $event->effectiveDailyLimit($prize);
               if ($limit !== null && $todayCounts->get($prize->id, 0) >= $limit) return false;
               return true;
          })->values();

          if ($pool->isEmpty()) {
               return response()->json(['error' => 'No prizes available in the current pool.'], 422);
          }

          $totalWeight = $pool->sum('probability');

          if ($totalWeight <= 0) {
               return response()->json(['error' => 'All prize weights are zero.'], 422);
          }

          // ── Simulation loop — 100% in memory ─────────────────────────────────
          $tally = $pool->mapWithKeys(fn(Prize $p) => [$p->id => 0])->toArray();

          for ($i = 0; $i < $count; $i++) {
               $rand       = random_int(1, $totalWeight);
               $cumulative = 0;
               foreach ($pool as $prize) {
                    $cumulative += $prize->probability;
                    if ($rand <= $cumulative) {
                         $tally[$prize->id]++;
                         break;
                    }
               }
          }

          // ── Build response ────────────────────────────────────────────────────
          $results = $pool->map(function (Prize $prize) use ($tally, $count, $totalWeight, $event) {
               $hits           = $tally[$prize->id] ?? 0;
               $theoreticalPct = $totalWeight > 0 ? round(($prize->probability / $totalWeight) * 100, 2) : 0;
               $achievedPct    = $count > 0        ? round(($hits / $count) * 100, 2)                    : 0;

               return [
                    'id'              => $prize->id,
                    'name'            => $prize->name,
                    'bg_color'        => $prize->bg_color,
                    'probability'     => $prize->probability,
                    'stock'           => $prize->is_infinite ? '∞' : $prize->stock,
                    'daily_limit'     => $event->effectiveDailyLimit($prize),
                    'hits'            => $hits,
                    'theoretical_pct' => $theoreticalPct,
                    'achieved_pct'    => $achievedPct,
                    'delta'           => round($achievedPct - $theoreticalPct, 2),
               ];
          })->values();

          return response()->json([
               'runs'            => $count,
               'pool_size'       => $pool->count(),
               'total_weight'    => $totalWeight,
               'excluded_prizes' => $allPrizes->count() - $pool->count(),
               'results'         => $results,
          ]);
     }

    // ─────────────────────────────────────────────────────────────────────────
    // Event Settings
    // ─────────────────────────────────────────────────────────────────────────

     /**
      * Save event duration settings.
      * POST /admin/event-settings
      */
     public function saveEventSettings(Request $request)
     {
          $data = $request->validate([
               'event_name' => ['required', 'string', 'max:100'],
               'start_date' => ['required', 'date'],
               'total_days' => ['required', 'integer', 'min:1', 'max:365'],
          ]);

          $event = EventSetting::current();
          $event->update($data);

          return back()->with(
               'success',
               "Pengaturan acara disimpan. Daily limit otomatis: stock ÷ {$data['total_days']} hari."
          );
     }
}

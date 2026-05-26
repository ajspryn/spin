<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Singleton-style model — the app always has exactly one row (id = 1).
 *
 * @property string      $event_name
 * @property string|null $start_date
 * @property int         $total_days
 */
class EventSetting extends Model
{
     protected $fillable = ['event_name', 'start_date', 'total_days'];

     protected $casts = [
          'start_date'  => 'date',
          'total_days'  => 'integer',
     ];

    // ─────────────────────────────────────────────────────────────────────────

     /**
      * Return the single event-settings row, creating a default one if absent.
      */
     public static function current(): self
     {
          return self::firstOrCreate(
               ['id' => 1],
               [
                    'event_name' => 'Lucky Spin Exhibition',
                    'start_date' => now()->toDateString(),
                    'total_days' => 1,
               ]
          );
     }

     /**
      * For a given prize, compute the effective daily limit:
      *   - If the prize has an explicit `daily_limit`, use that.
      *   - Otherwise, derive from initial_stock / total_days (floor).
      *   - Returns null when no limit should apply.
      */
     public function effectiveDailyLimit(Prize $prize): ?int
     {
          if ($prize->daily_limit !== null) {
               return (int) $prize->daily_limit;
          }

          if ($prize->initial_stock > 0 && $this->total_days > 0) {
               return (int) floor($prize->initial_stock / $this->total_days);
          }

          return null; // No limit
     }
}

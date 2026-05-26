<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpinLog extends Model
{
     protected $fillable = ['visitor_id', 'prize_id', 'claim_code', 'is_claimed'];

     protected $casts = [
          'is_claimed' => 'boolean',
     ];

     public function visitor(): BelongsTo
     {
          return $this->belongsTo(Visitor::class);
     }

     public function prize(): BelongsTo
     {
          return $this->belongsTo(Prize::class);
     }

     /**
      * Generate a unique claim code in the format SPIN-XXXXXX.
      */
     public static function generateClaimCode(): string
     {
          do {
               $code = 'SPIN-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
          } while (static::where('claim_code', $code)->exists());

          return $code;
     }
}

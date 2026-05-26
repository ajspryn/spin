<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Prize extends Model
{
     protected $fillable = [
          'name',
          'probability',
          'stock',
          'initial_stock',
          'daily_limit',
          'bg_color',
          'is_active',
          'is_infinite',
     ];

     protected $casts = [
          'probability'   => 'integer',
          'stock'         => 'integer',
          'initial_stock' => 'integer',
          'daily_limit'   => 'integer',
          'is_active'     => 'boolean',
          'is_infinite'   => 'boolean',
     ];

     public function spinLogs(): HasMany
     {
          return $this->hasMany(SpinLog::class);
     }

     /** Scopes */
     public function scopeActive($query)
     {
          return $query->where('is_active', true);
     }

     public function scopeAvailable($query)
     {
          return $query->active()->where(function ($q) {
               $q->where('is_infinite', true)->orWhere('stock', '>', 0);
          });
     }

     public function hasStock(): bool
     {
          return $this->is_infinite || $this->stock > 0;
     }

     /**
      * Return how many times this prize has been won today.
      */
     public function wonTodayCount(): int
     {
          return $this->spinLogs()
               ->whereDate('created_at', Carbon::today())
               ->count();
     }

     /**
      * Returns true if this prize has exhausted its daily_limit for today.
      * Prizes with daily_limit = NULL are never blocked by quota.
      */
     public function isDailyQuotaExhausted(): bool
     {
          if ($this->daily_limit === null) {
               return false;
          }
          return $this->wonTodayCount() >= $this->daily_limit;
     }

     /**
      * True when the prize is a consolation / "Coba Lagi" type.
      */
     public function isZonk(): bool
     {
          return $this->is_infinite;
     }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Visitor extends Model
{
     protected $fillable = ['name', 'email', 'whatsapp'];

     public function spinLogs(): HasMany
     {
          return $this->hasMany(SpinLog::class);
     }

     public function latestSpinLog(): HasOne
     {
          return $this->hasOne(SpinLog::class)->latestOfMany();
     }

     /**
      * Check if this visitor has already spun today.
      */
     public function hasSpunToday(): bool
     {
          return $this->spinLogs()
               ->whereDate('created_at', Carbon::today())
               ->exists();
     }
}

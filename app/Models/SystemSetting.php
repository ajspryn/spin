<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SystemSetting extends Model
{
     protected $primaryKey = 'key';
     protected $keyType    = 'string';
     public    $incrementing = false;

     protected $fillable = ['key', 'value'];

    // -------------------------------------------------------------------------

     /**
      * Fetch a setting value by key.
      */
     public static function get(string $key, mixed $default = null): mixed
     {
          $row = static::find($key);
          return $row ? $row->value : $default;
     }

     /**
      * Persist a setting (upsert).
      */
     public static function set(string $key, mixed $value): void
     {
          static::updateOrCreate(['key' => $key], ['value' => $value]);
     }

     /**
      * Atomically read-then-clear a setting in a single UPDATE … RETURNING–style operation.
      * Returns the previous value (or null if it wasn't set).
      * Uses a SELECT … FOR UPDATE inside a transaction to be race-condition safe.
      */
     public static function takeOnce(string $key): mixed
     {
          return DB::transaction(function () use ($key) {
               $row = static::where('key', $key)->lockForUpdate()->first();
               if (! $row || $row->value === null) {
                    return null;
               }
               $value = $row->value;
               $row->update(['value' => null]);
               return $value;
          });
     }
}

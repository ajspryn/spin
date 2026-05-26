<?php

namespace Database\Seeders;

use App\Models\Prize;
use Illuminate\Database\Seeder;

class PrizeSeeder extends Seeder
{
     public function run(): void
     {
          $prizes = [
               ['name' => 'Grand Prize – Smart TV',  'probability' => 2,  'stock' => 1,  'bg_color' => '#EF4444'],
               ['name' => 'Wireless Earbuds',         'probability' => 5,  'stock' => 5,  'bg_color' => '#F59E0B'],
               ['name' => 'Bluetooth Speaker',        'probability' => 8,  'stock' => 10, 'bg_color' => '#10B981'],
               ['name' => 'Tumbler Exclusive',        'probability' => 15, 'stock' => 20, 'bg_color' => '#3B82F6'],
               ['name' => 'Tote Bag',                 'probability' => 20, 'stock' => 30, 'bg_color' => '#8B5CF6'],
               ['name' => 'Sticker Pack',             'probability' => 25, 'stock' => 50, 'bg_color' => '#EC4899'],
               ['name' => 'Try Again! 😅',            'probability' => 25, 'stock' => 0, 'is_infinite' => true, 'bg_color' => '#6B7280'],
          ];

          foreach ($prizes as $prize) {
               Prize::firstOrCreate(['name' => $prize['name']], $prize);
          }
     }
}

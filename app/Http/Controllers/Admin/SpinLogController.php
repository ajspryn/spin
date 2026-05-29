<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpinLog;
use App\Models\Prize;
use Illuminate\Http\Request;

class SpinLogController extends Controller
{
     public function destroy($id)
     {
          $log = SpinLog::with('prize')->findOrFail($id);
          $prize = $log->prize;
          // Only return stock if not infinite
          if ($prize && !$prize->is_infinite) {
               $prize->stock += 1;
               $prize->save();
          }
          $log->delete();
          return redirect()->back()->with('success', 'Pemenang dihapus dan stok hadiah dikembalikan.');
     }
}

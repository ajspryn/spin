<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prize;
use Illuminate\Http\Request;

class PrizeController extends Controller
{
     public function index()
     {
          $prizes = Prize::orderBy('id')->get();
          return view('admin.prizes.index', compact('prizes'));
     }

     public function create()
     {
          return view('admin.prizes.form', ['prize' => new Prize()]);
     }

     public function store(Request $request)
     {
          $data = $this->validatePrize($request);
          // Auto-set initial_stock = stock when creating a new prize
          if (! isset($data['initial_stock']) || $data['initial_stock'] === null) {
               $data['initial_stock'] = $data['stock'];
          }
          Prize::create($data);
          return redirect()->route('admin.prizes.index')
               ->with('success', 'Hadiah berhasil dibuat.');
     }

     public function edit(Prize $prize)
     {
          return view('admin.prizes.form', compact('prize'));
     }

     public function update(Request $request, Prize $prize)
     {
          $data = $this->validatePrize($request);
          $prize->update($data);
          return redirect()->route('admin.prizes.index')
               ->with('success', 'Prize updated successfully.');
     }

     public function destroy(Prize $prize)
     {
          $prize->delete();
          return redirect()->route('admin.prizes.index')
               ->with('success', 'Prize deleted.');
     }

     // -------------------------------------------------------------------------

     private function validatePrize(Request $request): array
     {
          return $request->validate([
               'name'          => ['required', 'string', 'max:100'],
               'probability'   => ['required', 'integer', 'min:1', 'max:100'],
               'stock'         => ['required', 'integer', 'min:0'],
               'initial_stock' => ['nullable', 'integer', 'min:0'],
               'daily_limit'   => ['nullable', 'integer', 'min:1'],
               'bg_color'      => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
               'is_active'     => ['sometimes', 'boolean'],
               'is_infinite'   => ['sometimes', 'boolean'],
          ]);
     }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
     public function showLogin()
     {
          if (session('admin_authenticated')) {
               return redirect()->route('admin.dashboard');
          }
          return view('admin.login');
     }

     public function login(Request $request)
     {
          $request->validate([
               'username' => ['required', 'string'],
               'password' => ['required', 'string'],
          ]);

          $validUsername = config('admin.username');
          $validPassword = config('admin.password');

          if ($request->username === $validUsername && Hash::check($request->password, $validPassword)) {
               $request->session()->put('admin_authenticated', true);
               $request->session()->regenerate();
               return redirect()->route('admin.dashboard');
          }

          return back()->withErrors(['username' => 'Invalid credentials.'])->withInput();
     }

     public function logout(Request $request)
     {
          $request->session()->forget('admin_authenticated');
          $request->session()->regenerate();
          return redirect()->route('admin.login');
     }
}

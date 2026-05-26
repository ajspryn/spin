<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
     /**
      * Show the registration form (Screen 1 - Tablet).
      */
     public function showRegistration()
     {
          return view('visitor.register');
     }

     /**
      * Validate the visitor registration and gate against double-spinning.
      * On success, stores visitor in session and redirects to the controller screen.
      */
     public function register(Request $request)
     {

          $data = $request->validate([
               'name'      => ['required', 'string', 'max:100'],
               'email'     => ['nullable', 'email', 'max:150'],
               'whatsapp'  => ['nullable', 'string', 'regex:/^[0-9\+\-\s]+$/', 'max:20'],
          ]);

          // Normalise WhatsApp number (strip spaces/dashes for consistent check)
          if (!empty($data['whatsapp'])) {
               $data['whatsapp'] = preg_replace('/[\s\-]/', '', $data['whatsapp']);
          }

          // Anti-fraud: check if email OR WhatsApp has already spun today (if provided)
          if (!empty($data['email'])) {
               $existingByEmail = Visitor::where('email', $data['email'])->first();
               if ($existingByEmail && $existingByEmail->hasSpunToday()) {
                    return back()
                         ->withInput()
                         ->withErrors(['email' => 'This email has already participated today. Please come back tomorrow!']);
               }
          }
          if (!empty($data['whatsapp'])) {
               $existingByWhatsapp = Visitor::where('whatsapp', $data['whatsapp'])->first();
               if ($existingByWhatsapp && $existingByWhatsapp->hasSpunToday()) {
                    return back()
                         ->withInput()
                         ->withErrors(['whatsapp' => 'This WhatsApp number has already participated today. Please come back tomorrow!']);
               }
          }

          // Upsert visitor (find by email if provided, else by whatsapp if provided, else create new)
          if (!empty($data['email'])) {
               $visitor = Visitor::firstOrCreate(
                    ['email' => $data['email']],
                    ['name' => $data['name'], 'whatsapp' => $data['whatsapp'] ?? null],
               );
          } elseif (!empty($data['whatsapp'])) {
               $visitor = Visitor::firstOrCreate(
                    ['whatsapp' => $data['whatsapp']],
                    ['name' => $data['name'], 'email' => $data['email'] ?? null],
               );
          } else {
               // No email or whatsapp, just create a new visitor with name only
               $visitor = Visitor::create([
                    'name' => $data['name'],
                    'email' => null,
                    'whatsapp' => null,
               ]);
          }

          // Store visitor ID in session for the spin step
          $request->session()->put('pending_visitor_id', $visitor->id);
          $request->session()->put('pending_visitor_name', $visitor->name);

          return redirect()->route('visitor.controller');
     }

     /**
      * Show the TAP TO SPIN controller screen (Screen 1 - Tablet).
      * Requires a pending visitor in session.
      */
     public function showController(Request $request)
     {
          if (! $request->session()->has('pending_visitor_id')) {
               return redirect()->route('visitor.register');
          }

          return view('visitor.controller', [
               'visitorName' => $request->session()->get('pending_visitor_name'),
          ]);
     }
}

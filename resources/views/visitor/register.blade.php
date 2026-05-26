@extends('layouts.app')
@section('title', 'Register to Spin')

@section('content')
<div class="min-h-screen flex items-center justify-center relative overflow-hidden p-4"
     style="background: radial-gradient(ellipse at 60% 0%, #3b0a0a 0%, #080404 55%)">

    {{-- Decorative blobs --}}
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -left-20 w-[500px] h-[500px] rounded-full bg-red-700/15 blur-3xl"></div>
        <div class="absolute -bottom-40 -right-20 w-[500px] h-[500px] rounded-full bg-rose-800/10 blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-px h-full bg-gradient-to-b from-transparent via-red-900/20 to-transparent"></div>
    </div>

    <div class="relative w-full max-w-md">

        {{-- Header --}}
        <div class="text-center mb-8">
            <img src="{{ asset('logo.png') }}" alt="Logo">
            <h1 class="text-4xl font-black text-white tracking-tight">
                Ayoo<span class="text-red-500">Wargiii</span>
            </h1>
            <p class="mt-2 text-gray-500 text-sm">Isi data kamu dan dapatkan kesempatan memenangkan hadiah menarik!</p>
        </div>

        {{-- Card --}}
        <div class="bg-[#110808]/90 backdrop-blur-2xl border border-red-950/50 rounded-2xl p-8 shadow-2xl shadow-black/60">
            <form method="POST" action="{{ route('visitor.register.submit') }}" novalidate>
                @csrf

                {{-- Name --}}
                <div class="mb-5">
                    <label for="name" class="block text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">Nama Lengkap</label>
                    <input
                        id="name" name="name" type="text"
                        value="{{ old('name') }}"
                        placeholder="Budi Santoso"
                        autofocus
                        class="w-full bg-white/5 border @error('name') border-red-500 @else border-white/10 @enderror rounded-xl px-4 py-3 text-white placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-red-600/70 focus:border-red-600/50 transition"
                    >
                    @error('name')
                        <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">⚠ {{ $message }}</p>
                    @enderror
                </div>

                {{-- Email (optional) --}}
                <div class="mb-5">
                    <label for="email" class="block text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">Alamat Email <span class="text-gray-500 font-normal">(opsional)</span></label>
                    <input
                        id="email" name="email" type="email"
                        value="{{ old('email') }}"
                        placeholder="budi@email.com"
                        class="w-full bg-white/5 border @error('email') border-red-500 @else border-white/10 @enderror rounded-xl px-4 py-3 text-white placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-red-600/70 focus:border-red-600/50 transition"
                    >
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">⚠ {{ $message }}</p>
                    @enderror
                </div>

                {{-- WhatsApp (optional) --}}
                <div class="mb-8">
                    <label for="whatsapp" class="block text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">Nomor WhatsApp <span class="text-gray-500 font-normal">(opsional)</span></label>
                    <input
                        id="whatsapp" name="whatsapp" type="tel"
                        value="{{ old('whatsapp') }}"
                        placeholder="+62 812 3456 7890"
                        class="w-full bg-white/5 border @error('whatsapp') border-red-500 @else border-white/10 @enderror rounded-xl px-4 py-3 text-white placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-red-600/70 focus:border-red-600/50 transition"
                    >
                    @error('whatsapp')
                        <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">⚠ {{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full relative overflow-hidden bg-gradient-to-r from-red-700 to-rose-600 hover:from-red-600 hover:to-rose-500 text-white font-black py-4 rounded-xl text-lg shadow-lg shadow-red-900/50 active:scale-95 transition-all duration-150 tracking-wide"
                >
                    <span class="relative z-10">Putar Sekarang! 🎰</span>
                </button>
            </form>
        </div>

        <p class="text-center text-gray-700 text-xs mt-5">Satu putaran per orang per hari. Harap jujur!</p>
    </div>
</div>
@endsection

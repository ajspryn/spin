@extends('layouts.admin')
@section('title', $prize->exists ? 'Edit Hadiah' : 'Tambah Hadiah')

@section('content')
<div class="max-w-xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.prizes.index') }}"
           class="text-gray-600 hover:text-white transition text-sm flex items-center gap-1.5 hover:bg-white/5 px-3 py-1.5 rounded-lg">
            ← Kembali
        </a>
        <span class="text-gray-700">/</span>
        <h2 class="text-xl font-black text-white">{{ $prize->exists ? 'Edit Hadiah' : 'Tambah Hadiah Baru' }}</h2>
    </div>

    <form
        method="POST"
        action="{{ $prize->exists ? route('admin.prizes.update', $prize) : route('admin.prizes.store') }}"
        x-data="{ color: '{{ old('bg_color', $prize->bg_color ?? '#DC2626') }}' }"
        class="bg-[#110808]/80 backdrop-blur-xl border border-red-950/50 rounded-2xl p-8 space-y-5 shadow-2xl"
    >
        @csrf
        @if($prize->exists) @method('PUT') @endif

        {{-- Name --}}
        <div>
            <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">Nama Hadiah</label>
            <input name="name" type="text" value="{{ old('name', $prize->name) }}"
                placeholder="e.g. Wireless Earbuds"
                class="w-full bg-white/5 border @error('name') border-red-500 @else border-white/10 @enderror rounded-xl px-4 py-3 text-white placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-red-600/70 transition">
            @error('name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
        </div>

        {{-- Probability --}}
        <div>
            <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">
                Bobot Probabilitas <span class="text-gray-700 normal-case tracking-normal">(1 = paling langka, 100 = paling umum)</span>
            </label>
            <input name="probability" type="number" min="1" max="100"
                value="{{ old('probability', $prize->probability ?? 10) }}"
                class="w-full bg-white/5 border @error('probability') border-red-500 @else border-white/10 @enderror rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-red-600/70 transition">
            @error('probability') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
        </div>

        {{-- Stock + Initial Stock --}}
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">Stok Saat Ini</label>
                <input name="stock" type="number" min="0"
                    value="{{ old('stock', $prize->stock ?? 0) }}"
                    class="w-full bg-white/5 border @error('stock') border-red-500 @else border-white/10 @enderror rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-red-600/70 transition">
                @error('stock') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">
                    Stok Awal
                    <span class="text-gray-700 normal-case tracking-normal">(untuk auto daily limit)</span>
                </label>
                <input name="initial_stock" type="number" min="0"
                    value="{{ old('initial_stock', $prize->initial_stock ?? $prize->stock ?? 0) }}"
                    placeholder="= Stok awal event"
                    class="w-full bg-white/5 border @error('initial_stock') border-red-500 @else border-white/10 @enderror rounded-xl px-4 py-3 text-white placeholder-gray-700 focus:outline-none focus:ring-2 focus:ring-red-600/70 transition">
                @error('initial_stock') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Color picker --}}
        <div>
            <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">Warna Segmen Roda</label>
            <div class="flex items-center gap-4">
                <input type="color" name="bg_color" x-model="color"
                    value="{{ old('bg_color', $prize->bg_color ?? '#DC2626') }}"
                    class="w-14 h-14 rounded-xl border border-white/10 cursor-pointer bg-transparent p-1">
                <div class="flex-1 bg-white/5 border border-white/10 rounded-xl px-4 py-3">
                    <p class="text-xs text-gray-600 mb-1.5">Preview</p>
                    <div class="h-8 rounded-lg transition-colors shadow-inner" :style="'background-color: ' + color"></div>
                </div>
                <span class="font-mono text-gray-400 text-sm" x-text="color.toUpperCase()"></span>
            </div>
            @error('bg_color') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
        </div>

        {{-- Daily Limit --}}
        <div>
            <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">
                Limit Harian Manual <span class="text-gray-700 normal-case tracking-normal">(kosongkan = pakai auto-calc dari Pengaturan Acara)</span>
            </label>
            <input name="daily_limit" type="number" min="0"
                value="{{ old('daily_limit', $prize->daily_limit) }}"
                placeholder="Kosong = otomatis (stok awal ÷ total hari)"
                class="w-full bg-white/5 border @error('daily_limit') border-red-500 @else border-white/10 @enderror rounded-xl px-4 py-3 text-white placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-red-600/70 transition">
            @error('daily_limit') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
        </div>

        {{-- Infinite / Zonk toggle --}}
        <div class="bg-amber-950/20 border border-amber-800/40 rounded-xl p-4 space-y-2">
            <div class="flex items-center gap-3">
                <input type="hidden" name="is_infinite" value="0">
                <input id="is_infinite" name="is_infinite" type="checkbox" value="1"
                    {{ old('is_infinite', $prize->is_infinite ?? false) ? 'checked' : '' }}
                    class="w-5 h-5 rounded bg-white/5 border-amber-700 text-amber-500 focus:ring-amber-600">
                <label for="is_infinite" class="text-amber-400 text-sm font-semibold">Infinite / Coba Lagi (Zonk)</label>
            </div>
            <p class="text-xs text-gray-600 ml-8">
                Jika aktif: stok tidak pernah berkurang, kode klaim diset ke <code class="text-gray-500">NO-CLAIM</code>, dan otomatis ditandai sudah diklaim.
            </p>
        </div>

        {{-- Active toggle --}}
        <div class="flex items-center gap-3 bg-white/[0.02] border border-white/5 rounded-xl px-4 py-3">
            <input type="hidden" name="is_active" value="0">
            <input id="is_active" name="is_active" type="checkbox" value="1"
                {{ old('is_active', $prize->is_active ?? true) ? 'checked' : '' }}
                class="w-5 h-5 rounded bg-white/5 border-red-800 text-red-600 focus:ring-red-600">
            <label for="is_active" class="text-gray-300 text-sm">Aktif (tampil di roda)</label>
        </div>

        <button type="submit"
            class="w-full bg-gradient-to-r from-red-700 to-rose-600 hover:from-red-600 hover:to-rose-500 text-white font-bold py-3.5 rounded-xl transition active:scale-95 shadow-lg shadow-red-900/40 tracking-wide mt-2">
            {{ $prize->exists ? 'Simpan Perubahan' : 'Buat Hadiah' }}
        </button>
    </form>
</div>
@endsection

@extends('layouts.admin')
@section('title', 'Manage Prizes')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-black text-white">Daftar Hadiah</h2>
        <p class="text-gray-600 text-sm mt-0.5">{{ $prizes->count() }} hadiah terdaftar</p>
    </div>
    <a href="{{ route('admin.prizes.create') }}"
        class="bg-gradient-to-r from-red-700 to-rose-600 hover:from-red-600 hover:to-rose-500 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition active:scale-95 shadow shadow-red-900/40 flex items-center gap-2">
        <span class="text-base leading-none">+</span> Tambah Hadiah
    </a>
</div>

<div class="bg-[#110808]/70 border border-red-950/40 rounded-2xl overflow-hidden shadow-xl">
    <table class="w-full text-sm">
        <thead class="bg-red-950/30 text-gray-500 uppercase text-xs tracking-widest border-b border-red-950/40">
            <tr>
                <th class="px-6 py-4 text-left">Warna</th>
                <th class="px-6 py-4 text-left">Nama Hadiah</th>
                <th class="px-6 py-4 text-left">Bobot</th>
                <th class="px-6 py-4 text-left">Stok</th>
                <th class="px-6 py-4 text-left">Limit/Hari</th>
                <th class="px-6 py-4 text-left">Status</th>
                <th class="px-6 py-4 text-left">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-red-950/30">
            @forelse($prizes as $prize)
            <tr class="hover:bg-white/[0.02] transition {{ $prize->is_infinite ? 'opacity-70' : '' }}">
                <td class="px-6 py-4">
                    <div class="w-9 h-9 rounded-lg border border-white/10 shadow-sm" style="background-color: {{ $prize->bg_color }}"></div>
                </td>
                <td class="px-6 py-4">
                    <p class="font-semibold text-white">{{ $prize->name }}</p>
                    @if($prize->is_infinite)
                        <span class="text-xs text-amber-500/80 bg-amber-950/30 border border-amber-900/40 rounded px-1.5 py-0.5 mt-1 inline-block">∞ Zonk/Coba Lagi</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <div class="w-20 h-1.5 bg-white/10 rounded-full overflow-hidden">
                            <div class="h-full bg-red-600 rounded-full" style="width: {{ min($prize->probability, 100) }}%"></div>
                        </div>
                        <span class="text-gray-400 font-mono text-xs">{{ $prize->probability }}</span>
                    </div>
                </td>
                <td class="px-6 py-4">
                    @if($prize->is_infinite)
                        <span class="text-gray-500 font-mono">∞</span>
                    @else
                        <span class="font-bold font-mono {{ $prize->stock === 0 ? 'text-red-400' : 'text-white' }}">
                            {{ $prize->stock }}
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <span class="text-gray-500 font-mono text-xs">{{ $prize->daily_limit ?? '—' }}</span>
                </td>
                <td class="px-6 py-4">
                    @if($prize->is_active)
                        <span class="inline-flex items-center gap-1 text-xs bg-emerald-950/50 text-emerald-400 border border-emerald-900/60 rounded-full px-2.5 py-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span> Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-xs bg-white/5 text-gray-600 border border-white/10 rounded-full px-2.5 py-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-700 inline-block"></span> Nonaktif
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <div class="flex gap-2">
                        <a href="{{ route('admin.prizes.edit', $prize) }}"
                            class="text-xs bg-white/5 hover:bg-white/10 border border-white/10 text-gray-300 px-3 py-1.5 rounded-lg transition">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('admin.prizes.destroy', $prize) }}"
                            onsubmit="return confirm('Hapus hadiah {{ addslashes($prize->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="text-xs bg-red-950/50 hover:bg-red-900/60 border border-red-900/60 text-red-400 px-3 py-1.5 rounded-lg transition">
                                Hapus
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-16 text-center">
                    <p class="text-3xl mb-3">🎁</p>
                    <p class="text-gray-600">Belum ada hadiah. Buat yang pertama!</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

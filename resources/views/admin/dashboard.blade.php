@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')

{{-- ── EVENT SETTINGS CARD ─────────────────────────────────────────────────── --}}
<div class="bg-[#110808]/70 border border-red-950/40 rounded-2xl p-6 mb-8">
    <div class="flex items-center justify-between mb-5">
        <h2 class="font-bold text-white flex items-center gap-2">
            📅 Pengaturan Acara
            <span class="text-xs font-normal text-gray-600 bg-white/5 rounded-full px-2 py-0.5 border border-white/10">Event Settings</span>
        </h2>
        <div class="text-xs text-gray-600">
            Daily limit otomatis = <span class="text-gray-400 font-mono">initial_stock ÷ total_days</span>
            (jika manual daily limit tidak diset per hadiah)
        </div>
    </div>

    <form method="POST" action="{{ route('admin.event-settings') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @csrf
        <div>
            <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">Nama Acara</label>
            <input
                name="event_name"
                type="text"
                value="{{ old('event_name', $eventSetting->event_name) }}"
                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white text-sm placeholder-gray-700 focus:outline-none focus:ring-2 focus:ring-red-600/70"
                placeholder="Lucky Spin Exhibition"
            >
        </div>
        <div>
            <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">Tanggal Mulai</label>
            <input
                name="start_date"
                type="date"
                value="{{ old('start_date', $eventSetting->start_date?->toDateString()) }}"
                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-red-600/70"
            >
        </div>
        <div>
            <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">
                Total Hari Acara
                <span class="text-gray-700 ml-1 normal-case">(auto-calc daily limit)</span>
            </label>
            <div class="flex gap-2">
                <input
                    name="total_days"
                    type="number"
                    min="1"
                    max="365"
                    value="{{ old('total_days', $eventSetting->total_days) }}"
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-red-600/70"
                >
                <button
                    type="submit"
                    class="whitespace-nowrap bg-gradient-to-r from-red-700 to-rose-600 hover:from-red-600 hover:to-rose-500 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition active:scale-95 shadow shadow-red-900/40"
                >Simpan</button>
            </div>
        </div>
    </form>

    {{-- Per-prize effective daily limit preview --}}
    @if($prizeStats->isNotEmpty())
    <div class="mt-5 pt-4 border-t border-red-950/30">
        <p class="text-xs text-gray-600 uppercase tracking-widest mb-3">Preview Daily Limit Efektif</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
            @foreach($prizeStats as $p)
            <div class="bg-white/[0.02] border border-white/5 rounded-xl px-3 py-2 flex items-center gap-2">
                <div class="w-3 h-3 rounded-full shrink-0" style="background: {{ $p->bg_color }}"></div>
                <div class="min-w-0">
                    <p class="text-white text-xs font-semibold truncate">{{ $p->name }}</p>
                    @php
                        $limit = $p->daily_limit
                            ?? ($p->initial_stock > 0 && $eventSetting->total_days > 0
                                ? (int) floor($p->initial_stock / $eventSetting->total_days)
                                : null);
                    @endphp
                    <p class="text-gray-500 text-xs">
                        @if($p->is_infinite)
                            <span class="text-sky-400">∞ (Zonk)</span>
                        @elseif($limit)
                            <span class="text-emerald-400">{{ $limit }}/hari</span>
                            @if($p->daily_limit) <span class="text-amber-500 ml-1">(manual)</span> @endif
                        @else
                            <span class="text-gray-700">Tidak ada limit</span>
                        @endif
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

{{-- Stat cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    @foreach ([
        ['label' => 'Total Spin',     'value' => $totalSpins,          'icon' => '🎡', 'border' => 'border-red-900/50',     'glow' => 'bg-red-900/10'],
        ['label' => 'Total Pemenang', 'value' => $totalWinners,        'icon' => '🏆', 'border' => 'border-yellow-900/50',  'glow' => 'bg-yellow-900/10'],
        ['label' => 'Spin Hari Ini',  'value' => $todaySpins,          'icon' => '📅', 'border' => 'border-emerald-900/50', 'glow' => 'bg-emerald-900/10'],
        ['label' => 'Hadiah Aktif',   'value' => $prizeStats->count(), 'icon' => '🎁', 'border' => 'border-rose-900/50',    'glow' => 'bg-rose-900/10'],
    ] as $stat)
    <div class="{{ $stat['glow'] }} border {{ $stat['border'] }} rounded-2xl p-5 relative overflow-hidden">
        <p class="text-2xl mb-3">{{ $stat['icon'] }}</p>
        <p class="text-4xl font-black text-white tracking-tight">{{ number_format($stat['value']) }}</p>
        <p class="text-gray-600 text-xs mt-1.5 uppercase tracking-widest">{{ $stat['label'] }}</p>
    </div>
    @endforeach
</div>

{{-- ── GOD MODE PANEL ────────────────────────────────────────────────────── --}}
<div
    x-data="godModePanel()"
    class="bg-amber-950/20 border border-amber-800/40 rounded-2xl p-6 mb-8"
>
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-bold text-amber-400 flex items-center gap-2">
            ⚡ God Mode
            <span class="text-xs font-normal text-gray-500 bg-gray-800 rounded-full px-2 py-0.5">Single-Use Override</span>
        </h2>
        <span
            x-show="activeId"
            x-cloak
            class="text-xs text-amber-300 bg-amber-900/50 border border-amber-700 rounded-full px-3 py-1 animate-pulse"
        >
            🔥 Override armed — next spin is forced!
        </span>
    </div>

    <p class="text-gray-400 text-sm mb-4">
        Click a prize to force the <strong class="text-white">next single spin</strong> to always award it — regardless of probability or stock.
        The override is consumed immediately after use (one-shot).
    </p>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 mb-4">
        @foreach($prizeStats as $prize)
        <button
            @click="setGodMode({{ $prize->id }})"
            :class="activeId == {{ $prize->id }}
                ? 'ring-2 ring-amber-400 bg-amber-900/30'
                : 'bg-white/[0.03] hover:bg-white/[0.06]'"
            class="relative flex items-center gap-3 rounded-xl px-4 py-3 text-left transition border border-white/10 hover:border-white/20 active:scale-95"
        >
            <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $prize->bg_color }}"></span>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-white truncate">{{ $prize->name }}</p>
                <p class="text-xs text-gray-500">Stock: {{ $prize->is_infinite ? '∞' : $prize->stock }}</p>
            </div>
            <span x-show="activeId == {{ $prize->id }}" x-cloak class="absolute top-1.5 right-1.5 text-amber-400 text-xs">⚡</span>
        </button>
        @endforeach
    </div>

    <div class="flex items-center gap-3">
        <button
            @click="clearGodMode()"
            :disabled="!activeId"
            class="text-sm text-red-400 hover:text-red-300 bg-red-950/40 hover:bg-red-900/40 border border-red-900/60 rounded-lg px-4 py-2 transition disabled:opacity-30 disabled:cursor-not-allowed"
        >
            Clear Override
        </button>
        <span x-show="statusMsg" x-cloak class="text-sm text-gray-400 italic" x-text="statusMsg"></span>
    </div>
</div>

{{-- ── PROBABILITY SIMULATOR ─────────────────────────────────────────────── --}}
<div
    x-data="probabilitySimulator()"
    class="bg-red-950/10 border border-red-900/40 rounded-2xl p-6 mb-8"
>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <div>
            <h2 class="font-bold text-red-300 flex items-center gap-2">🔬 Probability Simulator</h2>
            <p class="text-gray-500 text-sm mt-0.5">Runs N spins in memory — <strong class="text-gray-400">zero database mutations</strong>.</p>
        </div>
        <div class="flex items-center gap-3">
            <select
                x-model="runCount"
                class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
            >
                <option value="100">100 spins</option>
                <option value="1000" selected>1,000 spins</option>
                <option value="5000">5,000 spins</option>
                <option value="10000">10,000 spins</option>
                <option value="100000">100,000 spins</option>
            </select>
            <button
                @click="runSimulation()"
                :disabled="running"
                class="flex items-center gap-2 bg-gradient-to-r from-red-700 to-rose-600 hover:from-red-600 hover:to-rose-500 disabled:opacity-40 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition active:scale-95 shadow shadow-red-900/40"
            >
                <span x-show="running" class="w-4 h-4 border-2 border-rose-300 border-t-transparent rounded-full animate-spin"></span>
                <span x-text="running ? 'Running…' : '▶ Run Simulation'"></span>
            </button>
        </div>
    </div>

    {{-- Results table --}}
    <div x-show="results.length > 0" x-cloak>
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs text-gray-500" x-text="`Results from ${meta.runs?.toLocaleString()} simulated spins (${meta.pool_size} prizes in pool, ${meta.excluded_prizes} excluded by quota/stock)`"></p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-xs text-gray-400 uppercase tracking-wider border-b border-gray-800">
                    <tr>
                        <th class="py-2 pr-4 text-left">Prize</th>
                        <th class="py-2 pr-4 text-right">Weight</th>
                        <th class="py-2 pr-4 text-right">Theoretical %</th>
                        <th class="py-2 pr-4 text-right">Achieved %</th>
                        <th class="py-2 pr-4 text-right">Hits</th>
                        <th class="py-2 text-right">Δ Drift</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/60">
                    <template x-for="row in results" :key="row.id">
                        <tr class="hover:bg-gray-800/30">
                            <td class="py-2.5 pr-4">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="'background-color:' + row.bg_color"></span>
                                    <span class="text-white font-medium" x-text="row.name"></span>
                                    <span x-show="row.stock === '∞'" class="text-xs text-gray-500 bg-gray-800 rounded px-1.5 py-0.5">Infinite</span>
                                </div>
                            </td>
                            <td class="py-2.5 pr-4 text-right text-gray-400 font-mono" x-text="row.probability"></td>
                            <td class="py-2.5 pr-4 text-right text-gray-300 font-mono" x-text="row.theoretical_pct + '%'"></td>
                            <td class="py-2.5 pr-4 text-right font-mono font-bold text-white" x-text="row.achieved_pct + '%'"></td>
                            <td class="py-2.5 pr-4 text-right text-rose-300 font-mono" x-text="row.hits.toLocaleString()"></td>
                            <td class="py-2.5 text-right font-mono text-xs"
                                :class="Math.abs(row.delta) < 0.5 ? 'text-green-400' : Math.abs(row.delta) < 2 ? 'text-yellow-400' : 'text-red-400'"
                                x-text="(row.delta > 0 ? '+' : '') + row.delta + '%'"
                            ></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Bar chart visualization --}}
        <div class="mt-4 space-y-2">
            <template x-for="row in results" :key="'bar-' + row.id">
                <div class="flex items-center gap-3 text-xs">
                    <span class="w-28 text-gray-400 truncate" x-text="row.name"></span>
                    <div class="flex-1 h-5 bg-gray-800 rounded overflow-hidden relative">
                        {{-- Theoretical (background) --}}
                        <div class="absolute inset-y-0 left-0 opacity-30 rounded"
                             :style="'width: ' + row.theoretical_pct + '%; background-color: ' + row.bg_color"></div>
                        {{-- Achieved (foreground) --}}
                        <div class="absolute inset-y-0 left-0 rounded transition-all duration-500"
                             :style="'width: ' + row.achieved_pct + '%; background-color: ' + row.bg_color"></div>
                    </div>
                    <span class="w-12 text-right font-mono text-white font-bold" x-text="row.achieved_pct + '%'"></span>
                </div>
            </template>
        </div>
    </div>

    {{-- Empty state --}}
    <div x-show="results.length === 0 && !running" class="text-center py-10 text-gray-600">
        <p class="text-3xl mb-2">🔬</p>
        <p>Hit "Run Simulation" to test current probability weights.</p>
    </div>

    {{-- Error --}}
    <div x-show="errorMsg" x-cloak class="mt-4 text-sm text-red-400 bg-red-950/40 border border-red-900 rounded-lg px-4 py-3" x-text="errorMsg"></div>
</div>

{{-- ── Prize stock overview ──────────────────────────────────────────────── --}}
<div class="bg-[#110808]/70 border border-red-950/40 rounded-2xl p-6 mb-8">
    <h2 class="font-bold text-gray-200 mb-4">🎁 Prize Stock Overview</h2>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
        @foreach($prizeStats as $prize)
        <div class="rounded-xl p-3 text-center" style="background-color: {{ $prize->bg_color }}22; border: 1px solid {{ $prize->bg_color }}66">
            <div class="w-4 h-4 rounded-full mx-auto mb-2" style="background-color: {{ $prize->bg_color }}"></div>
            <p class="text-sm font-semibold text-white truncate">{{ $prize->name }}</p>
            @if($prize->is_infinite)
                <p class="text-2xl font-black text-gray-400 mt-1">∞</p>
            @else
                <p class="text-2xl font-black {{ $prize->stock === 0 ? 'text-red-400' : 'text-white' }} mt-1">{{ $prize->stock }}</p>
            @endif
            <p class="text-xs text-gray-400">stock</p>
            @if($prize->daily_limit)
                <p class="text-xs text-indigo-400 mt-1">{{ $prize->today_count ?? 0 }}/{{ $prize->daily_limit }} today</p>
            @endif
            <p class="text-xs text-gray-600 mt-0.5">{{ $prize->spin_logs_count }} total won</p>
        </div>
        @endforeach
    </div>
</div>

{{-- ── Winners log ───────────────────────────────────────────────────────── --}}
<div class="bg-[#110808]/70 border border-red-950/40 rounded-2xl overflow-hidden">
    <div class="px-6 py-4 border-b border-red-950/40 flex items-center justify-between">
        <h2 class="font-bold text-gray-200">📋 Recent Winners</h2>
        <span class="text-xs text-gray-500">Last 50 entries</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-red-950/30 text-gray-500 uppercase text-xs tracking-widest">
                <tr>
                    <th class="px-6 py-3 text-left">#</th>
                    <th class="px-6 py-3 text-left">Visitor</th>
                    <th class="px-6 py-3 text-left">Prize</th>
                    <th class="px-6 py-3 text-left">Claim Code</th>
                    <th class="px-6 py-3 text-left">Claimed?</th>
                    <th class="px-6 py-3 text-left">Time</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($recentLogs as $log)
                <tr class="hover:bg-white/[0.02] transition {{ $log->prize->is_infinite ? 'opacity-50' : '' }}">
                    <td class="px-6 py-4 text-gray-500">{{ $log->id }}</td>
                    <td class="px-6 py-4">
                        <p class="font-semibold text-white">{{ $log->visitor->name }}</p>
                        <p class="text-gray-500 text-xs">{{ $log->visitor->email }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $log->prize->bg_color }}"></span>
                            <span class="{{ $log->prize->is_infinite ? 'text-gray-500' : 'text-white' }}">{{ $log->prize->name }}</span>
                            @if($log->prize->is_infinite)
                                <span class="text-xs text-gray-600 bg-gray-800 rounded px-1">Zonk</span>
                            @endif
                        </span>
                    </td>
                    <td class="px-6 py-4 font-mono {{ $log->claim_code === 'NO-CLAIM' ? 'text-gray-700' : 'text-red-300 tracking-widest text-sm' }}">{{ $log->claim_code }}</td>
                    <td class="px-6 py-4">
                        @if($log->is_claimed)
                            <span class="inline-flex items-center gap-1 text-xs bg-green-900/50 text-green-300 border border-green-800 rounded-full px-2.5 py-1">✓ Claimed</span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs bg-yellow-900/50 text-yellow-300 border border-yellow-800 rounded-full px-2.5 py-1">Pending</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-400">{{ $log->created_at->format('d M, H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-10 text-center text-gray-600">No spins recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// ── God Mode Panel ─────────────────────────────────────────────────────────
function godModePanel() {
    return {
        activeId: {{ $godModePrize ?: 'null' }},
        statusMsg: '',

        async setGodMode(prizeId) {
            this.activeId  = prizeId;
            this.statusMsg = '';
            const res  = await this._post({ prize_id: prizeId });
            const data = await res.json();
            this.statusMsg = data.message;
        },

        async clearGodMode() {
            this.activeId  = null;
            this.statusMsg = '';
            const res  = await this._post({ prize_id: null });
            const data = await res.json();
            this.statusMsg = data.message;
        },

        _post(body) {
            return fetch('{{ route('admin.god-mode') }}', {
                method:  'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body:    JSON.stringify(body),
            });
        },
    };
}

// ── Probability Simulator ──────────────────────────────────────────────────
function probabilitySimulator() {
    return {
        runCount: 1000,
        running:  false,
        results:  [],
        meta:     {},
        errorMsg: '',

        async runSimulation() {
            this.running  = true;
            this.results  = [];
            this.errorMsg = '';

            try {
                const res  = await fetch(`{{ route('admin.simulate-spin') }}?count=${this.runCount}`, {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await res.json();

                if (!res.ok) throw new Error(data.error || 'Simulation failed.');

                this.results = data.results;
                this.meta    = { runs: data.runs, pool_size: data.pool_size, excluded_prizes: data.excluded_prizes };
            } catch (err) {
                this.errorMsg = err.message;
            } finally {
                this.running = false;
            }
        },
    };
}
</script>
@endpush
@endsection

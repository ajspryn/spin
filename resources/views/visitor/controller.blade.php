@extends('layouts.app')
@section('title', 'Tap to Spin')

@section('content')
<div
    x-data="tabletController()"
    x-init="init()"
    class="min-h-screen flex flex-col items-center justify-center select-none overflow-hidden relative"
    style="background: radial-gradient(ellipse at 50% 0%, #2d0808 0%, #080404 60%)"
>
    {{-- Decorative blobs --}}
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute top-0 left-0 w-[500px] h-[500px] rounded-full bg-red-900/20 blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-[500px] h-[500px] rounded-full bg-rose-900/15 blur-3xl translate-x-1/2 translate-y-1/2"></div>
    </div>

    {{-- Brand top --}}
    <div class="absolute top-6 left-0 right-0 text-center pointer-events-none">
        <p class="text-red-700/60 text-xs tracking-[0.4em] uppercase font-semibold">Exhibition Booth</p>
    </div>

    <div class="relative z-10 flex flex-col items-center gap-8 px-6 w-full max-w-lg text-center">

        <div>
            <p class="text-gray-500 text-base tracking-widest uppercase font-semibold">Selamat datang,</p>
            <h1 class="text-5xl font-black text-white mt-1 leading-tight">{{ $visitorName }}</h1>
        </div>

        {{-- Idle state --}}
        <div x-show="state === 'idle'" x-cloak>
            <p class="text-gray-500 mb-10 text-sm">Rodamu sudah siap di layar besar. Tekan tombol di bawah!</p>

            <button
                @click="spin()"
                class="relative w-72 h-72 rounded-full cursor-pointer active:scale-90 transition-transform duration-150 group"
                style="background: conic-gradient(from 180deg, #b91c1c, #dc2626, #ef4444, #dc2626, #b91c1c); box-shadow: 0 0 80px rgba(220,38,38,0.55), 0 0 160px rgba(220,38,38,0.2)"
            >
                <span class="absolute inset-0 rounded-full animate-ping bg-red-600/20"></span>
                {{-- Inner ring --}}
                <span class="absolute inset-4 rounded-full border-4 border-white/10 group-active:border-white/20 transition"></span>
                <span class="relative flex flex-col items-center justify-center gap-2">
                    <span class="text-6xl drop-shadow-lg">🎯</span>
                    <span class="text-xl font-black text-white uppercase tracking-widest drop-shadow-md">TAP TO SPIN</span>
                </span>
            </button>
        </div>

        {{-- Spinning state --}}
        <div x-show="state === 'spinning'" x-cloak class="flex flex-col items-center gap-6 w-full">
            {{-- Animated ring --}}
            <div class="relative w-32 h-32">
                <div class="w-32 h-32 rounded-full border-8 border-red-900/40"></div>
                <div class="absolute inset-0 w-32 h-32 rounded-full border-8 border-red-500 border-t-transparent animate-spin"
                     style="box-shadow: 0 0 30px rgba(220,38,38,0.4)"></div>
                <div class="absolute inset-0 flex items-center justify-center text-4xl">🎡</div>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-red-300 animate-pulse">Roda sedang berputar…</p>
                <p class="text-gray-500 text-sm mt-1">Lihat layar besar! Hasilnya akan muncul sebentar lagi.</p>
            </div>
            {{-- Progress bar matching 6s wheel animation --}}
            <div class="w-full max-w-xs">
                <div class="w-full h-2 bg-white/10 rounded-full overflow-hidden">
                    <div
                        x-ref="spinBar"
                        class="h-full bg-gradient-to-r from-red-700 to-rose-400 rounded-full"
                        style="width: 0%; transition: none"
                    ></div>
                </div>
                <p class="text-center text-xs text-gray-600 mt-2">Menunggu roda berhenti…</p>
            </div>
        </div>

        {{-- Result state (normal prize) --}}
        <div x-show="state === 'result'" x-cloak class="flex flex-col items-center gap-4 w-full">
            <div class="text-7xl animate-bounce">🎉</div>
            <h2 class="text-3xl font-black text-red-400">Selamat!</h2>
            <div class="bg-white/5 border border-red-900/50 rounded-2xl p-6 w-full backdrop-blur-sm">
                <p class="text-gray-400 text-xs uppercase tracking-widest mb-1">Hadiah Kamu</p>
                <p class="text-2xl font-black text-white" x-text="prizeName"></p>
                <div class="border-t border-white/10 mt-4 pt-4">
                    <p class="text-xs text-gray-500 uppercase tracking-widest mb-1">Kode Klaim</p>
                    <p class="text-2xl font-mono font-black text-red-300 tracking-[0.3em]" x-text="claimCode"></p>
                </div>
            </div>
            <p class="text-gray-600 text-sm mt-2">
                Kembali ke awal dalam <span class="text-white font-bold" x-text="countdown"></span> detik…
            </p>
        </div>

        {{-- Zonk state --}}
        <div x-show="state === 'zonk'" x-cloak class="flex flex-col items-center gap-4 w-full">
            <div class="text-7xl" style="animation: gentleSway 2s ease-in-out infinite">🎊</div>
            <h2 class="text-2xl font-black text-sky-400">Yaaah!</h2>
            <div class="bg-white/5 border border-sky-900/50 rounded-2xl p-6 w-full backdrop-blur-sm">
                <p class="text-sky-200 font-semibold">Jangan berkecil hati, ya!</p>
                <p class="text-gray-500 text-sm mt-1">Terima kasih sudah mencoba. Semoga beruntung lain waktu!</p>
            </div>
            <p class="text-gray-600 text-sm mt-2">
                Kembali ke awal dalam <span class="text-white font-bold" x-text="countdown"></span> detik…
            </p>
        </div>

        {{-- Error state --}}
        <div x-show="state === 'error'" x-cloak class="text-center w-full">
            <div class="text-6xl mb-4">⚠️</div>
            <div class="bg-red-950/50 border border-red-800/60 rounded-2xl p-5">
                <p class="text-red-300 font-semibold text-lg" x-text="errorMessage"></p>
            </div>
            <p class="text-gray-600 text-sm mt-4">Kembali dalam <span class="text-white font-bold" x-text="countdown"></span> detik…</p>
        </div>

    </div>
</div>

@push('scripts')
<style>
@keyframes gentleSway {
    0%, 100% { transform: rotate(-6deg); }
    50%       { transform: rotate(6deg); }
}
</style>
<script>
function tabletController() {
    return {
        state: 'idle',   // idle | spinning | result | zonk | error
        prizeName: '',
        claimCode: '',
        errorMessage: '',
        countdown: 5,
        _timer: null,

        // ms — must match the TV wheel animation duration (6s + 200ms buffer)
        WHEEL_DURATION: 6200,

        init() {},

        _startSpinBar() {
            this.$nextTick(() => {
                const bar = this.$refs.spinBar;
                if (!bar) return;
                bar.style.transition = 'none';
                bar.style.width = '0%';
                requestAnimationFrame(() => requestAnimationFrame(() => {
                    bar.style.transition = `width ${this.WHEEL_DURATION}ms linear`;
                    bar.style.width = '100%';
                }));
            });
        },

        async spin() {
            if (this.state !== 'idle') return;
            this.state = 'spinning';
            this._startSpinBar();

            try {
                const callStart = Date.now();

                const response = await fetch('/api/spin/execute', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                });

                const data = await response.json();

                if (!response.ok) throw new Error(data.error || 'Terjadi kesalahan.');

                // Wait until the TV wheel has finished its 6.2 s animation,
                // counting from when the API call was first made (the server
                // broadcasts the WebSocket event before returning the response,
                // so the TV starts spinning at roughly the same moment).
                const elapsed   = Date.now() - callStart;
                const remaining = Math.max(0, this.WHEEL_DURATION - elapsed);
                await new Promise(resolve => setTimeout(resolve, remaining));

                this.prizeName = data.prizeName;
                this.claimCode = data.claimCode;
                this.state     = data.isZonk ? 'zonk' : 'result';

            } catch (err) {
                this.errorMessage = err.message;
                this.state = 'error';
            }

            this.startCountdown();
        },

        startCountdown() {
            this.countdown = 5;
            this._timer = setInterval(() => {
                this.countdown--;
                if (this.countdown <= 0) {
                    clearInterval(this._timer);
                    window.location.href = '/';
                }
            }, 1000);
        }
    };
}
</script>
@endpush
@endsection

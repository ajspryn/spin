<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lucky Spin – Live Display</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Pointer — sits on the right side of the wheel, tip points LEFT */
        .wheel-pointer {
            width: 0;
            height: 0;
            border-top:    24px solid transparent;
            border-bottom: 24px solid transparent;
            border-right:  48px solid #EF4444;
            filter: drop-shadow(-6px 0 16px rgba(239,68,68,0.95));
        }
        [x-cloak] { display: none !important; }
        @keyframes gentleSway {
            0%, 100% { transform: rotate(-5deg); }
            50%       { transform: rotate(5deg); }
        }
    </style>
</head>
<body class="bg-[#080404] text-white overflow-hidden select-none">

<div
    x-data="tvDisplay()"
    x-init="init()"
    class="relative w-screen h-screen flex"
>
    {{-- Background gradient --}}
    <div class="absolute inset-0 pointer-events-none" style="background: radial-gradient(ellipse at center, #3b0505 0%, #080404 65%)"></div>

    {{-- Subtle grid lines --}}
    <div class="absolute inset-0 pointer-events-none opacity-[0.03]" style="background-image: linear-gradient(to right, #fff 1px, transparent 1px), linear-gradient(to bottom, #fff 1px, transparent 1px); background-size: 40px 40px"></div>

    {{-- ── LEFT PANEL: Branding + Promo Video ─────────────────────────────── --}}
    <div class="relative z-10 w-[42%] h-full flex flex-col px-10 py-8 border-r border-red-950/30">

        {{-- Brand --}}
        <div class="mb-5">
            <h1 class="text-5xl font-black text-white tracking-tight leading-tight">
                <img src="{{ asset('logo.png') }}" alt="Logo"><span style="background: linear-gradient(to right, #EF4444, #FB7185); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text"></span>
            </h1>
            <p class="text-red-900/70 text-xs mt-2 tracking-[0.4em] uppercase font-semibold">Exhibition Booth · Prize Wheel</p>
        </div>

        {{-- Tagline --}}
        <div class="mb-5">
            <p class="text-3xl font-black text-white leading-snug">Putar roda &amp;<br>menangkan hadiah!</p>
            <p class="text-gray-600 text-sm mt-3 leading-relaxed">Daftar, putar, dan bawa pulang hadiah menarik.<br>Semoga beruntung! 🎉</p>
        </div>

        {{-- Live status --}}
        <div class="flex items-center gap-2 mb-6">
            <span
                class="w-2.5 h-2.5 rounded-full"
                :class="connected ? 'bg-green-400 animate-pulse' : 'bg-red-500'"
            ></span>
            <span
                class="text-xs font-semibold tracking-widest uppercase"
                :class="connected ? 'text-green-400' : 'text-red-400'"
                x-text="connected ? 'LIVE' : 'Reconnecting…'"
            ></span>
        </div>

        {{-- Promo video — letakkan video di public/videos/promo.mp4 --}}
        <div class="flex-1 rounded-2xl overflow-hidden border border-red-950/40 bg-black/40 min-h-0 relative">
            <video
                src="{{ asset('videos/promo.mp4') }}"
                autoplay muted loop playsinline
                class="absolute inset-0 w-full h-90 object-cover"
            ></video>
        </div>
    </div>

    {{-- ── RIGHT PANEL: Wheel ──────────────────────────────────────────────── --}}
    <div class="relative z-10 w-[58%] h-full flex flex-col items-center justify-center gap-6">

        {{-- Wheel + right-side pointer --}}
        <div class="flex items-center">

            {{-- Non-rotating outer wrapper (for center-hub overlay) --}}
            <div class="relative">
                {{-- Canvas wrapper — CSS rotation applied here --}}
                <div
                    id="wheel-wrapper"
                    class="rounded-full"
                    style="box-shadow: 0 0 120px rgba(220,38,38,0.5), 0 0 200px rgba(220,38,38,0.15)"
                    :style="'transform: rotate(' + currentRotation + 'deg); transition: ' + transitionStyle"
                >
                    <canvas id="wheelCanvas" width="580" height="580" class="block"></canvas>
                </div>

                {{-- Center hub — stays fixed (does NOT rotate) --}}
                <div class="absolute inset-0 flex items-center justify-center z-20 pointer-events-none">
                    <div class="w-14 h-14 rounded-full bg-[#080404] border-4 border-red-500 shadow-lg shadow-red-500/40 flex items-center justify-center">
                        <span class="text-xl">🎯</span>
                    </div>
                </div>
            </div>

            {{-- Pointer on the right side of the wheel --}}
            <div class="wheel-pointer -ml-3 relative z-20"></div>
        </div>

        {{-- Standby text below the wheel --}}
        <div
            x-show="state === 'standby' && !isSpinning"
            x-cloak
        >
            <div class="inline-flex items-center gap-3 bg-black/60 backdrop-blur-sm border border-red-900/40 rounded-full px-6 py-3">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                <span class="text-gray-400 text-lg font-semibold tracking-wide">Menunggu putaran berikutnya…</span>
            </div>
        </div>
    </div>

    {{-- ── SUCCESS Modal (Real Prize) ─────────────────────────────────────── --}}
    <div
        x-show="state === 'winner'"
        x-cloak
        x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 scale-75"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-75"
        class="absolute inset-0 z-30 flex items-center justify-center bg-black/70 backdrop-blur-md"
    >
        <div class="bg-[#0d0303]/95 backdrop-blur border-2 border-red-600/50 rounded-3xl p-12 max-w-2xl w-full mx-6 text-center" style="box-shadow: 0 0 100px rgba(220,38,38,0.35)">
            <div class="text-8xl mb-4 animate-bounce">🎉</div>
            <h2 class="text-3xl font-bold text-gray-400 mb-2">Selamat!</h2>
            <p class="text-5xl font-black text-red-400 mb-6 leading-tight" x-text="winner.name"></p>

            <div class="bg-white/5 rounded-2xl p-6 mb-6 border border-white/10">
                <p class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-2">Kamu Memenangkan</p>
                <p class="text-4xl font-black text-white" x-text="winner.prize"></p>
            </div>

            <div class="bg-red-950/50 border border-red-800/60 rounded-xl p-4">
                <p class="text-gray-500 text-xs uppercase tracking-widest mb-1">Kode Klaim</p>
                <p class="text-3xl font-mono font-black text-red-300 tracking-[0.3em]" x-text="winner.claimCode"></p>
            </div>

            <div class="mt-6 h-1.5 bg-white/10 rounded-full overflow-hidden">
                <div id="dismiss-bar" class="h-full rounded-full" style="width:100%; background: linear-gradient(to right, #dc2626, #fb7185)"></div>
            </div>
            <p class="text-gray-700 text-xs mt-2">Auto-dismiss…</p>
        </div>
    </div>

    {{-- ── ZONK / Coba Lagi Modal (Consolation) ─────────────────────────── --}}
    <div
        x-show="state === 'zonk'"
        x-cloak
        x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 scale-75"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-75"
        class="absolute inset-0 z-30 flex items-center justify-center bg-black/60 backdrop-blur-md"
    >
        <div class="bg-linear-to-br from-slate-900 to-slate-950 border-2 border-sky-700/50 rounded-3xl p-12 max-w-2xl w-full mx-6 text-center shadow-[0_0_80px_rgba(14,165,233,0.2)]">
            {{-- Gentle animation instead of bounce --}}
            <div class="text-8xl mb-4" style="animation: gentleSway 2s ease-in-out infinite">🎊</div>

            <h2 class="text-2xl font-bold text-sky-300 mb-3">Yaaah!</h2>
            <p class="text-4xl font-black text-white mb-4 leading-tight" x-text="winner.name"></p>

            <div class="bg-slate-800/60 rounded-2xl p-6 mb-6">
                <p class="text-sky-200 text-lg font-semibold leading-relaxed">
                    Jangan berkecil hati, ya! 😊
                </p>
                <p class="text-gray-400 mt-2 text-sm">
                    Terima kasih sudah mencoba. Semoga beruntung lain waktu!
                </p>
            </div>

            <p class="text-2xl font-black text-sky-400 mb-1" x-text="winner.name"></p>

            <div class="mt-6 h-1.5 bg-slate-800 rounded-full overflow-hidden">
                <div id="dismiss-bar-zonk" class="h-full bg-linear-to-r from-sky-500 to-blue-600 rounded-full" style="width:100%"></div>
            </div>
            <p class="text-gray-600 text-xs mt-2">Auto-dismissing…</p>
        </div>
    </div>

</div>

<script>
function tvDisplay() {
    return {
        prizes: [],
        // state: standby | spinning | winner | zonk
        state: 'standby',
        isSpinning: false,
        connected: false,
        currentRotation: 0,
        transitionStyle: 'none',
        winner: { name: '', prize: '', claimCode: '' },

        async init() {
            await this.loadPrizes();
            this.setupEcho();
        },

        async loadPrizes() {
            try {
                const res   = await fetch('/api/prizes');
                this.prizes = await res.json();
                this.$nextTick(() => this.drawWheel());
            } catch (e) {
                console.error('Failed to load prizes', e);
            }
        },

        setupEcho() {
            if (typeof window.Echo === 'undefined') {
                console.warn('Echo not available. Retrying in 2s…');
                setTimeout(() => this.setupEcho(), 2000);
                return;
            }

            window.Echo.connector.pusher.connection.bind('connected',    () => { this.connected = true;  });
            window.Echo.connector.pusher.connection.bind('disconnected', () => { this.connected = false; });
            window.Echo.connector.pusher.connection.bind('unavailable',  () => { this.connected = false; });

            window.Echo.channel('exhibition-channel')
                .listen('.spin.completed', (event) => this.handleSpin(event));

            this.connected = window.Echo.connector.pusher.connection.state === 'connected';
        },

        handleSpin({ prizeIndex, prizeName, visitorName, claimCode, isZonk }) {
            if (this.isSpinning) return;
            this.isSpinning = true;
            this.state = 'spinning';

            const numPrizes    = this.prizes.length;
            const segmentAngle = 360 / numPrizes;
            const stopAngle    = (90 - ((prizeIndex + 0.5) * segmentAngle) % 360 + 360) % 360;
            const baseRotation = this.currentRotation % 360;
            // Dramatic multi-phase spin: fast → medium → slow → very slow
            const totalSpins = 6 * 360; // total degrees to spin
            const phase1 = 1.0 * 360; // fast (1s)
            const phase2 = 1.5 * 360; // medium (1.5s)
            const phase3 = 2.0 * 360; // slow (2s)
            const phase4 = 1.5 * 360 + stopAngle - baseRotation; // final slow to stop (1.7s)
            // Each phase: [degrees, duration, easing]
            const phases = [
                { deg: phase1, dur: 1000, ease: 'cubic-bezier(0.25,0.8,0.5,1)' },
                { deg: phase2, dur: 1500, ease: 'cubic-bezier(0.4,0.7,0.6,1)' },
                { deg: phase3, dur: 2000, ease: 'cubic-bezier(0.6,0.8,0.7,1)' },
                { deg: phase4, dur: 1700, ease: 'cubic-bezier(0.8,0.9,0.9,1)' },
            ];
            let nextRotation = this.currentRotation;
            let phaseIdx = 0;
            const doPhase = () => {
                if (phaseIdx >= phases.length) {
                    // Done spinning, show result
                    this.winner     = { name: visitorName, prize: prizeName, claimCode };
                    this.isSpinning = false;
                    this.currentRotation = (baseRotation + phase1 + phase2 + phase3 + phase4) % 360;
                    this.transitionStyle = 'none';
                    if (isZonk) {
                        this.state = 'zonk';
                        this._startDismissBar('dismiss-bar-zonk', 6000);
                    } else {
                        this.state = 'winner';
                        this._startDismissBar('dismiss-bar', 7000);
                    }
                    return;
                }
                const { deg, dur, ease } = phases[phaseIdx];
                nextRotation += deg;
                this.transitionStyle = `transform ${dur}ms ${ease}`;
                this.currentRotation = nextRotation;
                phaseIdx++;
                setTimeout(doPhase, dur);
            };
            doPhase();
        },

        /**
         * Animate a dismiss progress bar then reset to standby.
         * @param {string} barId  — element id of the bar div
         * @param {number} duration — ms
         */
        _startDismissBar(barId, duration) {
            const bar = document.getElementById(barId);
            if (bar) {
                bar.style.transition = 'none';
                bar.style.width      = '100%';
                requestAnimationFrame(() => requestAnimationFrame(() => {
                    bar.style.transition = `width ${duration}ms linear`;
                    bar.style.width      = '0%';
                }));
            }
            setTimeout(() => {
                this.state  = 'standby';
                this.winner = { name: '', prize: '', claimCode: '' };
            }, duration);
        },

        // ── Canvas rendering ─────────────────────────────────────────────────

        drawWheel() {
            const canvas = document.getElementById('wheelCanvas');
            if (!canvas || !this.prizes.length) return;

            const ctx   = canvas.getContext('2d');
            const cx    = canvas.width  / 2;
            const cy    = canvas.height / 2;
            const r     = cx - 10;
            const n     = this.prizes.length;
            const arc   = (2 * Math.PI) / n;
            const start = -Math.PI / 2;  // 12 o'clock

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Outer glow ring
            ctx.save();
            ctx.shadowBlur  = 30;
            ctx.shadowColor = 'rgba(220,38,38,0.6)';
            ctx.beginPath();
            ctx.arc(cx, cy, r + 6, 0, 2 * Math.PI);
            ctx.strokeStyle = 'rgba(220,38,38,0.25)';
            ctx.lineWidth   = 12;
            ctx.stroke();
            ctx.restore();

            for (let i = 0; i < n; i++) {
                const prize    = this.prizes[i];
                const from     = start + i * arc;
                const to       = from  + arc;
                const midAngle = from  + arc / 2;
                const depleted = !prize.is_infinite && prize.stock === 0;

                // Segment fill
                ctx.beginPath();
                ctx.moveTo(cx, cy);
                ctx.arc(cx, cy, r, from, to);
                ctx.closePath();
                ctx.fillStyle   = prize.bg_color || '#6366F1';
                ctx.strokeStyle = 'rgba(0,0,0,0.35)';
                ctx.lineWidth   = 2;
                ctx.fill();
                ctx.stroke();

                // Depletion overlay (non-infinite, stock=0)
                if (depleted) {
                    ctx.save();
                    ctx.beginPath();
                    ctx.moveTo(cx, cy);
                    ctx.arc(cx, cy, r, from, to);
                    ctx.closePath();
                    ctx.fillStyle = 'rgba(0,0,0,0.55)';
                    ctx.fill();
                    ctx.restore();
                }

                // Prize label
                ctx.save();
                ctx.translate(cx, cy);
                ctx.rotate(midAngle);
                ctx.textAlign    = 'right';
                ctx.textBaseline = 'middle';
                ctx.fillStyle    = depleted ? '#6B7280' : '#FFFFFF';
                ctx.font         = `bold ${n > 8 ? 13 : 15}px system-ui, sans-serif`;
                ctx.shadowBlur   = 4;
                ctx.shadowColor  = 'rgba(0,0,0,0.8)';

                const label = depleted ? `${prize.name} (Habis)` : prize.name;
                this._wrapText(ctx, label, r - 20, n > 8 ? 13 : 15);
                ctx.restore();
            }
        },

        _wrapText(ctx, text, maxRadius, fontSize) {
            const maxWidth = maxRadius * 0.6;
            const lineH    = fontSize * 1.3;
            const words    = text.split(' ');
            const lines    = [];
            let current    = '';

            for (const word of words) {
                const test = current ? `${current} ${word}` : word;
                if (ctx.measureText(test).width > maxWidth && current) {
                    lines.push(current);
                    current = word;
                } else {
                    current = test;
                }
            }
            if (current) lines.push(current);

            const startY = -(lines.length * lineH) / 2 + lineH / 2;
            lines.forEach((line, idx) => ctx.fillText(line, maxRadius - 15, startY + idx * lineH));
        },
    };
}
</script>
</body>
</html>

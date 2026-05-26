<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login – Lucky Spin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center p-4 overflow-hidden"
      style="background: radial-gradient(ellipse at 50% 0%, #2d0808 0%, #080404 65%)">

    {{-- Background glow --}}
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-64 bg-red-800/20 blur-3xl rounded-full"></div>
    </div>

    <div class="relative w-full max-w-sm">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-red-600/15 border border-red-700/30 mb-4 shadow-lg shadow-red-900/30">
                <span class="text-3xl">🔐</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">
                Lucky<span class="text-red-500">Spin</span>
            </h1>
            <p class="text-gray-600 text-sm mt-1 tracking-widest uppercase">Admin Panel</p>
        </div>

        <form method="POST" action="{{ route('admin.login.submit') }}"
              class="bg-[#110808]/90 backdrop-blur-2xl border border-red-950/50 rounded-2xl p-8 shadow-2xl shadow-black/60">
            @csrf

            @if($errors->any())
                <div class="mb-5 text-sm text-red-300 bg-red-950/50 border border-red-800/60 rounded-xl px-4 py-3 flex items-center gap-2">
                    <span>⚠</span> {{ $errors->first() }}
                </div>
            @endif

            <div class="mb-4">
                <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2" for="username">Username</label>
                <input id="username" name="username" type="text" value="{{ old('username') }}" autofocus
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-red-600/70 focus:border-red-600/50 transition">
            </div>

            <div class="mb-7">
                <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2" for="password">Password</label>
                <input id="password" name="password" type="password"
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-red-600/70 focus:border-red-600/50 transition">
            </div>

            <button type="submit"
                class="w-full bg-gradient-to-r from-red-700 to-rose-600 hover:from-red-600 hover:to-rose-500 text-white font-bold py-3 rounded-xl transition active:scale-95 shadow-lg shadow-red-900/40 tracking-wide">
                Sign In
            </button>
        </form>
    </div>
</body>
</html>

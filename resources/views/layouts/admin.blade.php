<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') – Lucky Spin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#080404] text-gray-100 antialiased">

    {{-- Nav --}}
    <nav class="bg-[#110808]/90 backdrop-blur-xl border-b border-red-950/60 px-6 py-3.5 flex items-center justify-between sticky top-0 z-50">
        <span class="font-black text-lg tracking-tight">
            <span class="text-red-500">🎡</span>
            <span class="text-white ml-1.5">Lucky</span><span class="text-red-500">Spin</span>
            <span class="text-gray-600 font-normal text-xs ml-2 tracking-widest uppercase">Admin</span>
        </span>
        <div class="flex items-center gap-1 text-sm">
            <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-white/5 transition">Dashboard</a>
            <a href="{{ route('admin.prizes.index') }}" class="px-3 py-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-white/5 transition">Prizes</a>
            <form method="POST" action="{{ route('admin.logout') }}" class="inline ml-2">
                @csrf
                <button class="px-3 py-1.5 rounded-lg text-red-400 hover:text-white hover:bg-red-600/20 border border-red-900/50 hover:border-red-700/60 transition text-xs font-semibold tracking-wide">Logout</button>
            </form>
        </div>
    </nav>

    <main class="p-6 max-w-7xl mx-auto">
        @if(session('success'))
            <div class="mb-5 rounded-xl bg-emerald-950/50 border border-emerald-800/60 text-emerald-300 px-4 py-3 text-sm flex items-center gap-2">
                <span class="text-base">✓</span> {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>

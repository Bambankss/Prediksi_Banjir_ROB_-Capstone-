<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') — ROB Predictor</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col font-sans antialiased selection:bg-sky-500 selection:text-white">

    <!-- Admin Header -->
    <header class="sticky top-0 z-50 bg-slate-900 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-6">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center font-bold text-white text-sm shadow">
                            ROB
                        </div>
                        <span class="text-base font-bold text-white tracking-tight">Admin Portal</span>
                    </a>

                    <!-- Nav Links -->
                    <nav class="hidden md:flex items-center space-x-1">
                        <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-sky-400' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('admin.data.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.data.*') ? 'bg-slate-800 text-sky-400' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                            Kelola Data Harian
                        </a>
                        <a href="{{ route('admin.impor.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.impor.*') ? 'bg-slate-800 text-sky-400' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                            Riwayat Impor
                        </a>
                        <a href="{{ route('admin.aktivitas.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.aktivitas.*') ? 'bg-slate-800 text-sky-400' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                            Log Aktivitas
                        </a>
                    </nav>
                </div>

                <!-- Right Action -->
                <div class="flex items-center space-x-4">
                    <a href="{{ route('peta') }}" target="_blank" class="hidden sm:flex items-center space-x-1 text-xs text-sky-400 hover:text-sky-300 font-medium">
                        <span>Buka Peta Publik</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>

                    <div class="flex items-center space-x-2 pl-4 border-l border-slate-800">
                        <span class="text-xs text-slate-300 font-medium hidden sm:inline">{{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="px-2.5 py-1 rounded-md text-xs bg-slate-800 hover:bg-rose-950/40 text-slate-400 hover:text-rose-300 border border-slate-700 transition">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-950/60 border border-emerald-600/40 text-emerald-300 text-sm flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white">&times;</button>
            </div>
        @endif

        @if(session('warning'))
            <div class="p-4 rounded-xl bg-amber-950/60 border border-amber-600/40 text-amber-300 text-sm flex items-center justify-between">
                <span>{{ session('warning') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-amber-400 hover:text-white">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-950/60 border border-rose-600/40 text-rose-300 text-sm flex items-center justify-between">
                <span>{{ session('error') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-white">&times;</button>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-1 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
</html>


<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Peta Prediksi Banjir ROB Jakarta Utara') — ROB Predictor</title>

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- Google Fonts & Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col font-sans antialiased selection:bg-sky-500 selection:text-white">

    <!-- Header / Navbar -->
    <header class="sticky top-0 z-50 bg-slate-900/90 backdrop-blur border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand Logo -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('peta') }}" class="flex items-center space-x-3 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-sky-400 flex items-center justify-center shadow-lg shadow-blue-500/20 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-lg font-bold tracking-tight text-white flex items-center gap-1.5">
                                ROB Predictor
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-400 border border-blue-500/30">Jakut</span>
                            </span>
                            <span class="block text-xs text-slate-400">Prediksi Pasang Air Laut Jakarta Utara</span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <nav class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('peta') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('peta') ? 'bg-slate-800 text-sky-400 border border-slate-700/60' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                        Peta Kerentanan
                    </a>
                    <a href="{{ route('tentang') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('tentang') ? 'bg-slate-800 text-sky-400 border border-slate-700/60' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                        Tentang Data
                    </a>
                </nav>

                <!-- Right Action / Admin Dropdown -->
                <div class="flex items-center space-x-3">
                    @auth
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open" class="flex items-center space-x-2 px-3 py-1.5 rounded-lg bg-blue-950/70 border border-blue-600/40 text-blue-300 text-sm font-medium hover:bg-blue-900/60 transition">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>Admin ({{ Auth::user()->name }})</span>
                                <svg class="w-4 h-4 text-blue-300 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" x-transition.opacity.duration.200ms class="absolute right-0 mt-2 w-48 rounded-xl bg-slate-900 border border-slate-800 shadow-xl py-1 z-50">
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white">
                                    Dashboard Admin
                                </a>
                                <a href="{{ route('admin.data.index') }}" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white">
                                    Kelola Data Harian
                                </a>
                                <div class="border-t border-slate-800 my-1"></div>
                                <form method="POST" action="{{ route('admin.logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-rose-400 hover:bg-rose-950/40 hover:text-rose-300">
                                        Keluar (Logout)
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endauth

                    <!-- Mobile Menu Button -->
                    <div class="flex md:hidden" x-data="{ open: false }">
                        <button @click="open = !open" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <div x-show="open" @click.outside="open = false" class="absolute top-16 left-0 right-0 bg-slate-900 border-b border-slate-800 p-4 space-y-2 z-50">
                            <a href="{{ route('peta') }}" class="block px-3 py-2 rounded-lg text-slate-200 hover:bg-slate-800">Peta Kerentanan</a>
                            <a href="{{ route('tentang') }}" class="block px-3 py-2 rounded-lg text-slate-200 hover:bg-slate-800">Tentang Data</a>
                            @auth
                                <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg text-blue-400 hover:bg-slate-800">Dashboard Admin</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-950 border-t border-slate-800/80 text-slate-400 text-xs py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center space-x-2">
                <span class="font-medium text-slate-300">ROB Predictor Jakarta Utara</span>
                <span>&bull;</span>
                <span>Capstone Machine Learning</span>
                <span>&bull;</span>
                <span class="text-slate-500">31 Kelurahan</span>
            </div>
            <div class="text-center md:text-right text-slate-500">
                <span>Prediksi berbasis model untuk tujuan riset akademik &bull; <strong>Bukan peringatan resmi BMKG/BPBD</strong></span>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>


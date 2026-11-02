@extends('layouts.app')

@section('title', 'Login Administrator — ROB Predictor')

@section('content')
<div class="flex-1 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-8 space-y-6">
        
        <div class="text-center space-y-2">
            <div class="w-12 h-12 rounded-xl bg-blue-600/20 text-blue-400 border border-blue-500/30 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Login Administrator</h1>
            <p class="text-xs text-slate-400">Masuk untuk mengelola data harian dan menjalankan prediksi</p>
        </div>

        @if(session('status'))
            <div class="p-3 rounded-lg bg-emerald-950/60 border border-emerald-600/40 text-emerald-300 text-xs text-center font-medium">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-3 rounded-lg bg-rose-950/60 border border-rose-600/40 text-rose-300 text-xs space-y-1">
                @foreach($errors->all() as $err)
                    <p>{{ $err }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-4">
            @csrf

            <div class="space-y-1.5">
                <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Email Admin</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    placeholder="admin@rob.test"
                    class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
            </div>

            <div class="space-y-1.5">
                <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Kata Sandi</label>
                <input type="password" id="password" name="password" required
                    placeholder="••••••••"
                    class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
            </div>

            <div class="flex items-center justify-between text-xs text-slate-400">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded bg-slate-950 border-slate-700 text-sky-500 focus:ring-0">
                    <span>Ingat sesi saya</span>
                </label>
            </div>

            <button type="submit" class="w-full py-2.5 rounded-lg bg-gradient-to-r from-blue-600 to-sky-500 hover:from-blue-500 hover:to-sky-400 text-white font-semibold text-sm transition shadow-lg shadow-blue-500/20">
                Masuk ke Dashboard
            </button>
        </form>

        <div class="text-center pt-2 border-t border-slate-800">
            <a href="{{ route('peta') }}" class="text-xs text-slate-400 hover:text-white transition">
                &larr; Kembali ke Peta Publik
            </a>
        </div>
    </div>
</div>
@endsection


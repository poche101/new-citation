@extends('layouts.app')

@section('title', 'Admin Login - CELZ5')

@section('content')
<section class="w-full min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-700 via-purple-700 to-pink-600">
    <div class="w-full max-w-md p-10 bg-white/5 backdrop-blur-2xl rounded-3xl shadow-2xl border border-white/20 relative overflow-hidden">

        <!-- Decorative Circles -->
        <div class="absolute -top-20 -left-20 w-72 h-72 rounded-full bg-pink-500/30 animate-pulse-slow"></div>
        <div class="absolute -bottom-20 -right-20 w-72 h-72 rounded-full bg-indigo-500/30 animate-pulse-slow"></div>

        <!-- Header -->
        <div class="text-center mb-8 relative z-10">
            <h1 class="text-5xl font-extrabold text-white mb-2 drop-shadow-lg">Admin Login</h1>
            <p class="text-white/70 text-sm">Securely access the admin dashboard</p>
        </div>

        <!-- Login Form -->
        <form method="POST" action="{{ route('admin.login') }}" class="space-y-6 relative z-10">
            @csrf

            <!-- Email -->
            <div class="relative">
                <span class="absolute top-3 left-3 text-white/70">
                    <i data-lucide="mail" class="w-5 h-5"></i>
                </span>
                <input type="email" name="email" placeholder="Email Address" required
                    class="w-full pl-12 py-3 rounded-xl bg-white/20 text-white placeholder-white/60 border border-white/30 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition shadow-inner" />
            </div>

            <!-- Password -->
            <div class="relative">
                <span class="absolute top-3 left-3 text-white/70">
                    <i data-lucide="lock" class="w-5 h-5"></i>
                </span>
                <input type="password" name="password" placeholder="Password" required
                    class="w-full pl-12 py-3 rounded-xl bg-white/20 text-white placeholder-white/60 border border-white/30 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition shadow-inner" />
            </div>

            <!-- Remember Me & Forgot -->
            <div class="flex items-center justify-between text-white/70 text-sm">
                <label class="flex items-center gap-2">
                    <input type="checkbox" class="accent-purple-500">
                    Remember Me
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="w-full py-3 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-pink-500 hover:to-purple-500 text-white font-semibold rounded-xl shadow-lg transition transform hover:scale-105 hover:shadow-2xl flex items-center justify-center gap-2">
                <i data-lucide="log-in" class="w-5 h-5"></i> Sign In
            </button>
        </form>

        <!-- Divider -->
        <div class="flex items-center my-6 relative z-10">
            <hr class="flex-1 border-white/30" />
            <span class="px-3 text-white/50 text-sm">Welcome</span>
            <hr class="flex-1 border-white/30" />
        </div>

        <!-- Social Login -->
        <div class="flex justify-center gap-4 relative z-10">
            <a href="#" class="p-3 rounded-full bg-white/20 hover:bg-white/30 transition shadow-lg">
                <i data-lucide="github" class="w-5 h-5 text-white"></i>
            </a>
            <a href="#" class="p-3 rounded-full bg-white/20 hover:bg-white/30 transition shadow-lg">
                <i data-lucide="twitter" class="w-5 h-5 text-white"></i>
            </a>
            <a href="#" class="p-3 rounded-full bg-white/20 hover:bg-white/30 transition shadow-lg">
                <i data-lucide="google" class="w-5 h-5 text-white"></i>
            </a>
        </div>

        <!-- Footer -->
        <p class="mt-6 text-center text-white/60 text-sm relative z-10">
            © {{ date('Y') }} CELZ5. All rights reserved.
        </p>
    </div>
</section>
@endsection

@section('styles')
<style>
    /* Subtle pulse animation for decorative circles */
    @keyframes pulse-slow {
        0%, 100% { transform: scale(1); opacity: 0.4; }
        50% { transform: scale(1.2); opacity: 0.6; }
    }
    .animate-pulse-slow { animation: pulse-slow 6s ease-in-out infinite; }
</style>
@endsection

@section('scripts')
<script>
    // Initialize lucide icons
    lucide.createIcons();
</script>
@endsection

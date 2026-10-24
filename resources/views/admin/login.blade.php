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
        <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-6">
            @csrf

            <!-- Email -->
            <input type="email" name="email" placeholder="Email" required
                class="w-full p-3 rounded-lg border focus:ring-2 focus:ring-indigo-500" />

            <!-- Password with show/hide toggle -->
            <div class="relative">
                <input type="password" name="password" id="admin-password" placeholder="Password" required
                    class="w-full p-3 pr-12 rounded-lg border focus:ring-2 focus:ring-indigo-500" />

                <button type="button" id="toggle-password"
                    aria-label="Show password" aria-pressed="false"
                    onclick="
                        var input = document.getElementById('admin-password');
                        var show = input.type === 'password';
                        input.type = show ? 'text' : 'password';
                        this.setAttribute('aria-pressed', show ? 'true' : 'false');
                        this.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                        document.getElementById('eye-open').classList.toggle('hidden', show);
                        document.getElementById('eye-closed').classList.toggle('hidden', !show);
                    "
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-indigo-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded-r-lg">

                    <!-- Eye (password hidden) -->
                    <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>

                    <!-- Eye with slash (password visible) -->
                    <svg id="eye-closed" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 hidden" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>

            <!-- Remember Me -->
            <label>
                <input type="checkbox" name="remember"> Remember Me
            </label>

            <button type="submit"
                class="w-full py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-500">
                Login
            </button>

            @if ($errors->any())
                <div class="text-red-500 mt-2">
                    {{ $errors->first() }}
                </div>
            @endif
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

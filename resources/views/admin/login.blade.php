<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin sign in - CELZ5 Citation</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo2.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,500;6..72,600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: {
            fontFamily: { display: ['Newsreader', 'Georgia', 'serif'], sans: ['"Instrument Sans"', 'system-ui', 'sans-serif'] },
            colors: {
                royal: { 50: '#F3F6FC', 100: '#E3EBFA', 200: '#C4D4F2', 500: '#2557C7', 600: '#1B47A6', 700: '#143A94', 800: '#0E2C73', 900: '#0B2461', 950: '#081B4B' },
                gold: { 100: '#FBF1CF', 300: '#F6D77E', 400: '#F0BC3A', 500: '#E5A913', 600: '#C48F0C', 700: '#8A6200' },
                ink: '#0B1633'
            }
        } } }
    </script>
    <style>
        body { font-family: 'Instrument Sans', system-ui, sans-serif; color: #0B1633; }
        :focus-visible { outline: 3px solid #E5A913; outline-offset: 2px; }
    </style>
</head>
<body class="min-h-screen bg-royal-950">
<main class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10">
    <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-royal-900 via-royal-950 to-ink"></div>
    <div class="pointer-events-none absolute -top-32 -right-32 h-96 w-96 rounded-full bg-gold-500/10 blur-3xl"></div>

    <div class="relative w-full max-w-md">
        <div class="rounded-3xl bg-white p-8 shadow-2xl sm:p-10">
            <img src="{{ asset('images/logo2.png') }}" alt="Christ Embassy Lagos Zone 5" class="mx-auto h-14 object-contain">
            <h1 class="mt-6 text-center font-display text-3xl font-semibold text-royal-900">Admin sign in</h1>
            <p class="mt-1 text-center text-sm text-slate-500">Review and manage submitted reports.</p>

            <form method="POST" action="{{ route('admin.login.submit') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-semibold text-royal-900">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-royal-700 focus:outline-none focus:ring-2 focus:ring-royal-700/20">
                </div>

                <div>
                    <label for="admin-password" class="mb-1.5 block text-sm font-semibold text-royal-900">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="admin-password" required autocomplete="current-password"
                               class="w-full rounded-xl border border-slate-300 px-4 py-3 pr-12 text-sm focus:border-royal-700 focus:outline-none focus:ring-2 focus:ring-royal-700/20">
                        <button type="button" id="toggle-password" aria-label="Show password" aria-pressed="false"
                                class="absolute inset-y-0 right-0 flex items-center rounded-r-xl px-3 text-slate-500 hover:text-royal-700"
                                onclick="
                                    var input = document.getElementById('admin-password');
                                    var show = input.type === 'password';
                                    input.type = show ? 'text' : 'password';
                                    this.setAttribute('aria-pressed', show ? 'true' : 'false');
                                    this.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                                    document.getElementById('eye-open').classList.toggle('hidden', show);
                                    document.getElementById('eye-closed').classList.toggle('hidden', !show);">
                            <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg id="eye-closed" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-royal-700"> Remember me
                </label>

                @if ($errors->any())
                    <p role="alert" class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</p>
                @endif

                <button type="submit" class="w-full rounded-full bg-gold-500 px-6 py-3 text-sm font-bold text-royal-950 transition hover:bg-gold-400">
                    Sign in
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-royal-200">
            <a href="{{ url('/') }}" class="hover:text-white">&larr; Back to the website</a>
            <span class="mx-2 opacity-50">·</span>© {{ date('Y') }} CELZ5
        </p>
    </div>
</main>
</body>
</html>

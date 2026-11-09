<footer class="bg-royal-950 text-royal-200">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-5 py-8 text-sm">
        <div class="flex items-center gap-4">
            <span class="rounded-lg bg-white px-2.5 py-1.5">
                <img src="{{ asset('images/logo2.png') }}" alt="Christ Embassy Lagos Zone 5" class="h-8 w-auto">
            </span>
            <p>&copy; {{ now()->year }} Christ Embassy Lagos Zone 5</p>
        </div>
        <a href="{{ route('admin.login') }}" class="rounded-lg px-3 py-2 text-royal-200 hover:bg-white/10 hover:text-white">Admin sign in</a>
    </div>
</footer>

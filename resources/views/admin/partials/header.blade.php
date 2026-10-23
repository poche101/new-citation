@php
    $admin = Auth::guard('admin')->user();
    $fullName = $admin?->name ?? 'Admin';
    $initials = collect(explode(' ', $fullName))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode('');
@endphp

<header class="bg-ink text-white">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4">
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/logo2.png') }}" alt="" class="h-9 w-9 rounded-lg bg-white/10 object-contain p-1">
            <div>
                <p class="text-sm font-semibold leading-tight">CELZ5 Citation</p>
                <p class="text-xs text-white/60">Admin console</p>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <div class="hidden items-center gap-2 sm:flex">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gold text-xs font-bold">{{ $initials }}</span>
                <span class="text-sm text-white/80">{{ $fullName }}</span>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="rounded-lg border border-white/20 px-3 py-1.5 text-sm hover:bg-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">Log out</button>
            </form>
        </div>
    </div>

    <nav class="border-t border-white/10" aria-label="Admin menu">
        <div class="mx-auto flex max-w-7xl gap-1 overflow-x-auto px-5 text-sm font-semibold">
            <a href="{{ route('admin.dashboard') }}"
               @class([
                   'whitespace-nowrap border-b-2 px-3 py-3 transition',
                   'border-gold text-white' => request()->routeIs('admin.dashboard'),
                   'border-transparent text-white/60 hover:text-white' => ! request()->routeIs('admin.dashboard'),
               ])>
                Dashboard
            </a>
            <a href="{{ route('admin.departments.index') }}"
               @class([
                   'whitespace-nowrap border-b-2 px-3 py-3 transition',
                   'border-gold text-white' => request()->routeIs('admin.departments.*'),
                   'border-transparent text-white/60 hover:text-white' => ! request()->routeIs('admin.departments.*'),
               ])>
                Departments &amp; Groups
            </a>
        </div>
    </nav>
</header>

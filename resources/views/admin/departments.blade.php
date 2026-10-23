<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Departments &amp; Groups - CELZ5 Citation</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo2.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: {
            fontFamily: { sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'] },
            colors: { ink: '#14213D', paper: '#F4F6F9', teal: { DEFAULT: '#0F766E', soft: '#E3F2F0' }, gold: { DEFAULT: '#B8893B', soft: '#F7EEDC' } }
        } } }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
</head>

<body class="bg-paper font-sans text-ink antialiased"
      x-data="manager()"
      @keydown.escape.window="addOpen = false; editing.id = null; removing.id = null">

    @include('admin.partials.header')

    <main class="mx-auto max-w-7xl px-5 py-8">

        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                 class="mb-6 rounded-xl border border-teal/20 bg-teal-soft px-4 py-3 text-sm font-medium text-teal">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight">Departments &amp; Groups</h1>
                <p class="mt-1 text-sm text-slate-500">These are the options people see on the homepage and submission forms.</p>
            </div>
            <button @click="addOpen = true"
                    class="rounded-xl bg-teal px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal focus-visible:ring-offset-2">
                Add department or group
            </button>
        </div>

        <section class="rounded-2xl bg-white ring-1 ring-slate-200/70">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-4">
                <div class="inline-flex rounded-xl bg-paper p-1 text-sm font-semibold">
                    <button @click="tab = 'dept'" :class="tab === 'dept' ? 'bg-white shadow-sm text-ink' : 'text-slate-500'" class="rounded-lg px-4 py-1.5">
                        Departments <span class="ml-1 text-xs text-slate-400">{{ $departments->count() }}</span>
                    </button>
                    <button @click="tab = 'group'" :class="tab === 'group' ? 'bg-white shadow-sm text-ink' : 'text-slate-500'" class="rounded-lg px-4 py-1.5">
                        Groups <span class="ml-1 text-xs text-slate-400">{{ $groups->count() }}</span>
                    </button>
                </div>
                <input type="search" x-model="q" placeholder="Search by name"
                       class="w-56 rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20">
            </div>

            @foreach ([
                ['dept', $departments, 'department'],
                ['group', $groups, 'group'],
            ] as [$key, $rows, $noun])
                <div x-show="tab === '{{ $key }}'" x-cloak class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-paper text-xs font-semibold text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Citations</th>
                                <th class="px-4 py-3">Added</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($rows as $item)
                                <tr x-show="match($el)" class="hover:bg-paper/60">
                                    <td class="px-4 py-3 font-semibold">{{ $item->name }}</td>
                                    <td class="px-4 py-3 tabular-nums text-slate-600">{{ $item->citations_count }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $item->created_at?->format('d M Y') ?? 'N/A' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">
                                        <button type="button"
                                                @click="editing = { id: {{ $item->id }}, name: {{ Js::from($item->name) }}, type: '{{ $noun }}' }"
                                                class="rounded-lg px-3 py-1.5 text-xs font-semibold text-teal hover:bg-teal-soft">Edit</button>
                                        <button type="button"
                                                @click="removing = { id: {{ $item->id }}, name: {{ Js::from($item->name) }}, count: {{ $item->citations_count }}, type: '{{ $noun }}' }"
                                                class="rounded-lg px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">Delete</button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-12 text-center text-slate-500">No {{ $noun }}s yet. Use "Add department or group" to create one.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endforeach
        </section>
    </main>

    {{-- Add --}}
    <div x-show="addOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-ink/60 p-4" @click.self="addOpen = false">
        <form method="POST" action="{{ route('admin.departments.store') }}" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            @csrf
            <h3 class="text-lg font-bold">Add department or group</h3>
            <p class="mt-1 text-sm text-slate-500">It appears on the matching section of the homepage straight away.</p>

            <fieldset class="mt-5">
                <legend class="block text-sm font-semibold">Category</legend>
                <div class="mt-1 grid grid-cols-2 gap-2">
                    @foreach (['department' => 'Department', 'group' => 'Group'] as $value => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="{{ $value }}" class="peer sr-only"
                                   @checked(old('type', 'department') === $value)>
                            <span class="block rounded-xl border border-slate-200 px-3 py-2.5 text-center text-sm font-semibold text-slate-500 transition peer-checked:border-teal peer-checked:bg-teal-soft peer-checked:text-teal peer-focus-visible:ring-2 peer-focus-visible:ring-teal/40">
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>
                @if (! old('edit_id'))
                    @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                @endif
            </fieldset>

            <label for="add-name" class="mt-5 block text-sm font-semibold">Name</label>
            <input id="add-name" name="name" value="{{ old('edit_id') ? '' : old('name') }}" required maxlength="120" x-ref="addName"
                   x-effect="if (addOpen) $nextTick(() => $refs.addName.focus())"
                   class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20">
            @if (! old('edit_id'))
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            @endif

            <div class="mt-6 flex justify-end gap-2 text-sm font-semibold">
                <button type="button" @click="addOpen = false" class="rounded-xl px-4 py-2 text-slate-500 hover:bg-paper">Cancel</button>
                <button class="rounded-xl bg-teal px-5 py-2 text-white hover:bg-teal/90">Add</button>
            </div>
        </form>
    </div>

    {{-- Edit --}}
    <div x-show="editing.id" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-ink/60 p-4" @click.self="editing.id = null">
        <form method="POST" :action="updateUrl.replace('__ID__', editing.id)" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            @csrf
            @method('PUT')
            <input type="hidden" name="edit_id" :value="editing.id">

            <h3 class="text-lg font-bold" x-text="editing.type === 'group' ? 'Edit group' : 'Edit department'"></h3>
            <p class="mt-1 text-sm text-slate-500">Existing citations are updated to the new name automatically.</p>

            <label for="edit-name" class="mt-5 block text-sm font-semibold">Name</label>
            <input id="edit-name" name="name" x-model="editing.name" required maxlength="120" x-ref="editName"
                   x-effect="if (editing.id) $nextTick(() => $refs.editName.focus())"
                   class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20">
            @if (old('edit_id'))
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            @endif

            <div class="mt-6 flex justify-end gap-2 text-sm font-semibold">
                <button type="button" @click="editing.id = null" class="rounded-xl px-4 py-2 text-slate-500 hover:bg-paper">Cancel</button>
                <button class="rounded-xl bg-teal px-5 py-2 text-white hover:bg-teal/90">Save changes</button>
            </div>
        </form>
    </div>

    {{-- Delete --}}
    <div x-show="removing.id" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-ink/60 p-4" @click.self="removing.id = null">
        <form method="POST" :action="deleteUrl.replace('__ID__', removing.id)" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            @csrf
            @method('DELETE')

            <h3 class="text-lg font-bold" x-text="removing.type === 'group' ? 'Delete group?' : 'Delete department?'"></h3>
            <p class="mt-3 text-sm text-slate-700">
                <span class="font-semibold" x-text="removing.name"></span>
                will no longer appear on the homepage or submission forms.
            </p>
            <p class="mt-2 text-sm text-slate-500" x-show="removing.count > 0">
                <span x-text="removing.count"></span>
                existing citation(s) keep this name and stay visible on the dashboard.
            </p>

            <div class="mt-6 flex justify-end gap-2 text-sm font-semibold">
                <button type="button" @click="removing.id = null" class="rounded-xl px-4 py-2 text-slate-500 hover:bg-paper">Cancel</button>
                <button class="rounded-xl bg-red-600 px-5 py-2 text-white hover:bg-red-700">Delete</button>
            </div>
        </form>
    </div>

    <script>
        function manager() {
            return {
                tab: 'dept',
                q: '',
                addOpen: {{ ($errors->has('name') || $errors->has('type')) && ! old('edit_id') ? 'true' : 'false' }},
                editing: { id: null, name: '', type: 'department' },
                removing: { id: null, name: '', count: 0, type: 'department' },
                updateUrl: {{ Js::from(route('admin.departments.update', ['department' => '__ID__'])) }},
                deleteUrl: {{ Js::from(route('admin.departments.destroy', ['department' => '__ID__'])) }},
                init() {
                    @if (old('edit_id'))
                        this.editing = { id: {{ (int) old('edit_id') }}, name: {{ Js::from(old('name')) }}, type: 'department' };
                    @endif
                },
                match(row) {
                    return !this.q || row.innerText.toLowerCase().includes(this.q.toLowerCase());
                },
            };
        }
    </script>
</body>
</html>

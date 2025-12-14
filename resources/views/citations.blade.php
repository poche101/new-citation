<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CELZ5 Citation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
</head>

<body class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 font-['Inter'] min-h-screen p-6">
    <!-- Admin Navbar -->
    <nav
        class="bg-[rgba(255,255,255,0.06)] backdrop-blur-md border-b border-[rgba(255,255,255,0.12)] px-6 py-3 flex items-center justify-between">
        <!-- Left: Logo / Brand -->
        @php
            // Get the logged-in user
            $user = auth()->user();
            // Generate initials from full name
            $initials = collect(explode(' ', $user->name ?? $user->fullname))
                ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                ->take(2) // first 2 letters
                ->implode('');
        @endphp

        <div class="flex items-center space-x-3">
            <div class="h-8 w-8 rounded-full bg-indigo-400 flex items-center justify-center text-white font-semibold">
                {{ $initials }}
            </div>
            <span class="text-white font-semibold text-lg">{{ $user->name ?? $user->fullname }}</span>
        </div>


        <!-- Right: Actions -->
        <div class="flex items-center space-x-4">

            <!-- Logout -->
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit"
                    class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-pink-500 text-white rounded-lg hover:opacity-90 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 11-4 0v-1m0-8V7a2 2 0 114 0v1" />
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <br>
    <br>


    <div class="container mx-auto" x-data="citationDashboard()">

        <!-- Header -->
        <header class="mb-8 text-white">
            <h1 class="text-4xl font-bold mb-2 drop-shadow-lg">Citation Submission Dashboard</h1>
            <p class="text-white/80">Manage Departments, Groups & Citations</p>
        </header>

        <!-- Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

            <!-- Departments Card -->
            <div
                class="glass p-6 rounded-xl shadow-lg transform hover:-translate-y-2 hover:shadow-2xl transition-all duration-500 bg-white/20 backdrop-blur-md">
                <div class="flex items-center space-x-4">
                    <i data-feather="server" class="w-10 h-10 text-indigo-400"></i>
                    <div>
                        <h2 class="text-xl font-semibold text-white">Departments</h2>
                        <p class="text-gray-700 mt-1">
                            Total Departments: {{ $departmentsCount }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Citations Card -->
            <div
                class="glass p-6 rounded-xl shadow-lg transform hover:-translate-y-2 hover:shadow-2xl transition-all duration-500 bg-white/20 backdrop-blur-md">
                <div class="flex items-center space-x-4">
                    <i data-feather="file-text" class="w-10 h-10 text-yellow-400"></i>
                    <div>
                        <h2 class="text-xl font-semibold text-white">Citations</h2>
                        <p class="text-gray-700 mt-1">
                            Total Citations Submitted: {{ $citationsCount }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Groups Card -->
            <div
                class="glass p-6 rounded-xl shadow-lg transform hover:-translate-y-2 hover:shadow-2xl transition-all duration-500 bg-white/20 backdrop-blur-md">
                <div class="flex items-center space-x-4">
                    <i data-feather="users" class="w-10 h-10 text-green-400"></i>
                    <div>
                        <h2 class="text-xl font-semibold text-white">Groups</h2>
                        <p class="text-gray-700 mt-1">
                            Total Groups: {{ $groupsCount }}
                        </p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Initialize Feather icons -->
        <script>
            feather.replace();
        </script>

        <br>
        <br>

        <!-- Departments Table -->
        <h2 class="text-2xl text-white font-semibold my-6">Department Citations</h2>

        <!-- Search & Export -->
        <!-- ===================== SEARCH & EXPORT DEPARTMENT ===================== -->
        <div class="flex flex-col md:flex-row items-center justify-between mb-6 space-y-4 md:space-y-0">
            <!-- Department Search Bar -->
            <div class="w-full md:w-1/3 relative mb-4">
                <input type="text" id="departmentSearch" placeholder="Search Department Table..."
                    class="glass p-3 pl-10 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-300">
                <svg class="w-5 h-5 absolute left-3 top-3 text-gray-400 pointer-events-none" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Department Export Buttons -->
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('departments.exportExcel') }}"
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-500 transition-all">Excel</a>
                <a href="{{ route('departments.exportCSV') }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-500 transition-all">CSV</a>
                <a href="{{ route('departments.exportPDF') }}"
                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-500 transition-all">PDF</a>
                <a href="{{ route('departments.exportWord') }}"
                    class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-500 transition-all">Word</a>
            </div>
        </div>

        <!-- Department Table -->
        <div class="overflow-x-auto glass rounded-xl shadow-lg mb-10">
            <table class="min-w-full text-left border-collapse shadow-lg rounded-lg overflow-hidden"
                id="departmentTable">
                <thead
                    class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white text-sm tracking-wide">
                    <tr>
                        <th class="p-3">Title</th>
                        <th class="p-3">Full Name</th>
                        <th class="p-3">Unit</th>
                        <th class="p-3">Designation</th>
                        <th class="p-3">Kingschat Handle</th>
                        <th class="p-3">Phone</th>
                        <th class="p-3">Department</th>
                        <th class="p-3">Period</th>
                        <th class="p-3">Citation</th>
                        <th class="p-3">Created At</th>
                    </tr>
                </thead>
                <tbody class="bg-white/5 text-white/90 divide-y divide-white/20">
                    @forelse ($departmentCitations as $citation)
                        <tr class="hover:bg-white/10 transition-all">
                            <td class="p-3">{{ $citation->title ?? 'N/A' }}</td>
                            <td class="p-3">{{ $citation->fullname ?? 'N/A' }}</td>
                            <td class="p-3">{{ $citation->unit ?? 'N/A' }}</td>
                            <td class="p-3">{{ $citation->designation ?? 'N/A' }}</td>
                            <td class="p-3">{{ $citation->kingschat ?? 'N/A' }}</td>
                            <td class="p-3">{{ $citation->phone ?? 'N/A' }}</td>
                            <td class="p-3">{{ $citation->department ?? 'N/A' }}</td>
                            <td class="p-3">{{ $citation->period ?? 'N/A' }}</td>
                            <td class="p-3 flex items-center space-x-2">
                                <span>{{ Str::limit($citation->citation ?? 'N/A', 30, '...') }}</span>
                                @if (strlen($citation->citation ?? '') > 30)
                                    <button
                                        onclick="openDepartmentModal('{{ addslashes($citation->fullname ?? '') }}', '{{ addslashes($citation->citation ?? '') }}')"
                                        class="text-indigo-400 underline ml-2 hover:text-indigo-200 transition-all">
                                        View More
                                    </button>
                                @endif
                            </td>
                            <td class="p-3">{{ $citation->created_at?->format('d-m-Y H:i') ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="p-4 text-center text-white/80">No department citations found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Department Pagination --}}
            <div class="mt-6">
                {{ $departmentCitations->links() }}
            </div>
        </div>

        <!-- ===================== SEARCH & EXPORT GROUPS ===================== -->
        <div class="flex flex-col md:flex-row items-center justify-between mb-6 space-y-4 md:space-y-0">
            <!-- Group Search Bar -->
            <div class="w-full md:w-1/3 relative mb-4">
                <input type="text" id="groupSearch" placeholder="Search Group Table..."
                    class="glass p-3 pl-10 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-300">
                <svg class="w-5 h-5 absolute left-3 top-3 text-gray-400 pointer-events-none" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Group Export Buttons -->
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('groups.exportExcel') }}"
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-500 transition-all">Excel</a>
                <a href="{{ route('groups.exportCSV') }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-500 transition-all">CSV</a>
                <a href="{{ route('groups.exportPDF') }}"
                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-500 transition-all">PDF</a>
                <a href="{{ route('groups.exportWord') }}"
                    class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-500 transition-all">Word</a>
            </div>
        </div>

        <!-- Group Table -->
        <div class="overflow-x-auto glass rounded-xl shadow-lg mb-10">
            <table class="min-w-full text-left border-collapse shadow-lg rounded-lg overflow-hidden" id="groupTable">
                <thead
                    class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white text-sm tracking-wide">
                    <tr>
                        <th class="p-3">Title</th>
                        <th class="p-3">Full Name</th>
                        <th class="p-3">Unit</th>
                        <th class="p-3">Designation</th>
                        <th class="p-3">Kingschat Handle</th>
                        <th class="p-3">Phone</th>
                        <th class="p-3">Department</th>
                        <th class="p-3">Group Name</th>
                        <th class="p-3">Period</th>
                        <th class="p-3">Citation</th>
                        <th class="p-3">Created At</th>
                    </tr>
                </thead>
                <tbody class="bg-white/5 text-white/90 divide-y divide-white/20">
                    @forelse ($groupCitations as $citation)
                        <tr class="hover:bg-white/10 transition-all">
                            <td class="p-3">{{ $citation->title ?? 'N/A' }}</td>
                            <td class="p-3">{{ $citation->fullname ?? 'N/A' }}</td>
                            <td class="p-3">{{ $citation->unit ?? 'N/A' }}</td>
                            <td class="p-3">{{ $citation->designation ?? 'N/A' }}</td>
                            <td class="p-3">{{ $citation->kingschat ?? 'N/A' }}</td>
                            <td class="p-3">{{ $citation->phone ?? 'N/A' }}</td>
                            <td class="p-3">{{ $citation->department ?? 'N/A' }}</td>
                            <td class="p-3">{{ $citation->group_name ?? 'N/A' }}</td>
                            <td class="p-3">{{ $citation->period ?? 'N/A' }}</td>
                            <td class="p-3 flex items-center space-x-2">
                                <span>{{ Str::limit($citation->citation ?? 'N/A', 30, '...') }}</span>
                                @if (strlen($citation->citation ?? '') > 30)
                                    <button
                                        onclick="openGroupModal('{{ addslashes($citation->fullname ?? '') }}', '{{ addslashes($citation->citation ?? '') }}')"
                                        class="text-indigo-400 underline ml-2 hover:text-indigo-200 transition-all">
                                        View More
                                    </button>
                                @endif
                            </td>
                            <td class="p-3">{{ $citation->created_at?->format('d-m-Y H:i') ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="p-4 text-center text-white/80">No group citations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Group Pagination --}}
            <div class="mt-6">
                {{ $groupCitations->links() }}
            </div>
        </div>

        <!-- ===================== MODALS ===================== -->
        <!-- Department Modal -->
        <div id="departmentModal" class="fixed inset-0 hidden items-center justify-center z-50 bg-black/50">
            <div
                class="bg-white/10 backdrop-blur-md rounded-xl shadow-lg max-w-lg w-full p-6 relative overflow-y-auto max-h-[90vh]">
                <button onclick="closeDepartmentModal()"
                    class="absolute top-3 right-3 text-white hover:text-red-400 font-bold text-2xl">&times;</button>
                <h2 class="text-xl font-semibold text-white mb-4" id="departmentModalName"></h2>
                <p class="text-white/90 text-lg break-words" id="departmentModalCitation"></p>
            </div>
        </div>

        <!-- Group Modal -->
        <div id="groupModal" class="fixed inset-0 hidden items-center justify-center z-50 bg-black/50">
            <div
                class="bg-white/10 backdrop-blur-md rounded-xl shadow-lg max-w-lg w-full p-6 relative overflow-y-auto max-h-[90vh]">
                <button onclick="closeGroupModal()"
                    class="absolute top-3 right-3 text-white hover:text-red-400 font-bold text-2xl">&times;</button>
                <h2 class="text-xl font-semibold text-white mb-4" id="groupModalName"></h2>
                <p class="text-white/90 text-lg break-words" id="groupModalCitation"></p>
            </div>
        </div>

        <!-- ===================== SCRIPTS ===================== -->
        <script>
            // ------------------ Department Modal ------------------
            function openDepartmentModal(name, citation) {
                document.getElementById('departmentModalName').innerText = name;
                document.getElementById('departmentModalCitation').innerText = citation;
                document.getElementById('departmentModal').classList.remove('hidden');
                document.getElementById('departmentModal').classList.add('flex');
            }

            function closeDepartmentModal() {
                document.getElementById('departmentModal').classList.add('hidden');
                document.getElementById('departmentModal').classList.remove('flex');
            }

            // ------------------ Group Modal ------------------
            function openGroupModal(name, citation) {
                document.getElementById('groupModalName').innerText = name;
                document.getElementById('groupModalCitation').innerText = citation;
                document.getElementById('groupModal').classList.remove('hidden');
                document.getElementById('groupModal').classList.add('flex');
            }

            function closeGroupModal() {
                document.getElementById('groupModal').classList.add('hidden');
                document.getElementById('groupModal').classList.remove('flex');
            }

            // ------------------ Live Search ------------------
            document.getElementById('departmentSearch').addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                document.querySelectorAll('#departmentTable tbody tr').forEach(row => {
                    row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
                });
            });

            document.getElementById('groupSearch').addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                document.querySelectorAll('#groupTable tbody tr').forEach(row => {
                    row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
                });
            });
        </script>




</body>

</html>

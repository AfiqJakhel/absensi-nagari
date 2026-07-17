<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pegawai Dashboard') - Sistem Absensi Nagari</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AlpineJS for interactivity (Mobile Menu, Dropdowns) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @livewireStyles
</head>
<body class="bg-[#f8fafc] text-gray-800 font-sans antialiased" x-data="{ sidebarOpen: false }">

    <div class="flex h-screen overflow-hidden">

        <!-- Mobile sidebar backdrop -->
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-900/80 z-40 lg:hidden" @click="sidebarOpen = false" x-cloak></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="-translate-x-full fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-br from-[#f4ffe6] via-[#4adeac] to-[#00728a] text-teal-950 transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-auto lg:flex flex-col h-full border-r border-teal-200 shadow-xl overflow-hidden">
            
            <!-- Sidebar Background Image Overlay -->
            <div class="absolute bottom-0 left-0 right-0 h-48 bg-cover bg-bottom opacity-10 pointer-events-none" style="background-image: url('{{ asset('images/bg-login.png') }}'); mask-image: linear-gradient(to top, black, transparent); -webkit-mask-image: linear-gradient(to top, black, transparent);"></div>

            <!-- Logo Area -->
            <div class="flex flex-col items-center justify-center pt-6 pb-4 px-4 relative z-10">
                <div class="flex items-center gap-3 mb-3">
                    <img src="{{ asset('images/logo-pesisir.png') }}" alt="Logo Pesisir" class="h-10 w-auto mix-blend-multiply bg-transparent rounded">
                    <img src="{{ asset('images/logo-pantai.jpg') }}" alt="Logo Nagari" class="h-10 w-auto mix-blend-multiply bg-transparent rounded">
                </div>
                <h2 class="text-lg font-bold tracking-wide text-center text-teal-950">Sistem Absensi</h2>
                <p class="text-[10px] text-teal-800 mt-1 font-medium">Kantor Wali Nagari</p>
            </div>

            <!-- Navigation Links -->
            <div class="flex-1 overflow-y-auto py-2 px-3 relative z-10 scrollbar-hide">
                <nav class="space-y-1.5 text-sm">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-teal-700 text-white font-semibold shadow-md shadow-teal-900/30' : 'text-teal-900 hover:bg-white/30 hover:text-teal-950' }}">
                        <i class="fa-solid fa-house w-5 text-center"></i>
                        <span>Dashboard</span>
                    </a>

                    <!-- Absensi -->
                    <a href="{{ route('absensi') }}" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('absensi') ? 'bg-teal-700 text-white font-semibold shadow-md shadow-teal-900/30' : 'text-teal-900 hover:bg-white/30 hover:text-teal-950' }}">
                        <i class="fa-solid fa-camera w-5 text-center"></i>
                        <span>Absensi</span>
                    </a>

                    <!-- Riwayat Absensi -->
                    <a href="{{ route('riwayat') }}" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('riwayat') ? 'bg-teal-700 text-white font-semibold shadow-md shadow-teal-900/30' : 'text-teal-900 hover:bg-white/30 hover:text-teal-950' }}">
                        <i class="fa-solid fa-clock-rotate-left w-5 text-center"></i>
                        <span>Riwayat Absensi</span>
                    </a>
                </nav>
            </div>

            <!-- Logout Area -->
            <div class="p-3 relative z-10">
                <a href="{{ route('logout') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-red-600 hover:bg-red-500/10 hover:text-red-700 transition-all duration-200 w-full group text-sm font-medium">
                    <i class="fa-solid fa-arrow-right-from-bracket w-5 text-center group-hover:-translate-x-1 transition-transform"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative">
            
            <!-- Top Navbar -->
            <header class="bg-white sticky top-0 z-30 shadow-sm border-b border-gray-100">
                <div class="flex items-center justify-between px-6 py-3">
                    <div class="flex items-center gap-4">
                        <button @click="sidebarOpen = true" class="text-gray-500 hover:text-emerald-600 lg:hidden p-1.5 rounded-lg bg-gray-50 hover:bg-emerald-50 transition-colors">
                            <i class="fa-solid fa-bars text-lg"></i>
                        </button>
                        <h1 class="text-lg font-bold text-gray-800">@yield('page-title', 'Pegawai')</h1>
                    </div>

                    <div class="flex items-center gap-5">
                        <!-- Notification Bell -->
                        <button class="relative text-gray-500 hover:text-emerald-600 transition-colors flex items-center gap-1.5 font-medium text-sm">
                            <i class="fa-regular fa-bell text-lg"></i>
                            <span class="hidden md:inline">Notifikasi</span>
                            <span class="flex h-5 w-5 rounded-full bg-red-500 border border-white text-[10px] items-center justify-center text-white font-bold">2</span>
                        </button>

                        <!-- Profile Dropdown -->
                        <div class="relative" x-data="{ profileOpen: false }" @click.away="profileOpen = false">
                            <div @click="profileOpen = !profileOpen" class="flex items-center gap-3 border-l pl-5 border-gray-200 cursor-pointer group">
                                <div class="w-8 h-8 rounded-full bg-emerald-50 border border-transparent group-hover:border-emerald-500 transition-colors flex items-center justify-center overflow-hidden">
                                    <img src="{{ auth()->user()->profile_photo ? asset('storage/' . auth()->user()->profile_photo) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=ecfdf5&color=059669' }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                                </div>
                                <div class="hidden md:block text-right">
                                    <p class="text-sm font-bold text-gray-800 leading-tight">{{ auth()->user()->name }}</p>
                                    <p class="text-[10px] text-gray-500">{{ auth()->user()->division ? auth()->user()->division->name : 'Kantor Wali Nagari' }}</p>
                                </div>
                                <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 ml-1 transition-transform duration-200" :class="profileOpen ? 'rotate-180' : ''"></i>
                            </div>

                            <!-- Dropdown Menu -->
                            <div x-show="profileOpen" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-3 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50"
                                 style="display: none;">
                                
                                <div class="px-4 py-3 border-b border-gray-100 md:hidden">
                                    <p class="text-sm font-bold text-gray-800">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-gray-500">{{ auth()->user()->division ? auth()->user()->division->name : 'Kantor Wali Nagari' }}</p>
                                </div>

                                <a href="{{ route('logout') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors font-semibold">
                                    <i class="fa-solid fa-arrow-right-from-bracket w-4 text-center"></i> Keluar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Scrollable Wrapper -->
            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
                
                <!-- Footer -->
                <footer class="mt-12 text-center text-xs text-gray-400 pb-4">
                    <p>&copy; {{ date('Y') }} Kantor Wali Nagari. Semua hak dilindungi.</p>
                </footer>
            </main>

        </div>

    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>

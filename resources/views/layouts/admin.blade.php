<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - Sistem Absensi Nagari</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AlpineJS for interactivity (Mobile Menu, Dropdowns) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- ApexCharts for Graphs -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body class="bg-[#f4f7fb] text-gray-800 font-sans antialiased" x-data="{ sidebarOpen: false }">

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
                    <a href="{{ route('admin.dashboard') }}" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-teal-700 text-white font-semibold shadow-md shadow-teal-900/30' : 'text-teal-900 hover:bg-white/30 hover:text-teal-950' }}">
                        <i class="fa-solid fa-house w-5 text-center"></i>
                        <span>Dashboard</span>
                    </a>

                    <!-- Data Pegawai -->
                    <a href="{{ route('admin.pegawai') }}" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.pegawai') ? 'bg-teal-700 text-white font-semibold shadow-md shadow-teal-900/30' : 'text-teal-900 hover:bg-white/30 hover:text-teal-950' }}">
                        <i class="fa-solid fa-users w-5 text-center"></i>
                        <span>Data Pegawai</span>
                    </a>

                    <!-- Absensi -->
                    <a href="{{ route('admin.absensi') }}" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.absensi') ? 'bg-teal-700 text-white font-semibold shadow-md shadow-teal-900/30' : 'text-teal-900 hover:bg-white/30 hover:text-teal-950' }}">
                        <i class="fa-solid fa-clipboard-user w-5 text-center"></i>
                        <span>Absensi</span>
                    </a>

                    <!-- Riwayat Absensi -->
                    <a href="{{ route('admin.riwayat') }}" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.riwayat') ? 'bg-teal-700 text-white font-semibold shadow-md shadow-teal-900/30' : 'text-teal-900 hover:bg-white/30 hover:text-teal-950' }}">
                        <i class="fa-solid fa-clock-rotate-left w-5 text-center"></i>
                        <span>Riwayat Absensi</span>
                    </a>
                </nav>
            </div>

            <!-- Logout Area -->
            <div class="p-3 relative z-10">
                <a href="{{ route('login') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-red-600 hover:bg-red-500/10 hover:text-red-700 transition-all duration-200 w-full group text-sm font-medium">
                    <i class="fa-solid fa-arrow-right-from-bracket w-5 text-center group-hover:-translate-x-1 transition-transform"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative">
            
            <!-- Top Navbar -->
            <header class="bg-white/80 backdrop-blur-md sticky top-0 z-30 shadow-sm border-b border-gray-100">
                <div class="flex items-center justify-between px-6 py-3">
                    <div class="flex items-center gap-4">
                        <button @click="sidebarOpen = true" class="text-gray-500 hover:text-blue-600 lg:hidden p-1.5 rounded-lg bg-gray-50 hover:bg-blue-50 transition-colors">
                            <i class="fa-solid fa-bars text-lg"></i>
                        </button>
                        <h1 class="text-lg font-bold text-gray-800">@yield('page-title', 'Dashboard')</h1>
                    </div>

                    <div class="flex items-center gap-5">
                        <!-- Notification Bell -->
                        <button class="relative text-gray-500 hover:text-blue-600 transition-colors">
                            <i class="fa-regular fa-bell text-lg"></i>
                            <span class="absolute -top-1 -right-1 flex h-3.5 w-3.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-red-500 border-2 border-white text-[8px] items-center justify-center text-white font-bold">0</span>
                            </span>
                        </button>

                        <!-- Profile Dropdown -->
                        <div class="relative" x-data="{ profileOpen: false }" @click.away="profileOpen = false">
                            <div @click="profileOpen = !profileOpen" class="flex items-center gap-3 border-l pl-5 border-gray-200 cursor-pointer group">
                                <div class="w-8 h-8 rounded-full bg-blue-100 overflow-hidden border border-transparent group-hover:border-blue-500 transition-colors flex items-center justify-center">
                                    <img src="https://ui-avatars.com/api/?name=Admin&background=eff6ff&color=2563eb" alt="Admin" class="w-full h-full object-cover">
                                </div>
                                <div class="hidden md:block text-right">
                                    <p class="text-sm font-bold text-gray-800 leading-tight">Admin</p>
                                    <p class="text-[10px] text-gray-500">Kantor Wali Nagari</p>
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
                                    <p class="text-sm font-bold text-gray-800">Admin</p>
                                    <p class="text-xs text-gray-500">Kantor Wali Nagari</p>
                                </div>

                                <a href="{{ route('admin.akun') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors">
                                    <i class="fa-solid fa-user-gear w-4 text-center"></i> Pengaturan Akun
                                </a>
                                
                                <div class="border-t border-gray-100 my-1"></div>
                                
                                <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fa-solid fa-arrow-right-from-bracket w-4 text-center"></i> Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Scrollable Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f4f7fb]">
                <div class="w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 max-w-7xl">
                    @yield('content')
                </div>
                
                <!-- Footer -->
                <footer class="text-center py-4 text-xs text-gray-400 font-medium">
                    &copy; {{ date('Y') }} Pemerintah Nagari. Semua Hak Dilindungi.
                </footer>
            </main>
        </div>

    </div>

    @stack('scripts')
</body>
</html>

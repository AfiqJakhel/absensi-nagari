@extends('layouts.admin')
@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard')

@section('content')
<!-- Hero Section -->
<div class="relative bg-white rounded-[1.5rem] overflow-hidden shadow-sm mb-6 border border-gray-100 min-h-[160px]">
    <!-- Gradient background matching the design (light blue to white to image) -->
    <div class="absolute inset-0 bg-gradient-to-r from-[#eef2ff] via-white/80 to-transparent z-10"></div>
    
    <!-- Background Image (Rumah Gadang) on the right side -->
    <div class="absolute inset-y-0 right-0 w-full md:w-1/2 bg-cover bg-left z-0 opacity-20 md:opacity-100" style="background-image: url('{{ asset('images/bg-login.png') }}'); mask-image: linear-gradient(to right, transparent, black 40%); -webkit-mask-image: linear-gradient(to right, transparent, black 40%);"></div>

    <div class="relative z-20 p-6 md:p-8 w-full md:w-2/3 h-full flex flex-col justify-center">
        <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-2 flex items-center gap-2 flex-wrap">
            Selamat Datang, Admin! <span class="text-3xl animate-bounce inline-block" style="animation-duration: 2s;"> </span>
        </h2>
        <p class="text-gray-600 text-sm md:text-base max-w-sm md:max-w-none">Kelola data absensi dengan mudah dan akurat.</p>
    </div>
</div>

<!-- Stat Cards Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
    <!-- Total Pegawai -->
    <div class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4 mb-3">
            <div class="w-12 h-12 rounded-xl bg-blue-500 text-white flex items-center justify-center text-xl shadow-lg shadow-blue-500/30">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <p class="text-sm font-bold text-gray-900">Total Pegawai</p>
            </div>
        </div>
        <div>
            <h3 class="text-3xl font-black text-gray-900 mb-1">0 <span class="text-sm font-medium text-gray-400">Orang</span></h3>
            <p class="text-xs font-semibold text-gray-400 flex items-center gap-1">
                Belum ada data
            </p>
        </div>
    </div>

    <!-- Hadir Hari Ini -->
    <div class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col justify-between relative overflow-hidden hover:shadow-md transition-shadow">
        <!-- Progress bar at bottom -->
        <div class="absolute bottom-5 left-5 right-5 h-1.5 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full bg-emerald-500 rounded-full w-[0%]"></div>
        </div>
        
        <div class="flex items-center gap-4 mb-3">
            <div class="w-12 h-12 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-xl shadow-lg shadow-emerald-500/30">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div>
                <p class="text-sm font-bold text-gray-900">Hadir Hari Ini</p>
            </div>
        </div>
        <div class="mb-3">
            <h3 class="text-3xl font-black text-gray-900 mb-1">0 <span class="text-sm font-medium text-gray-400">Orang</span></h3>
            <p class="text-xs font-medium text-gray-500">0% dari total pegawai</p>
        </div>
    </div>

    <!-- Terlambat Hari Ini -->
    <div class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4 mb-3">
            <div class="w-12 h-12 rounded-xl bg-orange-500 text-white flex items-center justify-center text-xl shadow-lg shadow-orange-500/30">
                <i class="fa-solid fa-user-clock"></i>
            </div>
            <div>
                <p class="text-sm font-bold text-gray-900">Terlambat Hari Ini</p>
            </div>
        </div>
        <div>
            <h3 class="text-3xl font-black text-gray-900 mb-1">0 <span class="text-sm font-medium text-gray-400">Orang</span></h3>
            <p class="text-xs font-semibold text-gray-400 flex items-center gap-1">
                Belum ada data
            </p>
        </div>
    </div>

    <!-- Tidak Hadir Hari Ini -->
    <div class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4 mb-3">
            <div class="w-12 h-12 rounded-xl bg-red-500 text-white flex items-center justify-center text-xl shadow-lg shadow-red-500/30">
                <i class="fa-solid fa-user-xmark"></i>
            </div>
            <div>
                <p class="text-sm font-bold text-gray-900">Tidak Hadir Hari Ini</p>
            </div>
        </div>
        <div>
            <h3 class="text-3xl font-black text-gray-900 mb-1">0 <span class="text-sm font-medium text-gray-400">Orang</span></h3>
            <p class="text-xs font-semibold text-gray-400 flex items-center gap-1">
                Belum ada data
            </p>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Left Column (Chart & Table) -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Grafik Kehadiran -->
        <div class="bg-white rounded-[1.5rem] shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-gray-900">Grafik Kehadiran (7 Hari Terakhir)</h3>
                <select class="bg-gray-50 border border-gray-200 text-gray-700 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 block px-3 py-1.5 font-medium outline-none">
                    <option>Mingguan</option>
                    <option>Bulanan</option>
                </select>
            </div>
            <!-- Chart Container -->
            <div id="attendanceChart" class="w-full h-56"></div>
        </div>

        <!-- Rekap Absensi Hari Ini -->
        <div class="bg-white rounded-[1.5rem] shadow-sm border border-gray-100 p-5">
            <h3 class="text-base font-bold text-gray-900 mb-4">Rekap Absensi Hari Ini</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="text-xs text-gray-400 uppercase bg-gray-50/50 border-b border-gray-100">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-semibold rounded-tl-xl">Nama</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Jabatan</th>
                            <th scope="col" class="px-4 py-3 font-semibold text-center">Waktu Masuk</th>
                            <th scope="col" class="px-4 py-3 font-semibold text-center rounded-tr-xl">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <!-- Empty State -->
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-400">
                                <i class="fa-regular fa-folder-open text-3xl mb-2"></i>
                                <p>Belum ada data absensi hari ini.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column (List) -->
    <div class="space-y-6">
        
        <!-- Absensi Terakhir -->
        <div class="bg-white rounded-[1.5rem] shadow-sm border border-gray-100 p-5 h-full">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-gray-900">Absensi Terakhir <span class="text-gray-400 font-medium text-xs ml-1">(Hari Ini)</span></h3>
                <a href="#" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Lihat Semua <i class="fa-solid fa-chevron-right text-[8px] ml-1"></i></a>
            </div>

            <div class="flex flex-col items-center justify-center py-10 text-gray-400 text-center">
                <i class="fa-regular fa-clock text-4xl mb-3 text-gray-200"></i>
                <p class="text-sm">Belum ada pegawai yang melakukan absensi.</p>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    // Konfigurasi ApexCharts untuk Grafik Kehadiran
    document.addEventListener("DOMContentLoaded", function() {
        var options = {
            series: [{
                name: 'Hadir',
                data: [0, 0, 0, 0, 0, 0, 0]
            }],
            chart: {
                height: 240,
                type: 'area',
                fontFamily: 'inherit',
                toolbar: { show: false },
                zoom: { enabled: false }
            },
            colors: ['#3b82f6'],
            dataLabels: {
                enabled: true,
                offsetY: -5,
                style: {
                    fontSize: '11px',
                    fontWeight: 600,
                    colors: ['#1e40af']
                },
                background: { enabled: false }
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            xaxis: {
                categories: ['11 Jul', '12 Jul', '13 Jul', '14 Jul', '15 Jul', '16 Jul', '17 Jul'],
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: {
                        colors: '#9ca3af',
                        fontSize: '11px',
                        fontWeight: 500
                    }
                }
            },
            yaxis: {
                min: 0,
                max: 10,
                tickAmount: 2,
                labels: {
                    style: {
                        colors: '#9ca3af',
                        fontSize: '11px',
                        fontWeight: 500
                    },
                    formatter: function(val) { return Math.floor(val); }
                }
            },
            grid: {
                borderColor: '#f3f4f6',
                strokeDashArray: 4,
                yaxis: { lines: { show: true } }
            },
            markers: {
                size: 4,
                colors: ['#3b82f6'],
                strokeColors: '#fff',
                strokeWidth: 2,
                hover: { size: 6 }
            }
        };

        var chart = new ApexCharts(document.querySelector("#attendanceChart"), options);
        chart.render();
    });
</script>
@endpush

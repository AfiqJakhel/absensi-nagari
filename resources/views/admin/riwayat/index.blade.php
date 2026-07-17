@extends('layouts.admin')
@section('title', 'Riwayat Absensi')
@section('page-title', 'Riwayat Absensi')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center p-6 border-b border-gray-100 gap-4">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Riwayat Absensi - Kantor Wali Nagari</h2>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
            <!-- Calendar Picker (Replacing Pilih Periode) -->
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                    <i class="fa-regular fa-calendar text-sm"></i>
                </div>
                <select class="pl-10 pr-8 py-2.5 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none text-gray-700 w-full appearance-none font-medium">
                    <option>01 Juli 2026 - 17 Juli 2026</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                </div>
            </div>
            
            <button class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold flex items-center justify-center transition-colors whitespace-nowrap">
                Input Absen Manual
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600 whitespace-nowrap">
            <thead class="text-xs text-gray-500 uppercase bg-white border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4 font-semibold text-center w-16">NO</th>
                    <th class="px-6 py-4 font-semibold">NAMA PEGAWAI</th>
                    <th class="px-6 py-4 font-semibold text-center">JAM MASUK</th>
                    <th class="px-6 py-4 font-semibold text-center">JAM KELUAR</th>
                    <th class="px-6 py-4 font-semibold">KEGIATAN / JABATAN</th>
                    <th class="px-6 py-4 font-semibold text-center">VERIFIKASI</th>
                    <th class="px-6 py-4 font-semibold text-center">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <!-- Empty State -->
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center text-gray-400">
                        <i class="fa-regular fa-folder-open text-4xl mb-3 text-gray-300"></i>
                        <p class="text-sm font-medium text-gray-500">Belum ada riwayat absensi.</p>
                        <p class="text-xs mt-1 text-gray-400">Data riwayat akan terkumpul ketika pegawai melakukan absensi harian.</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="text-sm text-gray-500">
            Menampilkan <span class="font-semibold text-gray-900">0</span> dari <span class="font-semibold text-gray-900">0</span> data
        </div>
        <div class="flex items-center gap-1 opacity-50 pointer-events-none">
            <button class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 border border-transparent"><i class="fa-solid fa-chevron-left text-xs"></i></button>
            <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 text-gray-400 font-medium text-sm">1</button>
            <button class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 border border-transparent"><i class="fa-solid fa-chevron-right text-xs"></i></button>
        </div>
    </div>
</div>
@endsection

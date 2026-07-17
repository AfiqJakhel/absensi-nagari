@extends('layouts.admin')
@section('title', 'Data Pegawai')
@section('page-title', 'Data Pegawai')

@section('content')
<div x-data="{ isModalOpen: false }">
    <!-- Header & Actions -->
    <div class="flex flex-col lg:flex-row justify-between items-center gap-4 mb-6">
        <!-- Search & Filters -->
        <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-gray-400 text-sm"></i>
                </div>
                <input type="text" class="pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl w-full text-sm focus:ring-2 focus:ring-blue-500 outline-none text-gray-700" placeholder="Cari nama pegawai...">
            </div>
            <div class="relative w-full sm:w-48">
                <select class="w-full appearance-none bg-white border border-gray-200 rounded-xl pl-4 pr-10 py-2.5 text-sm text-gray-600 focus:ring-2 focus:ring-blue-500 outline-none">
                    <option>Semua Jabatan</option>
                    <option>Staf Umum</option>
                    <option>Staff Keuangan</option>
                    <option>Kasi Pemerintahan</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                </div>
            </div>
            <div class="relative w-full sm:w-40">
                <select class="w-full appearance-none bg-white border border-gray-200 rounded-xl pl-4 pr-10 py-2.5 text-sm text-gray-600 focus:ring-2 focus:ring-blue-500 outline-none">
                    <option>Semua Status</option>
                    <option>Aktif</option>
                    <option>Nonaktif</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                </div>
            </div>
        </div>
        
        <!-- Add Button -->
        <button @click="isModalOpen = true" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 transition-colors w-full lg:w-auto justify-center shadow-sm shadow-blue-600/20">
            <i class="fa-solid fa-plus"></i> Tambah Pegawai
        </button>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-[1.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 whitespace-nowrap">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50/50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 font-semibold text-center w-16">No</th>
                        <th class="px-6 py-4 font-semibold w-16">Foto</th>
                        <th class="px-6 py-4 font-semibold">Nama</th>
                        <th class="px-6 py-4 font-semibold">NIP</th>
                        <th class="px-6 py-4 font-semibold">Jabatan</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold">Email</th>
                        <th class="px-6 py-4 font-semibold">No. Telepon</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <!-- Empty State -->
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                            <i class="fa-regular fa-folder-open text-4xl mb-3 text-gray-300"></i>
                            <p class="text-sm font-medium">Belum ada data pegawai.</p>
                            <p class="text-xs mt-1 text-gray-400">Silakan klik tombol "Tambah Pegawai" untuk memasukkan data.</p>
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

    <!-- Modal Tambah Pegawai -->
    <div x-show="isModalOpen" 
         class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900/50 backdrop-blur-sm p-4 md:p-0"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">
        
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-auto"
             @click.away="isModalOpen = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-5 md:p-6 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-900">Tambah Pegawai Baru</h3>
                <button @click="isModalOpen = false" class="text-gray-400 bg-transparent hover:bg-gray-50 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex justify-center items-center transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-5 md:p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-900">Nama Lengkap</label>
                        <input type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 outline-none" placeholder="Masukkan nama lengkap">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-900">NIP</label>
                        <input type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 outline-none" placeholder="Masukkan NIP">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-900">Jabatan / Divisi</label>
                        <select class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 outline-none">
                            <option value="">Pilih Jabatan</option>
                            <option>Staf Umum</option>
                            <option>Staff Keuangan</option>
                            <option>Kasi Pemerintahan</option>
                            <option>Staff Pelayanan</option>
                            <option>Staf Kesejahteraan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-900">Email</label>
                        <input type="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 outline-none" placeholder="contoh@nagari.go.id">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-900">Nomor Telepon</label>
                        <input type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 outline-none" placeholder="08xx-xxxx-xxxx">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-900">Peran Akses</label>
                        <select class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 outline-none">
                            <option>Pegawai (User)</option>
                            <option>Admin</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end p-5 md:p-6 border-t border-gray-100 gap-3">
                <button @click="isModalOpen = false" type="button" class="text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 focus:ring-4 focus:outline-none focus:ring-gray-100 rounded-xl text-sm font-medium px-5 py-2.5 transition-colors">Batal</button>
                <button type="button" @click="isModalOpen = false" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-xl text-sm px-5 py-2.5 transition-colors shadow-sm shadow-blue-500/30">Simpan Data</button>
            </div>
        </div>
    </div>
</div>
@endsection

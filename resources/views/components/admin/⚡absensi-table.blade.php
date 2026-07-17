<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Attendance;
use App\Enums\AttendanceStatus;
use Illuminate\Support\Carbon;

new class extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'semua';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'semua'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function setTab($status)
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function with(): array
    {
        $query = Attendance::with(['user.division'])
            ->whereDate('attendance_date', Carbon::today());

        if (!empty($this->search)) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter === 'present') {
            $query->where('status', AttendanceStatus::PRESENT);
        } elseif ($this->statusFilter === 'late') {
            $query->where('status', AttendanceStatus::LATE);
        } elseif ($this->statusFilter === 'tidak-hadir') {
            // "Tidak Hadir" maps to absent (Alfa), permission (Izin), or sick (Sakit)
            $query->whereIn('status', [
                AttendanceStatus::ABSENT,
                AttendanceStatus::PERMISSION,
                AttendanceStatus::SICK
            ]);
        }

        return [
            'attendances' => $query->latest('check_in_at')->paginate(10),
        ];
    }
};
?>

<div wire:poll.60s>
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-1">Absensi Hari Ini</h2>
            <p class="text-sm text-gray-500">Kelola dan pantau absensi pegawai secara real-time.</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative w-full md:w-auto">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                    <i class="fa-regular fa-calendar text-sm"></i>
                </div>
                <select class="pl-10 pr-10 py-2.5 bg-white border border-gray-200 hover:border-gray-300 hover:bg-gray-50/50 rounded-xl text-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 shadow-sm transition-all duration-200 outline-none text-gray-800 w-full md:w-64 appearance-none font-semibold cursor-pointer">
                    <option>{{ \Illuminate\Support\Carbon::today()->translatedFormat('l, d F Y') }}</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-[1.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <!-- Tabs and Filters -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center p-5 border-b border-gray-100 gap-4">
            
            <!-- Tabs -->
            <div class="flex items-center gap-6 overflow-x-auto w-full lg:w-auto pb-2 lg:pb-0 hide-scrollbar pt-2">
                <button wire:click="setTab('semua')" class="text-sm font-semibold {{ $statusFilter === 'semua' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }} pb-3 px-1 whitespace-nowrap transition-colors">Semua</button>
                <button wire:click="setTab('present')" class="text-sm font-semibold {{ $statusFilter === 'present' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }} pb-3 px-1 whitespace-nowrap transition-colors">Hadir</button>
                <button wire:click="setTab('late')" class="text-sm font-semibold {{ $statusFilter === 'late' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }} pb-3 px-1 whitespace-nowrap transition-colors">Terlambat</button>
                <button wire:click="setTab('tidak-hadir')" class="text-sm font-semibold {{ $statusFilter === 'tidak-hadir' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }} pb-3 px-1 whitespace-nowrap transition-colors">Tidak Hadir</button>
            </div>

            <!-- Filters & Export -->
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-gray-400 text-sm"></i>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" class="pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl w-full text-sm focus:ring-2 focus:ring-blue-500 outline-none text-gray-700" placeholder="Cari nama pegawai...">
                </div>

                <button class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 transition-colors">
                    <i class="fa-solid fa-download text-gray-500"></i> Export
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 whitespace-nowrap">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50/50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 font-semibold text-center w-16">No</th>
                        <th class="px-6 py-4 font-semibold w-16">Foto</th>
                        <th class="px-6 py-4 font-semibold">Nama Pegawai</th>
                        <th class="px-6 py-4 font-semibold">Jabatan</th>
                        <th class="px-6 py-4 font-semibold text-center">Waktu Masuk</th>
                        <th class="px-6 py-4 font-semibold text-center">Status</th>
                        <th class="px-6 py-4 font-semibold">Lokasi</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($attendances as $index => $attendance)
                        <tr>
                            <td class="px-6 py-4 text-center text-sm text-gray-500">
                                {{ $attendances->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <img class="h-10 w-10 rounded-full object-cover" src="{{ $attendance->user->profile_photo ? asset('storage/' . $attendance->user->profile_photo) : 'https://ui-avatars.com/api/?name=' . urlencode($attendance->user->name) . '&background=eff6ff&color=2563eb' }}" alt="{{ $attendance->user->name }}">
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ $attendance->user->name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $attendance->user->division ? $attendance->user->division->name : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-center text-gray-500">
                                {{ $attendance->check_in_at ? $attendance->check_in_at->format('H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $badgeColor = match($attendance->status->value) {
                                        'present' => 'bg-green-50 text-green-700 border-green-200',
                                        'late' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'permission' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'sick' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'absent' => 'bg-red-50 text-red-700 border-red-200',
                                        default => 'bg-gray-50 text-gray-700 border-gray-200'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $badgeColor }}">
                                    {{ $attendance->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                @if ($attendance->check_in_latitude && $attendance->check_in_longitude)
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $attendance->check_in_latitude }},{{ $attendance->check_in_longitude }}" target="_blank" class="text-blue-600 hover:text-blue-800 flex items-center gap-1 font-medium">
                                        <i class="fa-solid fa-location-dot"></i> Lihat Peta
                                    </a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center text-sm font-medium">
                                <a href="#" class="text-blue-600 hover:text-blue-900 mr-3"><i class="fa-solid fa-eye" title="Detail"></i></a>
                                <a href="#" class="text-amber-600 hover:text-amber-900"><i class="fa-solid fa-pen-to-square" title="Edit"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center text-gray-400">
                                <i class="fa-regular fa-folder-open text-4xl mb-3 text-gray-300"></i>
                                <p class="text-sm font-medium text-gray-500">Belum ada data absensi hari ini.</p>
                                <p class="text-xs mt-1 text-gray-400">Data akan muncul secara otomatis ketika pegawai melakukan check-in.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-500">
                Menampilkan <span class="font-semibold text-gray-900">{{ $attendances->firstItem() ?? 0 }}</span> sampai <span class="font-semibold text-gray-900">{{ $attendances->lastItem() ?? 0 }}</span> dari <span class="font-semibold text-gray-900">{{ $attendances->total() }}</span> data
            </div>
            <div>
                {{ $attendances->links() }}
            </div>
        </div>
    </div>
</div>
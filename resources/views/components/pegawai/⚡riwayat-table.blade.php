<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Attendance;
use App\Enums\AttendanceStatus;
use Illuminate\Support\Carbon;

new class extends Component
{
    use WithPagination;

    public $monthFilter = '';
    public $statusFilter = '';

    protected $queryString = [
        'monthFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount()
    {
        if (empty($this->monthFilter)) {
            $this->monthFilter = now()->format('Y-m');
        }
    }

    public function updating($property)
    {
        if (in_array($property, ['monthFilter', 'statusFilter'])) {
            $this->resetPage();
        }
    }

    public function with(): array
    {
        $user = auth()->user();

        // Get list of last 12 months for filtering options
        $monthsList = [];
        for ($i = 0; $i < 12; $i++) {
            $date = now()->subMonths($i);
            $monthsList[$date->format('Y-m')] = $date->translatedFormat('F Y');
        }

        // Query history
        $query = $user->attendances()
            ->whereYear('attendance_date', Carbon::parse($this->monthFilter)->year)
            ->whereMonth('attendance_date', Carbon::parse($this->monthFilter)->month);

        if (!empty($this->statusFilter)) {
            if ($this->statusFilter === 'permission_sick') {
                $query->whereIn('status', [AttendanceStatus::PERMISSION, AttendanceStatus::SICK]);
            } else {
                $query->where('status', $this->statusFilter);
            }
        }

        return [
            'monthsList' => $monthsList,
            'history' => $query->latest('attendance_date')->paginate(10),
            'statusFilter' => $this->statusFilter,
        ];
    }
};
?>

<div class="space-y-6">
    <!-- Filters & Table Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        
        <!-- Filter Header -->
        <div class="p-6 border-b border-gray-100">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-gray-500 text-lg"></i>
                    <h3 class="text-base font-bold text-gray-900">Riwayat Absensi</h3>
                </div>

                <!-- Filters Row (Right) -->
                <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                    
                    <!-- Status Filter Dropdown -->
                    <div class="relative w-full sm:w-44">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-filter text-xs"></i>
                        </div>
                        <select wire:model.live="statusFilter" class="pl-9 pr-8 py-2.5 bg-white border border-gray-200 hover:border-gray-300 rounded-xl text-sm focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 shadow-sm transition-all duration-200 outline-none text-gray-700 w-full appearance-none font-semibold cursor-pointer">
                            <option value="">Semua Status</option>
                            <option value="present">Hadir</option>
                            <option value="late">Terlambat</option>
                            <option value="permission_sick">Izin / Sakit</option>
                            <option value="absent">Alfa (Tidak Hadir)</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>

                    <!-- Month Filter Dropdown -->
                    <div class="relative w-full sm:w-48">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-regular fa-calendar text-sm"></i>
                        </div>
                        <select wire:model.live="monthFilter" class="pl-10 pr-8 py-2.5 bg-white border border-gray-200 hover:border-gray-300 rounded-xl text-sm focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 shadow-sm transition-all duration-200 outline-none text-gray-700 w-full appearance-none font-semibold cursor-pointer">
                            @foreach ($monthsList as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>

                    <button class="px-4 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition-colors whitespace-nowrap w-full sm:w-auto">
                        <i class="fa-solid fa-download text-gray-500"></i> Export
                    </button>
                </div>

            </div>
        </div>

        <!-- Table View -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 whitespace-nowrap">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50/50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 font-semibold text-center">Foto</th>
                        <th class="px-6 py-4 font-semibold">Hari, Tanggal</th>
                        <th class="px-6 py-4 font-semibold text-center">Jam Masuk</th>
                        <th class="px-6 py-4 font-semibold text-center">Jam Pulang</th>
                        <th class="px-6 py-4 font-semibold text-center">Durasi Kerja</th>
                        <th class="px-6 py-4 font-semibold text-center">Keterlambatan</th>
                        <th class="px-6 py-4 font-semibold text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($history as $record)
                        <tr>
                            <td class="px-6 py-3 text-center">
                                <div class="w-10 h-10 rounded-full overflow-hidden border border-gray-100 shadow-sm mx-auto">
                                    <img src="{{ auth()->user()->profile_photo ? asset('storage/' . auth()->user()->profile_photo) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=eff6ff&color=2563eb' }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                {{ $record->attendance_date->translatedFormat('l, d F Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-center text-gray-600 font-medium">
                                {{ $record->check_in_at ? $record->check_in_at->format('H:i') . ' WIB' : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-center text-gray-600 font-medium">
                                {{ $record->check_out_at ? $record->check_out_at->format('H:i') . ' WIB' : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-center text-gray-500 font-medium">
                                @if ($record->check_in_at && $record->check_out_at)
                                    @php
                                        $checkIn = Carbon::parse($record->check_in_at);
                                        $checkOut = Carbon::parse($record->check_out_at);
                                        $hours = $checkIn->diffInHours($checkOut);
                                        $minutes = $checkIn->diffInMinutes($checkOut) % 60;
                                    @endphp
                                    {{ "{$hours} Jam {$minutes} Menit" }}
                                @elseif ($record->check_in_at)
                                    <span class="text-amber-600">Aktif</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-center text-gray-500">
                                {{ $record->late_minutes > 0 ? "{$record->late_minutes} Menit" : '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $badgeColor = match($record->status->value) {
                                        'present' => 'bg-green-50 text-green-700 border-green-200',
                                        'late' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'permission' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'sick' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'absent' => 'bg-red-50 text-red-700 border-red-200',
                                        default => 'bg-gray-50 text-gray-700 border-gray-200'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $badgeColor }}">
                                    {{ $record->status->label() }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-gray-400">
                                <i class="fa-regular fa-folder-open text-4xl mb-3 text-gray-300"></i>
                                <p class="text-sm font-medium text-gray-500">Belum ada riwayat absensi pada bulan/kategori ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($history->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Menampilkan <span class="font-semibold text-gray-900">{{ $history->firstItem() ?? 0 }}</span> sampai <span class="font-semibold text-gray-900">{{ $history->lastItem() ?? 0 }}</span> dari <span class="font-semibold text-gray-900">{{ $history->total() }}</span> data
                </div>
                <div>
                    {{ $history->links() }}
                </div>
            </div>
        @endif

    </div>
</div>

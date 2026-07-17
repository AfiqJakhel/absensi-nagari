<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Enums\AttendanceStatus;
use Illuminate\Support\Carbon;

new class extends Component
{
    use WithPagination;

    public $monthFilter = '';

    protected $queryString = [
        'monthFilter' => ['except' => ''],
    ];

    public function mount()
    {
        if (empty($this->monthFilter)) {
            $this->monthFilter = now()->format('Y-m');
        }
    }

    public function getGreeting()
    {
        $hour = now()->hour;
        if ($hour < 11) return 'Selamat pagi';
        if ($hour < 15) return 'Selamat siang';
        if ($hour < 19) return 'Selamat sore';
        return 'Selamat malam';
    }

    public function with(): array
    {
        $user = auth()->user();
        $today = Carbon::today()->toDateString();

        // 1. Get today's active schedule for the user
        $schedule = $user->schedules()
            ->whereDate('attendance_date', $today)
            ->where('is_active', true)
            ->first();

        // 2. Get today's attendance record
        $todayAttendance = $user->attendances()
            ->whereDate('attendance_date', $today)
            ->first();

        // Calculate Total Working Hours and Lateness
        $workingHours = '-';
        $lateness = '-';

        if ($todayAttendance) {
            if ($todayAttendance->check_in_at && $todayAttendance->check_out_at) {
                $checkIn = Carbon::parse($todayAttendance->check_in_at);
                $checkOut = Carbon::parse($todayAttendance->check_out_at);
                $hours = $checkIn->diffInHours($checkOut);
                $minutes = $checkIn->diffInMinutes($checkOut) % 60;
                $workingHours = "{$hours} Jam {$minutes} Menit";
            } elseif ($todayAttendance->check_in_at) {
                $workingHours = 'Sedang Bekerja';
            }

            if ($todayAttendance->late_minutes > 0) {
                $lateness = "{$todayAttendance->late_minutes} Menit";
            }
        }

        // Get list of last 6 months for filtering options
        $monthsList = [];
        for ($i = 0; $i < 6; $i++) {
            $date = now()->subMonths($i);
            $monthsList[$date->format('Y-m')] = $date->translatedFormat('F Y');
        }

        // 3. Query monthly history
        $historyQuery = $user->attendances()
            ->whereYear('attendance_date', Carbon::parse($this->monthFilter)->year)
            ->whereMonth('attendance_date', Carbon::parse($this->monthFilter)->month);

        return [
            'greeting' => $this->getGreeting(),
            'schedule' => $schedule,
            'workingHours' => $workingHours,
            'lateness' => $lateness,
            'monthsList' => $monthsList,
            'todayAttendance' => $todayAttendance,
            'history' => $historyQuery->latest('attendance_date')->paginate(10),
        ];
    }
};
?>

<div class="space-y-6">
    <!-- Hero Section / Greeting Card -->
    <div class="relative bg-white rounded-[1.5rem] overflow-hidden shadow-sm p-6 md:p-8 border border-gray-100 min-h-[160px] flex items-center">
        <!-- Gradient background matching the design -->
        <div class="absolute inset-0 bg-gradient-to-r from-[#eef2ff] via-white/95 to-transparent z-10"></div>
        
        <!-- Background Image (Rumah Gadang) on the right side -->
        <div class="absolute inset-y-0 right-0 w-full md:w-1/2 bg-cover bg-left z-0 opacity-20 md:opacity-100" style="background-image: url('{{ asset('images/bg-login.png') }}'); mask-image: linear-gradient(to right, transparent, black 40%); -webkit-mask-image: linear-gradient(to right, transparent, black 40%);"></div>

        <div class="relative z-20 w-full flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div>
                <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-2">
                    {{ $greeting }}, {{ auth()->user()->name }}!
                </h2>
                <p class="text-gray-600 text-sm md:text-base font-medium">Semangat bekerja hari ini!</p>
            </div>
            
            <div class="flex items-center gap-4 text-xs md:text-sm text-gray-700 font-semibold bg-white/80 backdrop-blur-md px-4 py-2.5 rounded-xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-2">
                    <i class="fa-regular fa-calendar text-emerald-600 text-base"></i>
                    <span>{{ \Illuminate\Support\Carbon::today()->translatedFormat('l, d F Y') }}</span>
                </div>
                <div class="h-4 w-px bg-gray-300"></div>
                <div class="flex items-center gap-2">
                    <i class="fa-regular fa-clock text-emerald-600 text-base"></i>
                    <span>{{ \Illuminate\Support\Carbon::now()->translatedFormat('H:i') }} WIB</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Information Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Jadwal Masuk -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4 transition-all hover:shadow-md duration-200">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                <i class="fa-regular fa-clock text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-0.5">Jadwal Masuk</p>
                <p class="text-base font-bold text-gray-800">
                    {{ $schedule ? \Illuminate\Support\Carbon::parse($schedule->start_time)->format('H:i') . ' WIB' : '-' }}
                </p>
            </div>
        </div>

        <!-- Jadwal Pulang -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4 transition-all hover:shadow-md duration-200">
            <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-500">
                <i class="fa-regular fa-clock text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-0.5">Jadwal Pulang</p>
                <p class="text-base font-bold text-gray-800">
                    {{ $schedule ? \Illuminate\Support\Carbon::parse($schedule->end_time)->format('H:i') . ' WIB' : '-' }}
                </p>
            </div>
        </div>

        <!-- Total Jam Kerja -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4 transition-all hover:shadow-md duration-200">
            <div class="w-12 h-12 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600">
                <i class="fa-solid fa-business-time text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-0.5">Total Jam Kerja</p>
                <p class="text-base font-bold text-gray-800">{{ $workingHours }}</p>
            </div>
        </div>

        <!-- Keterlambatan -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4 transition-all hover:shadow-md duration-200">
            <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600">
                <i class="fa-solid fa-circle-exclamation text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-0.5">Keterlambatan</p>
                <p class="text-base font-bold text-gray-800">{{ $lateness }}</p>
            </div>
        </div>
    </div>

    <!-- Attendance History Section -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <!-- Section Header / Filters -->
        <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-gray-500 text-lg"></i>
                <h3 class="text-base font-bold text-gray-900">Riwayat Absensi</h3>
            </div>
            
            <div class="flex items-center gap-3 w-full sm:w-auto">
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

                <button class="px-4 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition-colors whitespace-nowrap">
                    <i class="fa-solid fa-download text-gray-500"></i> Export
                </button>
            </div>
        </div>

        <!-- Table Area -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 whitespace-nowrap">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50/50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Tanggal</th>
                        <th class="px-6 py-4 font-semibold">Hari</th>
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
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                {{ $record->attendance_date->translatedFormat('d F Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $record->attendance_date->translatedFormat('l') }}
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
                                <p class="text-sm font-medium text-gray-500">Belum ada riwayat absensi pada bulan ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Area -->
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

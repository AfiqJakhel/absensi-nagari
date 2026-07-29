<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Division;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

new class extends Component
{
    use WithPagination;

    public $search = '';
    public $filterJabatan = '';
    public $filterStatus = '';

    public $isModalOpen = false;
    public $isEditMode = false;
    public $editId = null;

    // Form fields
    public $name = '';
    public $employee_number = '';
    public $division_id = '';
    public $email = '';
    public $phone = '';
    public $role = 'user';
    public $password = '';
    public $is_active = 1;

    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingFilterJabatan()
    {
        $this->resetPage();
    }
    
    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->isEditMode = false;
        $this->editId = null;
        $this->name = '';
        $this->employee_number = '';
        $this->division_id = '';
        $this->email = '';
        $this->phone = '';
        $this->role = 'user';
        $this->password = '';
        $this->is_active = 1;
        $this->resetErrorBag();
    }

    public function edit($id)
    {
        $this->resetForm();
        $user = User::findOrFail($id);
        $this->editId = $user->id;
        $this->name = $user->name;
        $this->employee_number = $user->employee_number;
        $this->division_id = $user->division_id;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->is_active = $user->is_active;
        $this->role = $user->hasRole('admin') ? 'admin' : 'user';
        
        $this->isEditMode = true;
        $this->isModalOpen = true;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'employee_number' => 'nullable|string|max:255',
            'division_id' => 'required|exists:divisions,id',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,user,operator',
            'is_active' => 'required|boolean',
        ];

        if ($this->isEditMode) {
            $rules['email'] = ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->editId)];
            $rules['password'] = 'nullable|string|min:8'; // Optional on edit
        } else {
            $rules['email'] = 'required|string|email|max:255|unique:users';
            $rules['password'] = 'required|string|min:8'; // Required on create
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'employee_number' => $this->employee_number,
            'division_id' => $this->division_id,
            'phone' => $this->phone,
            'is_active' => $this->is_active,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->isEditMode) {
            $user = User::findOrFail($this->editId);
            $user->update($data);
        } else {
            $user = User::create($data);
        }

        // Sync role
        $user->syncRoles([$this->role]);

        $this->closeModal();
    }

    public function delete($id)
    {
        // Don't allow deleting self
        if (auth()->id() == $id) {
            return;
        }
        User::findOrFail($id)->delete();
    }

    public function with()
    {
        $query = User::query()->with('division', 'roles');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('employee_number', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterJabatan !== '') {
            $query->where('division_id', $this->filterJabatan);
        }

        if ($this->filterStatus !== '') {
            $query->where('is_active', $this->filterStatus);
        }

        return [
            'users' => $query->latest()->paginate(10),
            'divisions' => Division::all(),
        ];
    }
};
?>

<div>
    <!-- Header & Actions -->
    <div class="flex flex-col lg:flex-row justify-between items-center gap-4 mb-6">
        <!-- Search & Filters -->
        <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-gray-400 text-sm"></i>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" class="pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl w-full text-sm focus:ring-2 focus:ring-blue-500 outline-none text-gray-700" placeholder="Cari nama pegawai...">
            </div>
            <div class="relative w-full sm:w-48">
                <select wire:model.live="filterJabatan" class="w-full appearance-none bg-white border border-gray-200 rounded-xl pl-4 pr-10 py-2.5 text-sm text-gray-600 focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Semua Jabatan</option>
                    @foreach($divisions as $div)
                        <option value="{{ $div->id }}">{{ $div->name }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                </div>
            </div>
            <div class="relative w-full sm:w-40">
                <select wire:model.live="filterStatus" class="w-full appearance-none bg-white border border-gray-200 rounded-xl pl-4 pr-10 py-2.5 text-sm text-gray-600 focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Semua Status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                </div>
            </div>
        </div>
        
        <!-- Add Button -->
        <button wire:click="openModal" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 transition-colors w-full lg:w-auto justify-center shadow-sm shadow-blue-600/20">
            <i class="fa-solid fa-plus"></i> Tambah Pegawai
        </button>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-[1.5rem] shadow-sm border border-gray-100 overflow-hidden relative">
        <!-- Loading Overlay -->
        <div wire:loading class="absolute inset-0 bg-white/50 backdrop-blur-sm z-10 flex items-center justify-center">
            <i class="fa-solid fa-spinner fa-spin text-blue-600 text-2xl"></i>
        </div>
        
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
                    @forelse($users as $index => $user)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-center">{{ $users->firstItem() + $index }}</td>
                            <td class="px-6 py-4">
                                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="px-6 py-4">{{ $user->employee_number ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $user->division ? $user->division->name : '-' }}</td>
                            <td class="px-6 py-4">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-xs font-medium bg-green-50 text-green-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-xs font-medium bg-red-50 text-red-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">{{ $user->email }}</td>
                            <td class="px-6 py-4">{{ $user->phone ?? '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button wire:click="edit({{ $user->id }})" class="w-8 h-8 rounded-lg text-blue-600 hover:bg-blue-50 flex items-center justify-center transition-colors" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    @if(auth()->id() !== $user->id)
                                    <button wire:click="delete({{ $user->id }})" wire:confirm="Apakah Anda yakin ingin menghapus pegawai ini?" class="w-8 h-8 rounded-lg text-red-600 hover:bg-red-50 flex items-center justify-center transition-colors" title="Hapus">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                                <i class="fa-regular fa-folder-open text-4xl mb-3 text-gray-300"></i>
                                <p class="text-sm font-medium">Belum ada data pegawai.</p>
                                <p class="text-xs mt-1 text-gray-400">Silakan klik tombol "Tambah Pegawai" untuk memasukkan data.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Modal Tambah/Edit Pegawai -->
    @if($isModalOpen)
    <div class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900/50 backdrop-blur-sm p-4 md:p-0">
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-auto my-8">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-5 md:p-6 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-900">{{ $isEditMode ? 'Edit Pegawai' : 'Tambah Pegawai Baru' }}</h3>
                <button wire:click="closeModal" class="text-gray-400 bg-transparent hover:bg-gray-50 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex justify-center items-center transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <form wire:submit="save">
                <div class="p-5 md:p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-900">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input wire:model="name" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 outline-none" placeholder="Masukkan nama lengkap">
                            @error('name') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-900">NIP</label>
                            <input wire:model="employee_number" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 outline-none" placeholder="Masukkan NIP">
                            @error('employee_number') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-900">Jabatan / Divisi <span class="text-red-500">*</span></label>
                            <select wire:model="division_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 outline-none">
                                <option value="">Pilih Jabatan</option>
                                @foreach($divisions as $div)
                                    <option value="{{ $div->id }}">{{ $div->name }}</option>
                                @endforeach
                            </select>
                            @error('division_id') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-900">Email <span class="text-red-500">*</span></label>
                            <input wire:model="email" type="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 outline-none" placeholder="contoh@nagari.go.id">
                            @error('email') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-900">Nomor Telepon</label>
                            <input wire:model="phone" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 outline-none" placeholder="08xx-xxxx-xxxx">
                            @error('phone') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-900">Peran Akses <span class="text-red-500">*</span></label>
                            <select wire:model="role" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 outline-none">
                                <option value="user">Pegawai (User)</option>
                                <option value="admin">Admin</option>
                            </select>
                            @error('role') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-900">
                                Password 
                                @if(!$isEditMode) <span class="text-red-500">*</span> @endif
                            </label>
                            <input wire:model="password" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 outline-none" placeholder="{{ $isEditMode ? 'Kosongkan jika tidak diubah' : 'Masukkan password' }}">
                            @error('password') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-900">Status Akun <span class="text-red-500">*</span></label>
                            <select wire:model="is_active" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 outline-none">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                            @error('is_active') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end p-5 md:p-6 border-t border-gray-100 gap-3">
                    <button wire:click="closeModal" type="button" class="text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 focus:ring-4 focus:outline-none focus:ring-gray-100 rounded-xl text-sm font-medium px-5 py-2.5 transition-colors">Batal</button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-xl text-sm px-5 py-2.5 transition-colors shadow-sm shadow-blue-500/30">
                        <span wire:loading.remove wire:target="save">Simpan Data</span>
                        <span wire:loading wire:target="save"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
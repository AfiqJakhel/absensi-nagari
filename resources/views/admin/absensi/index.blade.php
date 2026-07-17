@extends('layouts.admin')
@section('title', 'Absensi Hari Ini')
@section('page-title', 'Absensi')

@section('content')
    <livewire:admin.absensi-table />
@endsection

@push('scripts')
<style>
/* Hide scrollbar for tabs */
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
@endpush

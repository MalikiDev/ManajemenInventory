@extends('layouts.app')

@section('page-title', 'Data Penjualan')
@section('page-subtitle', 'Kelola data penjualan toko Anda')

@section('content')
@if(session('success'))
<div class="mb-6 bg-green-50 border-2 border-green-500 text-green-700 px-4 py-3 rounded-lg flex items-center justify-between">
    <div class="flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
@endif

@if(session('error'))
<div class="mb-6 bg-red-50 border-2 border-red-500 text-red-700 px-4 py-3 rounded-lg flex items-center justify-between">
    <div class="flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
@endif

<!-- Filter Section -->
<div class="bg-white rounded-xl border-2 border-bone-300 p-6 mb-6">
    <form method="GET" action="{{ route('sales.index') }}" class="space-y-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-black">Filter Data Penjualan</h3>
            @if(request()->hasAny(['start_date', 'end_date', 'date']))
            <a href="{{ route('sales.index') }}" class="text-sm text-gray-600 hover:text-black font-medium">
                Reset Filter
            </a>
            @endif
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Single Date Filter -->
            <div>
                <label for="date" class="block text-sm font-semibold text-black mb-2">Tanggal Spesifik</label>
                <input type="date" id="date" name="date" value="{{ request('date') }}"
                    class="input-field">
                <p class="text-xs text-gray-500 mt-1">Lihat data di tanggal tertentu</p>
            </div>

            <!-- Date Range Filter -->
            <div>
                <label for="start_date" class="block text-sm font-semibold text-black mb-2">Dari Tanggal</label>
                <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}"
                    class="input-field">
            </div>

            <div>
                <label for="end_date" class="block text-sm font-semibold text-black mb-2">Sampai Tanggal</label>
                <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}"
                    class="input-field">
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3">
            <button type="submit" class="btn-primary flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Terapkan Filter
            </button>
        </div>
    </form>
</div>

<!-- Summary Cards -->
@if(request()->hasAny(['start_date', 'end_date', 'date']))
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl border-2 border-bone-300 p-6">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-medium text-gray-600">Total Transaksi</h3>
            <div class="p-2 bg-blue-100 rounded-lg">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-black">{{ number_format($totalTransactions) }}</p>
    </div>

    <div class="bg-white rounded-xl border-2 border-bone-300 p-6">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-medium text-gray-600">Total Unit Terjual</h3>
            <div class="p-2 bg-green-100 rounded-lg">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-black">{{ number_format($totalJumlah) }}</p>
    </div>

    <div class="bg-white rounded-xl border-2 border-bone-300 p-6">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-medium text-gray-600">Periode</h3>
            <div class="p-2 bg-purple-100 rounded-lg">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
        <p class="text-sm font-semibold text-black">
            @if(request('date'))
                {{ \Carbon\Carbon::parse(request('date'))->format('d M Y') }}
            @else
                {{ request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->format('d M Y') : 'Awal' }}
                -
                {{ request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->format('d M Y') : 'Akhir' }}
            @endif
        </p>
    </div>
</div>
@endif

<!-- Header Actions -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h3 class="text-lg font-semibold text-black">
            @if(request()->hasAny(['start_date', 'end_date', 'date']))
                Hasil Filter
            @else
                Semua Data Penjualan
            @endif
        </h3>
        <p class="text-sm text-gray-500">{{ $sales->total() }} data ditemukan</p>
    </div>
    
    <div class="flex items-center space-x-3">
        <!-- Export Button -->
        <a href="{{ route('sales.export', request()->only(['start_date', 'end_date', 'date'])) }}" 
           class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export Excel
        </a>

        <!-- Import Button -->
        <button onclick="document.getElementById('importModal').classList.remove('hidden')" 
                class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            Import Excel
        </button>

        @can('create-sales')
        <a href="{{ route('sales.create') }}" class="btn-primary flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Data Penjualan
        </a>
        @endcan
    </div>
</div>

<!-- Sales Table -->
<div class="bg-white rounded-xl border-2 border-bone-300 overflow-hidden">
    <table class="w-full">
        <thead class="bg-bone-100 border-b-2 border-bone-300">
            <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">No</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Produk</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Tanggal</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Jumlah</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-bone-200">
            @forelse($sales as $index => $sale)
            <tr class="hover:bg-bone-50 transition-colors">
                <td class="px-6 py-4">
                    <span class="text-sm text-gray-700">{{ $sales->firstItem() + $index }}</span>
                </td>
                <td class="px-6 py-4">
                    <span class="text-sm font-semibold text-black">{{ $sale->product->name ?? 'N/A' }}</span>
                </td>
                <td class="px-6 py-4">
                    <span class="text-sm text-gray-700">{{ $sale->sale_date->format('d M Y') }}</span>
                </td>
                <td class="px-6 py-4">
                    <span class="text-sm font-semibold text-black">{{ number_format($sale->jumlah) }} unit</span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('sales.show', $sale) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Lihat">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                        @can('edit-sales')
                        <a href="{{ route('sales.edit', $sale) }}" class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        @endcan
                        @can('delete-sales')
                        <form action="{{ route('sales.destroy', $sale) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center">
                        <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="text-gray-500 font-medium">
                            @if(request()->hasAny(['start_date', 'end_date', 'date']))
                                Tidak ada data penjualan pada periode ini
                            @else
                                Belum ada data penjualan
                            @endif
                        </p>
                        <p class="text-sm text-gray-400 mt-1">
                            @if(request()->hasAny(['start_date', 'end_date', 'date']))
                                Coba ubah filter tanggal
                            @else
                                Tambahkan data penjualan pertama Anda
                            @endif
                        </p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if($sales->hasPages())
<div class="mt-6">
    {{ $sales->links() }}
</div>
@endif

<!-- Import Modal -->
<div id="importModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl border-2 border-bone-300 p-6 max-w-md w-full mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-black">Import Data Penjualan</h3>
            <button onclick="document.getElementById('importModal').classList.add('hidden')" class="text-gray-500 hover:text-black">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form action="{{ route('sales.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-4">
                <label class="block text-sm font-semibold text-black mb-2">File Excel</label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="text-xs text-gray-500 mt-2">Format: .xlsx, .xls, atau .csv (max 2MB)</p>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                <p class="text-xs text-gray-700 mb-2"><strong>Format Excel yang diperlukan:</strong></p>
                <ul class="text-xs text-gray-600 space-y-1 ml-4 list-disc">
                    <li><strong>product_id</strong> atau <strong>nama_produk</strong>: ID atau nama produk</li>
                    <li><strong>tanggal_penjualan</strong>: Format YYYY-MM-DD (contoh: 2026-01-15)</li>
                    <li><strong>jumlah</strong>: Jumlah unit terjual (angka)</li>
                </ul>
                <div class="mt-3 pt-2 border-t border-blue-300">
                    <p class="text-xs text-gray-700 mb-1"><strong>Produk yang tersedia:</strong></p>
                    <div class="max-h-32 overflow-y-auto bg-white rounded p-2 text-xs">
                        @foreach(\App\Models\Product::orderBy('name')->get() as $prod)
                        <div class="py-1">
                            <span class="font-mono text-gray-500">ID: {{ $prod->id }}</span> - 
                            <span class="font-semibold text-gray-700">{{ $prod->name }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                <a href="{{ route('sales.template') }}" class="text-xs text-blue-600 hover:underline mt-2 inline-block">
                    📥 Download Template Excel
                </a>
            </div>

            <div class="flex items-center justify-end space-x-3">
                <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')"
                    class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    Upload & Import
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

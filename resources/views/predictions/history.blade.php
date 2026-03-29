@extends('layouts.app')

@section('page-title', 'History Prediksi')
@section('page-subtitle', 'Riwayat prediksi penjualan yang pernah dilakukan')

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

<div class="flex items-center justify-between mb-6">
    <div>
        <h3 class="text-lg font-semibold text-black">Riwayat Prediksi</h3>
        <p class="text-sm text-gray-500">{{ $predictions->total() }} prediksi ditemukan</p>
    </div>
    
    <a href="{{ route('predictions.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Buat Prediksi Baru
    </a>
</div>

<div class="bg-white rounded-xl border-2 border-bone-300 overflow-hidden">
    <table class="w-full">
        <thead class="bg-bone-100 border-b-2 border-bone-300">
            <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">No</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Produk</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Tahun</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Horizon</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">R²</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">RMSE</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Dibuat</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-bone-200">
            @forelse($predictions as $index => $prediction)
            <tr class="hover:bg-bone-50 transition-colors">
                <td class="px-6 py-4">
                    <span class="text-sm text-gray-700">{{ $predictions->firstItem() + $index }}</span>
                </td>
                <td class="px-6 py-4">
                    <span class="text-sm font-semibold text-black">{{ $prediction->product->name ?? 'N/A' }}</span>
                </td>
                <td class="px-6 py-4">
                    <span class="text-sm text-gray-700">{{ $prediction->start_year }}</span>
                </td>
                <td class="px-6 py-4">
                    <span class="text-sm text-gray-700">{{ $prediction->horizon }} bulan</span>
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                        {{ $prediction->r2 >= 0.8 ? 'bg-green-100 text-green-800' : ($prediction->r2 >= 0.5 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ number_format($prediction->r2, 4) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <span class="text-sm text-gray-700">{{ number_format($prediction->rmse, 2) }}</span>
                </td>
                <td class="px-6 py-4">
                    <span class="text-sm text-gray-700">{{ $prediction->created_at->format('d M Y H:i') }}</span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center space-x-2">
                        <button onclick="viewPrediction({{ $prediction->id }})" 
                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" 
                                title="Lihat Detail">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                        <form action="{{ route('predictions.destroy', $prediction->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus prediksi ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center">
                        <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <p class="text-gray-500 font-medium">Belum ada prediksi</p>
                        <p class="text-sm text-gray-400 mt-1">Buat prediksi pertama Anda</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($predictions->hasPages())
<div class="mt-6">
    {{ $predictions->links() }}
</div>
@endif

<!-- Modal Detail Prediksi -->
<div id="detailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
    <div class="bg-white rounded-xl border-2 border-bone-300 p-6 max-w-4xl w-full mx-4 my-8 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-black">Detail Prediksi</h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-black">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div id="modalContent">
            <div class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-gray-600">Memuat data...</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let detailChart = null;

function closeModal() {
    document.getElementById('detailModal').classList.add('hidden');
    if (detailChart) {
        detailChart.destroy();
        detailChart = null;
    }
}

async function viewPrediction(id) {
    const modal = document.getElementById('detailModal');
    const content = document.getElementById('modalContent');
    
    modal.classList.remove('hidden');
    content.innerHTML = `
        <div class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-gray-600">Memuat data...</p>
        </div>
    `;
    
    try {
        const response = await fetch(`/predictions/show/${id}`);
        const data = await response.json();
        
        renderDetailContent(data);
    } catch (error) {
        content.innerHTML = `
            <div class="text-center py-8 text-red-600">
                <p>Gagal memuat data: ${error.message}</p>
            </div>
        `;
    }
}

function formatMonthLabel(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    if (isNaN(d)) return dateStr;
    return new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' }).format(d);
}

function renderDetailContent(data) {
    const content = document.getElementById('modalContent');
    
    const predictedValues = data.predicted_values || [];
    const labels = predictedValues.map(v => formatMonthLabel(v.date));
    const values = predictedValues.map(v => v.predicted);
    
    content.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600">Produk</p>
                <p class="text-lg font-bold text-blue-700">${data.product.name}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600">Tahun Prediksi</p>
                <p class="text-lg font-bold text-green-700">${data.start_year}</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600">R² (Akurasi)</p>
                <p class="text-lg font-bold text-purple-700">${Number(data.r2).toFixed(4)}</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600">RMSE</p>
                <p class="text-lg font-bold text-yellow-700">${Number(data.rmse).toFixed(2)}</p>
            </div>
        </div>
        
        <div class="mb-6">
            <h4 class="font-semibold mb-3">Grafik Prediksi</h4>
            <canvas id="detailChart" height="100"></canvas>
        </div>
        
        <div>
            <h4 class="font-semibold mb-3">Tabel Prediksi</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 border text-left text-xs font-semibold text-gray-700">Bulan</th>
                            <th class="px-4 py-2 border text-left text-xs font-semibold text-gray-700">Prediksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${predictedValues.map(v => `
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border text-sm">${formatMonthLabel(v.date)}</td>
                                <td class="px-4 py-2 border text-sm font-semibold">${v.predicted}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-4 text-xs text-gray-500">
            <p>Dibuat pada: ${data.created_at}</p>
            <p class="mt-1">Formula: y = ${Number(data.intercept).toFixed(2)} + ${Number(data.slope).toFixed(2)}x</p>
        </div>
    `;
    
    // Render chart
    const ctx = document.getElementById('detailChart').getContext('2d');
    if (detailChart) detailChart.destroy();
    
    detailChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Prediksi',
                data: values,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.2,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Jumlah' }
                },
                x: {
                    title: { display: true, text: 'Bulan' }
                }
            },
            plugins: {
                legend: { display: true }
            }
        }
    });
}
</script>
@endsection

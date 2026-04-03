@extends('layouts.app')

@section('page-title', 'Prediksi Penjualan')
@section('page-subtitle', 'Analisis dan forecast penjualan produk')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <span class="inline-flex items-center px-4 py-2 bg-green-100 border-2 border-green-500 text-green-700 rounded-lg text-sm font-semibold">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
        Regresi Linier
    </span>

    <a href="{{ route('predictions.history') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Lihat History
    </a>
</div>

<div class="mb-4 flex flex-wrap items-end gap-3">
    <div class="w-full md:w-1/3">
        <label for="product-select" class="block text-sm font-medium text-gray-700">Pilih Produk</label>
        <select id="product-select" class="mt-1 block w-full border rounded p-2">
            <option value="">-- Pilih Produk --</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}">{{ $product->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="w-full md:w-1/6">
        <label for="year-select" class="block text-sm font-medium text-gray-700">Tahun (label)</label>
        <select id="year-select" class="mt-1 block w-full border rounded p-2"></select>
    </div>

    <div class="w-full md:w-1/6">
        <label for="horizon-select" class="block text-sm font-medium text-gray-700">Periode Prediksi</label>
        <select id="horizon-select" class="mt-1 block w-full border rounded p-2">
            <option value="3">3 Bulan</option>
            <option value="6">6 Bulan</option>
            <option value="9">9 Bulan</option>
            <option value="12" selected>12 Bulan</option>
        </select>
    </div>

    <div class="flex items-center w-full md:w-auto mt-2 md:mt-0">
        <button id="btn-predict" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700 w-full justify-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m4-4H8"/></svg>
            Prediksi
        </button>
    </div>

    <div class="ml-auto text-sm text-gray-600 hidden md:block">
        <p>Catatan: Pilih produk dan periode, lalu klik <strong>Prediksi</strong>.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white p-4 rounded shadow">
        <h3 class="font-semibold mb-2">Grafik Actual vs Prediksi</h3>
        <canvas id="predictionChart" height="220"></canvas>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <h3 class="font-semibold mb-4 text-lg text-gray-800 border-b pb-2">Ringkasan Model Regresi</h3>
        <div id="model-summary" class="space-y-3">
            <div class="bg-blue-50 p-3 rounded-lg">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">Produk</span>
                    <span id="m-product" class="text-sm font-bold text-blue-700">-</span>
                </div>
            </div>

            <div class="bg-gray-50 p-3 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm font-medium text-gray-700">Observasi (n)</span>
                        <p class="text-xs text-gray-500 mt-0.5">Jumlah data historis</p>
                    </div>
                    <span id="m-n" class="text-sm font-bold text-gray-800">-</span>
                </div>
            </div>

            <div class="bg-green-50 p-3 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm font-medium text-gray-700">Intercept (b₀)</span>
                        <p class="text-xs text-gray-500 mt-0.5">Nilai awal prediksi</p>
                    </div>
                    <span id="m-intercept" class="text-sm font-bold text-green-700">-</span>
                </div>
            </div>

            <div class="bg-purple-50 p-3 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm font-medium text-gray-700">Slope (b₁)</span>
                        <p class="text-xs text-gray-500 mt-0.5">Tingkat pertumbuhan per periode</p>
                    </div>
                    <span id="m-slope" class="text-sm font-bold text-purple-700">-</span>
                </div>
            </div>

            <div class="bg-yellow-50 p-3 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm font-medium text-gray-700">R² (Koefisien Determinasi)</span>
                        <p class="text-xs text-gray-500 mt-0.5">Akurasi model (0-1)</p>
                    </div>
                    <span id="m-r2" class="text-sm font-bold text-yellow-700">-</span>
                </div>
            </div>

            <div class="bg-red-50 p-3 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm font-medium text-gray-700">RMSE</span>
                        <p class="text-xs text-gray-500 mt-0.5">Rata-rata error prediksi</p>
                    </div>
                    <span id="m-rmse" class="text-sm font-bold text-red-700">-</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-6 bg-white p-4 rounded shadow overflow-x-auto">
    <h3 class="font-semibold mb-2">Tabel Actual & Prediksi</h3>
    <table class="min-w-full border-collapse" id="pred-table">
        <thead>
            <tr>
                <th class="px-3 py-2 border bg-gray-50">Bulan</th>
                <th class="px-3 py-2 border bg-gray-50">Actual</th>
                <th class="px-3 py-2 border bg-gray-50">Persentase (%)</th>
                <th class="px-3 py-2 border bg-gray-50">Prediksi</th>
                <th class="px-3 py-2 border bg-gray-50">Keterangan</th>
            </tr>
        </thead>
        <tbody id="pred-table-body">
            <tr><td colspan="5" class="text-center py-4 text-gray-500">Pilih produk dan periode untuk melihat data</td></tr>
        </tbody>
    </table>
</div>

<div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
    <h3 class="font-semibold text-blue-900 mb-3">Tentang Prediksi Penjualan</h3>
    <div class="mt-4 pt-3 border-t border-blue-200">
        <p><strong>Prediksi (<span id="text-dynamic-horizon" class="font-bold text-blue-700"></span> Bulan):</strong></p>
    <div class="mt-1">
    Prediksi penjualan ini digunakan untuk <strong>menentukan kebutuhan bahan baku pembuatan produk di masa mendatang</strong>.
    Dengan mengetahui estimasi penjualan <span id="text-dynamic-horizon-2" class="font-bold text-blue-700"></span> bulan ke depan berdasarkan tren riwayat penjualan <span id="text-dynamic-horizon-3" class="font-bold text-blue-700"></span> bulan sebelumnya sebagai berikut:

    <ul id="dynamic-prediction-list" class="list-disc ml-6 my-3 space-y-1 text-gray-800 bg-white p-4 rounded-lg border border-blue-100 hidden"></ul>

    manajemen dapat merencanakan pengadaan bahan baku secara optimal, menghindari kekurangan stok, dan meminimalkan biaya penyimpanan yang berlebihan.
</div>
    </div>
    <div class="mt-3 pt-3 border-t border-blue-200">
        <p class="text-xs text-gray-500">
            <strong>Catatan:</strong> Persentase dihitung berdasarkan perubahan dari bulan sebelumnya.
            Keterangan menunjukkan tren naik/turun untuk membantu pengambilan keputusan.
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('product-select');
    const yearSelect = document.getElementById('year-select');
    const horizonSelect = document.getElementById('horizon-select');
    const btnPredict = document.getElementById('btn-predict');
    let chart = null;

    (function populateYearOptions() {
        const opt = document.createElement('option');
        opt.value = '2026';
        opt.textContent = '2026';
        opt.selected = true;
        yearSelect.appendChild(opt);
    })();

    function showLoading() {
        btnPredict.disabled = true;
        btnPredict.textContent = 'Loading...';
    }

    function hideLoading() {
        btnPredict.disabled = false;
        btnPredict.innerHTML = `<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m4-4H8"/></svg>Prediksi`;
    }

    async function fetchPrediction(productId, selectedHorizon) {
        showLoading();
        try {
            const startYear = encodeURIComponent(yearSelect.value || '');

            const urlCandidates = [
                `/api/predict/product/${productId}?horizon=${selectedHorizon}&start_year=${startYear}`,
                `/api/predict?product_id=${productId}&horizon=${selectedHorizon}&start_year=${startYear}`,
                `/predictions/data/${productId}?horizon=${selectedHorizon}&start_year=${startYear}`
            ];

            let res, text, data;
            for (const url of urlCandidates) {
                res = await fetch(url, { credentials: 'same-origin' });
                text = await res.text();
                try { data = JSON.parse(text); } catch(e) { data = null; }
                if (res.ok) {
                    hideLoading();
                    return data;
                }
                console.warn(`Request to ${url} failed: ${res.status}`);
            }

            throw new Error(`Semua endpoint gagal.`);
        } catch (err) {
            hideLoading();
            alert('Gagal memuat prediksi: ' + err.message);
            return null;
        }
    }

    function formatMonthLabel(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        if (isNaN(d)) {
            const parts = (dateStr || '').split('-');
            if (parts.length >= 2) {
                const yr = parseInt(parts[0], 10);
                const mo = parseInt(parts[1], 10) - 1;
                const dd = parts[2] ? parseInt(parts[2], 10) : 1;
                const ddObj = new Date(yr, mo, dd);
                if (!isNaN(ddObj)) {
                    return new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' }).format(ddObj);
                }
            }
            return dateStr;
        }
        return new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' }).format(d);
    }

    function buildChart(actualSeries, predictedSeries) {
        const actualLabels = actualSeries.map(s => formatMonthLabel(s.date));
        const predLabels = predictedSeries.map(s => formatMonthLabel(s.date));
        const labels = [...actualLabels, ...predLabels];

        const actualData = actualSeries.map(s => s.actual);
        const predictedData = [
            ...Array(actualSeries.length).fill(null),
            ...predictedSeries.map(s => s.predicted)
        ];

        const ctx = document.getElementById('predictionChart').getContext('2d');
        if (chart) chart.destroy();

        chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Actual',
                        data: actualData.concat(Array(predictedSeries.length).fill(null)),
                        tension: 0.2,
                        fill: false,
                        borderColor: '#3b82f6',
                        borderWidth: 2,
                    },
                    {
                        label: 'Predicted',
                        data: predictedData,
                        borderDash: [6,4],
                        tension: 0.2,
                        fill: false,
                        borderColor: '#f59e0b',
                        borderWidth: 2,
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    x: { display: true, title: { display: true, text: 'Bulan' } },
                    y: { display: true, title: { display: true, text: 'Jumlah' } }
                },
                plugins: { legend: { display: true } }
            }
        });
    }

    function renderTable(actualSeries, predictedSeries, modelData) {
        const tbody = document.getElementById('pred-table-body');
        tbody.innerHTML = '';

        actualSeries.forEach((a, idx) => {
            const tr = document.createElement('tr');
            let percentage = '-';
            let keterangan = 'Data penjualan';

            if (idx > 0) {
                const prev = actualSeries[idx - 1].actual;
                const current = a.actual;
                if (prev > 0) {
                    const pct = ((current - prev) / prev * 100).toFixed(2);
                    percentage = pct + '%';

                    if (parseFloat(pct) > 0) {
                        keterangan = `Data aktual, naik ${Math.abs(pct)}% dari bulan sebelumnya`;
                    } else if (parseFloat(pct) < 0) {
                        keterangan = `Data aktual, turun ${Math.abs(pct)}% dari bulan sebelumnya`;
                    } else {
                        keterangan = `Data aktual, stabil dari bulan sebelumnya`;
                    }
                }
            }

            tr.innerHTML = `
                <td class="px-3 py-2 border">${formatMonthLabel(a.date)}</td>
                <td class="px-3 py-2 border font-medium">${a.actual}</td>
                <td class="px-3 py-2 border text-center">${percentage}</td>
                <td class="px-3 py-2 border">-</td>
                <td class="px-3 py-2 border text-sm text-gray-600">${keterangan}</td>
            `;
            tbody.appendChild(tr);
        });

        predictedSeries.forEach((p, idx) => {
            const tr = document.createElement('tr');
            let percentage = '-';

            const periodX = actualSeries.length + idx + 1;
            const slope = modelData.slope || 0;
            const intercept = modelData.intercept || 0;

            let prev;
            if (idx === 0 && actualSeries.length > 0) {
                prev = actualSeries[actualSeries.length - 1].actual;
            } else if (idx > 0) {
                prev = predictedSeries[idx - 1].predicted;
            }

            if (prev !== undefined && prev > 0) {
                const current = p.predicted;
                const pct = ((current - prev) / prev * 100).toFixed(2);
                percentage = pct + '%';
            }

            // Kembalikan rumus hasil regresi di kolom keterangan
            const trendWord = slope > 0 ? 'naik' : (slope < 0 ? 'turun' : 'stabil');
            const keterangan = `Hasil regresi: ${intercept.toFixed(2)} + (${slope.toFixed(2)} × ${periodX}) = ${p.predicted}. Tren ${trendWord}.`;

            tr.innerHTML = `
                <td class="px-3 py-2 border bg-yellow-50">${formatMonthLabel(p.date)}</td>
                <td class="px-3 py-2 border bg-yellow-50">-</td>
                <td class="px-3 py-2 border bg-yellow-50 text-center">${percentage}</td>
                <td class="px-3 py-2 border bg-yellow-50 font-bold text-yellow-700">${p.predicted}</td>
                <td class="px-3 py-2 border bg-yellow-50 text-sm text-gray-600">${keterangan}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderSummary(data) {
        document.getElementById('m-product').textContent = data.product?.name ?? '-';
        document.getElementById('m-n').textContent = data.n_observations ?? '-';
        document.getElementById('m-slope').textContent = (data.slope !== undefined) ? Number(data.slope).toFixed(4) : '-';
        document.getElementById('m-intercept').textContent = (data.intercept !== undefined) ? Number(data.intercept).toFixed(4) : '-';
        document.getElementById('m-r2').textContent = (data.r2 !== undefined) ? Number(data.r2).toFixed(4) : '-';
        document.getElementById('m-rmse').textContent = (data.rmse !== undefined) ? Number(data.rmse).toFixed(4) : '-';
    }

    // Fungsi untuk merender list dinamis ke bawah pada kotak penjelasan
   // Fungsi untuk merender list dinamis ke bawah pada kotak penjelasan
    function renderExplanation(selectedHorizon, actualSeries, predictedSeries) {
        document.getElementById('text-dynamic-horizon').textContent = selectedHorizon;
        document.getElementById('text-dynamic-horizon-2').textContent = selectedHorizon;

        // --- INI YANG BERUBAH: Menggunakan selectedHorizon ---
        document.getElementById('text-dynamic-horizon-3').textContent = selectedHorizon;
        // -----------------------------------------------------

        const listContainer = document.getElementById('dynamic-prediction-list');
        listContainer.innerHTML = '';

        if (predictedSeries && predictedSeries.length > 0) {
            listContainer.classList.remove('hidden');

            predictedSeries.forEach((p, idx) => {
                let prev;
                if (idx === 0 && actualSeries.length > 0) {
                    prev = actualSeries[actualSeries.length - 1].actual;
                } else if (idx > 0) {
                    prev = predictedSeries[idx - 1].predicted;
                }

                let changeText = 'stabil';
                let colorClass = 'text-gray-600';

                if (prev !== undefined && prev > 0) {
                    const pct = ((p.predicted - prev) / prev * 100).toFixed(2);
                    if (pct > 0) {
                        changeText = `naik ${Math.abs(pct)}%`;
                        colorClass = 'text-green-600 font-bold';
                    } else if (pct < 0) {
                        changeText = `turun ${Math.abs(pct)}%`;
                        colorClass = 'text-red-600 font-bold';
                    }
                }

                const li = document.createElement('li');
                li.innerHTML = `Bulan ke-${idx + 1} (${formatMonthLabel(p.date)}): Prediksi <strong>${p.predicted}</strong> <span class="${colorClass}">(${changeText})</span>`;
                listContainer.appendChild(li);
            });
        } else {
            listContainer.classList.add('hidden');
        }
    }

    async function loadAndRender(productId) {
        const selectedHorizon = horizonSelect.value;
        const data = await fetchPrediction(productId, selectedHorizon);
        if (!data) return;

        const actualSeries = data.actual_series || [];
        const predictedSeries = data.predicted_series || [];

        const modelData = {
            product: data.product,
            n_observations: data.n_observations ?? data.n,
            slope: data.slope,
            intercept: data.intercept,
            r2: data.r2,
            rmse: data.rmse
        };

        buildChart(actualSeries, predictedSeries);
        renderTable(actualSeries, predictedSeries, modelData);
        renderSummary(modelData);

        // Panggil renderExplanation dengan mengirim array data aktual dan prediksi
        renderExplanation(selectedHorizon, actualSeries, predictedSeries);
    }

    btnPredict.addEventListener('click', function() {
        const pid = select.value;
        if (!pid) {
            alert('Pilih produk terlebih dahulu.');
            return;
        }
        loadAndRender(pid);
    });
});
</script>
@endsection

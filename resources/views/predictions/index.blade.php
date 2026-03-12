@extends('layouts.app')

@section('page-title', 'Prediksi Penjualan')
@section('page-subtitle', 'Analisis dan forecast penjualan 12 bulan kedepan')

@section('content')
<div class="mb-6">
    <span class="inline-flex items-center px-4 py-2 bg-green-100 border-2 border-green-500 text-green-700 rounded-lg text-sm font-semibold">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
        Regresi Linier
    </span>
</div>

<div class="mb-4 flex items-end gap-3">
    <div class="w-1/3">
        <label for="product-select" class="block text-sm font-medium text-gray-700">Pilih Produk</label>
        <select id="product-select" class="mt-1 block w-full border rounded p-2">
            @foreach($products as $product)
                <option value="{{ $product->id }}">{{ $product->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="w-1/6">
        <label for="year-select" class="block text-sm font-medium text-gray-700">Tahun (label)</label>
        <select id="year-select" class="mt-1 block w-full border rounded p-2"></select>
    </div>

    <div class="flex items-center">
        <button id="btn-predict" class="ml-2 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m4-4H8"/></svg>
            Prediksi
        </button>
    </div>

    <div class="ml-auto text-sm text-gray-600">
        <p>Catatan: prediksi 12 bulan ke depan. Pilih produk lalu klik <strong>Prediksi</strong>.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white p-4 rounded shadow">
        <h3 class="font-semibold mb-2">Grafik Actual vs Prediksi</h3>
        <canvas id="predictionChart" height="220"></canvas>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <h3 class="font-semibold mb-2">Ringkasan Model</h3>
        <div id="model-summary" class="text-sm">
            <p><strong>Produk:</strong> <span id="m-product">-</span></p>
            <p><strong>Observasi (n):</strong> <span id="m-n">-</span></p>
            <p><strong>Slope (b1):</strong> <span id="m-slope">-</span></p>
            <p><strong>Intercept (b0):</strong> <span id="m-intercept">-</span></p>
            <p><strong>R²:</strong> <span id="m-r2">-</span></p>
            <p><strong>RMSE:</strong> <span id="m-rmse">-</span></p>
        </div>
    </div>
</div>

<div class="mt-6 bg-white p-4 rounded shadow">
    <h3 class="font-semibold mb-2">Tabel Actual & Prediksi</h3>

    <table class="min-w-full border-collapse" id="pred-table">
        <thead>
            <tr>
                <th class="px-3 py-2 border">Bulan</th>
                <th class="px-3 py-2 border">Actual</th>
                <th class="px-3 py-2 border">Prediksi</th>
            </tr>
        </thead>
        <tbody id="pred-table-body"></tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('product-select');
    const yearSelect = document.getElementById('year-select');
    const btnPredict = document.getElementById('btn-predict');
    const horizon = 12; // 12 bulan ke depan
    let chart = null;

    // only 2026 option
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

    async function fetchPrediction(productId) {
        showLoading();
        try {
            const startYear = encodeURIComponent(yearSelect.value || '');
            const urlCandidates = [
                `/api/predict/product/${productId}?horizon=${horizon}&start_year=${startYear}`,
                `/api/predict?product_id=${productId}&horizon=${horizon}&start_year=${startYear}`,
                `/predictions/data/${productId}?horizon=${horizon}&start_year=${startYear}`
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
                console.warn(`Request to ${url} failed: ${res.status} ${res.statusText} - ${text}`);
            }

            throw new Error(`Semua endpoint gagal. Terakhir: ${res.status} ${res.statusText} - ${text}`);
        } catch (err) {
            hideLoading();
            console.error('Fetch prediction error:', err);
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
                        borderWidth: 2,
                    },
                    {
                        label: 'Predicted',
                        data: predictedData,
                        borderDash: [6,4],
                        tension: 0.2,
                        fill: false,
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

    function renderTable(actualSeries, predictedSeries) {
        const tbody = document.getElementById('pred-table-body');
        tbody.innerHTML = '';

        // first actual rows (should be 12 items from 2025)
        actualSeries.forEach(a => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-3 py-2 border">${formatMonthLabel(a.date)}</td>
                <td class="px-3 py-2 border">${a.actual}</td>
                <td class="px-3 py-2 border">-</td>
            `;
            tbody.appendChild(tr);
        });

        // then predicted rows (should be 12 items from 2026)
        predictedSeries.forEach(p => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-3 py-2 border">${formatMonthLabel(p.date)}</td>
                <td class="px-3 py-2 border">-</td>
                <td class="px-3 py-2 border">${p.predicted}</td>
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

    async function loadAndRender(productId) {
        const data = await fetchPrediction(productId);
        if (!data) return;

        const actualSeries = data.actual_series || [];
        const predictedSeries = data.predicted_series || [];

        buildChart(actualSeries, predictedSeries);
        renderTable(actualSeries, predictedSeries);
        renderSummary({
            product: data.product,
            n_observations: data.n_observations ?? data.n,
            slope: data.slope,
            intercept: data.intercept,
            r2: data.r2,
            rmse: data.rmse
        });
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

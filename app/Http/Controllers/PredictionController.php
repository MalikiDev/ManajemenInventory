<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Services\LinearRegressionService;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PredictionController extends Controller
{

    public function predict(Request $request)
{
    try {
        $horizon = (int) $request->input('horizon', 12);
        $productId = $request->input('product_id');

        if (!$productId) {
            return response()->json(['error' => 'product_id diperlukan.'], 422);
        }

        // Ambil semua data histori, group per bulan, urut dari terlama
        $sales = Sale::where('product_id', $productId)
            ->whereNotNull('sale_date')
            ->selectRaw('DATE_FORMAT(sale_date, "%Y-%m-01") as month, SUM(jumlah) as total')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        if ($sales->count() < 2) {
            return response()->json(['error' => 'Butuh minimal 2 bulan data penjualan.'], 422);
        }

        // actual_series: semua data histori (2025 + 2026 manual)
        $actualSeries = $sales->map(fn($r) => [
            'date'   => (string) $r->month,
            'actual' => (float) $r->total,
        ])->values();

        // X = 1..n, Y = total per bulan
        $x = range(1, $sales->count());
        $y = $sales->pluck('total')->map(fn($v) => (float)$v)->toArray();

        $model = LinearRegressionService::fit($x, $y);

        $lastX    = count($x);
        $lastDate = $sales->last()->month;

        // Prediksi horizon bulan ke depan setelah data terakhir
        $predictedSeries = [];
        for ($i = 1; $i <= $horizon; $i++) {
            $futureX    = $lastX + $i;
            $futureDate = Carbon::parse($lastDate)->addMonths($i)->startOfMonth()->toDateString();

            $predictedSeries[] = [
                'date'      => $futureDate,
                'predicted' => round(($model['predict'])($futureX), 2),
            ];
        }

        $product = Product::find($productId);

        return response()->json([
            'product'        => $product ? ['id' => $product->id, 'name' => $product->name] : null,
            'n_observations' => $model['n'],
            'slope'          => $model['slope'],
            'intercept'      => $model['intercept'],
            'r2'             => round($model['r2'], 4),
            'rmse'           => round($model['rmse'], 4),
            'actual_series'  => $actualSeries,
            'predicted_series' => $predictedSeries,
        ]);

    } catch (\Throwable $e) {
        Log::error('Prediction error: ' . $e->getMessage(), ['exception' => $e]);
        return response()->json(['message' => 'Internal server error: ' . $e->getMessage()], 500);
    }
}

    // wrapper supaya /api/predict/product/{id} bisa dipanggil
    public function predictByProduct($id, Request $request)
    {
        $request->merge(['product_id' => $id]);
        return $this->predict($request);
    }

    // render halaman index (dropdown products)
    public function index()
    {
        $products = Product::orderBy('name')->get(['id','name']);
        return view('predictions.index', compact('products'));
    }
}

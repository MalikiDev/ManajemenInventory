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
            if ($productId) {
                $sales = Sale::where('product_id', $productId)
                    ->whereNotNull('sale_date')
                    ->orderBy('sale_date')
                    ->get(['sale_date', 'jumlah']);

                if ($sales->count() < 2) {
                    return response()->json(['error' => 'Butuh minimal 2 data penjualan untuk product ini.'], 422);
                }

                // actual series (date + actual)
                $actualSeries = $sales->map(fn($r) => [
                    'date' => (string) $r->sale_date,
                    'actual' => (float) $r->jumlah
                ])->values();

                // X = 1..n, Y = jumlah
                $x = $sales->keys()->map(fn($k) => $k + 1)->toArray();
                $y = $sales->pluck('jumlah')->map(fn($v) => (float)$v)->toArray();

                $model = LinearRegressionService::fit($x, $y);

                $lastX = end($x);
                $lastDate = $sales->last()->sale_date;

                // Prediksi horizon hari ke depan (menggunakan addDays)
                $predictedSeries = [];
                for ($i = 1; $i <= $horizon; $i++) {
                    $futureX = $lastX + $i;
                    try {
                    $futureDate = $lastDate ? Carbon::parse($lastDate)->addMonths($i)->toDateString() : Carbon::now()->addMonths($i)->startOfMonth()->toDateString();
                    } catch (\Exception $e) {
                        $futureDate = null;
                    }
                    $predictedSeries[] = [
                        'date' => $futureDate,
                        'predicted' => round(($model['predict'])($futureX), 2)
                    ];
                }

                $product = Product::find($productId);

                            return response()->json([
                    'product' => $product ? ['id' => $product->id, 'name' => $product->name] : null,
                    'n_observations' => $model['n'],
                    'slope' => $model['slope'],
                    'intercept' => $model['intercept'],
                    'r2' => round($model['r2'], 4),
                    'rmse' => round($model['rmse'], 4),
                    'actual_series' => $actualSeries,
                    'predicted_series' => $predictedSeries
                ], 200);
            }

            // -----------------------
            // MODE fallback (seluruh sales)
            // -----------------------
            $sales = Sale::whereNotNull('sale_date')->orderBy('sale_date')->get(['sales_date', 'jumlah']);
            if ($sales->count() < 2) {
                return response()->json(['error' => 'Butuh minimal 2 data untuk prediksi (periode).'], 422);
            }

            // pakai index 1..n sebagai X
            $x = $sales->keys()->map(fn($k) => $k + 1)->toArray();
            $y = $sales->pluck('jumlah')->map(fn($v) => (float)$v)->toArray();

            $model = LinearRegressionService::fit($x, $y);

            $lastX = end($x);
            $lastDate = $sales->last()->sale_date;

            $predicted = [];
            for ($i = 1; $i <= $horizon; $i++) {
                $futureX = $lastX + $i;
                try {
                    $futureDate = $lastDate ? Carbon::parse($lastDate)->addDays($i)->toDateString() : Carbon::now()->addDays($i)->toDateString();
                } catch (\Exception $e) {
                    $futureDate = null;
                }
                $predicted[] = [
                    'date' => $futureDate,
                    'predicted' => round(($model['predict'])($futureX), 2)
                ];
            }

                    return response()->json([
                'n_observations' => $model['n'],
                'slope' => $model['slope'],
                'intercept' => $model['intercept'],
                'r2' => round($model['r2'], 4),
                'rmse' => round($model['rmse'], 4),
                'predictions' => $predicted
            ], 200);


        } catch (\Throwable $e) {
            Log::error("Prediction error: " . $e->getMessage(), ['exception' => $e]);
            // kembalikan message sederhana (development: kamu boleh tambahkan trace sementara)
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

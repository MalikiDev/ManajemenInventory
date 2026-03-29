<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Prediction;
use App\Services\LinearRegressionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PredictionController extends Controller
{
    public function predict(Request $request)
    {
        try {
            $horizon = (int) $request->input('horizon', 12);
            $productId = $request->input('product_id');
            $startYear = (int) $request->input('start_year', Carbon::now()->year);

            if (!$productId) {
                return response()->json(['error' => 'product_id diperlukan.'], 422);
            }

            // Ambil histori penjualan, digroup per bulan
            $sales = Sale::where('product_id', $productId)
                ->whereNotNull('sale_date')
                ->selectRaw('DATE_FORMAT(sale_date, "%Y-%m-01") as month, SUM(jumlah) as total')
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get();

            if ($sales->count() < 2) {
                return response()->json(['error' => 'Butuh minimal 2 bulan data penjualan.'], 422);
            }

            // actual series untuk chart
            $actualSeries = $sales->map(function ($r) {
                return [
                    'date' => (string) $r->month,
                    'actual' => (float) $r->total,
                ];
            })->values();

            // X = 1..n, Y = total per bulan
            $x = range(1, $sales->count());
            $y = $sales->pluck('total')->map(fn ($v) => (float) $v)->toArray();

            $model = LinearRegressionService::fit($x, $y);

            $lastX = count($x);
            $lastDate = Carbon::parse($sales->last()->month);

            // Prediksi 12 bulan ke depan mulai dari startYear
            $predictedSeries = [];
            $targetStart = Carbon::create($startYear, 1, 1)->startOfMonth();

            // hitung jarak bulan dari bulan terakhir histori ke Januari startYear
            $monthsAheadStart = 1;
            if ($targetStart->greaterThan($lastDate)) {
                $monthsAheadStart = $lastDate->diffInMonths($targetStart);
                if ($monthsAheadStart < 1) {
                    $monthsAheadStart = 1;
                }
            }

            for ($i = 0; $i < $horizon; $i++) {
                $futureX = $lastX + $monthsAheadStart + $i;
                $futureDate = $targetStart->copy()->addMonths($i)->toDateString();

                $predictedSeries[] = [
                    'date' => $futureDate,
                    'predicted' => round(($model['predict'])($futureX), 2),
                ];
            }

            $product = Product::find($productId);

            // Simpan ke database
            $savedPrediction = DB::transaction(function () use (
                $productId,
                $startYear,
                $horizon,
                $model,
                $predictedSeries
            ) {
                return Prediction::updateOrCreate(
                    [
                        'product_id' => $productId,
                        'start_year' => $startYear,
                    ],
                    [
                        'horizon' => $horizon,
                        'slope' => $model['slope'],
                        'intercept' => $model['intercept'],
                        'r2' => $model['r2'],
                        'rmse' => $model['rmse'],
                        'predicted_values' => $predictedSeries,
                    ]
                );
            });

            return response()->json([
                'saved_prediction_id' => $savedPrediction->id,
                'product' => $product ? ['id' => $product->id, 'name' => $product->name] : null,
                'n_observations' => $model['n'] ?? count($x),
                'slope' => $model['slope'],
                'intercept' => $model['intercept'],
                'r2' => round($model['r2'], 4),
                'rmse' => round($model['rmse'], 4),
                'actual_series' => $actualSeries,
                'predicted_series' => $predictedSeries,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Prediction error: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'message' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function predictByProduct($id, Request $request)
    {
        $request->merge(['product_id' => $id]);
        return $this->predict($request);
    }

    public function index()
    {
        $products = Product::orderBy('name')->get(['id', 'name']);
        return view('predictions.index', compact('products'));
    }
    
    public function history()
    {
        $predictions = Prediction::with('product')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('predictions.history', compact('predictions'));
    }
    
    public function show($id)
    {
        $prediction = Prediction::with('product')->findOrFail($id);
        
        return response()->json([
            'id' => $prediction->id,
            'product' => [
                'id' => $prediction->product->id,
                'name' => $prediction->product->name,
            ],
            'start_year' => $prediction->start_year,
            'horizon' => $prediction->horizon,
            'slope' => $prediction->slope,
            'intercept' => $prediction->intercept,
            'r2' => $prediction->r2,
            'rmse' => $prediction->rmse,
            'predicted_values' => $prediction->predicted_values,
            'created_at' => $prediction->created_at->format('d M Y H:i'),
        ]);
    }
    
    public function destroy($id)
    {
        $prediction = Prediction::findOrFail($id);
        $prediction->delete();
        
        return redirect()->route('predictions.history')
            ->with('success', 'Data prediksi berhasil dihapus!');
    }
}

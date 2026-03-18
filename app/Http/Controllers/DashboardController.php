<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get real data from database
        $totalProducts = \App\Models\Product::count();
        $totalSalesToday = \App\Models\Sale::whereDate('sale_date', today())->sum('jumlah');
        $totalSalesThisMonth = \App\Models\Sale::whereMonth('sale_date', now()->month)
                                              ->whereYear('sale_date', now()->year)
                                              ->count();
        
        // Get recent products (top 3)
        $topProducts = \App\Models\Product::latest()->take(3)->get();
        
        // Get recent sales for activity
        $recentSales = \App\Models\Sale::latest()->take(3)->get();
        
        return view('dashboard.index', compact(
            'user',
            'totalProducts',
            'totalSalesToday',
            'totalSalesThisMonth',
            'topProducts',
            'recentSales'
        ));
    }

    public function getSalesChartData(Request $request)
    {
        $period = $request->get('period', '7'); // default 7 days
        
        $startDate = match($period) {
            '7' => now()->subDays(7),
            '30' => now()->subDays(30),
            '365' => now()->subYear(),
            default => now()->subDays(7)
        };

        $sales = \App\Models\Sale::where('sale_date', '>=', $startDate)
            ->selectRaw('DATE(sale_date) as date, SUM(jumlah) as total')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Fill missing dates with zero
        $dates = [];
        $currentDate = $startDate->copy();
        $endDate = now();

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $saleData = $sales->firstWhere('date', $dateStr);
            
            $dates[] = [
                'date' => $dateStr,
                'label' => $currentDate->format('d M'),
                'total' => $saleData ? (int)$saleData->total : 0
            ];
            
            $currentDate->addDay();
        }

        return response()->json([
            'success' => true,
            'data' => $dates,
            'period' => $period
        ]);
    }
}

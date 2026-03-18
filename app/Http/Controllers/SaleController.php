<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Exports\SalesExport;
use App\Imports\SalesImport;
use App\Exports\SalesTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with('product');
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('sale_date', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('sale_date', '<=', $request->end_date);
        }
        
        // Filter by specific date
        if ($request->filled('date')) {
            $query->whereDate('sale_date', $request->date);
        }
        
        $sales = $query->latest('sale_date')->paginate(10)->withQueryString();
        
        // Calculate summary for filtered data
        $totalJumlah = $query->sum('jumlah');
        $totalTransactions = $query->count();
        
        return view('sales.index', compact('sales', 'totalJumlah', 'totalTransactions'));
    }

    public function create()
    {
        $products = Product::all();
        return view('sales.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'sale_date' => 'required|date',
            'jumlah' => 'required|integer|min:1',
        ]);

        Sale::create($validated);

        return redirect()->route('sales.index')
            ->with('success', 'Data penjualan berhasil ditambahkan!');
    }

    public function show(Sale $sale)
    {
        $sale->load('product');
        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $products = Product::all();
        return view('sales.edit', compact('sale', 'products'));
    }

    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'sale_date' => 'required|date',
            'jumlah' => 'required|integer|min:1',
        ]);

        $sale->update($validated);

        return redirect()->route('sales.index')
            ->with('success', 'Data penjualan berhasil diupdate!');
    }

    public function destroy(Sale $sale)
    {
        $sale->delete();

        return redirect()->route('sales.index')
            ->with('success', 'Data penjualan berhasil dihapus!');
    }

    public function export(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $date = $request->input('date');

        $filename = 'sales_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new SalesExport($startDate, $endDate, $date), $filename);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            $import = new SalesImport;
            Excel::import($import, $request->file('file'));

            $errors = $import->getErrors();
            $importedCount = $import->getImportedCount();
            
            if (!empty($errors)) {
                $errorMessage = "Import selesai: {$importedCount} data berhasil diimport. Error: " . implode(' | ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $errorMessage .= " ... dan " . (count($errors) - 5) . " error lainnya.";
                }
                
                return redirect()->route('sales.index')
                    ->with('error', $errorMessage);
            }

            return redirect()->route('sales.index')
                ->with('success', "Berhasil import {$importedCount} data penjualan!");
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }
            
            return redirect()->route('sales.index')
                ->with('error', 'Validasi gagal: ' . implode(' | ', $errorMessages));
        } catch (\Exception $e) {
            return redirect()->route('sales.index')
                ->with('error', 'Gagal import data: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new SalesTemplateExport, 'template_import_sales.xlsx');
    }
}

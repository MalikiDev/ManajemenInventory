<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $date;

    public function __construct($startDate = null, $endDate = null, $date = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->date = $date;
    }

    public function collection()
    {
        $query = Sale::with('product');

        if ($this->date) {
            $query->whereDate('sale_date', $this->date);
        } elseif ($this->startDate && $this->endDate) {
            $query->whereDate('sale_date', '>=', $this->startDate)
                  ->whereDate('sale_date', '<=', $this->endDate);
        } elseif ($this->startDate) {
            $query->whereDate('sale_date', '>=', $this->startDate);
        } elseif ($this->endDate) {
            $query->whereDate('sale_date', '<=', $this->endDate);
        }

        return $query->orderBy('sale_date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Produk',
            'Tanggal Penjualan',
            'Jumlah (Unit)',
            'Dibuat Pada',
        ];
    }

    public function map($sale): array
    {
        return [
            $sale->id,
            $sale->product->name ?? 'N/A',
            $sale->sale_date->format('Y-m-d'),
            $sale->jumlah,
            $sale->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

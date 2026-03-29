<?php

namespace App\Imports;

use App\Models\Sale;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Carbon\Carbon;

class SalesImport implements ToModel, WithHeadingRow, SkipsOnError, WithCustomCsvSettings
{
    use SkipsErrors;

    protected $errors = [];
    protected $importedCount = 0;
    protected $rowCount = 0;

    public function getCsvSettings(): array
    {
        return ['delimiter' => ';'];
    }
    
    public function headingRow(): int
    {
        return 1;
    }

    public function model(array $row)
    {
        $this->rowCount++;
    
    $row = array_map(fn($val) => is_string($val) ? trim($val, " \t\n\r\0\x0B") : $val, $row);

    if (empty($row['nama_produk'])) {
        \Log::info('Skipping empty row');
        return null;
    }

    // ✅ Hapus pencarian by product_id — cari HANYA by nama
    $product = Product::whereRaw('LOWER(name) = LOWER(?)', [$row['nama_produk']])->first();

    if (!$product) {
        $this->errors[] = "Produk tidak ditemukan: '{$row['nama_produk']}'";
        return null;
    }

        // Parse tanggal
        $dateValue = trim($row['tanggal_penjualan'] ?? '');
        if (empty($dateValue)) {
            $this->errors[] = "Tanggal kosong pada produk: '{$row['nama_produk']}'";
            return null;
        }

        try {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateValue)) {
                $saleDate = Carbon::createFromFormat('d/m/Y', $dateValue)->startOfDay();
            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateValue)) {
                $saleDate = Carbon::createFromFormat('Y-m-d', $dateValue)->startOfDay();
            } else {
                $saleDate = Carbon::parse($dateValue)->startOfDay();
            }
        } catch (\Exception $e) {
            $this->errors[] = "Format tanggal tidak valid: '{$dateValue}'";
            return null;
        }

        // Validasi jumlah
        $jumlah = (int) ($row['jumlah'] ?? 0);
        if ($jumlah < 1) {
            $this->errors[] = "Jumlah tidak valid pada produk: '{$row['nama_produk']}'";
            return null;
        }

        $this->importedCount++;

        return new Sale([
            'product_id' => $product->id,
            'sale_date'  => $saleDate,
            'jumlah'     => $jumlah,
        ]);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }
    
    public function getRowCount(): int
    {
        return $this->rowCount;
    }
}
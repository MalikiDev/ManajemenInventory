<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesSeeder extends Seeder
{
    public function run()
    {
        // map produk ke id (pastikan ProductsSeeder sudah dijalankan)
        $productMap = Product::whereIn('name', ['Terbang','Bass','Tam','Darbuka','Keprak'])
            ->get()
            ->keyBy('name')
            ->map(fn($p) => $p->id)
            ->toArray();

        // DATA BARU (dari file Word) -> month => [product => jumlah, ...]
        $data = [
            1  => ['Terbang'=>48, 'Bass'=>4,  'Tam'=>4, 'Darbuka'=>5, 'Keprak'=>10], // Januari
            2  => ['Terbang'=>42, 'Bass'=>3,  'Tam'=>3, 'Darbuka'=>4, 'Keprak'=>9],  // Februari
            3  => ['Terbang'=>60, 'Bass'=>6,  'Tam'=>5, 'Darbuka'=>6, 'Keprak'=>13], // Maret
            4  => ['Terbang'=>50, 'Bass'=>5,  'Tam'=>5, 'Darbuka'=>5, 'Keprak'=>11], // April
            5  => ['Terbang'=>63, 'Bass'=>6,  'Tam'=>6, 'Darbuka'=>7, 'Keprak'=>14], // Mei
            6  => ['Terbang'=>61, 'Bass'=>6,  'Tam'=>6, 'Darbuka'=>6, 'Keprak'=>13], // Juni
            7  => ['Terbang'=>55, 'Bass'=>5,  'Tam'=>5, 'Darbuka'=>6, 'Keprak'=>12], // Juli
            8  => ['Terbang'=>57, 'Bass'=>5,  'Tam'=>7, 'Darbuka'=>6, 'Keprak'=>12], // Agustus
            9  => ['Terbang'=>50, 'Bass'=>4,  'Tam'=>2, 'Darbuka'=>5, 'Keprak'=>10], // September
            10 => ['Terbang'=>52, 'Bass'=>5,  'Tam'=>5, 'Darbuka'=>6, 'Keprak'=>11], // Oktober
            11 => ['Terbang'=>58, 'Bass'=>6,  'Tam'=>6, 'Darbuka'=>6, 'Keprak'=>12], // November
            12 => ['Terbang'=>59, 'Bass'=>6,  'Tam'=>6, 'Darbuka'=>6, 'Keprak'=>13], // Desember
        ];

        $year = 2025; // ubah kalau mau tahun lain

        $rowsInserted = 0;

        foreach ($data as $month => $row) {
            // tanggal dipakai sebagai tanggal 1 bulan tersebut di $year
            $date = Carbon::create($year, $month, 1)->toDateString();

            foreach ($row as $productName => $jumlah) {
                // jika product tidak ada di productMap, coba cari lagi (toleransi)
                if (!isset($productMap[$productName])) {
                    $product = Product::where('name', $productName)->first();
                    if ($product) {
                        $productMap[$productName] = $product->id;
                    } else {
                        // coba buat product baru minimal — kalau migration butuh field lain, sesuaikan
                        try {
                            $product = Product::create(['name' => $productName]);
                            $productMap[$productName] = $product->id;
                            $this->command->info("Created product '{$productName}' (id {$product->id}).");
                        } catch (\Throwable $e) {
                            $this->command->error("Failed to create product '{$productName}': " . $e->getMessage());
                            continue; // skip insertion for this product
                        }
                    }
                }
            }
        }

        // Hapus dulu sales untuk produk & tahun yang bersangkutan supaya tidak duplikasi
        foreach ($productMap as $productName => $productId) {
            DB::table('sales')
                ->where('product_id', $productId)
                ->whereYear('sale_date', $year)
                ->delete();
        }

        // Sekarang insert data
        foreach ($data as $month => $row) {
            $date = Carbon::create($year, $month, 1)->toDateString();
            foreach ($row as $productName => $jumlah) {
                if (!isset($productMap[$productName])) {
                    // safety: jika masih tidak ada, skip
                    $this->command->warn("ProductMap missing for {$productName}, skipping month {$month}.");
                    continue;
                }

                Sale::create([
                    'product_id' => $productMap[$productName],
                    'sale_date' => $date,
                    'jumlah' => (int)$jumlah
                ]);
                $rowsInserted++;
            }
        }

        $this->command->info("Inserted {$rowsInserted} sales rows for year {$year}.");
    }
}

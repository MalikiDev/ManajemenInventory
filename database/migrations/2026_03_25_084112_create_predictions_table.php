<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('start_year');
            $table->unsignedTinyInteger('horizon')->default(12);

            $table->decimal('slope', 15, 6)->nullable();
            $table->decimal('intercept', 15, 6)->nullable();
            $table->decimal('r2', 15, 6)->nullable();
            $table->decimal('rmse', 15, 6)->nullable();

            $table->json('predicted_values'); // simpan hasil prediksi per bulan
            $table->timestamps();

            $table->unique(['product_id', 'start_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};

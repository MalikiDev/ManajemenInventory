<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prediction extends Model
{
    protected $fillable = [
        'product_id',
        'start_year',
        'horizon',
        'slope',
        'intercept',
        'r2',
        'rmse',
        'predicted_values',
    ];

    protected $casts = [
        'predicted_values' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

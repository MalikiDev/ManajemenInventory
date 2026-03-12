<?php
namespace App\Services;

class LinearRegressionService
{
    /**
     * Expect numeric arrays $x and $y of equal length.
     * Returns ['slope'=>..., 'intercept'=>..., 'predict'=>callable].
     */
    public static function fit(array $x, array $y)
    {
        $n = count($x);
        if ($n === 0 || $n !== count($y)) {
            throw new \InvalidArgumentException('Arrays must be same non-zero length');
        }

        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $meanX = $sumX / $n;
        $meanY = $sumY / $n;

        $num = 0.0;
        $den = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $dx = $x[$i] - $meanX;
            $dy = $y[$i] - $meanY;
            $num += $dx * $dy;
            $den += $dx * $dx;
        }

        if ($den == 0.0) {
            throw new \RuntimeException('Variance of X is zero; cannot fit linear regression');
        }

        $slope = $num / $den;
        $intercept = $meanY - $slope * $meanX;

        $ssRes = 0.0; $ssTot = 0.0;
for ($i = 0; $i < $n; $i++) {
    $yhat = $intercept + $slope * $x[$i];
    $res = $y[$i] - $yhat;
    $ssRes += $res * $res;
    $ssTot += ($y[$i] - $meanY) * ($y[$i] - $meanY);
}

$r2 = ($ssTot == 0.0) ? null : (1 - $ssRes / $ssTot);
$rmse = sqrt($ssRes / $n);

return [
    'n' => $n,
    'slope' => $slope,
    'intercept' => $intercept,
    'predict' => function($xval) use ($slope, $intercept) { return $intercept + $slope * $xval; },
    'r2' => $r2,
    'rmse' => $rmse,
];
    }
}

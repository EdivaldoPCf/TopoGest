<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Base;
use Illuminate\Support\Facades\DB;

$b = Base::where('nome','base 239')->first();
if (!$b) {
    echo "Base 239 not found\n";
    exit(1);
}
function utmToLatLng($easting, $northing, $zoneNumber, $southernHemisphere = true) {
    $a = 6378137.0;
    $e = 0.081819190842622;
    $e1sq = 0.0067394967565869;
    $k0 = 0.9996;
    $x = $easting - 500000.0;
    $y = $northing;
    if ($southernHemisphere) {
        $y -= 10000000.0;
    }
    $m = $y / $k0;
    $mu = $m / ($a * (1 - pow($e,2) / 4 - 3 * pow($e,4) / 64 - 5 * pow($e,6) / 256));
    $e1 = (1 - sqrt(1 - pow($e, 2))) / (1 + sqrt(1 - pow($e, 2)));
    $j1 = (3 * $e1 / 2 - 27 * pow($e1, 3) / 32);
    $j2 = (21 * pow($e1, 2) / 16 - 55 * pow($e1, 4) / 32);
    $j3 = (151 * pow($e1, 3) / 96);
    $j4 = (1097 * pow($e1, 4) / 512);
    $fp = $mu + $j1 * sin(2 * $mu) + $j2 * sin(4 * $mu) + $j3 * sin(6 * $mu) + $j4 * sin(8 * $mu);
    $c1 = $e1sq * pow(cos($fp), 2);
    $t1 = pow(tan($fp), 2);
    $r1 = $a * (1 - pow($e, 2)) / pow(1 - pow($e, 2) * pow(sin($fp), 2), 1.5);
    $n1 = $a / sqrt(1 - pow($e, 2) * pow(sin($fp), 2));
    $d = $x / ($n1 * $k0);
    $q1 = $n1 * tan($fp) / $r1;
    $q2 = pow($d, 2) / 2;
    $q3 = (5 + 3 * $t1 + 10 * $c1 - 4 * pow($c1, 2) - 9 * $e1sq) * pow($d, 4) / 24;
    $q4 = (61 + 90 * $t1 + 298 * $c1 + 45 * pow($t1, 2) - 252 * $e1sq - 3 * pow($c1, 2)) * pow($d, 6) / 720;
    $latRad = $fp - $q1 * ($q2 - $q3 + $q4);
    $q5 = $d;
    $q6 = (1 + 2 * $t1 + $c1) * pow($d, 3) / 6;
    $q7 = (5 - 2 * $c1 + 28 * $t1 - 3 * pow($c1, 2) + 8 * $e1sq + 24 * pow($t1, 2)) * pow($d, 5) / 120;
    $lonRad = deg2rad(($zoneNumber - 1) * 6 - 180 + 3) + ($q5 - $q6 + $q7) / cos($fp);
    return ['latitude' => rad2deg($latRad), 'longitude' => rad2deg($lonRad)];
}
$coords = utmToLatLng($b->este, $b->norte, 21, true);
echo "stored lat/long={$b->latitude},{$b->longitude}\n";
echo "computed lat/long={$coords['latitude']},{$coords['longitude']}\n";
$dist = sqrt(pow($b->latitude - $coords['latitude'], 2) + pow($b->longitude - $coords['longitude'], 2));
echo "lat/lng delta={$dist}\n";
$search = utmToLatLng(638781.636, 8894366.968, 21, true);
echo "search lat/long={$search['latitude']},{$search['longitude']}\n";
$results = DB::select('SELECT nome, latitude, longitude, SQRT(POW(latitude - ?,2)+POW(longitude - ?,2)) AS distancia FROM bases ORDER BY distancia ASC LIMIT 10', [$search['latitude'], $search['longitude']]);
foreach ($results as $row) {
    echo "{$row->nome}\t{$row->latitude}\t{$row->longitude}\t{$row->distancia}\n";
}

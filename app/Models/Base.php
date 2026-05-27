<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Base extends Model
{
    protected $fillable = ['nome', 'norte', 'este', 'norte_original', 'este_original', 'latitude', 'longitude', 'arquivo_zip'];

    protected $casts = [
        'norte' => 'double',
        'este' => 'double',
    ];

    /**
     * Get the Google Maps search URL for this base.
     */
    public function getGoogleMapsUrlAttribute()
    {
        $lat = $this->latitude;
        $lng = $this->longitude;

        if ($lat === null || $lng === null) {
            if ($this->norte && $this->este) {
                // Convert UTM to Lat/Lng (Defaulting to Zone 20, South hemisphere like in AdminController)
                $utm = $this->utmToLatLng((float) $this->este, (float) $this->norte, 20, true);
                $lat = $utm['latitude'];
                $lng = $utm['longitude'];
            }
        }

        if ($lat !== null && $lng !== null) {
            return "https://www.google.com/maps/search/?api=1&query=" . urlencode("{$lat},{$lng}");
        }

        return null;
    }

    /**
     * Converts UTM coordinates to Latitude/Longitude.
     */
    private function utmToLatLng(float $easting, float $northing, int $zoneNumber, bool $southernHemisphere = true): array
    {
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
        $mu = $m / ($a * (1 - pow($e, 2) / 4 - 3 * pow($e, 4) / 64 - 5 * pow($e, 6) / 256));

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

        return [
            'latitude' => rad2deg($latRad),
            'longitude' => rad2deg($lonRad),
        ];
    }
}
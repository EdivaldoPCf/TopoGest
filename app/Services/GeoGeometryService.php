<?php

namespace App\Services;

class GeoGeometryService
{
    /**
     * Converte coordenadas geográficas (Lat/Lng) WGS84 para coordenadas planas UTM.
     * Algoritmo baseado na projeção de Mercator Transversa (USGS / WGS84).
     */
    public function latLngToUtm(float $latitude, float $longitude, int $zone = null): array
    {
        if ($zone === null) {
            $zone = (int) floor(($longitude + 180) / 6) + 1;
        }

        $latRad = deg2rad($latitude);
        $lonRad = deg2rad($longitude);
        $lonOriginRad = deg2rad(($zone - 1) * 6 - 180 + 3);

        $a = 6378137.0; // Semieixo maior WGS84
        $f = 1.0 / 298.257223563; // Achatamento
        $b = $a * (1.0 - $f); // Semieixo menor
        $eSq = ($a * $a - $b * $b) / ($a * $a); // Primeira excentricidade ao quadrado
        $k0 = 0.9996; // Fator de escala no meridiano central

        $ePrimeSq = $eSq / (1.0 - $eSq);

        $N = $a / sqrt(1.0 - $eSq * sin($latRad) * sin($latRad));
        $T = tan($latRad) * tan($latRad);
        $C = $ePrimeSq * cos($latRad) * cos($latRad);
        $A = cos($latRad) * ($lonRad - $lonOriginRad);

        // M é o arco do meridiano do equador ao paralelo de latitude
        $M = $a * (
            (1.0 - $eSq / 4.0 - 3.0 * $eSq * $eSq / 64.0 - 5.0 * $eSq * $eSq * $eSq / 256.0) * $latRad
            - (3.0 * $eSq / 8.0 + 3.0 * $eSq * $eSq / 32.0 + 45.0 * $eSq * $eSq * $eSq / 1024.0) * sin(2.0 * $latRad)
            + (15.0 * $eSq * $eSq / 256.0 + 45.0 * $eSq * $eSq * $eSq / 1024.0) * sin(4.0 * $latRad)
            - (35.0 * $eSq * $eSq * $eSq / 3072.0) * sin(6.0 * $latRad)
        );

        $easting = $k0 * $N * (
            $A
            + (1.0 - $T + $C) * $A * $A * $A / 6.0
            + (5.0 - 18.0 * $T + $T * $T + 72.0 * $C - 58.0 * $ePrimeSq) * $A * $A * $A * $A * $A / 120.0
        ) + 500000.0;

        $northing = $k0 * (
            $M
            + $N * tan($latRad) * (
                $A * $A / 2.0
                + (5.0 - $T + 9.0 * $C + 4.0 * $C * $C) * $A * $A * $A * $A / 24.0
                + (61.0 - 58.0 * $T + $T * $T + 600.0 * $C - 330.0 * $ePrimeSq) * $A * $A * $A * $A * $A * $A / 720.0
            )
        );

        if ($latitude < 0) {
            $northing += 10000000.0; // Deslocamento para o Hemisfério Sul
        }

        return [
            'easting' => $easting,
            'northing' => $northing,
            'zone' => $zone,
            'hemisphere' => $latitude < 0 ? 'S' : 'N'
        ];
    }

    /**
     * Retorna o fuso (zona) UTM correspondente a uma longitude.
     */
    public function zonaPorLongitude(float $longitude): int
    {
        return (int) floor(($longitude + 180) / 6) + 1;
    }

    /**
     * Calcula a distância euclidiana plana entre dois pontos UTM.
     */
    public function calcularDistancia(float $x1, float $y1, float $x2, float $y2): float
    {
        return sqrt(pow($x2 - $x1, 2) + pow($y2 - $y1, 2));
    }

    /**
     * Calcula o azimute plano entre dois pontos UTM, retornado em formato GMS (Graus, Minutos, Segundos).
     */
    public function calcularAzimute(float $x1, float $y1, float $x2, float $y2): string
    {
        $dx = $x2 - $x1;
        $dy = $y2 - $y1;

        $rad = atan2($dx, $dy);
        $deg = rad2deg($rad);

        if ($deg < 0) {
            $deg += 360.0;
        }

        $degrees = floor($deg);
        $remainder = ($deg - $degrees) * 60;
        $minutes = floor($remainder);
        $seconds = round(($remainder - $minutes) * 60);

        if ($seconds >= 60) {
            $seconds = 0;
            $minutes += 1;
        }
        if ($minutes >= 60) {
            $minutes = 0;
            $degrees += 1;
        }
        if ($degrees >= 360) {
            $degrees = 0;
        }

        return sprintf("%02d°%02d'%02d\"", $degrees, $minutes, $seconds);
    }

    /**
     * Converte uma coordenada decimal (graus) para o formato GMS (Grau, Minuto, Segundo).
     */
    public function decimalParaDms(float $decimal, bool $ehLatitude = true): string
    {
        $abs = abs($decimal);
        $graus = floor($abs);
        $minutosDecimal = ($abs - $graus) * 60;
        $minutos = floor($minutosDecimal);
        $segundos = round(($minutosDecimal - $minutos) * 60, 3);

        if ($segundos >= 60) {
            $segundos = 0;
            $minutos += 1;
        }
        if ($minutos >= 60) {
            $minutos = 0;
            $graus += 1;
        }

        $segInt = floor($segundos);
        $segDec = round(($segundos - $segInt) * 1000);
        $segundosStr = sprintf('%02d,%03d', $segInt, $segDec);

        $sufixo = $ehLatitude
            ? ($decimal < 0 ? ' S' : ' N')
            : ($decimal < 0 ? ' W' : ' E');

        return $graus . '°' . sprintf('%02d', $minutos) . "'" . $segundosStr . '"' . $sufixo;
    }

    /**
     * Converte coordenadas planas UTM para coordenadas geográficas (Lat/Lng) WGS84.
     */
    public function utmToLatLng(float $easting, float $northing, int $zone, string $hemisphere = 'S'): array
    {
        $a = 6378137.0; // Semieixo maior WGS84
        $f = 1.0 / 298.257223563; // Achatamento
        $b = $a * (1.0 - $f);
        $eSq = ($a * $a - $b * $b) / ($a * $a);
        $k0 = 0.9996;

        $x = $easting - 500000.0;
        $y = $northing;
        if (strtoupper($hemisphere) === 'S') {
            $y -= 10000000.0;
        }

        $e1 = (1.0 - sqrt(1.0 - $eSq)) / (1.0 + sqrt(1.0 - $eSq));
        $M = $y / $k0;
        $mu = $M / ($a * (1.0 - $eSq / 4.0 - 3.0 * $eSq * $eSq / 64.0 - 5.0 * $eSq * $eSq * $eSq / 256.0));

        $phi1Rad = $mu + (3.0 * $e1 / 2.0 - 27.0 * $e1 * $e1 * $e1 / 32.0) * sin(2.0 * $mu) 
                 + (21.0 * $e1 * $e1 / 16.0 - 55.0 * $e1 * $e1 * $e1 * $e1 / 32.0) * sin(4.0 * $mu)
                 + (151.0 * $e1 * $e1 * $e1 / 96.0) * sin(6.0 * $mu);

        $N1 = $a / sqrt(1.0 - $eSq * sin($phi1Rad) * sin($phi1Rad));
        $T1 = tan($phi1Rad) * tan($phi1Rad);
        $C1 = $eSq / (1.0 - $eSq) * cos($phi1Rad) * cos($phi1Rad);
        $R1 = $a * (1.0 - $eSq) / pow(1.0 - $eSq * sin($phi1Rad) * sin($phi1Rad), 1.5);
        $D = $x / ($N1 * $k0);

        $lat = $phi1Rad - ($N1 * tan($phi1Rad) / $R1) * (
            $D * $D / 2.0
            - (5.0 + 3.0 * $T1 + 10.0 * $C1 - 4.0 * $C1 * $C1 - 9.0 * $e1 * $e1) * $D * $D * $D * $D / 24.0
            + (61.0 + 90.0 * $T1 + 298.0 * $C1 + 45.0 * $T1 * $T1 - 252.0 * $e1 * $e1 - 3.0 * $C1 * $C1) * $D * $D * $D * $D * $D * $D / 720.0
        );

        $lon = ($D - (1.0 + 2.0 * $T1 + $C1) * $D * $D * $D / 6.0 + (5.0 - 2.0 * $C1 + 28.0 * $T1 - 3.0 * $C1 * $C1 + 8.0 * $e1 * $e1 + 24.0 * $T1 * $T1) * $D * $D * $D * $D * $D / 120.0) / cos($phi1Rad);

        $lonOrigin = ($zone - 1) * 6 - 180 + 3;

        return [
            'latitude' => rad2deg($lat),
            'longitude' => rad2deg($lon) + $lonOrigin
        ];
    }
}

<?php

namespace App\Services\Sigef;

use App\Services\GeoGeometryService;

/**
 * Converte os vértices "crus" de um ODS SIGEF (geodésicos ou UTM) para o formato
 * usado na geração de planta/memorial, detectando o fuso UTM automaticamente.
 *
 * Antes essa conversão estava repetida 5x entre SigefMapaController e AdminGeradorController.
 */
class PreparadorVertices
{
    public function __construct(private GeoGeometryService $geo)
    {
    }

    /**
     * Detecta o fuso/hemisfério a partir do primeiro vértice geodésico (padrão Zona 20 / Sul).
     *
     * @param  array<int, array<string, mixed>> $verticesRaw
     * @return array{zona: int, hemisferio: string}
     */
    public function detectarFuso(array $verticesRaw): array
    {
        foreach ($verticesRaw as $v) {
            if ($v['tipo'] === 'geodesica') {
                return [
                    'zona' => $this->geo->zonaPorLongitude((float) $v['E']),
                    'hemisferio' => $v['N'] < 0 ? 'S' : 'N',
                ];
            }
        }

        return ['zona' => 20, 'hemisferio' => 'S'];
    }

    /**
     * Prepara os vértices para o DXF: apenas código e coordenadas planas (x, y, z).
     *
     * @param  array<int, array<string, mixed>> $verticesRaw
     * @return array<int, array{codigo: string, x: float, y: float, z: float}>
     */
    public function prepararParaDxf(array $verticesRaw): array
    {
        $zona = $this->detectarFuso($verticesRaw)['zona'];

        return array_map(function ($v) use ($zona) {
            [$easting, $northing] = $this->paraUtm($v, $zona);

            return [
                'codigo' => $v['codigo'],
                'x' => $easting,
                'y' => $northing,
                'z' => (float) ($v['altitude'] ?? 0.0),
            ];
        }, $verticesRaw);
    }

    /**
     * Prepara os vértices para o memorial: mantém os dados originais e acrescenta
     * coordenadas planas (x, y) e geográficas (lat, lon).
     *
     * @param  array<int, array<string, mixed>> $verticesRaw
     * @return array{vertices: array<int, array<string, mixed>>, zona: int, hemisferio: string}
     */
    public function prepararCompleto(array $verticesRaw): array
    {
        $fuso = $this->detectarFuso($verticesRaw);
        $vertices = [];

        foreach ($verticesRaw as $v) {
            if ($v['tipo'] === 'utm') {
                $easting = (float) $v['E'];
                $northing = (float) $v['N'];
                $latLng = $this->geo->utmToLatLng($easting, $northing, $fuso['zona'], $fuso['hemisferio']);
                $latitude = $latLng['latitude'];
                $longitude = $latLng['longitude'];
            } else {
                $latitude = (float) $v['N'];
                $longitude = (float) $v['E'];
                $utm = $this->geo->latLngToUtm($latitude, $longitude, $fuso['zona']);
                $easting = $utm['easting'];
                $northing = $utm['northing'];
            }

            $vertices[] = array_merge($v, [
                'x' => $easting,
                'y' => $northing,
                'lat' => $latitude,
                'lon' => $longitude,
            ]);
        }

        return ['vertices' => $vertices, 'zona' => $fuso['zona'], 'hemisferio' => $fuso['hemisferio']];
    }

    /** @return array{0: float, 1: float} [easting, northing] */
    private function paraUtm(array $v, int $zona): array
    {
        if ($v['tipo'] === 'utm') {
            return [(float) $v['E'], (float) $v['N']];
        }

        $utm = $this->geo->latLngToUtm((float) $v['N'], (float) $v['E'], $zona);

        return [$utm['easting'], $utm['northing']];
    }
}

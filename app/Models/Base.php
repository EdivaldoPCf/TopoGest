<?php

namespace App\Models;

use App\Services\GeoGeometryService;
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

        if (($lat === null || $lng === null) && $this->norte && $this->este) {
            // Converte UTM -> Lat/Lng (padrão Zona 20, hemisfério Sul) via serviço de geometria.
            $coords = app(GeoGeometryService::class)->utmToLatLng((float) $this->este, (float) $this->norte, 20, 'S');
            $lat = $coords['latitude'];
            $lng = $coords['longitude'];
        }

        if ($lat !== null && $lng !== null) {
            return 'https://www.google.com/maps/search/?api=1&query=' . urlencode("{$lat},{$lng}");
        }

        return null;
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Base;
use App\Services\GeoGeometryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Localizador de bases geodésicas (cadastro, busca por proximidade UTM e mapa).
 */
class BaseController extends Controller
{
    public function __construct(private GeoGeometryService $geo)
    {
    }

    public function index()
    {
        $bases = Base::orderBy('nome')->paginate(20)->withQueryString();

        return view('admin.bases.index', compact('bases'));
    }

    public function buscar(Request $request)
    {
        $norte = $this->normalizarDecimal($request->input('norte'));
        $este = $this->normalizarDecimal($request->input('este'));
        $nome = $request->input('nome');

        $query = Base::query();

        if ($nome) {
            $query->where('nome', 'LIKE', "%{$nome}%");
        }

        if ($this->ehDecimalValido($norte) && $this->ehDecimalValido($este)) {
            $query->select('*')
                ->selectRaw('SQRT(POW(norte - ?, 2) + POW(este - ?, 2)) AS distancia', [(float) $norte, (float) $este])
                ->orderBy('distancia', 'asc');
        }

        $bases = $query->orderBy('nome')->paginate(20)->withQueryString();
        $destaqueId = ($norte && $este && $bases->count() > 0) ? $bases->first()->id : null;
        $filtroCoordenada = ($norte && $este);

        return view('admin.bases.index', compact('bases', 'destaqueId', 'filtroCoordenada'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|unique:bases,nome',
            'norte' => 'required',
            'este' => 'required',
            'arquivo_zip' => 'required|mimes:zip|max:20480',
        ]);

        if (Base::where('norte', $request->norte)->where('este', $request->este)->exists()) {
            return redirect()->back()->withErrors(['norte' => 'Coordenadas já cadastradas.']);
        }

        $path = $request->file('arquivo_zip')->store('bases_zip', 'public');
        [$latitude, $longitude] = $this->extrairCoordenadasDoKml(storage_path('app/public/' . $path));

        Base::create([
            'nome' => $request->nome,
            'norte' => $request->norte,
            'este' => $request->este,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'arquivo_zip' => $path,
        ]);

        return redirect()->route('admin.bases.index')->with('success', 'Base cadastrada!');
    }

    public function destroy($id)
    {
        $base = Base::findOrFail($id);

        if ($base->arquivo_zip) {
            Storage::disk('public')->delete($base->arquivo_zip);
        }

        $base->delete();

        return redirect()->route('admin.bases.index')->with('success', 'Base removida!');
    }

    public function mapa($id)
    {
        $base = Base::findOrFail($id);
        $searchNorte = request()->query('norte');
        $searchEste = request()->query('este');

        $baseLat = $base->latitude;
        $baseLng = $base->longitude;
        $utmZone = 20;
        $isSouthern = true;

        if ($baseLat !== null && $baseLng !== null) {
            $utmZone = $this->geo->zonaPorLongitude((float) $baseLng);
            $isSouthern = $baseLat < 0;
        }

        if (($baseLat === null || $baseLng === null) && $base->norte && $base->este) {
            $coords = $this->paraLatLng($base->este, $base->norte, $utmZone, $isSouthern);
            $baseLat = $coords['latitude'];
            $baseLng = $coords['longitude'];
        }

        $searchLat = null;
        $searchLng = null;

        if ($searchNorte !== null && $searchEste !== null) {
            $searchNorte = $this->normalizarDecimal($searchNorte);
            $searchEste = $this->normalizarDecimal($searchEste);
            if ($this->ehDecimalValido($searchNorte) && $this->ehDecimalValido($searchEste)) {
                $coords = $this->paraLatLng($searchEste, $searchNorte, $utmZone, $isSouthern);
                $searchLat = $coords['latitude'];
                $searchLng = $coords['longitude'];
            }
        }

        if (($baseLat === null || $baseLng === null) && $searchLat !== null && $searchLng !== null) {
            $baseLat = $searchLat;
            $baseLng = $searchLng;
        }

        $norteVal = $this->normalizarDecimal($searchNorte);
        $esteVal = $this->normalizarDecimal($searchEste);
        $centroNorte = ($norteVal && $this->ehDecimalValido($norteVal)) ? (float) $norteVal : (float) $base->norte;
        $centroEste = ($esteVal && $this->ehDecimalValido($esteVal)) ? (float) $esteVal : (float) $base->este;

        $basesProximas = Base::where('id', '!=', $base->id)
            ->select('*')
            ->selectRaw('SQRT(POW(norte - ?, 2) + POW(este - ?, 2)) AS distancia', [$centroNorte, $centroEste])
            ->orderBy('distancia', 'asc')
            ->take(3)
            ->get()
            ->map(function ($b) use ($utmZone, $isSouthern) {
                $lat = $b->latitude;
                $lng = $b->longitude;
                if (($lat === null || $lng === null) && $b->norte && $b->este) {
                    $coords = $this->paraLatLng($b->este, $b->norte, $utmZone, $isSouthern);
                    $lat = $coords['latitude'];
                    $lng = $coords['longitude'];
                }
                $b->lat_resolvido = $lat;
                $b->lng_resolvido = $lng;

                return $b;
            });

        return view('admin.bases.mapa', compact('base', 'searchNorte', 'searchEste', 'baseLat', 'baseLng', 'searchLat', 'searchLng', 'basesProximas'));
    }

    /** Converte UTM (este/norte) para latitude/longitude usando o serviço de geometria. */
    private function paraLatLng($este, $norte, int $zona, bool $sul): array
    {
        return $this->geo->utmToLatLng((float) $este, (float) $norte, $zona, $sul ? 'S' : 'N');
    }

    /** Lê a primeira coordenada de um KML dentro do ZIP da base. @return array{0: ?string, 1: ?string} [latitude, longitude] */
    private function extrairCoordenadasDoKml(string $caminhoZip): array
    {
        if (! class_exists('ZipArchive')) {
            return [null, null];
        }

        $zip = new \ZipArchive();
        if ($zip->open($caminhoZip) !== true) {
            return [null, null];
        }

        $latitude = $longitude = null;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (pathinfo($filename, PATHINFO_EXTENSION) !== 'kml') {
                continue;
            }

            $dom = new \DOMDocument();
            @$dom->loadXML($zip->getFromIndex($i));
            $coordsTags = $dom->getElementsByTagName('coordinates');

            if ($coordsTags->length > 0) {
                $partes = explode(',', trim($coordsTags->item(0)->nodeValue));
                if (count($partes) >= 2) {
                    $longitude = trim($partes[0]);
                    $latitude = trim($partes[1]);
                }
            }

            break;
        }

        $zip->close();

        return [$latitude, $longitude];
    }

    private function normalizarDecimal($valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $valor = trim((string) $valor);

        return $valor === '' ? null : str_replace(',', '.', $valor);
    }

    private function ehDecimalValido($valor): bool
    {
        return $valor !== null && is_numeric($valor);
    }
}

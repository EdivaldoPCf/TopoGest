<?php

namespace App\Http\Controllers;

use App\Services\SigefOdsParserService;
use App\Services\GeoGeometryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminGeradorController extends Controller
{
    protected $parserService;
    protected $geoService;

    public function __construct(SigefOdsParserService $parserService, GeoGeometryService $geoService)
    {
        $this->parserService = $parserService;
        $this->geoService = $geoService;
    }

    /**
     * Exibe a tela inicial de upload da planilha.
     */
    public function index()
    {
        return view('admin.gerador.index');
    }

    /**
     * Processa a planilha ODS na hora, salva temporariamente e exibe os resultados na tela.
     */
    public function processar(Request $request)
    {
        $request->validate([
            'arquivo_ods' => 'required|file|max:10240',
        ]);

        $file = $request->file('arquivo_ods');

        // Cria o diretório temporário se não existir
        if (!Storage::disk('local')->exists('temp')) {
            Storage::disk('local')->makeDirectory('temp');
        }

        $tempId = Str::uuid()->toString();
        $filename = $tempId . '.ods';
        
        // Salva o arquivo temporário
        Storage::disk('local')->putFileAs('temp', $file, $filename);
        $absolutePath = Storage::disk('local')->path('temp/' . $filename);

        try {
            $dadosOds = $this->parserService->parseOdsFile($absolutePath);
            $identificacao = $dadosOds['identificacao'];
            $verticesRaw = $dadosOds['vertices'];
        } catch (\Throwable $e) {
            // Remove o arquivo temporário se falhar
            Storage::disk('local')->delete('temp/' . $filename);
            return redirect()->back()->with('error', 'Erro ao ler a planilha: ' . $e->getMessage());
        }

        if (empty($verticesRaw)) {
            Storage::disk('local')->delete('temp/' . $filename);
            return redirect()->back()->with('error', 'Nenhum vértice válido encontrado na planilha.');
        }

        $vertices = [];
        $utmZone = 20; 
        $hemisphere = 'S';

        foreach ($verticesRaw as $v) {
            if ($v['tipo'] === 'geodesica') {
                $utmZone = (int) floor(($v['E'] + 180) / 6) + 1;
                $hemisphere = $v['N'] < 0 ? 'S' : 'N';
                break;
            }
        }

        foreach ($verticesRaw as $v) {
            $easting = 0.0;
            $northing = 0.0;
            $latitude = 0.0;
            $longitude = 0.0;

            if ($v['tipo'] === 'utm') {
                $easting = (float) $v['E'];
                $northing = (float) $v['N'];
                $latLng = $this->geoService->utmToLatLng($easting, $northing, $utmZone, $hemisphere);
                $latitude = $latLng['latitude'];
                $longitude = $latLng['longitude'];
            } else {
                $latitude = (float) $v['N'];
                $longitude = (float) $v['E'];
                $utm = $this->geoService->latLngToUtm($latitude, $longitude, $utmZone);
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

        $imovel = $identificacao['imovel'] ?? 'Nome do Imóvel não definido';
        $detentor = $identificacao['detentor'] ?? 'Proprietário não definido';
        $cpfCnpj = $identificacao['cpf_cnpj'] ?? 'Não informado';
        $municipio = $identificacao['municipio'] ?? 'Não informado';
        $area = $identificacao['area_ha'] ?? '0.0000';
        $sncr = $identificacao['sncr'] ?? 'Não informado';

        $totalVertices = count($vertices);
        $linhas = [];

        $linhas[] = "MEMORIAL DESCRITIVO";
        $linhas[] = "================================================================================";
        $linhas[] = "IMÓVEL: " . mb_strtoupper($imovel);
        $linhas[] = "PROPRIETÁRIO: " . mb_strtoupper($detentor);
        $linhas[] = "CPF/CNPJ: " . $cpfCnpj;
        $linhas[] = "MUNICÍPIO/UF: " . mb_strtoupper($municipio);
        $linhas[] = "ÁREA CERTIFICADA: " . $area . " ha";
        $linhas[] = "CÓDIGO SNCR/INCRA: " . $sncr;
        $linhas[] = "================================================================================";
        $linhas[] = "";

        // Plain Text Description (no HTML tags)
        $desc = "Inicia-se a descrição deste perímetro no vértice " . $vertices[0]['codigo'] . ", de coordenadas (Longitude: " . $this->decToDmsPhp($vertices[0]['lon'], false) . ", Latitude: " . $this->decToDmsPhp($vertices[0]['lat'], true) . " e Altitude: " . number_format((float)($vertices[0]['altitude'] ?? 0.0), 2, ',', '.') . " m); " . ($vertices[0]['limite'] ?: 'Linha ideal') . "; deste, segue confrontando com o " . ($vertices[0]['confrontante'] ?: 'Limite do Imóvel') . ", com os seguintes azimutes e distâncias: ";

        for ($i = 0; $i < $totalVertices; $i++) {
            $atual = $vertices[$i];
            $proximo = $vertices[($i + 1) % $totalVertices];

            $dist = $this->geoService->calcularDistancia($atual['x'], $atual['y'], $proximo['x'], $proximo['y']);
            $azi = $this->geoService->calcularAzimute($atual['x'], $atual['y'], $proximo['x'], $proximo['y']);

            $limite = $proximo['limite'] ?: 'Linha ideal';
            $confrontante = $proximo['confrontante'] ?: 'Limite do Imóvel';

            $desc .= $azi . " e " . number_format($dist, 2, ',', '.') . " m até o vértice " . $proximo['codigo'];

            if (($i + 1) === $totalVertices) {
                $desc .= ", ponto inicial da descrição deste perímetro.";
            } else {
                $desc .= ", (Longitude: " . $this->decToDmsPhp($proximo['lon'], false) . ", Latitude: " . $this->decToDmsPhp($proximo['lat'], true) . " e Altitude: " . number_format((float)($proximo['altitude'] ?? 0.0), 2, ',', '.') . " m); " . $limite . "; deste, segue confrontando com o " . $confrontante . ", com os seguintes azimutes e distâncias: ";
            }
        }

        // HTML Description (with <strong> tags)
        $memorialHtml = "Inicia-se a descrição deste perímetro no vértice <strong>" . htmlspecialchars($vertices[0]['codigo']) . "</strong>, de coordenadas (Longitude: " . $this->decToDmsPhp($vertices[0]['lon'], false) . ", Latitude: " . $this->decToDmsPhp($vertices[0]['lat'], true) . " e Altitude: " . number_format((float)($vertices[0]['altitude'] ?? 0.0), 2, ',', '.') . " m); " . htmlspecialchars($vertices[0]['limite'] ?: 'Linha ideal') . "; deste, segue confrontando com o <strong>" . htmlspecialchars($vertices[0]['confrontante'] ?: 'Limite do Imóvel') . "</strong>, com os seguintes azimutes e distâncias: ";

        for ($i = 0; $i < $totalVertices; $i++) {
            $atual = $vertices[$i];
            $proximo = $vertices[($i + 1) % $totalVertices];

            $dist = $this->geoService->calcularDistancia($atual['x'], $atual['y'], $proximo['x'], $proximo['y']);
            $azi = $this->geoService->calcularAzimute($atual['x'], $atual['y'], $proximo['x'], $proximo['y']);

            $limite = $proximo['limite'] ?: 'Linha ideal';
            $confrontante = $proximo['confrontante'] ?: 'Limite do Imóvel';

            $memorialHtml .= "<strong>" . htmlspecialchars($azi) . "</strong> e <strong>" . number_format($dist, 2, ',', '.') . " m</strong> até o vértice <strong>" . htmlspecialchars($proximo['codigo']) . "</strong>";

            if (($i + 1) === $totalVertices) {
                $memorialHtml .= ", ponto inicial da descrição deste perímetro.";
            } else {
                $memorialHtml .= ", (Longitude: " . $this->decToDmsPhp($proximo['lon'], false) . ", Latitude: " . $this->decToDmsPhp($proximo['lat'], true) . " e Altitude: " . number_format((float)($proximo['altitude'] ?? 0.0), 2, ',', '.') . " m); " . htmlspecialchars($limite) . "; deste, segue confrontando com o <strong>" . htmlspecialchars($confrontante) . "</strong>, com os seguintes azimutes e distâncias: ";
            }
        }

        $linhas[] = wordwrap($desc, 80, "\n");
        $linhas[] = "";
        $linhas[] = "Todas as coordenadas aqui descritas estão georreferenciadas ao Sistema Geodésico Brasileiro tendo como datum o SIRGAS2000. A área foi obtida pelas coordenadas cartesianas locais, referenciada ao Sistema Geodésico Local (SGL-SIGEF). Todos os azimutes foram calculados pela fórmula do Problema Geodésico Inverso (Puissant). Perímetro e Distâncias foram calculados pelas coordenadas cartesianas geocêntricas.";
        $linhas[] = "";
        $linhas[] = "================================================================================";
        $linhas[] = "Gerado eletronicamente por TopoGest em " . date('d/m/Y H:i:s');
        $linhas[] = "================================================================================";

        $memorialTexto = implode("\n", $linhas);

        return view('admin.gerador.index', compact('tempId', 'identificacao', 'vertices', 'memorialTexto', 'memorialHtml', 'utmZone', 'hemisphere'));
    }

    /**
     * Força o download do arquivo DXF temporário
     */
    public function dxf($tempId)
    {
        $filename = $tempId . '.ods';
        if (!Storage::disk('local')->exists('temp/' . $filename)) {
            return redirect()->route('admin.gerador.index')->with('error', 'Arquivo expirado ou não encontrado.');
        }

        $absolutePath = Storage::disk('local')->path('temp/' . $filename);

        try {
            $dadosOds = $this->parserService->parseOdsFile($absolutePath);
            $identificacao = $dadosOds['identificacao'];
            $verticesRaw = $dadosOds['vertices'];
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Erro ao processar o arquivo.');
        }

        $vertices = [];
        $utmZone = 20;

        foreach ($verticesRaw as $v) {
            if ($v['tipo'] === 'geodesica') {
                $utmZone = (int) floor(($v['E'] + 180) / 6) + 1;
                break;
            }
        }

        foreach ($verticesRaw as $v) {
            $easting = 0.0;
            $northing = 0.0;

            if ($v['tipo'] === 'utm') {
                $easting = (float) $v['E'];
                $northing = (float) $v['N'];
            } else {
                $utm = $this->geoService->latLngToUtm((float) $v['N'], (float) $v['E'], $utmZone);
                $easting = $utm['easting'];
                $northing = $utm['northing'];
            }

            $vertices[] = [
                'codigo' => $v['codigo'],
                'x' => $easting,
                'y' => $northing,
                'z' => (float) ($v['altitude'] ?? 0.0),
            ];
        }

        $dxf = [];
        $dxf[] = "  0";
        $dxf[] = "SECTION";
        $dxf[] = "  2";
        $dxf[] = "HEADER";
        $dxf[] = "  0";
        $dxf[] = "ENDSEC";
        
        $dxf[] = "  0";
        $dxf[] = "SECTION";
        $dxf[] = "  2";
        $dxf[] = "TABLES";
        
        $dxf[] = "  0";
        $dxf[] = "TABLE";
        $dxf[] = "  2";
        $dxf[] = "LAYER";
        $dxf[] = " 70";
        $dxf[] = "3";
        
        $dxf[] = "  0";
        $dxf[] = "LAYER";
        $dxf[] = "  2";
        $dxf[] = "PERIMETRO";
        $dxf[] = " 70";
        $dxf[] = "0";
        $dxf[] = " 62";
        $dxf[] = "3"; // Verde
        
        $dxf[] = "  0";
        $dxf[] = "LAYER";
        $dxf[] = "  2";
        $dxf[] = "VERTICES";
        $dxf[] = " 70";
        $dxf[] = "0";
        $dxf[] = " 62";
        $dxf[] = "5"; // Azul
        
        $dxf[] = "  0";
        $dxf[] = "LAYER";
        $dxf[] = "  2";
        $dxf[] = "TEXTOS_VERTICES";
        $dxf[] = " 70";
        $dxf[] = "0";
        $dxf[] = " 62";
        $dxf[] = "7"; // Branco
        
        $dxf[] = "  0";
        $dxf[] = "ENDTAB";
        $dxf[] = "  0";
        $dxf[] = "ENDSEC";
        
        $dxf[] = "  0";
        $dxf[] = "SECTION";
        $dxf[] = "  2";
        $dxf[] = "ENTITIES";

        $dxf[] = "  0";
        $dxf[] = "LWPOLYLINE";
        $dxf[] = "  8";
        $dxf[] = "PERIMETRO";
        $dxf[] = " 90";
        $dxf[] = count($vertices);
        $dxf[] = " 70";
        $dxf[] = "1";
        $dxf[] = " 43";
        $dxf[] = "0.0";

        foreach ($vertices as $v) {
            $dxf[] = " 10";
            $dxf[] = sprintf("%.6f", $v['x']);
            $dxf[] = " 20";
            $dxf[] = sprintf("%.6f", $v['y']);
        }

        foreach ($vertices as $v) {
            $dxf[] = "  0";
            $dxf[] = "POINT";
            $dxf[] = "  8";
            $dxf[] = "VERTICES";
            $dxf[] = " 10";
            $dxf[] = sprintf("%.6f", $v['x']);
            $dxf[] = " 20";
            $dxf[] = sprintf("%.6f", $v['y']);
            $dxf[] = " 30";
            $dxf[] = sprintf("%.6f", $v['z']);

            $dxf[] = "  0";
            $dxf[] = "TEXT";
            $dxf[] = "  8";
            $dxf[] = "TEXTOS_VERTICES";
            $dxf[] = " 10";
            $dxf[] = sprintf("%.6f", $v['x'] + 1.5);
            $dxf[] = " 20";
            $dxf[] = sprintf("%.6f", $v['y'] + 1.5);
            $dxf[] = " 40";
            $dxf[] = "2.5";
            $dxf[] = "  1";
            $dxf[] = $v['codigo'];
            $dxf[] = " 50";
            $dxf[] = "0.0";
        }

        $dxf[] = "  0";
        $dxf[] = "ENDSEC";
        $dxf[] = "  0";
        $dxf[] = "EOF";

        $dxfContent = implode("\r\n", $dxf);
        $imovel = $identificacao['imovel'] ?? 'planta';
        $dxfFilename = str_replace(' ', '_', mb_strtolower($imovel)) . '_planta.dxf';

        return response($dxfContent, 200)
            ->header('Content-Type', 'image/vnd.dxf')
            ->header('Content-Disposition', 'attachment; filename="' . $dxfFilename . '"');
    }

    /**
     * Força o download do memorial descritivo temporário
     */
    public function txt($tempId)
    {
        $filename = $tempId . '.ods';
        if (!Storage::disk('local')->exists('temp/' . $filename)) {
            return redirect()->route('admin.gerador.index')->with('error', 'Arquivo expirado ou não encontrado.');
        }

        $absolutePath = Storage::disk('local')->path('temp/' . $filename);

        try {
            $dadosOds = $this->parserService->parseOdsFile($absolutePath);
            $identificacao = $dadosOds['identificacao'];
            $verticesRaw = $dadosOds['vertices'];
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Erro ao processar o arquivo.');
        }

        $vertices = [];
        $utmZone = 20;
        $hemisphere = 'S';

        foreach ($verticesRaw as $v) {
            if ($v['tipo'] === 'geodesica') {
                $utmZone = (int) floor(($v['E'] + 180) / 6) + 1;
                $hemisphere = $v['N'] < 0 ? 'S' : 'N';
                break;
            }
        }

        foreach ($verticesRaw as $v) {
            $easting = 0.0;
            $northing = 0.0;
            $latitude = 0.0;
            $longitude = 0.0;

            if ($v['tipo'] === 'utm') {
                $easting = (float) $v['E'];
                $northing = (float) $v['N'];
                $latLng = $this->geoService->utmToLatLng($easting, $northing, $utmZone, $hemisphere);
                $latitude = $latLng['latitude'];
                $longitude = $latLng['longitude'];
            } else {
                $latitude = (float) $v['N'];
                $longitude = (float) $v['E'];
                $utm = $this->geoService->latLngToUtm($latitude, $longitude, $utmZone);
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

        $imovel = $identificacao['imovel'] ?? 'Nome do Imóvel não definido';
        $detentor = $identificacao['detentor'] ?? 'Proprietário não definido';
        $cpfCnpj = $identificacao['cpf_cnpj'] ?? 'Não informado';
        $municipio = $identificacao['municipio'] ?? 'Não informado';
        $area = $identificacao['area_ha'] ?? '0.0000';
        $sncr = $identificacao['sncr'] ?? 'Não informado';

        $totalVertices = count($vertices);
        $linhas = [];

        $linhas[] = "MEMORIAL DESCRITIVO";
        $linhas[] = "================================================================================";
        $linhas[] = "IMÓVEL: " . mb_strtoupper($imovel);
        $linhas[] = "PROPRIETÁRIO: " . mb_strtoupper($detentor);
        $linhas[] = "CPF/CNPJ: " . $cpfCnpj;
        $linhas[] = "MUNICÍPIO/UF: " . mb_strtoupper($municipio);
        $linhas[] = "ÁREA CERTIFICADA: " . $area . " ha";
        $linhas[] = "CÓDIGO SNCR/INCRA: " . $sncr;
        $linhas[] = "================================================================================";
        $linhas[] = "";

        // Plain Text Description (no HTML tags)
        $desc = "Inicia-se a descrição deste perímetro no vértice " . $vertices[0]['codigo'] . ", de coordenadas (Longitude: " . $this->decToDmsPhp($vertices[0]['lon'], false) . ", Latitude: " . $this->decToDmsPhp($vertices[0]['lat'], true) . " e Altitude: " . number_format((float)($vertices[0]['altitude'] ?? 0.0), 2, ',', '.') . " m); " . ($vertices[0]['limite'] ?: 'Linha ideal') . "; deste, segue confrontando com o " . ($vertices[0]['confrontante'] ?: 'Limite do Imóvel') . ", com os seguintes azimutes e distâncias: ";

        for ($i = 0; $i < $totalVertices; $i++) {
            $atual = $vertices[$i];
            $proximo = $vertices[($i + 1) % $totalVertices];

            $dist = $this->geoService->calcularDistancia($atual['x'], $atual['y'], $proximo['x'], $proximo['y']);
            $azi = $this->geoService->calcularAzimute($atual['x'], $atual['y'], $proximo['x'], $proximo['y']);

            $limite = $proximo['limite'] ?: 'Linha ideal';
            $confrontante = $proximo['confrontante'] ?: 'Limite do Imóvel';

            $desc .= $azi . " e " . number_format($dist, 2, ',', '.') . " m até o vértice " . $proximo['codigo'];

            if (($i + 1) === $totalVertices) {
                $desc .= ", ponto inicial da descrição deste perímetro.";
            } else {
                $desc .= ", (Longitude: " . $this->decToDmsPhp($proximo['lon'], false) . ", Latitude: " . $this->decToDmsPhp($proximo['lat'], true) . " e Altitude: " . number_format((float)($proximo['altitude'] ?? 0.0), 2, ',', '.') . " m); " . $limite . "; deste, segue confrontando com o " . $confrontante . ", com os seguintes azimutes e distâncias: ";
            }
        }

        $linhas[] = wordwrap($desc, 80, "\n");
        $linhas[] = "";
        $linhas[] = "Todas as coordenadas aqui descritas estão georreferenciadas ao Sistema Geodésico Brasileiro tendo como datum o SIRGAS2000. A área foi obtida pelas coordenadas cartesianas locais, referenciada ao Sistema Geodésico Local (SGL-SIGEF). Todos os azimutes foram calculados pela fórmula do Problema Geodésico Inverso (Puissant). Perímetro e Distâncias foram calculados pelas coordenadas cartesianas geocêntricas.";
        $linhas[] = "";
        $linhas[] = "================================================================================";
        $linhas[] = "Gerado eletronicamente por TopoGest em " . date('d/m/Y H:i:s');
        $linhas[] = "================================================================================";

        $textoCompleto = implode("\n", $linhas);
        $txtFilename = str_replace(' ', '_', mb_strtolower($imovel)) . '_memorial.txt';

        return response($textoCompleto, 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $txtFilename . '"');
    }

    /**
     * Converte coordenada decimal em GMS (Grau, Minuto, Segundo) formatado.
     */
    protected function decToDmsPhp($dec, $isLat = true)
    {
        $absDec = abs($dec);
        $degrees = floor($absDec);
        $minutesDecimal = ($absDec - $degrees) * 60;
        $minutes = floor($minutesDecimal);
        $seconds = ($minutesDecimal - $minutes) * 60;
        $seconds = round($seconds, 3);
        
        if ($seconds >= 60) {
            $seconds = 0;
            $minutes += 1;
        }
        if ($minutes >= 60) {
            $minutes = 0;
            $degrees += 1;
        }
        
        $secInt = floor($seconds);
        $secDec = round(($seconds - $secInt) * 1000);
        $secondsStr = sprintf("%02d,%03d", $secInt, $secDec);
        
        if ($isLat) {
            $suffix = $dec < 0 ? ' S' : ' N';
        } else {
            $suffix = $dec < 0 ? ' W' : ' E';
        }
        return $degrees . '°' . sprintf('%02d', $minutes) . "'" . $secondsStr . '"' . $suffix;
    }
}

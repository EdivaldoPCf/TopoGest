<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\Pasta;
use App\Services\SigefOdsParserService;
use App\Services\GeoGeometryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SigefMapaController extends Controller
{
    protected $parserService;
    protected $geoService;

    public function __construct(SigefOdsParserService $parserService, GeoGeometryService $geoService)
    {
        $this->parserService = $parserService;
        $this->geoService = $geoService;
    }

    /**
     * Localiza o ODS do SIGEF em uma pasta (ou suas subpastas) e retorna
     * os vértices e metadados para desenhar o mapa.
     */
    public function parsear($pastaId)
    {
        $pasta = Pasta::with(['arquivos', 'subpastas.arquivos', 'subpastas.subpastas.arquivos'])
            ->findOrFail($pastaId);

        $arquivoOds = $this->encontrarOds($pasta);

        if (!$arquivoOds) {
            return response()->json(['erro' => 'Nenhum arquivo ODS encontrado nesta pasta.'], 404);
        }

        $caminho = Storage::disk('public')->path($arquivoOds->path);

        if (!file_exists($caminho)) {
            return response()->json(['erro' => 'Arquivo ODS não encontrado no servidor.'], 404);
        }

        try {
            $dadosOds = $this->parserService->parseOdsFile($caminho);
            $identificacao = $dadosOds['identificacao'];
            $vertices = $dadosOds['vertices'];
        } catch (\Throwable $e) {
            return response()->json(['erro' => 'Erro ao processar o ODS: ' . $e->getMessage()], 422);
        }

        if (empty($vertices)) {
            return response()->json(['erro' => 'Nenhum vértice encontrado na planilha ODS.'], 422);
        }

        // =========================================================================
        // Auto-save/update de marcos na base global
        // =========================================================================
        $imovelName = $identificacao['imovel'] ?? 'Imóvel Desconhecido';
        foreach ($vertices as $v) {
            $codigo = trim($v['codigo']);
            if (preg_match('/^([A-Z0-9]+)\-([A-Z])\-(.+)$/i', $codigo, $mParts)) {
                $cred = strtoupper($mParts[1]);
                $tipo = strtoupper($mParts[2]);
                $num = str_pad($mParts[3], 4, '0', STR_PAD_LEFT);

                $marcoExistente = \App\Models\Marco::where('credencial', $cred)
                    ->where('tipo', $tipo)
                    ->where('numero', $num)
                    ->first();

                if ($marcoExistente) {
                    // Já existe. Atualiza apenas se NÃO tiver coordenada.
                    if (is_null($marcoExistente->latitude) && is_null($marcoExistente->easting)) {
                        if ($v['tipo'] === 'geodesica') {
                            $marcoExistente->latitude = $v['N'];
                            $marcoExistente->longitude = $v['E'];
                        } else {
                            $marcoExistente->easting = $v['E'];
                            $marcoExistente->northing = $v['N'];
                        }
                        $marcoExistente->save();
                    }
                } else {
                    // Não existe. Insere novo.
                    $novoMarco = new \App\Models\Marco();
                    $novoMarco->user_id = auth()->id() ?? 1;
                    $novoMarco->credencial = $cred;
                    $novoMarco->tipo = $tipo;
                    $novoMarco->numero = $num;
                    $novoMarco->imovel = substr($imovelName, 0, 100);

                    if ($v['tipo'] === 'geodesica') {
                        $novoMarco->latitude = $v['N'];
                        $novoMarco->longitude = $v['E'];
                    } else {
                        $novoMarco->easting = $v['E'];
                        $novoMarco->northing = $v['N'];
                    }
                    $novoMarco->save();
                }
            }
        }
        // =========================================================================

        return response()->json([
            'arquivo_id'   => $arquivoOds->id,
            'arquivo_nome' => $arquivoOds->nome ?? $arquivoOds->nome_original,
            'imovel'       => $identificacao['imovel']   ?? null,
            'detentor'     => $identificacao['detentor'] ?? null,
            'cpf_cnpj'     => $identificacao['cpf_cnpj'] ?? null,
            'municipio'    => $identificacao['municipio'] ?? null,
            'area_ha'      => $identificacao['area_ha']  ?? null,
            'sncr'         => $identificacao['sncr']     ?? null,
            'vertices'     => $vertices,
        ]);
    }

    // -------------------------------------------------------------------------
    // Busca recursiva do arquivo ODS
    // -------------------------------------------------------------------------
    private function encontrarOds(Pasta $pasta): ?Arquivo
    {
        foreach ($pasta->arquivos as $arquivo) {
            $ext  = strtolower(pathinfo($arquivo->path ?? '', PATHINFO_EXTENSION));
            $tipo = strtolower($arquivo->tipo ?? '');
            if ($ext === 'ods' || $tipo === 'ods') {
                return $arquivo;
            }
        }

        foreach ($pasta->subpastas as $sub) {
            $sub->load(['arquivos', 'subpastas.arquivos']);
            $found = $this->encontrarOds($sub);
            if ($found) return $found;
        }

        return null;
    }

    /**
     * Gera o Memorial Descritivo no padrão oficial INCRA/SIGEF
     */
    public function gerarMemorial($pastaId, Request $request)
    {
        $pasta = Pasta::with(['arquivos', 'subpastas.arquivos', 'subpastas.subpastas.arquivos'])
            ->findOrFail($pastaId);

        $arquivoOds = $this->encontrarOds($pasta);
        if (!$arquivoOds) {
            return redirect()->back()->with('error', 'Nenhum arquivo ODS encontrado nesta pasta.');
        }

        $caminho = Storage::disk('public')->path($arquivoOds->path);
        if (!file_exists($caminho)) {
            return redirect()->back()->with('error', 'Arquivo ODS não encontrado no servidor.');
        }

        try {
            $dadosOds = $this->parserService->parseOdsFile($caminho);
            $identificacao = $dadosOds['identificacao'];
            $verticesRaw = $dadosOds['vertices'];
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Erro ao ler o arquivo ODS: ' . $e->getMessage());
        }

        if (empty($verticesRaw)) {
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

            if ($v['tipo'] === 'utm') {
                $easting = (float) $v['E'];
                $northing = (float) $v['N'];
            } else {
                $utm = $this->geoService->latLngToUtm((float) $v['N'], (float) $v['E'], $utmZone);
                $easting = $utm['easting'];
                $northing = $utm['northing'];
            }

            $vertices[] = array_merge($v, [
                'x' => $easting,
                'y' => $northing,
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

        $desc = "Inicia-se a descrição deste perímetro no vértice " . $vertices[0]['codigo'] . ", de coordenadas ";
        $desc .= "Este (E): " . number_format($vertices[0]['x'], 3, ',', '.') . " m e ";
        $desc .= "Norte (N): " . number_format($vertices[0]['y'], 3, ',', '.') . " m, ";
        $desc .= "situado no fuso UTM " . $utmZone . $hemisphere . " (Datum SIRGAS2000). ";

        for ($i = 0; $i < $totalVertices; $i++) {
            $atual = $vertices[$i];
            $proximo = $vertices[($i + 1) % $totalVertices];

            $dist = $this->geoService->calcularDistancia($atual['x'], $atual['y'], $proximo['x'], $proximo['y']);
            $azi = $this->geoService->calcularAzimute($atual['x'], $atual['y'], $proximo['x'], $proximo['y']);

            $limite = $atual['limite'] ?: 'Cerca';
            $confrontante = $atual['confrontante'] ?: 'Limite do Imóvel';

            $desc .= "Deste vértice, segue confrontando com " . mb_strtoupper($confrontante);
            $desc .= ", através do limite " . mb_strtolower($limite);
            $desc .= ", com azimute plano de " . $azi;
            $desc .= " e distância de " . number_format($dist, 2, ',', '.') . " metros, ";

            if (($i + 1) === $totalVertices) {
                $desc .= "até retornar ao vértice inicial " . $proximo['codigo'] . ", fechando assim o perímetro medido.";
            } else {
                $desc .= "até o vértice " . $proximo['codigo'] . ", de coordenadas ";
                $desc .= "E: " . number_format($proximo['x'], 3, ',', '.') . " m e ";
                $desc .= "N: " . number_format($proximo['y'], 3, ',', '.') . " m; ";
            }
        }

        $linhas[] = wordwrap($desc, 80, "\n");
        $linhas[] = "";
        $linhas[] = "================================================================================";
        $linhas[] = "Gerado eletronicamente por TopoGest em " . date('d/m/Y H:i:s');
        $linhas[] = "================================================================================";

        $textoCompleto = implode("\n", $linhas);

        if ($request->query('download') === 'txt') {
            $filename = str_replace(' ', '_', mb_strtolower($imovel)) . '_memorial.txt';
            return response($textoCompleto, 200)
                ->header('Content-Type', 'text/plain; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        }

        return response()->json([
            'texto' => $textoCompleto,
            'imovel' => $imovel,
            'vertices' => $vertices,
            'fuso' => $utmZone . $hemisphere,
        ]);
    }

    /**
     * Gera e baixa a Planta do perímetro em formato DXF para AutoCAD
     */
    public function gerarDxf($pastaId)
    {
        $pasta = Pasta::with(['arquivos', 'subpastas.arquivos', 'subpastas.subpastas.arquivos'])
            ->findOrFail($pastaId);

        $arquivoOds = $this->encontrarOds($pasta);
        if (!$arquivoOds) {
            return redirect()->back()->with('error', 'Nenhum arquivo ODS encontrado nesta pasta.');
        }

        $caminho = Storage::disk('public')->path($arquivoOds->path);
        if (!file_exists($caminho)) {
            return redirect()->back()->with('error', 'Arquivo ODS não encontrado no servidor.');
        }

        try {
            $dadosOds = $this->parserService->parseOdsFile($caminho);
            $identificacao = $dadosOds['identificacao'];
            $verticesRaw = $dadosOds['vertices'];
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Erro ao ler o arquivo ODS: ' . $e->getMessage());
        }

        if (empty($verticesRaw)) {
            return redirect()->back()->with('error', 'Nenhum vértice válido encontrado na planilha.');
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
        $filename = str_replace(' ', '_', mb_strtolower($imovel)) . '_planta.dxf';

        return response($dxfContent, 200)
            ->header('Content-Type', 'image/vnd.dxf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}

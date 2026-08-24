<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class SigefOdsParserService
{
    /**
     * Faz o parse do arquivo ODS do SIGEF e retorna os dados de identificação e vértices.
     */
    public function parseOdsFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("Arquivo não encontrado.");
        }

        $spreadsheet = IOFactory::load($filePath);

        $identificacao = $this->lerIdentificacao($spreadsheet);
        $vertices = $this->lerPerimetro($spreadsheet);

        return [
            'identificacao' => $identificacao,
            'vertices' => $vertices,
        ];
    }

    private function lerIdentificacao(Spreadsheet $spreadsheet): array
    {
        $nomeAbas = ['identificacao', 'identificação', 'Identificação', 'Identificacao', 'IDENTIFICAÇÃO'];
        $sheet = null;

        foreach ($nomeAbas as $nome) {
            $sheet = $spreadsheet->getSheetByName($nome);
            if ($sheet) break;
        }

        if (!$sheet) {
            $sheet = $spreadsheet->getSheet(0);
        }

        $dados = [];
        $highestRow = min($sheet->getHighestRow(), 100);

        $mapeamento = [
            'imovel'    => ['denominação', 'denominacao'],
            'detentor'  => ['nome:'],
            'cpf_cnpj'  => ['cpf:', 'cnpj:', 'cpf/cnpj'],
            'municipio' => ['município(s):', 'municipio(s):', 'município:', 'municipio:'],
            'area_ha'   => ['área (ha)', 'area (ha)', 'área total'],
            'sncr'      => ['código do imóvel', 'codigo do imovel', 'sncr'],
            'matricula' => ['matrícula:', 'matricula:', 'matrícula', 'matricula'],
            'cns'       => ['cns:', 'cns'],
        ];

        for ($row = 1; $row <= $highestRow; $row++) {
            $cellA = mb_strtolower(trim((string) $sheet->getCell('A' . $row)->getValue()));
            $cellB = trim((string) $sheet->getCell('B' . $row)->getValue());

            if (empty($cellA)) continue;

            foreach ($mapeamento as $campo => $palavras) {
                if (isset($dados[$campo])) continue;
                foreach ($palavras as $palavra) {
                    if (str_contains($cellA, $palavra)) {
                        if (!empty($cellB)) {
                            $dados[$campo] = $cellB;
                        } elseif ($row < $highestRow) {
                            $nextA = trim((string) $sheet->getCell('A' . ($row + 1))->getValue());
                            $nextB = trim((string) $sheet->getCell('B' . ($row + 1))->getValue());
                            $val = $nextB ?: $nextA;
                            if (!empty($val)) {
                                $dados[$campo] = $val;
                            }
                        }
                        break;
                    }
                }
            }

            if (!isset($dados['municipio'])) {
                if (preg_match('/^[A-Za-záàãâéèêíïóõôúüçñ ]+\-[A-Z]{2}$/', trim((string) $sheet->getCell('A' . $row)->getValue()))) {
                    $dados['municipio'] = trim((string) $sheet->getCell('A' . $row)->getValue());
                }
            }
        }

        return $dados;
    }

    private function lerPerimetro(Spreadsheet $spreadsheet): array
    {
        $sheet = null;
        foreach ($spreadsheet->getSheetNames() as $nome) {
            if (str_starts_with(mb_strtolower($nome), 'perimetro') ||
                str_starts_with(mb_strtolower($nome), 'perímetro') ||
                str_starts_with(mb_strtolower($nome), 'vertice') ||
                str_starts_with(mb_strtolower($nome), 'vértice')) {
                $sheet = $spreadsheet->getSheetByName($nome);
                break;
            }
        }

        if (!$sheet) {
            $count = $spreadsheet->getSheetCount();
            $sheet = $count > 1 ? $spreadsheet->getSheet(1) : $spreadsheet->getSheet(0);
        }

        $highestRow = $sheet->getHighestRow();

        $linhaHeader  = null;
        $colunaVertice = 'A';
        $colunaE       = 'B';
        $colunaN       = 'D';
        $colunaAlt     = 'F';
        $colunaMetodo  = 'H';
        $colunaConf    = 'I';
        $colunaLim     = 'J';
        $tipoCoord     = 'dms';

        for ($row = 1; $row <= min($highestRow, 20); $row++) {
            $cellA = mb_strtolower(trim((string) $sheet->getCell('A' . $row)->getValue()));

            if (str_contains($cellA, 'vértice') || str_contains($cellA, 'vertice') || $cellA === 'vértice') {
                $linhaHeader = $row;

                for ($col = 'A'; $col <= 'Z'; $col++) {
                    $v = mb_strtolower(trim((string) $sheet->getCell($col . $row)->getValue()));
                    if (str_contains($v, 'e/long') || $v === 'e' || str_contains($v, 'longitude') || str_contains($v, 'leste')) {
                        $colunaE = $col;
                    }
                    if (str_contains($v, 'n/lat') || $v === 'n' || str_contains($v, 'latitude') || str_contains($v, 'norte')) {
                        $colunaN = $col;
                    }
                    if (str_contains($v, 'altitude') || str_contains($v, 'altura') || $v === 'h') {
                        $colunaAlt = $col;
                    }
                    if (str_contains($v, 'médodo') || str_contains($v, 'metodo') || str_contains($v, 'posic')) {
                        $colunaMetodo = $col;
                    }
                    if ((str_contains($v, 'confrontante') || str_contains($v, 'confronto') || str_contains($v, 'vizinho') || str_contains($v, 'confinante') || str_contains($v, 'descrição') || str_contains($v, 'descricao') || str_contains($v, 'descritivo')) && !str_contains($v, 'tipo')) {
                        $colunaConf = $col;
                    }
                    if (str_contains($v, 'limite') || str_contains($v, 'tipo de divisa') || str_contains($v, 'código limite') || str_contains($v, 'tipo de confrontante')) {
                        $colunaLim = $col;
                    }
                    if ($col === 'Z') break;
                }
                break;
            }
        }

        for ($row = 1; $row <= min($highestRow, 15); $row++) {
            $cellA = mb_strtolower(trim((string) $sheet->getCell('A' . $row)->getValue()));
            $cellB = mb_strtolower(trim((string) $sheet->getCell('B' . $row)->getValue()));
            if (str_contains($cellA, 'tipo de coordenada')) {
                if (str_contains($cellB, 'geogr')) {
                    $tipoCoord = 'dms';
                } elseif (str_contains($cellB, 'utm')) {
                    $tipoCoord = 'utm';
                }
                break;
            }
        }

        if (!$linhaHeader) {
            return [];
        }

        $vertices = [];

        for ($row = $linhaHeader + 1; $row <= $highestRow; $row++) {
            $codigoVertice = trim((string) $sheet->getCell($colunaVertice . $row)->getValue());

            if (empty($codigoVertice)) continue;

            if (mb_strtolower($codigoVertice) === 'vértice' || mb_strtolower($codigoVertice) === 'vertice') continue;

            $valE = trim((string) $sheet->getCell($colunaE . $row)->getValue());
            $valN = trim((string) $sheet->getCell($colunaN . $row)->getValue());

            if (empty($valE) || empty($valN)) continue;

            $lon = $this->parsearCoordenada($valE, 'longitude');
            $lat = $this->parsearCoordenada($valN, 'latitude');

            if ($lon === null || $lat === null) continue;

            $isUtm = ($tipoCoord === 'utm' || ($lat > 100000 || $lon > 100000));

            if ($isUtm) {
                if ($lat < 5000000 || $lat > 10000000 || $lon < 100000 || $lon > 900000) continue;
            } else {
                if ($lat < -36 || $lat > 7 || $lon < -76 || $lon > -28) continue;
            }

            $valAlt = trim((string) $sheet->getCell($colunaAlt . $row)->getValue());
            $valMetodo = trim((string) $sheet->getCell($colunaMetodo . $row)->getValue());
            $valConf = trim((string) $sheet->getCell($colunaConf . $row)->getValue());
            $valLim = trim((string) $sheet->getCell($colunaLim . $row)->getValue());

            $vertices[] = [
                'codigo'       => $codigoVertice,
                'E'            => $lon,
                'N'            => $lat,
                'tipo'         => $isUtm ? 'utm' : 'geodesica',
                'altitude'     => $valAlt ?: '0.00',
                'metodo'       => $valMetodo ?: '—',
                'confrontante' => $valConf ?: 'SEM CONFRONTANTE CADASTRADO',
                'limite'       => $valLim ?: 'Muro/Cerca/Outros',
            ];
        }

        return $vertices;
    }

    private function parsearCoordenada(string $valor, string $tipo): ?float
    {
        $valor = trim($valor);
        if (empty($valor)) return null;

        $soNumerosVirgula = str_replace(',', '.', $valor);
        if (is_numeric($soNumerosVirgula)) {
            $dec = (float) $soNumerosVirgula;
            if ($tipo === 'longitude' && $dec > 0 && $dec < 80) {
                $dec = -$dec;
            }
            if ($tipo === 'latitude'  && $dec > 0 && $dec < 36) {
                $dec = -$dec;
            }
            return $dec;
        }

        $limpo = str_replace(['°', "'", '"', ','], [' ', ' ', ' ', '.'], $valor);
        $limpo = preg_replace('/\s+/', ' ', trim($limpo));

        $direcao = null;
        if (preg_match('/([NSEW])$/i', $limpo, $m)) {
            $direcao = strtoupper($m[1]);
            $limpo = trim(substr($limpo, 0, -1));
        } elseif (preg_match('/^([NSEW])/i', $limpo, $m)) {
            $direcao = strtoupper($m[1]);
            $limpo = trim(substr($limpo, 1));
        }

        $partes = array_filter(explode(' ', $limpo), fn($p) => $p !== '');
        $partes = array_values($partes);

        if (count($partes) < 2) return null;

        $graus    = (float) ($partes[0] ?? 0);
        $minutos  = (float) ($partes[1] ?? 0);
        $segundos = (float) ($partes[2] ?? 0);

        $decimal = $graus + ($minutos / 60) + ($segundos / 3600);

        if ($direcao === 'W' || $direcao === 'S') {
            $decimal = -$decimal;
        }

        if ($direcao === null) {
            if ($tipo === 'longitude' && $decimal > 0) $decimal = -$decimal;
            if ($tipo === 'latitude'  && $decimal > 0) $decimal = -$decimal;
        }

        return $decimal;
    }
}

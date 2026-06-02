<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Marco;
use ZipArchive;
use Exception;
use Illuminate\Support\Facades\DB;

class ImportMarcosOdsCommand extends Command
{
    protected $signature = 'marcos:import-ods {dir}';
    protected $description = 'Importa marcos de todos os ODS em um diretorio';

    public function handle()
    {
        $dir = $this->argument('dir');
        
        if (!is_dir($dir)) {
            $this->error("Diretorio nao encontrado: $dir");
            return;
        }

        $files = glob($dir . DIRECTORY_SEPARATOR . '*.ods');
        $files = array_merge($files, glob($dir . DIRECTORY_SEPARATOR . '*.ODS'));
        $files = array_unique($files);

        $this->info("Encontrados " . count($files) . " arquivos ODS.");

        foreach ($files as $file) {
            $this->processOds($file);
        }
        
        $this->info("Finalizado.");
    }

    private function processOds($file)
    {
        $zip = new ZipArchive;
        if ($zip->open($file) !== TRUE) {
            return;
        }

        $content = $zip->getFromName('content.xml');
        $zip->close();

        if (!$content) {
            return;
        }

        $contentWithSpaces = str_replace(['><', '</text:p>'], ['> <', ' </text:p>'], $content);
        $text = strip_tags($contentWithSpaces);

        // Extrair tabelas para tentar pegar nome do imovel
        preg_match_all('/<table:table[^>]*table:name="([^"]+)"[^>]*>(.*?)<\/table:table>/is', $content, $tables);
        $imovel = $this->extractImovelName($tables);

        if (!$imovel) {
            if (preg_match('/Denomina[cç][aã]o:\s*([^\n<>\|]+)/i', $text, $match)) {
                $imovel = trim($match[1]);
            } else if (preg_match('/Denomina[cç][aã]o\s*([^\n<>\|]+)/i', $text, $match)) {
                $imovel = trim($match[1]);
            } else if (preg_match('/Im[oó]vel:\s*([^\n<>\|]+)/i', $text, $match)) {
                $imovel = trim($match[1]);
            } else {
                $imovel = basename($file, '.ods');
                $imovel = basename($imovel, '.ODS');
            }
        }

        // Pega todos os matches que pareçam marcos ou codigos (Global Fallback)
        $marcosEncontrados = [];
        if (preg_match_all('/([A-Z0-9]+)\-([A-Z])\-(\d{1,5})(?![0-9])/i', $text, $matches)) {
            foreach ($matches[0] as $m) {
                $marcosEncontrados[] = $m;
            }
        }
        
        if (empty($marcosEncontrados)) {
            if (preg_match('/V[eé]rtice(.*)/is', $text, $afterVertice)) {
                $subText = $afterVertice[1];
                $words = preg_split('/\s+/', trim($subText));
                $count = 0;
                foreach ($words as $word) {
                    $word = trim($word);
                    if (preg_match('/^[0-9]{3,}$/', $word)) {
                        $marcosEncontrados[] = $word;
                        $count++;
                    } else if (preg_match('/^[A-Z0-9]+\-[A-Z0-9\-]+$/i', $word)) {
                        $marcosEncontrados[] = $word;
                        $count++;
                    }
                    if ($count > 200) break;
                    if (stripos($word, 'Sistema') !== false) break;
                }
            }
        }

        $marcosData = [];
        $marcosEncontrados = array_unique($marcosEncontrados);

        // Preenche o array base de marcos
        foreach ($marcosEncontrados as $marcoRaw) {
            $credencial = 'BCA';
            $tipo = 'M';
            $numero = $marcoRaw;
            
            if (preg_match('/^([A-Z0-9]+)\-([A-Z])\-(.+)$/i', $marcoRaw, $mParts)) {
                $credencial = strtoupper($mParts[1]);
                $tipo = strtoupper($mParts[2]);
                $numero = str_pad($mParts[3], 4, '0', STR_PAD_LEFT);
            } else if (preg_match('/^([A-Z])\-(.+)$/i', $marcoRaw, $mParts)) {
                $tipo = strtoupper($mParts[1]);
                $numero = str_pad($mParts[2], 4, '0', STR_PAD_LEFT);
            } else if (preg_match('/^[0-9]+$/', $marcoRaw)) {
                $numero = str_pad($marcoRaw, 4, '0', STR_PAD_LEFT);
            }

            if ($credencial === 'ATN') {
                continue;
            }

            $chave = "{$credencial}-{$tipo}-{$numero}";
            $marcosData[$chave] = [
                'user_id' => 1,
                'credencial' => $credencial,
                'tipo' => $tipo,
                'numero' => $numero,
                'imovel' => $imovel,
                'latitude' => null,
                'longitude' => null,
                'easting' => null,
                'northing' => null,
                'meridiano_central' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Tentar enriquecer com coordenadas lendo as tabelas
        $mc = null;
        if (!empty($tables) && isset($tables[1])) {
            foreach ($tables[1] as $index => $tableName) {
                $tableHtml = $tables[2][$index];

                if (preg_match('/Meridiano Central[^>]*>.*?([-\d]+)/is', $tableHtml, $mcMatch)) {
                    $mc = (int)$mcMatch[1];
                }

                if (stripos($tableName, 'perimetro') !== false || stripos($tableName, 'perímetro') !== false || stripos($tableName, 'Vertices') !== false || stripos($tableName, 'Lote') !== false) {
                    if (!preg_match_all('/<table:table-row[^>]*>(.*?)<\/table:table-row>/is', $tableHtml, $rows)) {
                        continue;
                    }

                    $started = false;
                    $e_col = -1;
                    $n_col = -1;

                    foreach ($rows[1] as $rowHtml) {
                        if (!preg_match_all('/<table:table-cell[^>]*>(.*?)<\/table:table-cell>/is', $rowHtml, $cells)) continue;

                        $rowText = [];
                        foreach ($cells[1] as $cellHtml) {
                            $txt = trim(strip_tags($cellHtml));
                            if ($txt !== '') $rowText[] = $txt;
                        }

                        if (empty($rowText)) continue;

                        if (!$started && (stripos($rowText[0], 'Vértice') !== false || stripos($rowText[0], 'Vertice') !== false)) {
                            $started = true;
                            foreach ($rowText as $i => $h) {
                                $hUpper = strtoupper(trim($h));
                                if ($hUpper == 'E' || $hUpper == 'E/LONG') $e_col = $i;
                                if ($hUpper == 'N' || $hUpper == 'N/LAT') $n_col = $i;
                            }
                            continue;
                        }

                        if ($started) {
                            $marcoRawTable = $rowText[0];
                            if (preg_match('/^([A-Z0-9]+)-([A-Z])-(.+)$/i', $marcoRawTable, $parts)) {
                                $c = strtoupper($parts[1]);
                                $t = strtoupper($parts[2]);
                                
                                if ($c === 'ATN') {
                                    continue;
                                }

                                $n = str_pad($parts[3], 4, '0', STR_PAD_LEFT);
                                $chave = "{$c}-{$t}-{$n}";

                                if (isset($marcosData[$chave])) {
                                    $e_val = isset($rowText[$e_col]) ? $rowText[$e_col] : '';
                                    $n_val = isset($rowText[$n_col]) ? $rowText[$n_col] : '';
                                    
                                    if (stripos($e_val, ' W') !== false || stripos($e_val, ' E') !== false) {
                                        $marcosData[$chave]['longitude'] = $this->dmsToDd($e_val);
                                        $marcosData[$chave]['latitude'] = $this->dmsToDd($n_val);
                                    } else if ($e_val != '') {
                                        $marcosData[$chave]['easting'] = str_replace(',', '.', str_replace('.', '', $e_val));
                                        $marcosData[$chave]['northing'] = str_replace(',', '.', str_replace('.', '', $n_val));
                                        $marcosData[$chave]['meridiano_central'] = $mc;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // Caso o MC tenha sido encontrado em outras tabelas e não setado:
        if ($mc !== null) {
            foreach ($marcosData as &$mD) {
                if ($mD['easting'] !== null && $mD['meridiano_central'] === null) {
                    $mD['meridiano_central'] = $mc;
                }
            }
        }

        if (count($marcosData) > 0) {
            $chunks = array_chunk(array_values($marcosData), 500);
            foreach ($chunks as $chunk) {
                DB::table('marcos')->insertOrIgnore($chunk);
            }
            $this->info("-> Imovel: {$imovel} | Marcos encontrados: " . count($marcosData));
        }
    }

    private function dmsToDd($dmsStr)
    {
        // Example: "67 32 08,146 W"
        if (preg_match('/(\d+)\s+(\d+)\s+([\d,]+)\s*([A-Z])/i', trim($dmsStr), $m)) {
            $deg = (float)$m[1];
            $min = (float)$m[2];
            $sec = (float)str_replace(',', '.', $m[3]);
            $dir = strtoupper($m[4]);
            
            $dd = $deg + ($min / 60) + ($sec / 3600);
            if ($dir == 'S' || $dir == 'W') {
                $dd = $dd * -1;
            }
            return round($dd, 8);
        }
        return null;
    }

    private function extractImovelName($tables)
    {
        foreach ($tables[1] as $index => $tableName) {
            if (stripos($tableName, 'identifica') !== false || stripos($tableName, 'identificação') !== false) {
                $tableHtml = $tables[2][$index];
                $cleanText = strip_tags(str_replace('><', '> <', $tableHtml));
                if (preg_match('/Denominação[^a-zA-Z0-9]*([A-Za-z0-9_ \-\.\(\)]+)/i', $cleanText, $match)) {
                    $val = trim($match[1]);
                    if (!empty($val) && stripos($val, 'Módulo') === false) {
                        return substr($val, 0, 100);
                    }
                }
            }
        }
        return null;
    }
}

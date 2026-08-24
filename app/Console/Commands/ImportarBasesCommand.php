<?php

namespace App\Console\Commands;

use App\Models\Base;
use FilesystemIterator;
use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ImportarBasesCommand extends Command
{
    protected $signature = 'bases:import {directory} {--map=} {--recursive} {--force} {--verbose-import}';

    protected $description = 'Importa arquivos ZIP de bases de um diretório local para o banco de dados.';

    public function handle()
    {
        $directory = trim($this->argument('directory'), '"\'');
        $realDirectory = realpath($directory);

        if (! $realDirectory || ! is_dir($realDirectory)) {
            $this->error("Diretório inválido: {$directory}");

            return 1;
        }

        $this->info("Importando arquivos ZIP de: {$realDirectory}");

        $paths = $this->coletarZips($realDirectory, (bool) $this->option('recursive'));
        if (empty($paths)) {
            $this->warn('Nenhum arquivo ZIP encontrado.');

            return 0;
        }

        $map = $this->carregarMapa();
        if ($map === false) {
            return 1;
        }

        $storageRoot = storage_path('app/public/bases_zip');
        if (! is_dir($storageRoot)) {
            mkdir($storageRoot, 0755, true);
        }

        $imported = 0;
        foreach ($paths as $filePath) {
            if ($this->importarZip($filePath, $map)) {
                $imported++;
            }
        }

        $this->info("Importação concluída. Total importado: {$imported}");

        return 0;
    }

    /** @return array<int, string> */
    private function coletarZips(string $diretorio, bool $recursivo): array
    {
        $iterator = $recursivo
            ? new RecursiveIteratorIterator(new RecursiveDirectoryIterator($diretorio))
            : new FilesystemIterator($diretorio);

        $paths = [];
        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === 'zip') {
                $paths[] = $file->getRealPath();
            }
        }

        return $paths;
    }

    /**
     * Lê o CSV de metadados (--map), se informado.
     *
     * @return array<string, array<string, ?string>>|false  false em caso de caminho inválido
     */
    private function carregarMapa(): array|false
    {
        $mapPath = $this->option('map');
        if (! $mapPath) {
            return [];
        }

        $realMap = realpath(trim($mapPath, '"\''));
        if (! $realMap || ! is_file($realMap)) {
            $this->error("Arquivo de mapeamento inválido: {$mapPath}");

            return false;
        }

        $this->info("Carregando mapa de metadados: {$realMap}");

        $map = [];
        if (($handle = fopen($realMap, 'r')) !== false) {
            $headers = [];
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                if (empty($row)) {
                    continue;
                }

                if (empty($headers)) {
                    $headers = array_map('strtolower', array_map('trim', $row));
                    if (! empty($headers[0])) {
                        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
                    }
                    continue;
                }

                $entry = array_combine($headers, $row);
                if (! $entry) {
                    continue;
                }

                $key = pathinfo($entry['file'] ?? $entry['arquivo'] ?? $entry['nome'] ?? '', PATHINFO_FILENAME);
                if ($key === '') {
                    continue;
                }

                $map[$key] = [
                    'nome' => $entry['nome'] ?? $entry['file'] ?? $key,
                    'norte' => trim($entry['norte'] ?? $entry['utm_norte'] ?? null),
                    'este' => trim($entry['este'] ?? $entry['utm_este'] ?? null),
                ];
            }
            fclose($handle);
        }

        return $map;
    }

    /** @param array<string, array<string, ?string>> $map */
    private function importarZip(string $filePath, array $map): bool
    {
        $fileName = pathinfo($filePath, PATHINFO_FILENAME);
        $baseData = $map[$fileName] ?? ['nome' => $fileName, 'norte' => null, 'este' => null];

        // 1. Tenta extrair coordenadas do próprio nome do arquivo.
        if (! is_numeric($baseData['norte']) || ! is_numeric($baseData['este'])) {
            preg_match_all('/-?\d+(?:[\.,]\d+)?/', $fileName, $numbers);
            if (count($numbers[0]) >= 2) {
                $baseData['norte'] = str_replace(',', '.', $numbers[0][0]);
                $baseData['este'] = str_replace(',', '.', $numbers[0][1]);
            }
        }

        // 2. Caso ainda falte, tenta extrair do PDF dentro do ZIP.
        if (! is_numeric($baseData['norte']) || ! is_numeric($baseData['este'])) {
            $coords = $this->extrairCoordenadasDoPdf($filePath, $fileName);
            if ($coords) {
                $baseData['norte'] = $coords['norte'];
                $baseData['este'] = $coords['este'];
            }
        }

        if (! is_numeric($baseData['norte']) || ! is_numeric($baseData['este'])) {
            $this->warn("Pulando '{$fileName}': coordenadas UTM não encontradas. Use --map para fornecer norte/este ou inclua KML/PDF com coordenadas.");

            return false;
        }

        $norteOriginal = isset($baseData['norte']) ? trim($baseData['norte']) : null;
        $esteOriginal = isset($baseData['este']) ? trim($baseData['este']) : null;
        $norte = (float) str_replace(',', '.', (string) $baseData['norte']);
        $este = (float) str_replace(',', '.', (string) $baseData['este']);
        $nome = trim($baseData['nome']) ?: trim($fileName);

        $existing = Base::where('nome', $nome)
            ->orWhere(fn ($query) => $query->where('norte', $norte)->where('este', $este))
            ->first();

        if ($existing && ! $this->option('force')) {
            $this->warn("Pulando '{$nome}': base já existente no banco.");

            return false;
        }

        $targetPath = 'bases_zip/' . $fileName . '-' . uniqid() . '.zip';
        copy($filePath, storage_path('app/public/' . $targetPath));

        [$latitude, $longitude] = $this->extrairLatLngDoKml(storage_path('app/public/' . $targetPath));

        $data = [
            'nome' => $nome,
            'norte' => $norte,
            'este' => $este,
            'norte_original' => $norteOriginal,
            'este_original' => $esteOriginal,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'arquivo_zip' => $targetPath,
        ];

        $existing ? $existing->update($data) : Base::create($data);

        $this->info("Importado: {$nome}");

        return true;
    }

    /**
     * Extrai NORTE/ESTE do texto de um PDF contido no ZIP.
     *
     * @return array{norte: string, este: string}|null
     */
    private function extrairCoordenadasDoPdf(string $filePath, string $fileName): ?array
    {
        if (! class_exists(\ZipArchive::class)) {
            return null;
        }

        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            return null;
        }

        $resultado = null;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $nameIndex = $zip->getNameIndex($i);
            if (strtolower(pathinfo($nameIndex, PATHINFO_EXTENSION)) !== 'pdf') {
                continue;
            }

            $raw = $zip->getFromIndex($i);
            if ($raw === false) {
                continue;
            }

            $tmpPdf = tempnam(sys_get_temp_dir(), 'base_pdf_') . '.pdf';
            file_put_contents($tmpPdf, $raw);

            $text = $this->lerTextoPdf($tmpPdf);
            [$norte, $este] = $this->procurarNorteEste($text);

            if ($norte !== null && $este !== null) {
                $resultado = ['norte' => $norte, 'este' => $este];
                if ($this->option('verbose-import')) {
                    file_put_contents(storage_path('logs/bases_import.log'), '[' . date('Y-m-d H:i:s') . "] {$fileName} -> extracted NORTH={$norte} EAST={$este}\n", FILE_APPEND);
                    file_put_contents(storage_path('logs/bases_import_' . $fileName . '.txt'), "---EXTRACTED TEXT---\n" . $text . "\n", LOCK_EX);
                }
                @unlink($tmpPdf);
                break;
            }

            @unlink($tmpPdf);
        }

        $zip->close();

        return $resultado;
    }

    private function lerTextoPdf(string $tmpPdf): string
    {
        $whereCmd = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'where' : 'which';
        $pdftotextAvailable = function_exists('shell_exec') && ! empty(@shell_exec($whereCmd . ' pdftotext 2>&1'));

        if ($pdftotextAvailable) {
            $out = @shell_exec('pdftotext -layout ' . escapeshellarg($tmpPdf) . ' - 2>&1');
            if ($out !== null) {
                return $out;
            }
        }

        // Fallback grosseiro: extrai trechos legíveis do conteúdo bruto.
        preg_match_all('/[\w\s,\.\-ºª°]+/', file_get_contents($tmpPdf), $chunks);

        return implode("\n", array_map('trim', $chunks[0]));
    }

    /** @return array{0: ?string, 1: ?string} [norte, este] */
    private function procurarNorteEste(string $text): array
    {
        $norte = $este = null;
        $patterns = [
            '/norte[\s:\-]*(-?\d+[\d\.,]*)/i',
            '/utm[\s\-]*norte[\s:\-]*(-?\d+[\d\.,]*)/i',
            '/este[\s:\-]*(-?\d+[\d\.,]*)/i',
            '/utm[\s\-]*este[\s:\-]*(-?\d+[\d\.,]*)/i',
        ];

        foreach ($patterns as $p) {
            if (preg_match($p, $text, $m)) {
                $val = str_replace(',', '.', $m[1]);
                if (stripos($p, 'norte') !== false && $norte === null) {
                    $norte = $val;
                }
                if (stripos($p, 'este') !== false && $este === null) {
                    $este = $val;
                }
            }
        }

        if ($norte === null || $este === null) {
            preg_match_all('/-?\d{5,}[\.,]?\d*/', $text, $found);
            if (count($found[0]) >= 2) {
                $norte ??= str_replace(',', '.', $found[0][0]);
                $este ??= str_replace(',', '.', $found[0][1]);
            }
        }

        return [$norte, $este];
    }

    /** @return array{0: ?string, 1: ?string} [latitude, longitude] */
    private function extrairLatLngDoKml(string $caminhoZip): array
    {
        if (! class_exists(\ZipArchive::class)) {
            return [null, null];
        }

        $zip = new \ZipArchive();
        if ($zip->open($caminhoZip) !== true) {
            return [null, null];
        }

        $latitude = $longitude = null;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            if (strtolower(pathinfo($zip->getNameIndex($i), PATHINFO_EXTENSION)) !== 'kml') {
                continue;
            }

            $dom = new \DOMDocument();
            @$dom->loadXML($zip->getFromIndex($i));
            $coordsTags = $dom->getElementsByTagName('coordinates');
            if ($coordsTags->length > 0) {
                $parts = explode(',', trim($coordsTags->item(0)->nodeValue));
                if (count($parts) >= 2) {
                    $longitude = trim($parts[0]);
                    $latitude = trim($parts[1]);
                }
            }
            break;
        }

        $zip->close();

        return [$latitude, $longitude];
    }
}

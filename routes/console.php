<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('bases:import {directory} {--map=} {--recursive} {--force} {--verbose-import}', function ($directory) {
    $directory = trim($directory, '"\'');
    $realDirectory = realpath($directory);

    if (! $realDirectory || ! is_dir($realDirectory)) {
        $this->error("Diretório inválido: {$directory}");
        return 1;
    }

    $this->info("Importando arquivos ZIP de: {$realDirectory}");

    $paths = [];
    if ($this->option('recursive')) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($realDirectory));
        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === 'zip') {
                $paths[] = $file->getRealPath();
            }
        }
    } else {
        $iterator = new FilesystemIterator($realDirectory);
        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === 'zip') {
                $paths[] = $file->getRealPath();
            }
        }
    }

    if (empty($paths)) {
        $this->warn('Nenhum arquivo ZIP encontrado.');
        return 0;
    }

    $map = [];
    if ($mapPath = $this->option('map')) {
        $realMap = realpath(trim($mapPath, '"\''));
        if (! $realMap || ! is_file($realMap)) {
            $this->error("Arquivo de mapeamento inválido: {$mapPath}");
            return 1;
        }

        $this->info("Carregando mapa de metadados: {$realMap}");
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
    }

    $storageRoot = storage_path('app/public/bases_zip');
    if (! is_dir($storageRoot)) {
        mkdir($storageRoot, 0755, true);
    }

    $imported = 0;
    foreach ($paths as $filePath) {
        $fileName = pathinfo($filePath, PATHINFO_FILENAME);
        $baseData = $map[$fileName] ?? [
            'nome' => $fileName,
            'norte' => null,
            'este' => null,
        ];
        $verbose = $this->option('verbose-import');

        if (! is_numeric($baseData['norte']) || ! is_numeric($baseData['este'])) {
            preg_match_all('/-?\d+(?:[\.,]\d+)?/', $fileName, $numbers);
            if (count($numbers[0]) >= 2) {
                $baseData['norte'] = str_replace(',', '.', $numbers[0][0]);
                $baseData['este'] = str_replace(',', '.', $numbers[0][1]);
            }
        }

        if (! is_numeric($baseData['norte']) || ! is_numeric($baseData['este'])) {
            if (class_exists(\ZipArchive::class)) {
                $zip = new \ZipArchive;
                if ($zip->open($filePath) === true) {
                    $tempDir = sys_get_temp_dir();
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $nameIndex = $zip->getNameIndex($i);
                        if (strtolower(pathinfo($nameIndex, PATHINFO_EXTENSION)) === 'pdf') {
                            $raw = $zip->getFromIndex($i);
                            if ($raw === false) { continue; }

                            $tmpPdf = tempnam($tempDir, 'base_pdf_') . '.pdf';
                            file_put_contents($tmpPdf, $raw);

                            $text = null;
                            $whereCmd = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'where' : 'which';
                            $pdftotextAvailable = false;
                            if (function_exists('shell_exec')) {
                                $check = @shell_exec($whereCmd . ' pdftotext 2>&1');
                                if (!empty($check)) { $pdftotextAvailable = true; }
                            }

                            if ($pdftotextAvailable && function_exists('shell_exec')) {
                                $out = @shell_exec("pdftotext -layout " . escapeshellarg($tmpPdf) . " - 2>&1");
                                if ($out !== null) { $text = $out; }
                            }

                            if ($text === null) {
                                $rawContent = file_get_contents($tmpPdf);
                                preg_match_all('/[\w\s,\.\-ºª°]+/', $rawContent, $chunks);
                                $text = implode("\n", array_map('trim', $chunks[0]));
                            }

                            $norte = null; $este = null;
                            $patterns = [
                                '/norte[\s:\-]*(-?\d+[\d\.,]*)/i',
                                '/utm[\s\-]*norte[\s:\-]*(-?\d+[\d\.,]*)/i',
                                '/este[\s:\-]*(-?\d+[\d\.,]*)/i',
                                '/utm[\s\-]*este[\s:\-]*(-?\d+[\d\.,]*)/i',
                            ];

                            foreach ($patterns as $p) {
                                if (preg_match($p, $text, $m)) {
                                    $val = str_replace(',', '.', $m[1]);
                                    if (stripos($p, 'norte') !== false && $norte === null) { $norte = $val; }
                                    if (stripos($p, 'este') !== false && $este === null) { $este = $val; }
                                }
                            }

                            if ($norte === null || $este === null) {
                                preg_match_all('/-?\d{5,}[\.,]?\d*/', $text, $found);
                                if (count($found[0]) >= 2) {
                                    $norte = $norte ?? str_replace(',', '.', $found[0][0]);
                                    $este = $este ?? str_replace(',', '.', $found[0][1]);
                                }
                            }

                            if ($norte !== null && $este !== null) {
                                $baseData['norte'] = $norte;
                                $baseData['este'] = $este;
                                if ($verbose) {
                                    $logLine = "[" . date('Y-m-d H:i:s') . "] {$fileName} -> extracted NORTH={$norte} EAST={$este}\n";
                                    file_put_contents(storage_path('logs/bases_import.log'), $logLine, FILE_APPEND);
                                    file_put_contents(storage_path('logs/bases_import_' . $fileName . '.txt'), "---EXTRACTED TEXT---\n" . $text . "\n", LOCK_EX);
                                }
                                @unlink($tmpPdf);
                                break;
                            }

                            @unlink($tmpPdf);
                        }
                    }
                    $zip->close();
                }
            }
        }

        if (! is_numeric($baseData['norte']) || ! is_numeric($baseData['este'])) {
            $this->warn("Pulando '{$fileName}': coordenadas UTM não encontradas. Use --map para fornecer norte/este ou inclua KML/PDF com coordenadas.");
            continue;
        }

        // Preserve original CSV/raw values
        $norte_original = isset($baseData['norte']) ? trim($baseData['norte']) : null;
        $este_original = isset($baseData['este']) ? trim($baseData['este']) : null;

        $norte = (float) str_replace(',', '.', (string) $baseData['norte']);
        $este = (float) str_replace(',', '.', (string) $baseData['este']);
        $nome = trim($baseData['nome']) ?: trim($fileName);

        $existing = \App\Models\Base::where('nome', $nome)
            ->orWhere(function ($query) use ($norte, $este) {
                $query->where('norte', $norte)->where('este', $este);
            })->first();

        if ($existing && ! $this->option('force')) {
            $this->warn("Pulando '{$nome}': base já existente no banco.");
            continue;
        }

        $targetName = $fileName . '-' . uniqid() . '.zip';
        $targetPath = 'bases_zip/' . $targetName;
        copy($filePath, storage_path('app/public/' . $targetPath));

        $latitude = null;
        $longitude = null;
        if (class_exists(\ZipArchive::class)) {
            $zip = new \ZipArchive;
            if ($zip->open(storage_path('app/public/' . $targetPath)) === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filenameIndex = $zip->getNameIndex($i);
                    if (strtolower(pathinfo($filenameIndex, PATHINFO_EXTENSION)) === 'kml') {
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
                }
                $zip->close();
            }
        }

        $data = [
            'nome' => $nome,
            'norte' => $norte,
            'este' => $este,
            'norte_original' => $norte_original,
            'este_original' => $este_original,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'arquivo_zip' => $targetPath,
        ];

        if ($existing) {
            $existing->update($data);
        } else {
            \App\Models\Base::create($data);
        }

        $imported++;
        $this->info("Importado: {$nome}");
    }

    $this->info("Importação concluída. Total importado: {$imported}");
    return 0;
})->purpose('Importa arquivos ZIP de bases de um diretório local para o banco de dados.');

Artisan::command('bases:clean {--older-than=} {--missing-files} {--dry-run} {--force}', function () {
    $olderThan = $this->option('older-than');
    $missingFiles = $this->option('missing-files');
    $dryRun = $this->option('dry-run');
    $force = $this->option('force');

    if (!$olderThan && !$missingFiles) {
        $this->error('Especifique pelo menos uma opção: --older-than=<dias> ou --missing-files');
        return 1;
    }

    $this->info('Procurando bases para limpeza...');

    $query = \App\Models\Base::query();
    if ($olderThan) {
        $cutoff = \Carbon\Carbon::now()->subDays((int)$olderThan);
        $query->where('created_at', '<', $cutoff);
    }

    $candidates = $query->get();

    $toDelete = [];
    foreach ($candidates as $base) {
        $missing = false;
        if ($missingFiles) {
            if (empty($base->arquivo_zip) || !@file_exists(storage_path('app/public/' . $base->arquivo_zip))) {
                $missing = true;
            }
        }

        if ($olderThan && $missingFiles) {
            if ($missing) { $toDelete[] = $base; }
        } elseif ($missingFiles) {
            if ($missing) { $toDelete[] = $base; }
        } else {
            $toDelete[] = $base;
        }
    }

    if (count($toDelete) === 0) {
        $this->info('Nenhuma base encontrada para os critérios informados.');
        return 0;
    }

    $this->info('Bases encontradas: ' . count($toDelete));
    foreach ($toDelete as $b) {
        $this->line(" - {$b->id} | {$b->nome} | norte={$b->norte} este={$b->este} arquivo={$b->arquivo_zip}");
    }

    if ($dryRun) {
        $this->info('Dry-run ativado — nenhuma alteração será feita.');
        return 0;
    }

    if (!$force) {
        if (!$this->confirm('Confirma exclusão das bases listadas?')) {
            $this->info('Operação cancelada.');
            return 0;
        }
    }

    $deleted = 0;
    foreach ($toDelete as $b) {
        if (!empty($b->arquivo_zip) && file_exists(storage_path('app/public/' . $b->arquivo_zip))) {
            @unlink(storage_path('app/public/' . $b->arquivo_zip));
        }

        $b->delete();
        $deleted++;
    }

    $this->info("Removidas: {$deleted} base(s)");
    return 0;
})->purpose('Limpa bases antigas ou sem arquivo associado do banco de dados.');
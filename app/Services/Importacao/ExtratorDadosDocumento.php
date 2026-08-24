<?php

namespace App\Services\Importacao;

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Extrai CPF e códigos de vértices (BCA) de documentos importados.
 *
 * Unifica as cópias idênticas de parsePdfForData / parseZipBasedFileForData /
 * isCpfAdmin que existiam em AdminImportacaoController e SyncPendentesCommand.
 */
class ExtratorDadosDocumento
{
    private const REGEX_CPF = '/\d{3}\.\d{3}\.\d{3}\-\d{2}/';

    /** Formato canônico do vértice SIGEF, ex.: BCA-M-1234. */
    private const REGEX_VERTICE = '/BCA-[MPV]-\d{1,5}(?![0-9])/';

    /** Cache dos CPFs de administradores (consultado uma única vez por request). */
    private ?array $cpfsAdmin = null;

    /**
     * Extrai dados de um arquivo de acordo com sua extensão.
     *
     * @return array{cpf: ?string, vertices: array<int, string>}
     */
    public function extrair(string $caminho, string $extensao): array
    {
        $extensao = strtolower($extensao);

        if ($extensao === 'pdf') {
            return $this->extrairDoPdf($caminho);
        }

        if (in_array($extensao, ['ods', 'odt', 'docx'])) {
            return $this->extrairDeArquivoCompactado($caminho);
        }

        return ['cpf' => null, 'vertices' => []];
    }

    /** @return array{cpf: ?string, vertices: array<int, string>} */
    public function extrairDoPdf(string $caminho): array
    {
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $texto = $parser->parseFile($caminho)->getText();

            return $this->extrairDoTexto($texto);
        } catch (\Throwable $e) {
            Log::warning("Falha ao ler o PDF {$caminho}: " . $e->getMessage());

            return ['cpf' => null, 'vertices' => []];
        }
    }

    /**
     * Lê o XML interno de arquivos ODS/ODT (content.xml) e DOCX (word/document.xml).
     *
     * @return array{cpf: ?string, vertices: array<int, string>}
     */
    public function extrairDeArquivoCompactado(string $caminho): array
    {
        try {
            $zip = new \ZipArchive();
            if ($zip->open($caminho) !== true) {
                return ['cpf' => null, 'vertices' => []];
            }

            $conteudo = $zip->getFromName('content.xml') ?: $zip->getFromName('word/document.xml');
            $zip->close();

            if (! $conteudo) {
                return ['cpf' => null, 'vertices' => []];
            }

            // Insere espaços entre as tags para que textos de células vizinhas não se juntem.
            $texto = strip_tags(str_replace('><', '> <', $conteudo));

            return $this->extrairDoTexto($texto);
        } catch (\Throwable $e) {
            Log::warning("Falha ao extrair texto do arquivo compactado {$caminho}: " . $e->getMessage());

            return ['cpf' => null, 'vertices' => []];
        }
    }

    /** @return array{cpf: ?string, vertices: array<int, string>} */
    private function extrairDoTexto(string $texto): array
    {
        $resultado = ['cpf' => null, 'vertices' => []];

        if (preg_match_all(self::REGEX_CPF, $texto, $matches)) {
            foreach ($matches[0] as $cpf) {
                if (! $this->cpfEhAdmin($cpf)) {
                    $resultado['cpf'] = $cpf; // primeiro CPF que não seja de admin
                    break;
                }
            }
        }

        if (preg_match_all(self::REGEX_VERTICE, $texto, $matches)) {
            $resultado['vertices'] = $matches[0];
        }

        return $resultado;
    }

    /** O CPF pertence a um administrador (e portanto deve ser ignorado)? */
    public function cpfEhAdmin(string $cpf): bool
    {
        if ($this->cpfsAdmin === null) {
            $this->cpfsAdmin = User::where('role', 'admin')
                ->pluck('cpf')
                ->map(fn ($c) => preg_replace('/[^0-9]/', '', (string) $c))
                ->toArray();
        }

        return in_array(preg_replace('/[^0-9]/', '', $cpf), $this->cpfsAdmin);
    }
}

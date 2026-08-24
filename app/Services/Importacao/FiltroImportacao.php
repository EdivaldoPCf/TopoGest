<?php

namespace App\Services\Importacao;

/**
 * Centraliza as regras de "o que ignorar" durante a importação de pastas.
 *
 * Antes essas listas e checagens estavam duplicadas em AdminImportacaoController
 * (processarPasta e uploadWeb) e em SyncPendentesCommand.
 */
class FiltroImportacao
{
    /** Pastas técnicas que não devem ser exibidas ao cliente. */
    public const PASTAS_IGNORADAS = ['GNSS', 'RTK', 'BASE', 'ROVER', 'ARQUIVO METRICA', 'ARQUIVO MÉRICA'];

    /** Extensões de arquivos de trabalho/backup que não interessam ao cliente. */
    public const EXTENSOES_IGNORADAS = ['topo', 'tbkp', 'dwl', 'dwl2', 'bak'];

    /** Arquivos que nunca devem ser importados. */
    public const ARQUIVOS_IGNORADOS = ['thumbs.db'];

    /** Arquivo temporário do sistema operacional (lock do Office, thumbs do Windows). */
    public function ehTemporario(string $nomeArquivo, string $basenameMinusculo): bool
    {
        return str_starts_with($nomeArquivo, '~$') || $basenameMinusculo === 'thumbs.db';
    }

    /** O arquivo deve ser marcado como oculto pela extensão/nome? */
    public function extensaoIgnorada(string $extensao, string $basenameMinusculo): bool
    {
        return in_array($extensao, self::EXTENSOES_IGNORADAS)
            || in_array($basenameMinusculo, self::ARQUIVOS_IGNORADOS)
            || str_contains($basenameMinusculo, 'topo.zip')
            || str_contains($basenameMinusculo, 'topos.zip');
    }

    /** A pasta é uma das técnicas que devem ficar ocultas? */
    public function pastaIgnorada(string $nomePasta): bool
    {
        $maiusculo = mb_strtoupper($nomePasta, 'UTF-8');

        return in_array($maiusculo, ['GNSS', 'RTK', 'BASE', 'ROVER'])
            || stripos($maiusculo, 'METRICA') !== false
            || stripos($maiusculo, 'MÉTRICA') !== false;
    }

    /** A pasta é a de bases PPP (que recebem tratamento especial)? */
    public function ehPastaPpp(string $nomePasta): bool
    {
        return mb_strtoupper($nomePasta, 'UTF-8') === 'PPP';
    }
}

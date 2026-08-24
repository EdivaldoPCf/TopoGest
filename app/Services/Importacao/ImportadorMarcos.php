<?php

namespace App\Services\Importacao;

use App\Events\MarcosAtualizadosEvent;
use App\Models\Marco;
use App\Services\SigefOdsParserService;
use Illuminate\Support\Facades\Log;

/**
 * Cria/atualiza marcos geodésicos a partir de documentos importados.
 *
 * Unifica os métodos processarOdsParaMarcos (idênticos) e a gravação de
 * vértices em texto que existiam em AdminImportacaoController e SyncPendentesCommand.
 */
class ImportadorMarcos
{
    /** Credenciais de marco aceitas pelo sistema. */
    private const CREDENCIAIS_VALIDAS = ['BCA', 'EMES'];

    public function __construct(private SigefOdsParserService $parserOds)
    {
    }

    /**
     * Extrai coordenadas de um ODS SIGEF e cria/completa os marcos do imóvel.
     */
    public function importarDeOds(string $caminho, string $imovel, ?int $userId = null): void
    {
        try {
            $dados = $this->parserOds->parseOdsFile($caminho);
            $this->importarVertices($dados['vertices'] ?? [], $imovel, $userId);

            event(new MarcosAtualizadosEvent('Novos marcos importados via ODS'));
        } catch (\Throwable $e) {
            Log::warning('Erro ao processar ODS para extrair coordenadas: ' . $e->getMessage());
        }
    }

    /**
     * Cria/completa marcos a partir de vértices SIGEF já parseados.
     *
     * @param  array<int, array<string, mixed>> $vertices
     * @param  ?array<int, string>              $credenciaisPermitidas null = aceita qualquer credencial
     */
    public function importarVertices(array $vertices, string $imovel, ?int $userId = null, ?array $credenciaisPermitidas = self::CREDENCIAIS_VALIDAS): void
    {
        foreach ($vertices as $vertice) {
            $codigo = trim($vertice['codigo']);

            if (! preg_match('/^([A-Z0-9]+)\-([A-Z])\-(.+)$/i', $codigo, $partes)) {
                continue;
            }

            $credencial = strtoupper($partes[1]);

            if ($credenciaisPermitidas !== null && ! in_array($credencial, $credenciaisPermitidas)) {
                continue;
            }

            $numero = str_pad($partes[3], 4, '0', STR_PAD_LEFT);

            $this->gravarCoordenadasMarco($credencial, strtoupper($partes[2]), $numero, $imovel, $vertice, $userId);
        }
    }

    /**
     * Cria marcos a partir dos códigos de vértices encontrados em texto (ex.: BCA-M-001).
     *
     * @param array<int, string> $vertices
     */
    public function salvarVerticesTexto(array $vertices, string $imovel, ?int $userId = null): void
    {
        foreach (array_unique($vertices) as $vertice) {
            $partes = explode('-', $vertice);

            if (count($partes) < 3) {
                continue;
            }

            Marco::firstOrCreate([
                'credencial' => $partes[0],
                'tipo' => $partes[1],
                'numero' => $partes[2],
            ], [
                'imovel' => $imovel,
                'user_id' => $userId ?: 1,
            ]);
        }
    }

    /**
     * Grava as coordenadas do vértice, criando o marco ou completando um já existente
     * que ainda não tenha coordenadas.
     *
     * @param array{tipo: string, N: mixed, E: mixed} $vertice
     */
    private function gravarCoordenadasMarco(
        string $credencial,
        string $tipo,
        string $numero,
        string $imovel,
        array $vertice,
        ?int $userId
    ): void {
        $marco = Marco::where('credencial', $credencial)
            ->where('tipo', $tipo)
            ->where('numero', $numero)
            ->first();

        if ($marco) {
            // Só completa coordenadas se o marco ainda não tiver nenhuma; preserva quem o cadastrou.
            if (! is_null($marco->latitude) || ! is_null($marco->easting)) {
                return;
            }
        } else {
            $marco = new Marco();
            $marco->user_id = $userId ?: 1;
            $marco->credencial = $credencial;
            $marco->tipo = $tipo;
            $marco->numero = $numero;
            $marco->imovel = substr($imovel, 0, 100);
        }

        if (($vertice['tipo'] ?? null) === 'geodesica') {
            $marco->latitude = $vertice['N'];
            $marco->longitude = $vertice['E'];
        } else {
            $marco->easting = $vertice['E'];
            $marco->northing = $vertice['N'];
        }

        $marco->save();
    }
}

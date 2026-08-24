<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\Pasta;
use App\Services\Importacao\ImportadorMarcos;
use App\Services\Sigef\GeradorDxf;
use App\Services\Sigef\GeradorMemorial;
use App\Services\Sigef\PreparadorVertices;
use App\Services\SigefOdsParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SigefMapaController extends Controller
{
    public function __construct(
        private SigefOdsParserService $parser,
        private PreparadorVertices $preparador,
        private GeradorDxf $geradorDxf,
        private GeradorMemorial $geradorMemorial,
        private ImportadorMarcos $marcos,
    ) {
    }

    /**
     * Localiza os ODS do SIGEF na pasta (e subpastas), retorna os polígonos para o mapa
     * e atualiza os marcos na base global.
     */
    public function parsear($pastaId)
    {
        $pasta = $this->carregarPastaComArquivos($pastaId);
        $arquivosOds = $this->encontrarTodosOds($pasta);

        if (empty($arquivosOds)) {
            return response()->json(['erro' => 'Nenhum arquivo ODS encontrado nesta pasta.'], 404);
        }

        $poligonos = [];

        foreach ($arquivosOds as $arquivoOds) {
            $caminho = Storage::disk('public')->path($arquivoOds->path);
            if (! file_exists($caminho)) {
                continue;
            }

            try {
                $dadosOds = $this->parser->parseOdsFile($caminho);

                if (! empty($dadosOds['vertices'])) {
                    $poligonos[] = $this->montarPoligono($arquivoOds, $dadosOds);
                }
            } catch (\Throwable $e) {
                // Ignora arquivos individuais com erro para que os demais ainda carreguem.
            }
        }

        if (empty($poligonos)) {
            return response()->json(['erro' => 'Nenhum vértice encontrado nas planilhas ODS ou erro de leitura.'], 422);
        }

        foreach ($poligonos as $p) {
            $this->marcos->importarVertices($p['vertices'], $p['imovel'] ?? 'Imóvel Desconhecido', auth()->id(), null);
        }

        return response()->json(['poligonos' => $poligonos]);
    }

    /**
     * Gera o Memorial Descritivo no padrão oficial INCRA/SIGEF.
     */
    public function gerarMemorial($pastaId, Request $request)
    {
        $resultado = $this->lerOds($pastaId);
        if (! is_array($resultado)) {
            return $resultado;
        }

        $preparado = $this->preparador->prepararCompleto($resultado['vertices']);
        $texto = $this->geradorMemorial->gerarPadraoSigef(
            $resultado['identificacao'],
            $preparado['vertices'],
            $preparado['zona'],
            $preparado['hemisferio']
        );

        $imovel = $resultado['identificacao']['imovel'] ?? 'Nome do Imóvel não definido';

        if ($request->query('download') === 'txt') {
            return $this->respostaTexto($texto, $imovel, 'memorial', 'txt');
        }

        return response()->json([
            'texto' => $texto,
            'imovel' => $imovel,
            'vertices' => $preparado['vertices'],
            'fuso' => $preparado['zona'] . $preparado['hemisferio'],
        ]);
    }

    /**
     * Gera e baixa a Planta do perímetro em formato DXF para AutoCAD.
     */
    public function gerarDxf($pastaId)
    {
        $resultado = $this->lerOds($pastaId);
        if (! is_array($resultado)) {
            return $resultado;
        }

        $vertices = $this->preparador->prepararParaDxf($resultado['vertices']);
        $dxf = $this->geradorDxf->gerar($vertices);
        $imovel = $resultado['identificacao']['imovel'] ?? 'planta';

        return $this->respostaTexto($dxf, $imovel, 'planta', 'dxf', 'image/vnd.dxf');
    }

    /**
     * Lê o primeiro ODS da pasta e retorna identificação + vértices, ou um redirect de erro.
     *
     * @return array{identificacao: array, vertices: array}|\Illuminate\Http\RedirectResponse
     */
    private function lerOds($pastaId)
    {
        $pasta = $this->carregarPastaComArquivos($pastaId);
        $arquivoOds = $this->encontrarOds($pasta);

        if (! $arquivoOds) {
            return redirect()->back()->with('error', 'Nenhum arquivo ODS encontrado nesta pasta.');
        }

        $caminho = Storage::disk('public')->path($arquivoOds->path);
        if (! file_exists($caminho)) {
            return redirect()->back()->with('error', 'Arquivo ODS não encontrado no servidor.');
        }

        try {
            $dadosOds = $this->parser->parseOdsFile($caminho);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Erro ao ler o arquivo ODS: ' . $e->getMessage());
        }

        if (empty($dadosOds['vertices'])) {
            return redirect()->back()->with('error', 'Nenhum vértice válido encontrado na planilha.');
        }

        return ['identificacao' => $dadosOds['identificacao'], 'vertices' => $dadosOds['vertices']];
    }

    /** @param array<string, mixed> $dadosOds @return array<string, mixed> */
    private function montarPoligono(Arquivo $arquivoOds, array $dadosOds): array
    {
        $ident = $dadosOds['identificacao'];

        return [
            'arquivo_id' => $arquivoOds->id,
            'arquivo_nome' => $arquivoOds->nome ?? $arquivoOds->nome_original,
            'imovel' => $ident['imovel'] ?? null,
            'detentor' => $ident['detentor'] ?? null,
            'cpf_cnpj' => $ident['cpf_cnpj'] ?? null,
            'municipio' => $ident['municipio'] ?? null,
            'area_ha' => $ident['area_ha'] ?? null,
            'sncr' => $ident['sncr'] ?? null,
            'vertices' => $dadosOds['vertices'],
        ];
    }

    private function carregarPastaComArquivos($pastaId): Pasta
    {
        return Pasta::with(['arquivos', 'subpastas.arquivos', 'subpastas.subpastas.arquivos'])
            ->findOrFail($pastaId);
    }

    private function respostaTexto(string $conteudo, string $imovel, string $sufixo, string $ext, string $contentType = 'text/plain; charset=UTF-8')
    {
        $filename = str_replace(' ', '_', mb_strtolower($imovel)) . '_' . $sufixo . '.' . $ext;

        return response($conteudo, 200)
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /** Busca recursiva de todos os arquivos ODS. @return array<int, Arquivo> */
    private function encontrarTodosOds(Pasta $pasta): array
    {
        $arquivos = [];

        foreach ($pasta->arquivos as $arquivo) {
            $ext = strtolower(pathinfo($arquivo->path ?? '', PATHINFO_EXTENSION));
            $tipo = strtolower($arquivo->tipo ?? '');
            if ($ext === 'ods' || $tipo === 'ods') {
                $arquivos[] = $arquivo;
            }
        }

        foreach ($pasta->subpastas as $sub) {
            $sub->load(['arquivos', 'subpastas.arquivos']);
            $arquivos = array_merge($arquivos, $this->encontrarTodosOds($sub));
        }

        return $arquivos;
    }

    private function encontrarOds(Pasta $pasta): ?Arquivo
    {
        return $this->encontrarTodosOds($pasta)[0] ?? null;
    }
}

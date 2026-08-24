<?php

namespace App\Http\Controllers;

use App\Services\Sigef\GeradorDxf;
use App\Services\Sigef\GeradorMemorial;
use App\Services\Sigef\PreparadorVertices;
use App\Services\SigefOdsParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Gerador Express: faz upload de um ODS avulso e gera planta (DXF) e memorial na hora.
 */
class AdminGeradorController extends Controller
{
    public function __construct(
        private SigefOdsParserService $parser,
        private PreparadorVertices $preparador,
        private GeradorDxf $geradorDxf,
        private GeradorMemorial $geradorMemorial,
    ) {
    }

    public function index()
    {
        return view('admin.gerador.index');
    }

    /**
     * Processa a planilha ODS enviada, salva-a temporariamente e exibe os resultados.
     */
    public function processar(Request $request)
    {
        $request->validate(['arquivo_ods' => 'required|file|max:10240']);

        if (! Storage::disk('local')->exists('temp')) {
            Storage::disk('local')->makeDirectory('temp');
        }

        $tempId = Str::uuid()->toString();
        Storage::disk('local')->putFileAs('temp', $request->file('arquivo_ods'), $tempId . '.ods');
        $absolutePath = Storage::disk('local')->path('temp/' . $tempId . '.ods');

        try {
            $dadosOds = $this->parser->parseOdsFile($absolutePath);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete('temp/' . $tempId . '.ods');

            return redirect()->back()->with('error', 'Erro ao ler a planilha: ' . $e->getMessage());
        }

        if (empty($dadosOds['vertices'])) {
            Storage::disk('local')->delete('temp/' . $tempId . '.ods');

            return redirect()->back()->with('error', 'Nenhum vértice válido encontrado na planilha.');
        }

        $identificacao = $dadosOds['identificacao'];
        $preparado = $this->preparador->prepararCompleto($dadosOds['vertices']);
        $vertices = $preparado['vertices'];
        $utmZone = $preparado['zona'];
        $hemisphere = $preparado['hemisferio'];

        $memorial = $this->geradorMemorial->gerarDetalhado($identificacao, $vertices);
        $memorialTexto = $memorial['texto'];
        $memorialHtml = $memorial['html'];

        return view('admin.gerador.index', compact('tempId', 'identificacao', 'vertices', 'memorialTexto', 'memorialHtml', 'utmZone', 'hemisphere'));
    }

    /**
     * Força o download da planta DXF gerada a partir do ODS temporário.
     */
    public function dxf($tempId)
    {
        $resultado = $this->lerOdsTemporario($tempId);
        if (! is_array($resultado)) {
            return $resultado;
        }

        $vertices = $this->preparador->prepararParaDxf($resultado['vertices']);
        $dxf = $this->geradorDxf->gerar($vertices);
        $imovel = $resultado['identificacao']['imovel'] ?? 'planta';

        return $this->respostaDownload($dxf, $imovel, 'planta', 'dxf', 'image/vnd.dxf');
    }

    /**
     * Força o download do memorial descritivo gerado a partir do ODS temporário.
     */
    public function txt($tempId)
    {
        $resultado = $this->lerOdsTemporario($tempId);
        if (! is_array($resultado)) {
            return $resultado;
        }

        $preparado = $this->preparador->prepararCompleto($resultado['vertices']);
        $memorial = $this->geradorMemorial->gerarDetalhado($resultado['identificacao'], $preparado['vertices']);
        $imovel = $resultado['identificacao']['imovel'] ?? 'Nome do Imóvel não definido';

        return $this->respostaDownload($memorial['texto'], $imovel, 'memorial', 'txt');
    }

    /**
     * Lê o ODS temporário e retorna identificação + vértices, ou um redirect de erro.
     *
     * @return array{identificacao: array, vertices: array}|\Illuminate\Http\RedirectResponse
     */
    private function lerOdsTemporario($tempId)
    {
        $relativo = 'temp/' . $tempId . '.ods';

        if (! Storage::disk('local')->exists($relativo)) {
            return redirect()->route('admin.gerador.index')->with('error', 'Arquivo expirado ou não encontrado.');
        }

        try {
            $dadosOds = $this->parser->parseOdsFile(Storage::disk('local')->path($relativo));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Erro ao processar o arquivo.');
        }

        return ['identificacao' => $dadosOds['identificacao'], 'vertices' => $dadosOds['vertices']];
    }

    private function respostaDownload(string $conteudo, string $imovel, string $sufixo, string $ext, string $contentType = 'text/plain; charset=UTF-8')
    {
        $filename = str_replace(' ', '_', mb_strtolower($imovel)) . '_' . $sufixo . '.' . $ext;

        return response($conteudo, 200)
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}

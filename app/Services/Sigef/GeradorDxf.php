<?php

namespace App\Services\Sigef;

/**
 * Gera o conteúdo de uma planta DXF (AutoCAD) a partir dos vértices do perímetro.
 *
 * Antes esse bloco de ~110 linhas estava duplicado idêntico em SigefMapaController
 * e AdminGeradorController.
 */
class GeradorDxf
{
    /**
     * @param  array<int, array{codigo: string, x: float, y: float, z: float}> $vertices
     */
    public function gerar(array $vertices): string
    {
        $dxf = [];

        $this->secaoCabecalho($dxf);
        $this->secaoLayers($dxf);

        $dxf[] = '  0';
        $dxf[] = 'SECTION';
        $dxf[] = '  2';
        $dxf[] = 'ENTITIES';

        $this->perimetro($dxf, $vertices);
        $this->pontosETextos($dxf, $vertices);

        $dxf[] = '  0';
        $dxf[] = 'ENDSEC';
        $dxf[] = '  0';
        $dxf[] = 'EOF';

        return implode("\r\n", $dxf);
    }

    private function secaoCabecalho(array &$dxf): void
    {
        $dxf[] = '  0';
        $dxf[] = 'SECTION';
        $dxf[] = '  2';
        $dxf[] = 'HEADER';
        $dxf[] = '  0';
        $dxf[] = 'ENDSEC';
    }

    private function secaoLayers(array &$dxf): void
    {
        $dxf[] = '  0';
        $dxf[] = 'SECTION';
        $dxf[] = '  2';
        $dxf[] = 'TABLES';

        $dxf[] = '  0';
        $dxf[] = 'TABLE';
        $dxf[] = '  2';
        $dxf[] = 'LAYER';
        $dxf[] = ' 70';
        $dxf[] = '3';

        // nome => cor
        foreach (['PERIMETRO' => '3', 'VERTICES' => '5', 'TEXTOS_VERTICES' => '7'] as $nome => $cor) {
            $dxf[] = '  0';
            $dxf[] = 'LAYER';
            $dxf[] = '  2';
            $dxf[] = $nome;
            $dxf[] = ' 70';
            $dxf[] = '0';
            $dxf[] = ' 62';
            $dxf[] = $cor;
        }

        $dxf[] = '  0';
        $dxf[] = 'ENDTAB';
        $dxf[] = '  0';
        $dxf[] = 'ENDSEC';
    }

    /** @param array<int, array<string, mixed>> $vertices */
    private function perimetro(array &$dxf, array $vertices): void
    {
        $dxf[] = '  0';
        $dxf[] = 'LWPOLYLINE';
        $dxf[] = '  8';
        $dxf[] = 'PERIMETRO';
        $dxf[] = ' 90';
        $dxf[] = count($vertices);
        $dxf[] = ' 70';
        $dxf[] = '1';
        $dxf[] = ' 43';
        $dxf[] = '0.0';

        foreach ($vertices as $v) {
            $dxf[] = ' 10';
            $dxf[] = sprintf('%.6f', $v['x']);
            $dxf[] = ' 20';
            $dxf[] = sprintf('%.6f', $v['y']);
        }
    }

    /** @param array<int, array<string, mixed>> $vertices */
    private function pontosETextos(array &$dxf, array $vertices): void
    {
        foreach ($vertices as $v) {
            $dxf[] = '  0';
            $dxf[] = 'POINT';
            $dxf[] = '  8';
            $dxf[] = 'VERTICES';
            $dxf[] = ' 10';
            $dxf[] = sprintf('%.6f', $v['x']);
            $dxf[] = ' 20';
            $dxf[] = sprintf('%.6f', $v['y']);
            $dxf[] = ' 30';
            $dxf[] = sprintf('%.6f', $v['z']);

            $dxf[] = '  0';
            $dxf[] = 'TEXT';
            $dxf[] = '  8';
            $dxf[] = 'TEXTOS_VERTICES';
            $dxf[] = ' 10';
            $dxf[] = sprintf('%.6f', $v['x'] + 1.5);
            $dxf[] = ' 20';
            $dxf[] = sprintf('%.6f', $v['y'] + 1.5);
            $dxf[] = ' 40';
            $dxf[] = '2.5';
            $dxf[] = '  1';
            $dxf[] = $v['codigo'];
            $dxf[] = ' 50';
            $dxf[] = '0.0';
        }
    }
}

<?php

namespace App\Services\Sigef;

use App\Services\GeoGeometryService;

/**
 * Gera o texto do memorial descritivo a partir dos vértices do perímetro.
 *
 * Há dois formatos em uso no sistema:
 *  - padrão SIGEF (coordenadas Este/Norte) usado pelo mapa da pasta;
 *  - detalhado (Lat/Lon em GMS) usado pelo Gerador Express, que antes estava
 *    duplicado entre os métodos processar() e txt().
 */
class GeradorMemorial
{
    public function __construct(private GeoGeometryService $geo)
    {
    }

    /**
     * Memorial no padrão SIGEF (coordenadas planas Este/Norte).
     *
     * @param array<string, mixed>            $ident
     * @param array<int, array<string, mixed>> $vertices vértices preparados (com x, y)
     */
    public function gerarPadraoSigef(array $ident, array $vertices, int $zona, string $hemisferio): string
    {
        $linhas = $this->cabecalho($ident);
        $total = count($vertices);

        $desc = 'Inicia-se a descrição deste perímetro no vértice ' . $vertices[0]['codigo'] . ', de coordenadas ';
        $desc .= 'Este (E): ' . number_format($vertices[0]['x'], 3, ',', '.') . ' m e ';
        $desc .= 'Norte (N): ' . number_format($vertices[0]['y'], 3, ',', '.') . ' m, ';
        $desc .= 'situado no fuso UTM ' . $zona . $hemisferio . ' (Datum SIRGAS2000). ';

        for ($i = 0; $i < $total; $i++) {
            $atual = $vertices[$i];
            $proximo = $vertices[($i + 1) % $total];

            $dist = $this->geo->calcularDistancia($atual['x'], $atual['y'], $proximo['x'], $proximo['y']);
            $azi = $this->geo->calcularAzimute($atual['x'], $atual['y'], $proximo['x'], $proximo['y']);

            $desc .= 'Deste vértice, segue confrontando com ' . mb_strtoupper($atual['confrontante'] ?: 'Limite do Imóvel');
            $desc .= ', através do limite ' . mb_strtolower($atual['limite'] ?: 'Cerca');
            $desc .= ', com azimute plano de ' . $azi;
            $desc .= ' e distância de ' . number_format($dist, 2, ',', '.') . ' metros, ';

            if (($i + 1) === $total) {
                $desc .= 'até retornar ao vértice inicial ' . $proximo['codigo'] . ', fechando assim o perímetro medido.';
            } else {
                $desc .= 'até o vértice ' . $proximo['codigo'] . ', de coordenadas ';
                $desc .= 'E: ' . number_format($proximo['x'], 3, ',', '.') . ' m e ';
                $desc .= 'N: ' . number_format($proximo['y'], 3, ',', '.') . ' m; ';
            }
        }

        $linhas[] = wordwrap($desc, 80, "\n");
        $linhas[] = '';
        $linhas[] = '================================================================================';
        $linhas[] = 'Gerado eletronicamente por TopoGest em ' . date('d/m/Y H:i:s');
        $linhas[] = '================================================================================';

        return implode("\n", $linhas);
    }

    /**
     * Memorial detalhado (Lat/Lon em GMS), nas versões texto puro e HTML.
     *
     * @param array<string, mixed>            $ident
     * @param array<int, array<string, mixed>> $vertices vértices preparados (com x, y, lat, lon)
     * @return array{texto: string, html: string}
     */
    public function gerarDetalhado(array $ident, array $vertices): array
    {
        $total = count($vertices);

        $texto = $this->aberturaDetalhada($vertices[0], false);
        $html = $this->aberturaDetalhada($vertices[0], true);

        for ($i = 0; $i < $total; $i++) {
            $atual = $vertices[$i];
            $proximo = $vertices[($i + 1) % $total];

            $dist = $this->geo->calcularDistancia($atual['x'], $atual['y'], $proximo['x'], $proximo['y']);
            $azi = $this->geo->calcularAzimute($atual['x'], $atual['y'], $proximo['x'], $proximo['y']);
            $ehUltimo = ($i + 1) === $total;

            $texto .= $this->trechoDetalhado($proximo, $dist, $azi, $ehUltimo, false);
            $html .= $this->trechoDetalhado($proximo, $dist, $azi, $ehUltimo, true);
        }

        $linhas = $this->cabecalho($ident);
        $linhas[] = wordwrap($texto, 80, "\n");
        $linhas[] = '';
        $linhas[] = 'Todas as coordenadas aqui descritas estão georreferenciadas ao Sistema Geodésico Brasileiro tendo como datum o SIRGAS2000. A área foi obtida pelas coordenadas cartesianas locais, referenciada ao Sistema Geodésico Local (SGL-SIGEF). Todos os azimutes foram calculados pela fórmula do Problema Geodésico Inverso (Puissant). Perímetro e Distâncias foram calculados pelas coordenadas cartesianas geocêntricas.';
        $linhas[] = '';
        $linhas[] = '================================================================================';
        $linhas[] = 'Gerado eletronicamente por TopoGest em ' . date('d/m/Y H:i:s');
        $linhas[] = '================================================================================';

        return ['texto' => implode("\n", $linhas), 'html' => $html];
    }

    /** Cabeçalho comum (identificação do imóvel). @param array<string, mixed> $ident @return array<int, string> */
    private function cabecalho(array $ident): array
    {
        return [
            'MEMORIAL DESCRITIVO',
            '================================================================================',
            'IMÓVEL: ' . mb_strtoupper($ident['imovel'] ?? 'Nome do Imóvel não definido'),
            'PROPRIETÁRIO: ' . mb_strtoupper($ident['detentor'] ?? 'Proprietário não definido'),
            'CPF/CNPJ: ' . ($ident['cpf_cnpj'] ?? 'Não informado'),
            'MUNICÍPIO/UF: ' . mb_strtoupper($ident['municipio'] ?? 'Não informado'),
            'ÁREA CERTIFICADA: ' . ($ident['area_ha'] ?? '0.0000') . ' ha',
            'CÓDIGO SNCR/INCRA: ' . ($ident['sncr'] ?? 'Não informado'),
            '================================================================================',
            '',
        ];
    }

    /** @param array<string, mixed> $v */
    private function aberturaDetalhada(array $v, bool $html): string
    {
        $codigo = $html ? '<strong>' . htmlspecialchars($v['codigo']) . '</strong>' : $v['codigo'];
        $limite = $v['limite'] ?: 'Linha ideal';
        $confrontante = $v['confrontante'] ?: 'Limite do Imóvel';

        if ($html) {
            $limite = htmlspecialchars($limite);
            $confrontante = '<strong>' . htmlspecialchars($confrontante) . '</strong>';
        }

        return 'Inicia-se a descrição deste perímetro no vértice ' . $codigo . ', de coordenadas '
            . $this->coordenadasGms($v)
            . '; ' . $limite . '; deste, segue confrontando com o ' . $confrontante
            . ', com os seguintes azimutes e distâncias: ';
    }

    /** @param array<string, mixed> $proximo */
    private function trechoDetalhado(array $proximo, float $dist, string $azi, bool $ehUltimo, bool $html): string
    {
        $distFmt = number_format($dist, 2, ',', '.');

        if ($html) {
            $trecho = '<strong>' . htmlspecialchars($azi) . '</strong> e <strong>' . $distFmt . ' m</strong> até o vértice <strong>' . htmlspecialchars($proximo['codigo']) . '</strong>';
        } else {
            $trecho = $azi . ' e ' . $distFmt . ' m até o vértice ' . $proximo['codigo'];
        }

        if ($ehUltimo) {
            return $trecho . ', ponto inicial da descrição deste perímetro.';
        }

        $limite = $proximo['limite'] ?: 'Linha ideal';
        $confrontante = $proximo['confrontante'] ?: 'Limite do Imóvel';

        if ($html) {
            $limite = htmlspecialchars($limite);
            $confrontante = '<strong>' . htmlspecialchars($confrontante) . '</strong>';
        }

        return $trecho . ', ' . $this->coordenadasGms($proximo)
            . '; ' . $limite . '; deste, segue confrontando com o ' . $confrontante
            . ', com os seguintes azimutes e distâncias: ';
    }

    /** Bloco "(Longitude: ..., Latitude: ... e Altitude: ... m)". @param array<string, mixed> $v */
    private function coordenadasGms(array $v): string
    {
        return '(Longitude: ' . $this->geo->decimalParaDms($v['lon'], false)
            . ', Latitude: ' . $this->geo->decimalParaDms($v['lat'], true)
            . ' e Altitude: ' . number_format((float) ($v['altitude'] ?? 0.0), 2, ',', '.') . ' m)';
    }
}

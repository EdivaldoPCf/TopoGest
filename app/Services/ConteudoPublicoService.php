<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;

/**
 * Gerencia o conteúdo editável do site público (textos, FAQ, contatos) e as
 * configurações de integração gravadas no .env. Antes essa lógica vivia dentro
 * do AdminController e era chamada também por closures de rotas.
 */
class ConteudoPublicoService
{
    private const ARQUIVO = 'site_content.json';

    /** Carrega o conteúdo público, completando com os valores padrão. */
    public function carregarConteudo(): array
    {
        $path = storage_path('app/' . self::ARQUIVO);

        if (! file_exists($path)) {
            $this->salvarConteudo($this->conteudoPadrao());
        }

        $conteudo = json_decode(file_get_contents($path), true);

        if (! is_array($conteudo)) {
            $conteudo = $this->conteudoPadrao();
        }

        return array_merge($this->conteudoPadrao(), $conteudo);
    }

    /** Persiste o conteúdo público em JSON. */
    public function salvarConteudo(array $conteudo): void
    {
        $destino = dirname(storage_path('app/' . self::ARQUIVO));

        if (! is_dir($destino)) {
            mkdir($destino, 0755, true);
        }

        file_put_contents(
            storage_path('app/' . self::ARQUIVO),
            json_encode($conteudo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * Atualiza/insere variáveis no arquivo .env e limpa o cache de configuração.
     *
     * @param array<string, string> $variaveis
     */
    public function atualizarEnv(array $variaveis): void
    {
        $path = base_path('.env');

        if (! file_exists($path)) {
            return;
        }

        $conteudo = file_get_contents($path);

        foreach ($variaveis as $chave => $valor) {
            // Envolve em aspas se houver espaços ou caracteres especiais.
            if (str_contains($valor, ' ') || str_contains($valor, '"') || str_contains($valor, "'") || str_contains($valor, '$')) {
                $valor = '"' . str_replace('"', '\"', $valor) . '"';
            }

            if (preg_match("/^{$chave}=/m", $conteudo)) {
                $conteudo = preg_replace("/^{$chave}=.*/m", "{$chave}={$valor}", $conteudo);
            } else {
                $conteudo .= "\n{$chave}={$valor}";
            }
        }

        file_put_contents($path, $conteudo);

        try {
            Artisan::call('config:clear');
        } catch (\Exception $e) {
            // Ignora se não for possível executar no CLI.
        }
    }

    /** Conteúdo padrão exibido quando ainda não há personalização salva. */
    public function conteudoPadrao(): array
    {
        return [
            'sobre' => "O TopoGest é uma plataforma web integrada desenvolvida para modernizar a gestão de serviços no setor de topografia. Nossa proposta é substituir processos manuais e descentralizados por uma solução digital robusta, transparente e eficiente, proporcionando mais controle operacional e melhor experiência para clientes e equipes técnicas. Através de um ambiente intuitivo e inteligente, organizamos documentos, acompanhamentos, arquivos técnicos e comunicação em tempo real dentro de um único sistema.",
            'quem_somos' => "O TopoGest nasceu com o propósito de modernizar a forma como serviços topográficos são organizados, acompanhados e entregues. Nossa plataforma centraliza informações, melhora a comunicação entre equipes e reduz falhas operacionais. Transformamos processos técnicos tradicionais em fluxos digitais inteligentes, proporcionando mais eficiência para profissionais e transparência total para os clientes. Com um ambiente intuitivo e seguro, oferecemos controle completo sobre arquivos, solicitações, pendências e acompanhamento em tempo real.",
            'faq' => [
                [
                    'question' => 'Como acompanho o progresso do meu serviço?',
                    'answer' => 'Basta acessar o seu painel e clicar em “Meus Serviços”. O status é atualizado em tempo real pela nossa equipe, mostrando cada etapa do processo como “Equipe em Campo”, “Em fase de Desenho” ou “Finalizado”.',
                ],
                [
                    'question' => 'Onde encontro meus arquivos finais?',
                    'answer' => 'Todos os arquivos ficam disponíveis permanentemente na área “Meus Arquivos”. Você poderá baixar documentos em formatos como PDF, DWG e memoriais técnicos sempre que precisar, sem custo adicional.',
                ],
                [
                    'question' => 'Como envio a documentação inicial?',
                    'answer' => 'Ao iniciar um novo serviço, o sistema abrirá automaticamente um campo de upload de arquivos, permitindo enviar fotos, PDFs ou documentos diretamente pelo celular, tablet ou computador.',
                ],
                [
                    'question' => 'Posso agendar uma visita técnica?',
                    'answer' => 'Sim. Na área do cliente, utilize o botão “Agendar Horário” para visualizar datas e horários disponíveis em nosso calendário de atendimento técnico.',
                ],
            ],
            'whatsapp' => [
                'contato' => '',
                'suporte' => '',
                'agendar' => '',
            ],
        ];
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ConteudoPublicoService;
use Illuminate\Http\Request;

/**
 * Edição das imagens e do conteúdo do site público, além das configurações de
 * integração (WhatsApp e e-mail) gravadas no .env.
 */
class ConteudoController extends Controller
{
    public function __construct(private ConteudoPublicoService $conteudo)
    {
    }

    public function imagens()
    {
        $imageFiles = [
            'background_topo' => ['label' => 'Background Principal', 'name' => 'background-topo.jpg'],
            'logo_icon' => ['label' => 'Logo Ícone', 'name' => 'logo-icon.png'],
            'logo_text' => ['label' => 'Logo Texto', 'name' => 'logo-text.png'],
            'logo_completa' => ['label' => 'Logo Completa', 'name' => 'logo-completa.png'],
        ];

        $content = $this->conteudo->carregarConteudo();

        return view('admin.imagens', compact('imageFiles', 'content'));
    }

    public function salvar(Request $request)
    {
        $request->validate([
            'background_topo' => 'nullable|mimes:jpg,jpeg,png,webp,svg|max:10240',
            'logo_icon' => 'nullable|mimes:jpg,jpeg,png,webp,svg|max:10240',
            'logo_text' => 'nullable|mimes:jpg,jpeg,png,webp,svg|max:10240',
            'logo_completa' => 'nullable|mimes:jpg,jpeg,png,webp,svg|max:10240',
            'sobre_text' => 'nullable|string',
            'quem_somos_text' => 'nullable|string',
            'faq_1_question' => 'nullable|string|max:255',
            'faq_1_answer' => 'nullable|string|max:1000',
            'faq_2_question' => 'nullable|string|max:255',
            'faq_2_answer' => 'nullable|string|max:1000',
            'faq_3_question' => 'nullable|string|max:255',
            'faq_3_answer' => 'nullable|string|max:1000',
            'wpp_contato' => 'nullable|string|max:255',
            'wpp_suporte' => 'nullable|string|max:255',
            'wpp_agendar' => 'nullable|string|max:255',
        ]);

        $this->salvarImagens($request);
        $this->salvarTextos($request);

        $activeTab = $request->input('active_tab', 'imagens');

        if ($activeTab === 'configuracoes') {
            $this->salvarConfiguracoes($request);

            return redirect()->route('admin.imagens.index', ['tab' => 'configuracoes'])
                ->with('success', 'Configurações de integração atualizadas com sucesso!');
        }

        return redirect()->route('admin.imagens.index', ['tab' => $activeTab])
            ->with('success', 'Imagens e conteúdo atualizados com sucesso!');
    }

    /** Move as imagens enviadas para public/images com nomes fixos. */
    private function salvarImagens(Request $request): void
    {
        $uploads = [
            'background_topo' => 'background-topo.jpg',
            'logo_icon' => 'logo-icon.png',
            'logo_text' => 'logo-text.png',
            'logo_completa' => 'logo-completa.png',
        ];

        $destino = public_path('images');
        if (! is_dir($destino)) {
            mkdir($destino, 0755, true);
        }

        foreach ($uploads as $input => $nome) {
            if ($request->hasFile($input)) {
                $request->file($input)->move($destino, $nome);
            }
        }
    }

    /** Atualiza textos institucionais, FAQ e números de WhatsApp do site público. */
    private function salvarTextos(Request $request): void
    {
        $content = $this->conteudo->carregarConteudo();

        $content['sobre'] = $request->input('sobre_text', $content['sobre']);
        $content['quem_somos'] = $request->input('quem_somos_text', $content['quem_somos']);

        $content['faq'] = [];
        for ($i = 1; $i <= 3; $i++) {
            $content['faq'][] = [
                'question' => $request->input("faq_{$i}_question", $content['faq'][$i - 1]['question'] ?? ''),
                'answer' => $request->input("faq_{$i}_answer", $content['faq'][$i - 1]['answer'] ?? ''),
            ];
        }

        $content['whatsapp'] = [
            'contato' => $request->input('wpp_contato', $content['whatsapp']['contato'] ?? ''),
            'suporte' => $request->input('wpp_suporte', $content['whatsapp']['suporte'] ?? ''),
            'agendar' => $request->input('wpp_agendar', $content['whatsapp']['agendar'] ?? ''),
        ];

        $this->conteudo->salvarConteudo($content);
    }

    /** Grava as configurações de integração (WhatsApp e e-mail) no .env. */
    private function salvarConfiguracoes(Request $request): void
    {
        $request->validate([
            'wpp_api_enabled' => 'required|in:0,1',
            'wpp_api_url' => 'nullable|string',
            'wpp_api_token' => 'nullable|string',
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|integer',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string|in:tls,ssl,none',
            'mail_from_address' => 'nullable|email',
            'mail_from_name' => 'nullable|string',
        ]);

        $encryption = $request->input('mail_encryption');

        $this->conteudo->atualizarEnv([
            'WHATSAPP_API_ENABLED' => $request->input('wpp_api_enabled') == '1' ? 'true' : 'false',
            'WHATSAPP_API_URL' => $request->input('wpp_api_url') ?? '',
            'WHATSAPP_API_TOKEN' => $request->input('wpp_api_token') ?? '',
            'MAIL_HOST' => $request->input('mail_host') ?? '',
            'MAIL_PORT' => $request->input('mail_port') ?? '',
            'MAIL_USERNAME' => $request->input('mail_username') ?? '',
            'MAIL_PASSWORD' => $request->input('mail_password') ?? '',
            'MAIL_ENCRYPTION' => $encryption === 'none' ? 'null' : ($encryption ?? 'null'),
            'MAIL_FROM_ADDRESS' => $request->input('mail_from_address') ?? '',
            'MAIL_FROM_NAME' => $request->input('mail_from_name') ?? '',
        ]);
    }
}

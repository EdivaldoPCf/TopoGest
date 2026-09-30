@extends('layouts.admin')

@push('styles')
<style>
[x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-thumb { background: #003366; border-radius: 999px; }
        ::-webkit-scrollbar-track { background: rgba(0,0,0,.05); }
        .tab-active { border-bottom: 3px solid #003366; color: #003366; font-weight: 900; }
        .checkerboard {
            background-color: #f8fafc;
            background-image: linear-gradient(45deg, #e2e8f0 25%, transparent 25%),
                              linear-gradient(-45deg, #e2e8f0 25%, transparent 25%),
                              linear-gradient(45deg, transparent 75%, #e2e8f0 75%),
                              linear-gradient(-45deg, transparent 75%, #e2e8f0 75%);
            background-size: 20px 20px;
            background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
        }
</style>
@endpush

@section('content')


    

    <div class="relative z-10 max-w-7xl mx-auto p-4 md:p-10">

        

        <div class="bg-white rounded-[32px] border border-slate-200 shadow-sm overflow-hidden p-8" x-data="{ tab: '{{ request('tab', 'imagens') }}' }">
            <div class="mb-8 bg-white/90 backdrop-blur-md p-6 rounded-2xl border border-slate-200 shadow-sm inline-block w-max pr-12">
        <h2 class="text-3xl font-black text-slate-800 tracking-tight">Atualizar Sistema</h2>
        <p class="text-slate-600 mt-1 font-medium">Administração</p>
    </div>

            @if(session('success'))
                <div class="mb-6 rounded-3xl bg-emerald-500/15 border border-emerald-500/30 p-4 text-emerald-900">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 rounded-3xl bg-rose-500/15 border border-rose-500/30 p-4 text-rose-900">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Botões de Abas (Tabs) -->
            <div class="flex flex-col sm:flex-row gap-4 border-b border-slate-200 pb-6 mb-8">
                <button type="button" 
                        @click="tab = 'imagens'"
                        :class="tab === 'imagens' ? 'bg-orange-500 text-slate-800 shadow-lg shadow-orange-500/30' : 'bg-slate-50 border border-slate-200 hover:bg-slate-100 border border-slate-200 text-slate-800/80 border border-slate-200'"
                        class="px-6 py-4 rounded-2xl font-black uppercase text-xs tracking-wider transition duration-300 flex items-center justify-center gap-2">
                    🖼️ Alterar Imagens (Logos & Fundo)
                </button>
                <button type="button" 
                        @click="tab = 'textos'"
                        :class="tab === 'textos' ? 'bg-orange-500 text-slate-800 shadow-lg shadow-orange-500/30' : 'bg-slate-50 border border-slate-200 hover:bg-slate-100 border border-slate-200 text-slate-800/80 border border-slate-200'"
                        class="px-6 py-4 rounded-2xl font-black uppercase text-xs tracking-wider transition duration-300 flex items-center justify-center gap-2">
                    📝 Alterar Textos (Sobre, FAQ, etc.)
                </button>
                <button type="button" 
                        @click="tab = 'configuracoes'"
                        :class="tab === 'configuracoes' ? 'bg-orange-500 text-slate-800 shadow-lg shadow-orange-500/30' : 'bg-slate-50 border border-slate-200 hover:bg-slate-100 border border-slate-200 text-slate-800/80 border border-slate-200'"
                        class="px-6 py-4 rounded-2xl font-black uppercase text-xs tracking-wider transition duration-300 flex items-center justify-center gap-2">
                    ⚙️ Configurações (API & E-mail)
                </button>
            </div>

            <!-- SEÇÃO 1: IMAGENS -->
            <form action="{{ route('admin.imagens.store') }}"
                  method="POST"
                  enctype="multipart/form-data"
                  x-show="tab === 'imagens'" 
                  x-transition 
                  class="space-y-8">
                @csrf
                <input type="hidden" name="active_tab" value="imagens">

                <div class="grid gap-8 lg:grid-cols-2">
                    @foreach([
                        'background_topo' => ['label' => 'Background Principal', 'name' => 'background-topo.jpg', 'desc' => 'Imagem utilizada como fundo principal da página inicial.'],
                        'logo_icon' => ['label' => 'Logo Ícone', 'name' => 'logo-icon.png', 'desc' => 'Símbolo da marca. Recomendado: PNG, WEBP ou SVG com fundo transparente.'],
                        'logo_text' => ['label' => 'Logo Texto', 'name' => 'logo-text.png', 'desc' => 'Logotipo textual TopoGest. Recomendado: fundo transparente.'],
                        'logo_completa' => ['label' => 'Logo Completa', 'name' => 'logo-completa.png', 'desc' => 'Versão completa da identidade visual. Recomendado: fundo transparente.']
                    ] as $field => $info)
                        <div class="rounded-3xl border border-slate-200 bg-slate-100 p-6 shadow-xl">
                            <p class="text-xs uppercase tracking-[3px] text-slate-500 font-bold mb-1">{{ $info['label'] }}</p>
                            <p class="text-xs text-slate-500 mb-4 h-8">{{ $info['desc'] }}</p>
                            <div class="mb-4 h-40 overflow-hidden rounded-3xl border border-slate-200 {{ $field === 'background_topo' ? 'bg-slate-900/50' : 'checkerboard' }}">
                                <img src="{{ asset('images/' . $info['name']) }}?v={{ time() }}"
                                     alt="{{ $info['label'] }}"
                                     class="w-full max-h-40 object-contain rounded-3xl">
                            </div>
                            <label class="block text-slate-800/80 font-semibold mb-2">
                                Arquivo de imagem
                            </label>
                            <input type="file"
                                   name="{{ $field }}"
                                   accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                   class="w-full text-sm text-white file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white bg-white rounded-2xl p-3 outline-none"
                            >
                            <p class="mt-2 text-xs text-slate-500">Formatos aceitos: JPG, PNG, WEBP, SVG. Máx. 10 MB.</p>
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between pt-6">
                    <div class="text-sm text-slate-500">
                        Deixe o campo vazio para manter a imagem atual.
                    </div>
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-3xl bg-orange-500 px-8 py-3.5 text-xs font-black uppercase tracking-[1px] text-slate-800 shadow-lg shadow-orange-500/30 hover:bg-orange-600 transition">
                        Salvar Imagens
                    </button>
                </div>
            </form>

            <!-- SEÇÃO 2: TEXTOS -->
            <form action="{{ route('admin.imagens.store') }}"
                  method="POST"
                  x-show="tab === 'textos'" 
                  x-transition 
                  class="space-y-8">
                @csrf
                <input type="hidden" name="active_tab" value="textos">

                <div class="rounded-3xl border border-slate-200 bg-slate-100 border border-slate-200 p-6 shadow-xl">
                    <div class="mb-8">
                        <p class="text-xs uppercase tracking-[3px] text-slate-500 font-bold">
                            Conteúdo público
                        </p>
                        <h2 class="text-2xl font-black tracking-tight">
                            Atualizar Sobre, FAQ, Quem Somos e WhatsApp
                        </h2>
                        <p class="mt-3 text-slate-500 leading-relaxed">
                            Edite os textos das páginas públicas e configure os links do WhatsApp para os botões de Contato, Suporte e Agendar Horário.
                        </p>
                    </div>

                    <div class="grid gap-8 lg:grid-cols-2">
                        <div>
                            <label class="block text-slate-800/80 font-semibold mb-2">Texto da página Sobre</label>
                            <textarea name="sobre_text" rows="6" class="w-full rounded-3xl bg-white border border-slate-200 p-4 text-slate-800 outline-none">{{ old('sobre_text', $content['sobre'] ?? '') }}</textarea>
                            <p class="mt-2 text-xs text-slate-500">Use quebras de linha para criar parágrafos.</p>
                        </div>

                        <div>
                            <label class="block text-slate-800/80 font-semibold mb-2">Texto da página Quem Somos</label>
                            <textarea name="quem_somos_text" rows="6" class="w-full rounded-3xl bg-white border border-slate-200 p-4 text-slate-800 outline-none">{{ old('quem_somos_text', $content['quem_somos'] ?? '') }}</textarea>
                            <p class="mt-2 text-xs text-slate-500">Texto exibido na página Quem Somos.</p>
                        </div>
                    </div>

                    <div class="grid gap-6 mt-8">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="rounded-3xl border border-slate-200 bg-white p-4">
                                <p class="text-sm uppercase tracking-[2px] text-slate-500 font-bold mb-3">FAQ 1</p>
                                <input name="faq_1_question" type="text" placeholder="Pergunta" value="{{ old('faq_1_question', data_get($content, 'faq.0.question', '')) }}" class="w-full rounded-3xl bg-slate-800 border border-slate-200 p-3 text-white outline-none mb-3">
                                <textarea name="faq_1_answer" rows="4" placeholder="Resposta" class="w-full rounded-3xl bg-slate-800 border border-slate-200 p-3 text-white outline-none">{{ old('faq_1_answer', data_get($content, 'faq.0.answer', '')) }}</textarea>
                            </div>
                            <div class="rounded-3xl border border-slate-200 bg-white p-4">
                                <p class="text-sm uppercase tracking-[2px] text-slate-500 font-bold mb-3">FAQ 2</p>
                                <input name="faq_2_question" type="text" placeholder="Pergunta" value="{{ old('faq_2_question', data_get($content, 'faq.1.question', '')) }}" class="w-full rounded-3xl bg-slate-800 border border-slate-200 p-3 text-white outline-none mb-3">
                                <textarea name="faq_2_answer" rows="4" placeholder="Resposta" class="w-full rounded-3xl bg-slate-800 border border-slate-200 p-3 text-white outline-none">{{ old('faq_2_answer', data_get($content, 'faq.1.answer', '')) }}</textarea>
                            </div>
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-white p-4">
                            <p class="text-sm uppercase tracking-[2px] text-slate-500 font-bold mb-3">FAQ 3</p>
                            <input name="faq_3_question" type="text" placeholder="Pergunta" value="{{ old('faq_3_question', data_get($content, 'faq.2.question', '')) }}" class="w-full rounded-3xl bg-slate-800 border border-slate-200 p-3 text-white outline-none mb-3">
                            <textarea name="faq_3_answer" rows="4" placeholder="Resposta" class="w-full rounded-3xl bg-slate-800 border border-slate-200 p-3 text-white outline-none">{{ old('faq_3_answer', data_get($content, 'faq.2.answer', '')) }}</textarea>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3 mt-8">
                        <div>
                            <label class="block text-slate-800/80 font-semibold mb-2">WhatsApp Contato</label>
                            <input name="wpp_contato" type="text" value="{{ old('wpp_contato', data_get($content, 'whatsapp.contato', '')) }}" placeholder="https://api.whatsapp.com/send?phone=..." class="w-full rounded-3xl bg-white border border-slate-200 p-3 text-slate-800 outline-none">
                        </div>
                        <div>
                            <label class="block text-slate-800/80 font-semibold mb-2">WhatsApp Suporte</label>
                            <input name="wpp_suporte" type="text" value="{{ old('wpp_suporte', data_get($content, 'whatsapp.suporte', '')) }}" placeholder="https://api.whatsapp.com/send?phone=..." class="w-full rounded-3xl bg-white border border-slate-200 p-3 text-slate-800 outline-none">
                        </div>
                        <div>
                            <label class="block text-slate-800/80 font-semibold mb-2">WhatsApp Agendar</label>
                            <input name="wpp_agendar" type="text" value="{{ old('wpp_agendar', data_get($content, 'whatsapp.agendar', '')) }}" placeholder="https://api.whatsapp.com/send?phone=..." class="w-full rounded-3xl bg-white border border-slate-200 p-3 text-slate-800 outline-none">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-6">
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-3xl bg-orange-500 px-8 py-3.5 text-xs font-black uppercase tracking-[1px] text-slate-800 shadow-lg shadow-orange-500/30 hover:bg-orange-600 transition">
                        Salvar Textos
                    </button>
                </div>
            </form>

            <!-- SEÇÃO 3: CONFIGURAÇÕES (API & EMAIL) -->
            <form action="{{ route('admin.imagens.store') }}"
                  method="POST"
                  x-show="tab === 'configuracoes'" 
                  x-transition 
                  class="space-y-8 animate-fade">
                @csrf
                <input type="hidden" name="active_tab" value="configuracoes">

                <div class="rounded-3xl border border-slate-200 bg-slate-100 border border-slate-200 p-6 shadow-xl">
                    <div class="mb-8">
                        <p class="text-xs uppercase tracking-[3px] text-slate-500 font-bold">
                            APIs e Notificações
                        </p>
                        <h2 class="text-2xl font-black tracking-tight">
                            Configurações de E-mail e WhatsApp
                        </h2>
                        <p class="mt-3 text-slate-500 leading-relaxed">
                            Defina as chaves de integraçãotificações.
                        </p>
                    </div>

                    <!-- Configuração do WhatsApp -->
                    <div class="border-b border-slate-200 pb-8 mb-8">
                        <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                            <span>💬</span> API do WhatsApp Cloud (Meta)
                        </h3>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label class="block text-slate-800/80 font-semibold mb-2">WhatsApp Integrado</label>
                                <select name="wpp_api_enabled" class="w-full rounded-3xl bg-white border border-slate-200 p-3.5 text-slate-800 outline-none">
                                    <option value="1" {{ env('WHATSAPP_API_ENABLED') == true ? 'selected' : '' }}>Habilitado</option>
                                    <option value="0" {{ env('WHATSAPP_API_ENABLED') == false ? 'selected' : '' }}>Desabilitado</option>
                                </select>
                                <p class="mt-2 text-xs text-slate-500">Ativa ou desativa o envio automático de mensagens.</p>
                            </div>

                            <div>
                                <label class="block text-slate-800/80 font-semibold mb-2">URL da API (com Phone Number ID)</label>
                                <input name="wpp_api_url" type="text" value="{{ old('wpp_api_url', env('WHATSAPP_API_URL')) }}" placeholder="https://graph.facebook.com/v18.0/..." class="w-full rounded-3xl bg-white border border-slate-200 p-3 text-slate-800 outline-none">
                                <p class="mt-2 text-xs text-slate-500">URL com a identificação na Meta.</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="block text-slate-800/80 font-semibold mb-2">Token de Acesso da Meta</label>
                            <textarea name="wpp_api_token" rows="3" placeholder="Token EAAN..." class="w-full rounded-3xl bg-white border border-slate-200 p-4 text-slate-800 outline-none font-mono text-sm">{{ old('wpp_api_token', env('WHATSAPP_API_TOKEN')) }}</textarea>
                            <p class="mt-2 text-xs text-slate-500">Insira o token permanente gerado no Meta Business Manager.</p>
                        </div>
                    </div>

                    <!-- Configuração de E-mail (SMTP) -->
                    <div>
                        <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                            <span>✉️</span> Servidor de E-mail (SMTP)
                        </h3>

                        <div class="grid gap-6 md:grid-cols-3">
                            <div>
                                <label class="block text-slate-800/80 font-semibold mb-2">Host SMTP</label>
                                <input name="mail_host" type="text" value="{{ old('mail_host', env('MAIL_HOST')) }}" placeholder="smtp.mailtrap.io" class="w-full rounded-3xl bg-white border border-slate-200 p-3 text-slate-800 outline-none">
                            </div>

                            <div>
                                <label class="block text-slate-800/80 font-semibold mb-2">Porta SMTP</label>
                                <input name="mail_port" type="number" value="{{ old('mail_port', env('MAIL_PORT')) }}" placeholder="2525" class="w-full rounded-3xl bg-white border border-slate-200 p-3 text-slate-800 outline-none">
                            </div>

                            <div>
                                <label class="block text-slate-800/80 font-semibold mb-2">Criptografia</label>
                                <select name="mail_encryption" class="w-full rounded-3xl bg-white border border-slate-200 p-3.5 text-slate-800 outline-none">
                                    <option value="none" {{ env('MAIL_ENCRYPTION') == 'none' || env('MAIL_ENCRYPTION') == null || env('MAIL_ENCRYPTION') == 'null' ? 'selected' : '' }}>Nenhuma (null)</option>
                                    <option value="tls" {{ env('MAIL_ENCRYPTION') == 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ env('MAIL_ENCRYPTION') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2 mt-6">
                            <div>
                                <label class="block text-slate-800/80 font-semibold mb-2">Usuário SMTP</label>
                                <input name="mail_username" type="text" value="{{ old('mail_username', env('MAIL_USERNAME')) }}" placeholder="usuario_smtp" class="w-full rounded-3xl bg-white border border-slate-200 p-3 text-slate-800 outline-none">
                            </div>

                            <div>
                                <label class="block text-slate-800/80 font-semibold mb-2">Senha SMTP</label>
                                <input name="mail_password" type="password" value="{{ old('mail_password', env('MAIL_PASSWORD')) }}" placeholder="••••••••" class="w-full rounded-3xl bg-white border border-slate-200 p-3 text-slate-800 outline-none">
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2 mt-6">
                            <div>
                                <label class="block text-slate-800/80 font-semibold mb-2">E-mail do Remetente (From Address)</label>
                                <input name="mail_from_address" type="email" value="{{ old('mail_from_address', env('MAIL_FROM_ADDRESS')) }}" placeholder="contato@empresa.com.br" class="w-full rounded-3xl bg-white border border-slate-200 p-3 text-slate-800 outline-none">
                            </div>

                            <div>
                                <label class="block text-slate-800/80 font-semibold mb-2">Nome do Remetente (From Name)</label>
                                <input name="mail_from_name" type="text" value="{{ old('mail_from_name', env('MAIL_FROM_NAME')) }}" placeholder="TopoGest" class="w-full rounded-3xl bg-white border border-slate-200 p-3 text-slate-800 outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-6">
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-3xl bg-orange-500 px-8 py-3.5 text-xs font-black uppercase tracking-[1px] text-slate-800 shadow-lg shadow-orange-500/30 hover:bg-orange-600 transition">
                        Salvar Configurações
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

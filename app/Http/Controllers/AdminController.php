<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Base;
use App\Models\Pasta;
use App\Models\Arquivo;
use App\Models\Marco;
use App\Rules\CpfValido;
use Illuminate\Http\Request;
use App\Notifications\StatusPermissaoNotification;
use App\Notifications\SolicitacaoExclusaoPasta;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Services\WhatsappService;

class AdminController extends Controller
{
    /**
     * DASHBOARD PRINCIPAL
     */
    public function index()
    {
        // Verifica se existe algum admin pendente de aprovação
        $temPendentes = User::where('role', 'admin')
                            ->where('approved', false)
                            ->exists();

        $totalClientes = User::where('role', 'cliente')->count();
        $totalServicosPendentes = Pasta::where('tipo_servico', 'pendente')
                                        ->whereHas('parent', function($q) {
                                            $q->whereHas('parent', function($q2) {
                                                $q2->whereNull('parent_id');
                                            })->whereNotNull('parent_id');
                                        })
                                        ->count();
        $totalBases = Base::count();
        
        $totalBca = Marco::where('credencial', 'BCA')->count();
        $totalEmes = Marco::where('credencial', 'EMES')->count();

        return view('admin.dashboard', compact('temPendentes', 'totalClientes', 'totalServicosPendentes', 'totalBases', 'totalBca', 'totalEmes'));
    }

    /**
     * GESTÃO DE CLIENTES
     */
    public function clientes(Request $request)
    {
        $search = $request->input('search');
        $query = User::where('role', 'cliente')->latest()->get();
        
        $usuarios = $this->aplicarFiltroPesquisa($query, $search);
        
        return view('admin.clientes', compact('usuarios'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'cpf' => ['required', 'string', new CpfValido(), 'unique:users,cpf,' . $id],
            'phone' => 'required|string',
        ]);

        $documentoLimpo = preg_replace('/[^0-9]/', '', $request->cpf);
        
        $user->update([
            'name'  => ucwords(mb_strtolower($request->name)),
            'email' => $request->email,
            'cpf'   => $documentoLimpo,
            'tipo'  => (strlen($documentoLimpo) > 11) ? 'PJ' : 'PF',
            'phone' => preg_replace('/[^0-9]/', '', $request->phone),
        ]);

        return response()->json(['success' => true, 'message' => 'Dados atualizados com sucesso!']);
    }

    public function gestaoCliente($id)
    {
        $cliente = User::findOrFail($id);

        $pendentes = Pasta::ownedBy($id)
            ->where('tipo_servico', 'pendente')
            ->whereHas('parent.parent', function($query) {
                $query->whereNull('parent_id');
            })->get();

        $prontos = Pasta::ownedBy($id)
            ->where('tipo_servico', 'pronto')
            ->whereHas('parent.parent', function($query) {
                $query->whereNull('parent_id');
            })->get();

        $documentos = Arquivo::whereHas('pasta', function($q) use ($id) {
            $q->where('cliente_id', $id);
        })->latest()->get();

        return view('admin.clientes.gestao', compact('cliente', 'pendentes', 'prontos', 'documentos'));
    }

    /**
     * GESTÃO DE PERMISSÕES ADM
     */
    public function pendentes(Request $request)
    {
        $searchPendentes = $request->input('search_pendentes');
        $searchAdmins = $request->input('search_admins');

        $solicitacoesRaw = User::where('role', 'admin')->where('approved', 0)->latest()->get();
        
        $administradoresRaw = User::where(function($query) {
            $query->where('role', 'admin')->where('approved', 1);
        })->orWhere('id', auth()->id())->latest()->get();

        $solicitacoes = $this->aplicarFiltroPesquisa($solicitacoesRaw, $searchPendentes);
        $administradores = $this->aplicarFiltroPesquisa($administradoresRaw, $searchAdmins);

        return view('admin.pendentes', compact('solicitacoes', 'administradores'));
    }

    public function gestaoPermissoes(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $action = $request->input('action');
        $adminAtual = auth()->user()->name;
        $linkWpp = null;

        $whatsappService = app(WhatsappService::class);

        if ($action === 'confirmar') {
            $user->update(['role' => 'admin', 'approved' => 1]);
            $user->notify(new StatusPermissaoNotification('aprovado', $adminAtual));
            $msgWpp = "Olá {$user->name}, sua solicitação para Administrador no TopoGest foi APROVADA por {$adminAtual}.";
            $linkWpp = null;
            if ($whatsappService->enabled()) {
                $whatsappService->send($user->phone, $msgWpp);
            } else {
                $linkWpp = $this->gerarLinkWhatsApp($user->phone, $msgWpp);
            }
            $mensagem = 'Privilégio concedido.';
            
        } elseif ($action === 'negar') {
            $user->update(['role' => 'cliente', 'approved' => 1]);
            $user->notify(new StatusPermissaoNotification('reprovada', $adminAtual));
            $msgWpp = "Olá {$user->name}, sua solicitação para Administrador no TopoGest foi RECUSADA por {$adminAtual}.";
            $linkWpp = null;
            if ($whatsappService->enabled()) {
                $whatsappService->send($user->phone, $msgWpp);
            } else {
                $linkWpp = $this->gerarLinkWhatsApp($user->phone, $msgWpp);
            }
            $mensagem = 'Solicitação negada.';
            
        } elseif ($action === 'remover') {
            $user->update(['role' => 'cliente', 'approved' => 1]);
            $mensagem = 'Privilégio removido.';
        }

        return response()->json(['success' => true, 'message' => $mensagem, 'whatsapp_url' => $linkWpp]);
    }

    /**
     * LOCALIZADOR DE BASES (UTM & KML)
     */
    public function bases()
    {
        $bases = Base::orderBy('nome')->paginate(20)->withQueryString();
        return view('admin.bases.index', compact('bases'));
    }

    public function buscarBase(Request $request)
    {
        $norte = $this->normalizeDecimalInput($request->input('norte'));
        $este = $this->normalizeDecimalInput($request->input('este'));
        $nome = $request->input('nome');

        $query = Base::query();

        if ($nome) { $query->where('nome', 'LIKE', "%{$nome}%"); }

        if ($this->isValidDecimal($norte) && $this->isValidDecimal($este)) {
            $query->select('*')
                ->selectRaw("SQRT(POW(norte - ?, 2) + POW(este - ?, 2)) AS distancia", [(float) $norte, (float) $este])
                ->orderBy('distancia', 'asc');
        }

        $bases = $query->orderBy('nome')->paginate(20)->withQueryString();
        $destaqueId = ($norte && $este && $bases->count() > 0) ? $bases->first()->id : null;
        $filtroCoordenada = ($norte && $este);

        return view('admin.bases.index', compact('bases', 'destaqueId', 'filtroCoordenada'));
    }

    public function storeBase(Request $request)
    {
        $request->validate([
            'nome' => 'required|unique:bases,nome',
            'norte' => 'required',
            'este' => 'required',
            'arquivo_zip' => 'required|mimes:zip|max:20480',
        ]);

        if (Base::where('norte', $request->norte)->where('este', $request->este)->exists()) {
            return redirect()->back()->withErrors(['norte' => 'Coordenadas já cadastradas.']);
        }

        $path = $request->file('arquivo_zip')->store('bases_zip', 'public');
        $latitude = $longitude = null;

        if (class_exists('ZipArchive')) {
            $zip = new \ZipArchive;
            if ($zip->open(storage_path('app/public/' . $path)) === TRUE) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);
                    if (pathinfo($filename, PATHINFO_EXTENSION) == 'kml') {
                        $dom = new \DOMDocument();
                        @$dom->loadXML($zip->getFromIndex($i));
                        $coordsTags = $dom->getElementsByTagName('coordinates');
                        if ($coordsTags->length > 0) {
                            $partes = explode(',', trim($coordsTags->item(0)->nodeValue));
                            if (count($partes) >= 2) {
                                $longitude = trim($partes[0]);
                                $latitude = trim($partes[1]);
                            }
                        }
                        break;
                    }
                }
                $zip->close();
            }
        }

        Base::create([
            'nome' => $request->nome, 'norte' => $request->norte, 'este' => $request->este,
            'latitude' => $latitude, 'longitude' => $longitude, 'arquivo_zip' => $path
        ]);

        return redirect()->route('admin.bases.index')->with('success', 'Base cadastrada!');
    }

    public function destroyBase($id)
    {
        $base = Base::findOrFail($id);
        if ($base->arquivo_zip) { Storage::disk('public')->delete($base->arquivo_zip); }
        $base->delete();
        return redirect()->route('admin.bases.index')->with('success', 'Base removida!');
    }

    public function imagens()
    {
        $imageFiles = [
            'background_topo' => ['label' => 'Background Principal', 'name' => 'background-topo.jpg'],
            'logo_icon' => ['label' => 'Logo Ícone', 'name' => 'logo-icon.png'],
            'logo_text' => ['label' => 'Logo Texto', 'name' => 'logo-text.png'],
            'logo_completa' => ['label' => 'Logo Completa', 'name' => 'logo-completa.png'],
        ];

        $content = $this->loadPublicContent();

        return view('admin.imagens', compact('imageFiles', 'content'));
    }

    public function storeImagens(Request $request)
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

        $uploads = [
            'background_topo' => 'background-topo.jpg',
            'logo_icon' => 'logo-icon.png',
            'logo_text' => 'logo-text.png',
            'logo_completa' => 'logo-completa.png',
        ];

        $destination = public_path('images');
        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        foreach ($uploads as $input => $filename) {
            if ($request->hasFile($input)) {
                $request->file($input)->move($destination, $filename);
            }
        }

        $content = $this->loadPublicContent();

        $content['sobre'] = $request->input('sobre_text', $content['sobre']);
        $content['quem_somos'] = $request->input('quem_somos_text', $content['quem_somos']);
        $content['faq'] = [
            [
                'question' => $request->input('faq_1_question', $content['faq'][0]['question'] ?? ''),
                'answer' => $request->input('faq_1_answer', $content['faq'][0]['answer'] ?? ''),
            ],
            [
                'question' => $request->input('faq_2_question', $content['faq'][1]['question'] ?? ''),
                'answer' => $request->input('faq_2_answer', $content['faq'][1]['answer'] ?? ''),
            ],
            [
                'question' => $request->input('faq_3_question', $content['faq'][2]['question'] ?? ''),
                'answer' => $request->input('faq_3_answer', $content['faq'][2]['answer'] ?? ''),
            ],
        ];

        $content['whatsapp'] = [
            'contato' => $request->input('wpp_contato', $content['whatsapp']['contato'] ?? ''),
            'suporte' => $request->input('wpp_suporte', $content['whatsapp']['suporte'] ?? ''),
            'agendar' => $request->input('wpp_agendar', $content['whatsapp']['agendar'] ?? ''),
        ];

        $this->savePublicContent($content);

        $activeTab = $request->input('active_tab', 'imagens');

        if ($activeTab === 'configuracoes') {
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

            $this->updateEnvFile([
                'WHATSAPP_API_ENABLED' => $request->input('wpp_api_enabled') == '1' ? 'true' : 'false',
                'WHATSAPP_API_URL' => $request->input('wpp_api_url') ?? '',
                'WHATSAPP_API_TOKEN' => $request->input('wpp_api_token') ?? '',
                'MAIL_HOST' => $request->input('mail_host') ?? '',
                'MAIL_PORT' => $request->input('mail_port') ?? '',
                'MAIL_USERNAME' => $request->input('mail_username') ?? '',
                'MAIL_PASSWORD' => $request->input('mail_password') ?? '',
                'MAIL_ENCRYPTION' => $request->input('mail_encryption') === 'none' ? 'null' : ($request->input('mail_encryption') ?? 'null'),
                'MAIL_FROM_ADDRESS' => $request->input('mail_from_address') ?? '',
                'MAIL_FROM_NAME' => $request->input('mail_from_name') ?? '',
            ]);

            try {
                \Illuminate\Support\Facades\Artisan::call('config:clear');
            } catch (\Exception $e) {
                // Ignora se não for possível executar no CLI
            }

            return redirect()->route('admin.imagens.index', ['tab' => 'configuracoes'])->with('success', 'Configurações de integração atualizadas com sucesso!');
        }

        return redirect()->route('admin.imagens.index', ['tab' => $activeTab])->with('success', 'Imagens e conteúdo atualizados com sucesso!');
    }

    private function updateEnvFile(array $data): void
    {
        $path = base_path('.env');
        if (!file_exists($path)) {
            return;
        }

        $content = file_get_contents($path);

        foreach ($data as $key => $value) {
            // Se o valor contiver espaços ou caracteres especiais, envolve em aspas
            if (str_contains($value, ' ') || str_contains($value, '"') || str_contains($value, "'") || str_contains($value, '$')) {
                $value = '"' . str_replace('"', '\"', $value) . '"';
            }

            // Substitui se já existe, caso contrário adiciona no final
            if (preg_match("/^{$key}=/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
        }

        file_put_contents($path, $content);
    }

    public function loadPublicContent(): array
    {
        $path = storage_path('app/site_content.json');

        if (!file_exists($path)) {
            $this->savePublicContent($this->defaultPublicContent());
        }

        $content = json_decode(file_get_contents($path), true);

        if (!is_array($content)) {
            $content = $this->defaultPublicContent();
        }

        return array_merge($this->defaultPublicContent(), $content);
    }

    private function savePublicContent(array $content): void
    {
        $destination = dirname(storage_path('app/site_content.json'));

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        file_put_contents(storage_path('app/site_content.json'), json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function defaultPublicContent(): array
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

    private function longitudeToUtmZone(float $longitude): int
    {
        return (int) floor(($longitude + 180) / 6) + 1;
    }

    private function normalizeDecimalInput($value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        return str_replace(',', '.', $value);
    }

    private function isValidDecimal($value): bool
    {
        return $value !== null && is_numeric($value);
    }

    /**
     * Converte coordenadas UTM para Latitude/Longitude.
     */
    private function utmToLatLng(float $easting, float $northing, int $zoneNumber, bool $southernHemisphere = true): array
    {
        $a = 6378137.0;
        $e = 0.081819190842622;
        $e1sq = 0.0067394967565869;
        $k0 = 0.9996;

        $x = $easting - 500000.0;
        $y = $northing;
        if ($southernHemisphere) {
            $y -= 10000000.0;
        }

        $m = $y / $k0;
        $mu = $m / ($a * (1 - pow($e, 2) / 4 - 3 * pow($e, 4) / 64 - 5 * pow($e, 6) / 256));

        $e1 = (1 - sqrt(1 - pow($e, 2))) / (1 + sqrt(1 - pow($e, 2)));

        $j1 = (3 * $e1 / 2 - 27 * pow($e1, 3) / 32);
        $j2 = (21 * pow($e1, 2) / 16 - 55 * pow($e1, 4) / 32);
        $j3 = (151 * pow($e1, 3) / 96);
        $j4 = (1097 * pow($e1, 4) / 512);

        $fp = $mu + $j1 * sin(2 * $mu) + $j2 * sin(4 * $mu) + $j3 * sin(6 * $mu) + $j4 * sin(8 * $mu);

        $c1 = $e1sq * pow(cos($fp), 2);
        $t1 = pow(tan($fp), 2);
        $r1 = $a * (1 - pow($e, 2)) / pow(1 - pow($e, 2) * pow(sin($fp), 2), 1.5);
        $n1 = $a / sqrt(1 - pow($e, 2) * pow(sin($fp), 2));
        $d = $x / ($n1 * $k0);

        $q1 = $n1 * tan($fp) / $r1;
        $q2 = pow($d, 2) / 2;
        $q3 = (5 + 3 * $t1 + 10 * $c1 - 4 * pow($c1, 2) - 9 * $e1sq) * pow($d, 4) / 24;
        $q4 = (61 + 90 * $t1 + 298 * $c1 + 45 * pow($t1, 2) - 252 * $e1sq - 3 * pow($c1, 2)) * pow($d, 6) / 720;
        $latRad = $fp - $q1 * ($q2 - $q3 + $q4);

        $q5 = $d;
        $q6 = (1 + 2 * $t1 + $c1) * pow($d, 3) / 6;
        $q7 = (5 - 2 * $c1 + 28 * $t1 - 3 * pow($c1, 2) + 8 * $e1sq + 24 * pow($t1, 2)) * pow($d, 5) / 120;
        $lonRad = deg2rad(($zoneNumber - 1) * 6 - 180 + 3) + ($q5 - $q6 + $q7) / cos($fp);

        return [
            'latitude' => rad2deg($latRad),
            'longitude' => rad2deg($lonRad),
        ];
    }

    public function visualizarMapa($id)
    {
        $base = Base::findOrFail($id);
        $searchNorte = request()->query('norte');
        $searchEste = request()->query('este');

        $baseLat = $base->latitude;
        $baseLng = $base->longitude;
        $utmZone = 20;
        $isSouthern = true;

        if ($baseLat !== null && $baseLng !== null) {
            $utmZone = $this->longitudeToUtmZone((float) $baseLng);
            $isSouthern = $baseLat < 0;
        }

        if (($baseLat === null || $baseLng === null) && $base->norte && $base->este) {
            $utm = $this->utmToLatLng((float) $base->este, (float) $base->norte, $utmZone, $isSouthern);
            $baseLat = $utm['latitude'];
            $baseLng = $utm['longitude'];
        }

        $searchLat = null;
        $searchLng = null;

        if ($searchNorte !== null && $searchEste !== null) {
            $searchNorte = $this->normalizeDecimalInput($searchNorte);
            $searchEste = $this->normalizeDecimalInput($searchEste);
            if ($this->isValidDecimal($searchNorte) && $this->isValidDecimal($searchEste)) {
                $searchUtm = $this->utmToLatLng((float) $searchEste, (float) $searchNorte, $utmZone, $isSouthern);
                $searchLat = $searchUtm['latitude'];
                $searchLng = $searchUtm['longitude'];
            }
        }

        if (($baseLat === null || $baseLng === null) && $searchLat !== null && $searchLng !== null) {
            $baseLat = $searchLat;
            $baseLng = $searchLng;
        }

        // Buscar as 3 bases mais próximas
        $norteVal = $this->normalizeDecimalInput($searchNorte);
        $esteVal = $this->normalizeDecimalInput($searchEste);
        $centroNorte = ($norteVal && $this->isValidDecimal($norteVal)) ? (float) $norteVal : (float) $base->norte;
        $centroEste = ($esteVal && $this->isValidDecimal($esteVal)) ? (float) $esteVal : (float) $base->este;

        $basesProximas = Base::where('id', '!=', $base->id)
            ->select('*')
            ->selectRaw("SQRT(POW(norte - ?, 2) + POW(este - ?, 2)) AS distancia", [$centroNorte, $centroEste])
            ->orderBy('distancia', 'asc')
            ->take(3)
            ->get();

        $basesProximas = $basesProximas->map(function($b) use ($utmZone, $isSouthern) {
            $lat = $b->latitude;
            $lng = $b->longitude;
            if (($lat === null || $lng === null) && $b->norte && $b->este) {
                $utm = $this->utmToLatLng((float) $b->este, (float) $b->norte, $utmZone, $isSouthern);
                $lat = $utm['latitude'];
                $lng = $utm['longitude'];
            }
            $b->lat_resolvido = $lat;
            $b->lng_resolvido = $lng;
            return $b;
        });

        return view('admin.bases.mapa', compact('base', 'searchNorte', 'searchEste', 'baseLat', 'baseLng', 'searchLat', 'searchLng', 'basesProximas'));
    }

    /**
     * SEGURANÇA: DUPLA AUTORIZAÇÃO (PASTAS)
     */
    public function solicitarExclusao($id)
{
    // Certifique-se de que é \App\Models\Pasta (no plural, conforme seu banco)
    $pasta = \App\Models\Pasta::findOrFail($id);

    $admins = \App\Models\User::where('role', 'admin')
                                ->where('id', '!=', auth()->id())
                                ->get();

    // Se você estiver testando sozinho e não houver OUTRO admin, 
    // a notificação não tem para onde ir. Tente criar um segundo admin no HeidiSQL para teste.
    \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\SolicitacaoExclusaoPasta($pasta));

    return response()->json([
        'success' => true, 
        'message' => 'Solicitação enviada! A exclusão aguarda a aprovação de outro administrador.'
    ]);
}

    public function processarExclusao(Request $request)
    {
        $request->validate(['notificacao_id' => 'required', 'status' => 'required|in:aprovar,recusar']);
        $notificacao = auth()->user()->notifications()->findOrFail($request->notificacao_id);
        $dados = $notificacao->data;

        if ($request->status === 'aprovar') {
            $pasta = Pasta::find($dados['pasta_id']);
            if ($pasta) {
                $pasta->delete();
                $mensagem = "A pasta '{$dados['pasta_nome']}' foi excluída.";
                DB::table('notifications')->where('data', 'like', '%"pasta_id":' . $dados['pasta_id'] . '%')->delete();
            } else {
                $mensagem = "Pasta já excluída.";
                $notificacao->delete();
            }
        } else {
            $mensagem = "Exclusão recusada.";
            $notificacao->markAsRead();
            $notificacao->delete();
        }

        return response()->json(['success' => true, 'message' => $mensagem]);
    }

    /**
     * AUXILIARES
     */
    private function gerarLinkWhatsApp($telefone, $mensagemTexto)
    {
        if (!$telefone) return null;
        $numero = preg_replace('/[^0-9]/', '', $telefone);
        if (! str_starts_with($numero, '55')) {
            $numero = '55' . $numero;
        }
        return "https://api.whatsapp.com/send?phone={$numero}&text=" . urlencode($mensagemTexto);
    }

    private function aplicarFiltroPesquisa($usuarios, $search)
    {
        if (empty($search)) return $usuarios;
        $num = preg_replace('/[^0-9]/', '', $search);
        return $usuarios->filter(function ($u) use ($search, $num) {
            $nomeMatch = !empty($u->name) && mb_stripos($u->name, $search) !== false;
            $cpfMatch = !empty($num) && strlen($num) >= 4 && str_contains(preg_replace('/[^0-9]/', '', $u->cpf), $num);
            return $nomeMatch || $cpfMatch;
        })->values();
    }
}
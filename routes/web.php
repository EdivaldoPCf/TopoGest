<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServicoController;
use App\Http\Controllers\ArquivoController;
use App\Http\Controllers\PastaController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NotificacaoController;
use App\Http\Controllers\MarcoController;
use App\Http\Controllers\SigefMapaController;
use App\Http\Controllers\AdminGeradorController;

/*
|--------------------------------------------------------------------------
| Web Routes - TopoGest
|--------------------------------------------------------------------------
*/



Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/sobre', function () {
    $conteudo = app(AdminController::class)->loadPublicContent();
    return view('sobre', ['conteudo' => $conteudo]);
})->name('sobre');

Route::get('/quem-somos', function () {
    $conteudo = app(AdminController::class)->loadPublicContent();
    return view('quem_somos', ['conteudo' => $conteudo]);
})->name('quem_somos');

Route::get('/perguntas-frequentes', function () {
    $conteudo = app(AdminController::class)->loadPublicContent();
    return view('perguntas_frequentes', ['conteudo' => $conteudo]);
})->name('perguntas_frequentes');

// --- Rota Dashboard (Redirecionamento Inteligente) ---
// Corrigido para usar redirect()->route() e evitar erros de instância no primeiro login
Route::get('/dashboard', function () {
    $user = Auth::user();

    // Se for o dono (seu email) OU se for admin e já estiver aprovado
    if ($user->email === 'edifilho25022004@gmail.com' || ($user->role === 'admin' && $user->approved)) {
        return redirect()->route('admin.dashboard');
    }

    $userId = $user->id;
    $totalPastas = $user->pastas()->count();
    $totalArquivos = \App\Models\Arquivo::whereHas('pasta', function ($query) use ($userId) {
        $query->where('cliente_id', $userId);
    })->count();
    $pendenciasAbertas = \App\Models\Pendencia::whereHas('pasta', function ($query) use ($userId) {
        $query->where('cliente_id', $userId);
    })->where('status', 'pendente')->count();

    return view('client.home', compact('totalPastas', 'totalArquivos', 'pendenciasAbertas'));
})->middleware(['auth', 'verified'])->name('dashboard');

// --- Rotas Autenticadas (Acesso Geral: Admin e Cliente) ---
Route::middleware('auth')->group(function () {
    
    // Perfil do Usuário
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Serviços e Arquivos
    Route::get('/meus-servicos', [ServicoController::class, 'meusServicos'])->name('meus.servicos');
    Route::get('/servico/{id}', [ServicoController::class, 'show'])->name('servico.show');
    Route::post('/servico/store', [ServicoController::class, 'store'])->name('servico.store');
    Route::post('/servico/{id}/finalizar', [ServicoController::class, 'finalizar'])->name('servico.finalizar');
    Route::delete('/servico/{id}', [ServicoController::class, 'destroy'])->name('servico.destroy');
    Route::post('/pastas/{id}/upload', [PastaController::class, 'uploadArquivo'])->name('arquivos.store');
    Route::post('/pastas/{id}/pendencia', [PastaController::class, 'storePendencia'])->name('pendencias.store');
    Route::patch('/pastas/{id}/finalizar', [PastaController::class, 'finalizar'])->name('admin.pastas.finalizar');

    // Rota para abrir uma pasta específica e ver seu conteúdo
    Route::get('/admin/pastas/{id}', [App\Http\Controllers\PastaController::class, 'show'])->name('admin.pastas.show');
    Route::get('/meus-servicos/{id}', [ServicoController::class, 'show'])->name('client.servico.show')->middleware('auth');

    // Pastas e Uploads (Gestão de Documentos)
    Route::get('/pasta/{id}', [PastaController::class, 'show'])->name('pasta.show');
    Route::post('/pasta', [PastaController::class, 'store'])->name('pasta.store');
    Route::post('/pasta/{id}/upload', [ArquivoController::class, 'store'])->name('arquivo.upload');
    Route::post('/servico/{id}/pendencia', [ArquivoController::class, 'storePendencia'])->name('arquivo.pendencia');
    Route::delete('/arquivo/{id}', [ArquivoController::class, 'destroy'])->name('arquivo.destroy');
    Route::delete('/pastas/{id}', [PastaController::class, 'destroy'])->name('admin.pastas.destroy');
    Route::delete('/arquivos/{id}', [PastaController::class, 'destroyArquivo'])->name('arquivos.destroy');
    Route::delete('/pendencias/{id}', [PastaController::class, 'destroyPendencia'])->name('pendencias.destroy');

    // Notificações do Sistema
    Route::get('/notificacoes', [NotificacaoController::class, 'index'])->name('notificacoes.index');
    Route::post('/notificacoes/{id}/ler', [NotificacaoController::class, 'marcarComoLida'])->name('notificacoes.ler');

    Route::get('/arquivo/{id}/download', [ArquivoController::class, 'download'])->name('arquivo.download');
    Route::get('/pasta/{id}/mapa-sigef', [SigefMapaController::class, 'parsear'])->name('pasta.mapa-sigef');
    Route::get('/pasta/{id}/memorial-descritivo', [SigefMapaController::class, 'gerarMemorial'])->name('pasta.memorial-descritivo');
    Route::get('/pasta/{id}/dxf', [SigefMapaController::class, 'gerarDxf'])->name('pasta.dxf');
    Route::get('/documentos/recentes/json', function () {
        $userId = auth()->id();
        
        $arquivosEnviados = \App\Models\Arquivo::with('pasta')
            ->whereHas('pasta', function($query) use ($userId) {
                $query->where('cliente_id', $userId);
            })
            ->latest()
            ->take(12)
            ->get();
        
        $downloadsRecentes = \App\Models\ActivityLog::with('user')
            ->where('user_id', $userId)
            ->where('acao', 'download')
            ->latest()
            ->take(12)
            ->get();

        return response()->json([
            'arquivosEnviados' => $arquivosEnviados,
            'downloadsRecentes' => $downloadsRecentes,
        ]);
    })->name('documentos.recentes.json');

    Route::get('/documentos/recentes', function () {
        $userId = auth()->id();
        
        $arquivosEnviados = \App\Models\Arquivo::with('pasta')
            ->whereHas('pasta', function($query) use ($userId) {
                $query->where('cliente_id', $userId);
            })
            ->latest()
            ->take(12)
            ->get();
        
        $downloadsRecentes = \App\Models\ActivityLog::with('user')
            ->where('user_id', $userId)
            ->where('acao', 'download')
            ->latest()
            ->take(12)
            ->get();

        return view('documentos.index', compact('arquivosEnviados', 'downloadsRecentes'));
    })->name('documentos.recentes');
});

// --- Rotas Administrativas (Protegidas por Aprovação Técnica) ---
    Route::middleware(['auth', 'check.approval'])->group(function () {
    
    // Painel Principal
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    
    Route::post('/gerador-mapa/pdf', [AdminController::class, 'generateMapPdf'])->name('admin.gerador.pdf');

    // Importação de Trabalhos
    Route::get('/importacao', [\App\Http\Controllers\AdminImportacaoController::class, 'index'])->name('admin.importacao.index');
    Route::post('/importacao/buscar', [\App\Http\Controllers\AdminImportacaoController::class, 'buscarPastas'])->name('admin.importacao.buscar');
    Route::post('/importacao/processar', [\App\Http\Controllers\AdminImportacaoController::class, 'processarPasta'])->name('admin.importacao.processar');
    Route::post('/importacao/upload-web', [\App\Http\Controllers\AdminImportacaoController::class, 'uploadWeb'])->name('admin.importacao.uploadWeb');
    Route::post('/importacao/sync-settings', [\App\Http\Controllers\AdminImportacaoController::class, 'salvarConfiguracaoSync'])->name('admin.importacao.syncSettings');
    Route::post('/importacao/sync-now', [\App\Http\Controllers\AdminImportacaoController::class, 'syncAgora'])->name('admin.importacao.syncNow');
    Route::get('/importacao/sync-progress', [\App\Http\Controllers\AdminImportacaoController::class, 'getSyncProgress'])->name('admin.importacao.syncProgress');
    Route::post('/pastas/{pasta}/sync', [\App\Http\Controllers\AdminImportacaoController::class, 'syncPastaUnica'])->name('admin.pastas.sync');
    Route::post('/admin/arquivos/{id}/toggle-oculto', [\App\Http\Controllers\AdminImportacaoController::class, 'toggleArquivoOculto'])->name('admin.arquivos.toggle-oculto');
    Route::post('/admin/pastas/{id}/toggle-oculto', [\App\Http\Controllers\AdminImportacaoController::class, 'togglePastaOculto'])->name('admin.pastas.toggle-oculto');

    // Gestão de Clientes e Permissões
    Route::get('/admin/clientes', [AdminController::class, 'clientes'])->name('admin.clientes');
    Route::put('/admin/clientes/{id}', [AdminController::class, 'update'])->name('admin.clientes.update');
    
    // Gestão de ADMs Pendentes
    Route::get('/admin/pendentes', [AdminController::class, 'pendentes'])->name('admin.pendentes');
    Route::post('/admin/gestao-permissoes/{id}', [AdminController::class, 'gestaoPermissoes'])->name('admin.gestao.permissoes');

    // Gestão de Marcos (Topografia)
    Route::get('/admin/marcos', [MarcoController::class, 'index'])->name('admin.marcos.index');
    Route::post('/admin/marcos', [MarcoController::class, 'store'])->name('admin.marcos.store');
    Route::delete('/admin/marcos/{id}', [MarcoController::class, 'destroy'])->name('admin.marcos.destroy');
    Route::post('/admin/marcos/destroy-multiple', [MarcoController::class, 'destroyMultiple'])->name('admin.marcos.destroy-multiple');
    Route::get('/admin/marcos/mapa/{imovel}', [MarcoController::class, 'mapa'])->name('admin.marcos.mapa');

    // Processamento da Dupla Aprovação
    Route::post('/admin/pastas/processar-exclusao', [AdminController::class, 'processarExclusao'])->name('admin.pastas.processar_exclusao');

    // Listagem de pastas (Onde o erro está acontecendo)
    Route::get('/admin/pastas', [PastaController::class, 'index'])->name('admin.pastas.index');
    
    // Criação de novas pastas
    Route::post('/admin/pastas', [PastaController::class, 'store'])->name('pasta.store');
    
    // Solicitação de exclusão (Dupla autorização)
    Route::post('/admin/pastas/{id}/solicitar-exclusao', [PastaController::class, 'solicitarExclusao'])->name('admin.pastas.solicitar-exclusao');
    
    // Processamento da exclusão pela Central de Notificações
    Route::post('/admin/pastas/processar-exclusao', [AdminController::class, 'processarExclusao'])->name('admin.pastas.processar_exclusao');

    Route::patch('/pastas/{id}/tornar-pendente', [\App\Http\Controllers\PastaController::class, 'tornarPendente'])->name('admin.pastas.tornar-pendente');

    Route::patch('/admin/pastas/{id}/tornar-pendente', [\App\Http\Controllers\PastaController::class, 'tornarPendente'])->name('admin.pastas.tornar-pendente');

    Route::patch('/admin/pastas/{id}/sigef', [\App\Http\Controllers\PastaController::class, 'updateSigef'])->name('admin.pastas.update-sigef');
    Route::post('/admin/pastas/{id}/cpf', [\App\Http\Controllers\PastaController::class, 'vincularCpf'])->name('admin.pastas.vincularCpf');

    Route::get('/admin/clientes/{id}', [App\Http\Controllers\AdminController::class, 'gestaoCliente'])->name('admin.clientes.gestao');

    // routes/web.php (Dentro do grupo de rotas do admin)

Route::get('/admin/bases', [App\Http\Controllers\AdminController::class, 'bases'])->name('admin.bases.index');
Route::post('/admin/bases', [App\Http\Controllers\AdminController::class, 'storeBase'])->name('admin.bases.store');
Route::get('/admin/bases/buscar', [App\Http\Controllers\AdminController::class, 'buscarBase'])->name('admin.bases.buscar');

Route::delete('/admin/bases/{id}', [App\Http\Controllers\AdminController::class, 'destroyBase'])->name('admin.bases.destroy');

Route::get('/admin/bases/{id}/mapa', [AdminController::class, 'visualizarMapa'])->name('admin.bases.mapa');

Route::get('/admin/imagens', [AdminController::class, 'imagens'])->name('admin.imagens.index');
Route::post('/admin/imagens', [AdminController::class, 'storeImagens'])->name('admin.imagens.store');

Route::post('/admin/pastas/{id}/solicitar', [AdminController::class, 'solicitarExclusao'])->name('admin.pastas.solicitar');

Route::post('/admin/pastas/{id}/solicitar', [AdminController::class, 'solicitarExclusao'])->name('admin.pastas.solicitar');

    // Gerador Express de Planta e Memorial
    Route::get('/admin/gerador-express', [AdminGeradorController::class, 'index'])->name('admin.gerador.index');
    Route::post('/admin/gerador-express', [AdminGeradorController::class, 'processar'])->name('admin.gerador.processar');
    Route::get('/admin/gerador-express/{tempId}/dxf', [AdminGeradorController::class, 'dxf'])->name('admin.gerador.dxf');
    Route::get('/admin/gerador-express/{tempId}/txt', [AdminGeradorController::class, 'txt'])->name('admin.gerador.txt');

});

// Inclui as rotas de autenticação padrão do Laravel (Login, Register, etc.)
require __DIR__.'/auth.php';
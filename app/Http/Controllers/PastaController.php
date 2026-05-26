<?php

namespace App\Http\Controllers;

use App\Models\Pasta;
use App\Models\User;
use App\Models\Arquivo;
use App\Models\Pendencia;
use App\Notifications\SolicitacaoExclusaoPasta;
use App\Notifications\NovaMovimentacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use App\Services\WhatsappService;

class PastaController extends Controller
{
    // Lista as pastas de ano na tela principal
    public function index(Request $request)
    {
        $status = $request->query('status', 'pendente');
        
        $pastas = Pasta::whereNull('parent_id')
                    ->where('tipo_servico', $status)
                    ->orderBy('nome', 'desc')
                    ->get();

        return response()
            ->view('admin.pastas.index', compact('pastas', 'status'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    // Criar nova pasta de ano ou subpasta (3º nível vincula cliente)
    public function store(Request $request)
    {
        $parent = Pasta::find($request->parent_id);
        
        $data = [
            'nome' => $request->nome,
            'parent_id' => $request->parent_id,
            'tipo_servico' => $parent ? $parent->tipo_servico : ($request->tipo_servico ?? 'pendente'),
        ];

        // Se o pai for Nível 2 (Ano > Categoria), estamos criando o Nível 3 (Imóvel)
        if ($parent && $parent->parent_id && !$parent->parent->parent_id) {
            $identificador = $request->identificador_cliente;
            $identificadorLimpo = preg_replace('/[^0-9]/', '', $identificador);

            if (strlen($identificadorLimpo) !== 11) {
                return back()->with('error', 'O CPF informado deve conter exatamente 11 dígitos.')->withInput();
            }

            $cliente = User::where(function($query) use ($identificador, $identificadorLimpo) {
                $query->where('cpf', $identificador)
                      ->orWhere('cpf', $identificadorLimpo);
            })->first();

            if (!$cliente) {
                $data['cliente_id'] = null;
                $data['identificador_cliente'] = $identificadorLimpo;
                $data['categoria_servico'] = $request->categoria_servico;
                
                Pasta::create($data);
                return back()->with('success', 'Imóvel criado com sucesso! Cliente com este CPF não foi cadastrado ainda; o imóvel ficará aguardando o cadastro dele.');
            }

            $data['cliente_id'] = $cliente->id;
            $data['identificador_cliente'] = $cliente->cpf;
            $data['categoria_servico'] = $request->categoria_servico;
        } 
        // Se estivermos no Nível 3 ou mais fundo, herda o cliente do pai automaticamente
        elseif ($parent && ($parent->cliente_id || $parent->identificador_cliente)) {
            $data['cliente_id'] = $parent->cliente_id;
            $data['identificador_cliente'] = $parent->identificador_cliente;
            $data['categoria_servico'] = $parent->categoria_servico;
        }

        Pasta::create($data);
        return back()->with('success', 'Criado com sucesso!');
    }

    // Abre o diretório e carrega subpastas, arquivos e pendências
    public function show($id) {
        $pasta = Pasta::with(['subpastas.cliente', 'arquivos', 'pendencias', 'parent.parent'])->findOrFail($id);
        return response()
            ->view('admin.pastas.show', compact('pasta'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    // Upload de arquivos com notificação automática e exclusão de pendência
    public function uploadArquivo(Request $request, $pastaId) 
    {
        $request->validate(['arquivo' => 'required|file|max:20480']);

        $pasta = Pasta::find($pastaId);
        if (!$pasta) {
            return back()->with('error', 'Pasta não encontrada.');
        }

        $file = $request->file('arquivo');
        $path = $file->store('documentos', 'public');

        // Cria o arquivo
        $arquivo = \App\Models\Arquivo::create([
            'nome' => $request->nome,
            'path' => $path,
            'tamanho' => round($file->getSize() / 1024 / 1024, 2),
            'tipo' => strtoupper($file->getClientOriginalExtension()),
            'pasta_id' => $pastaId
        ]);

        // Apaga a pendência com o mesmo nome
        \App\Models\Pendencia::where('pasta_id', $pastaId)
            ->where('titulo', $request->nome)
            ->delete();

        // Envia notificação para os Admins se for cliente enviando
        if (auth()->user()->role !== 'admin') {
            $admins = \App\Models\User::where('role', 'admin')->get();
            $clienteNome = auth()->user()->name;
            
            // Mensagem detalhada
            $mensagem = "O cliente {$clienteNome} enviou o documento '{$request->nome}' para o imóvel '{$pasta->nome}'.";
            
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\NovaMovimentacao(
                    "Novo Arquivo: {$request->nome}", 
                    $mensagem, 
                    $pastaId
                ));
            }

            $whatsappService = app(WhatsappService::class);
            if ($whatsappService->enabled()) {
                foreach ($admins as $admin) {
                    $whatsappService->send($admin->phone, $mensagem);
                }
            }
        }

        return back()->with('success', 'Arquivo enviado com sucesso!');
    }

    // Criar pendência e notificar cliente com link direto
    public function storePendencia(Request $request, $pastaId)
    {
        $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['required', 'string'],
        ]);

        // 1. Primeiro buscamos a pasta para saber quem é o cliente vinculado
        $pasta = Pasta::find($pastaId);

        // 2. Criamos a pendência no banco
        $pendencia = \App\Models\Pendencia::create([
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'pasta_id' => $pastaId,
            'status' => 'aberto'
        ]);

        // 3. Agora que $pasta existe, podemos notificar o cliente
        if($pasta && $pasta->cliente_id) {
            $cliente = \App\Models\User::find($pasta->cliente_id);
            
            // Enviamos o Título e a Descrição separadamente para a central ficar bonita
            $cliente->notify(new NovaMovimentacao(
                $request->titulo, 
                $request->descricao, 
                $pastaId
            ));
            $whatsappService = app(WhatsappService::class);
            if ($whatsappService->enabled()) {
                $whatsappService->send($cliente->phone, "{$request->titulo}: {$request->descricao}");
            }        }

        return back()->with('success', 'Pendência registrada com sucesso!');
    }

    // Finaliza o serviço e move para a lista de "Prontos"
   // Finaliza o serviço, recria a estrutura no status 'pronto' e move a pasta Nível 3
    public function finalizar($id)
    {
        $pasta3 = Pasta::findOrFail($id);
        $pasta2 = $pasta3->parent;
        $pasta1 = $pasta2->parent;

        $novoNivel1 = Pasta::firstOrCreate([
            'nome' => $pasta1->nome,
            'parent_id' => null,
            'tipo_servico' => 'pronto'
        ]);

        $novoNivel2 = Pasta::firstOrCreate([
            'nome' => $pasta2->nome,
            'parent_id' => $novoNivel1->id,
            'tipo_servico' => 'pronto'
        ]);

        $pasta3->update([
            'parent_id' => $novoNivel2->id,
            'tipo_servico' => 'pronto'
        ]);

        if($pasta3->cliente_id) {
            $cliente = User::find($pasta3->cliente_id);
            $mensagem = "Seu serviço '" . $pasta3->nome . "' foi finalizado com sucesso!";
            $cliente->notify(new NovaMovimentacao('Serviço Finalizado', $mensagem, $pasta3->id));
        }

        return redirect()->route('admin.pastas.index', ['status' => 'pronto'])->with('success', 'Serviço finalizado!');
    }

    // Retorna o serviço para 'pendente', recria a estrutura e move a pasta Nível 3
    public function tornarPendente($id)
{
    $pasta3 = Pasta::findOrFail($id);
    $pasta2 = $pasta3->parent;
    $pasta1 = $pasta2->parent;

    // Garante a estrutura no status 'pendente'
    $novoNivel1 = Pasta::firstOrCreate([
        'nome' => $pasta1->nome,
        'parent_id' => null,
        'tipo_servico' => 'pendente'
    ]);

    $novoNivel2 = Pasta::firstOrCreate([
        'nome' => $pasta2->nome,
        'parent_id' => $novoNivel1->id,
        'tipo_servico' => 'pendente'
    ]);

    // Move a pasta do serviço (Nível 3) de volta
    $pasta3->update([
        'parent_id' => $novoNivel2->id,
        'tipo_servico' => 'pendente'
    ]);

    return redirect()->route('admin.pastas.index', ['status' => 'pendente'])->with('success', 'Serviço retornado para pendente!');
}

    public function updateSigef(Request $request, $id)
    {
        $pasta = Pasta::findOrFail($id);
        
        $request->validate([
            'codigo_sigef' => 'nullable|string|max:255',
        ]);

        $pasta->update($request->only('codigo_sigef'));

        return back()->with('success', 'Código SIGEF atualizado com sucesso!');
    }

    // Gatilho para a Exclusão Sensível entre Admins
    public function solicitarExclusao($id)
    {
        $pasta = Pasta::findOrFail($id);
        $solicitante = auth()->user();

        $outrosAdmins = User::where('role', 'admin')
                            ->where('id', '!=', $solicitante->id)
                            ->get();

        Notification::send($outrosAdmins, new SolicitacaoExclusaoPasta($pasta, $solicitante));

        return response()->json(['message' => 'Pedido enviado para aprovação conjunta.']);
    }

    // Excluir Pasta ou Subpasta
    public function destroy($id) {
        Pasta::findOrFail($id)->delete();
        return back()->with('success', 'Pasta excluída!');
    }

    // Excluir Arquivo
    public function destroyArquivo($id) {
        $arquivo = \App\Models\Arquivo::findOrFail($id);
        \Illuminate\Support\Facades\Storage::disk('public')->delete($arquivo->path);
        $arquivo->delete();
        return back()->with('success', 'Arquivo excluído!');
    }

    // Excluir Pendência
    public function destroyPendencia($id) {
        \App\Models\Pendencia::findOrFail($id)->delete();
        return back()->with('success', 'Pendência excluída!');
    }
}
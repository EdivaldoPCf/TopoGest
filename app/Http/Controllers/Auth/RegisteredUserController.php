<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Rules\CpfValido; // Certifique-se de atualizar esta regra para aceitar CNPJ ou remova-a da validação se for restritiva apenas a CPF.
use App\Notifications\NovaSolicitacaoAdmNotification;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        // Limpa a formatação para processamento
        $documentoLimpo = preg_replace('/[^0-9]/', '', $request->cpf);

        $request->merge([
            'cpf' => $documentoLimpo,
            'phone' => preg_replace('/[^0-9]/', '', $request->phone),
        ]);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'unique:users,cpf', new CpfValido()],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'min:10'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'password.confirmed' => 'As senhas digitadas não são iguais.',
            'cpf.unique' => 'Este CPF ou CNPJ já está cadastrado.',
        ]);

        // Define se é PF ou PJ com base na quantidade de dígitos
        $tipo = (strlen($documentoLimpo) > 11) ? 'PJ' : 'PF';

        $nomeFormatado = ucwords(mb_strtolower($request->name));

        $isOwner = ($request->email === 'edifilho25022004@gmail.com' || $request->cpf === '05089607206');
        
        $user = User::create([
            'name' => $nomeFormatado, 
            'cpf' => $request->cpf,
            'tipo' => $tipo, // Salva PF ou PJ
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'approved' => $isOwner ? true : ($request->role === 'cliente'),
        ]);

        if ($request->role === 'admin' && !$isOwner) {
            $admins = User::where('email', 'edifilho25022004@gmail.com')
                ->orWhere(function($query) {
                    $query->where('role', 'admin')->where('approved', 1);
                })->get();

            try {
                Notification::send($admins, new NovaSolicitacaoAdmNotification($user));
            } catch (\Throwable $exception) {
                Log::error('Falha ao enviar notificação de solicitação de administrador', [
                    'user_id' => $user->id,
                    'admin_ids' => $admins->pluck('id')->all(),
                    'exception' => $exception->getMessage(),
                ]);
            }
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard'));
    }
}
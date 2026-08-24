<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NovaSolicitacaoAdmNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $novoUsuario;

    public function __construct($novoUsuario)
    {
        $this->novoUsuario = $novoUsuario;
    }

    public function via($notifiable)
    {
        return ['database', 'mail']; // Central e e-mail automático
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Nova solicitação de administrador')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("O usuário {$this->novoUsuario->name} solicitou acesso de administrador ao sistema.")
            ->line("CPF: {$this->novoUsuario->cpf}")
            ->action('Ver solicitações', route('admin.pendentes'))
            ->line('Acesse o painel para aprovar ou recusar a solicitação.');
    }

    public function toArray($notifiable)
    {
        return [
            'titulo' => 'Nova Solicitação de ADM',
            'previa' => "{$this->novoUsuario->name} solicitou acesso de administrador.",
            'mensagem' => "O usuário {$this->novoUsuario->name} (CPF: {$this->novoUsuario->cpf}) se cadastrou e solicitou privilégios de administrador. Acesse a área de pendentes para confirmar ou negar.",
            'action_url' => route('admin.pendentes'),
        ];
    }
}
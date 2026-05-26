<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StatusPermissaoNotification extends Notification
{
    use Queueable;

    private $status;
    private $adminAvaliador;

    public function __construct($status, $adminAvaliador)
    {
        $this->status = $status; // 'aprovado' ou 'reprovado'
        $this->adminAvaliador = $adminAvaliador;
    }

    public function via($notifiable)
    {
        return ['database', 'mail']; // Salva no banco (Central) e envia Email
    }

    public function toMail($notifiable)
    {
        $mensagem = $this->status === 'aprovado' 
            ? "Sua solicitação para Administrador foi APROVADA por {$this->adminAvaliador}." 
            : "Sua solicitação para Administrador foi RECUSADA por {$this->adminAvaliador}.";

        return (new MailMessage)
                    ->subject('Status da Solicitação de Administrador')
                    ->greeting("Olá, {$notifiable->name}!")
                    ->line($mensagem)
                    ->action('Acessar Sistema', url('/dashboard'))
                    ->line('Obrigado por usar o TopoGest!');
    }

    public function toArray($notifiable)
    {
        return [
            'titulo' => 'Status de Administrador',
            'previa' => "Sua solicitação foi {$this->status}.",
            'mensagem' => "Sua solicitação para perfil de Administrador foi {$this->status} por {$this->adminAvaliador}.",
            'action_url' => route('dashboard'),
        ];
    }
}
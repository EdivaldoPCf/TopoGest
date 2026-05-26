<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SolicitacaoExclusaoPasta extends Notification
{
    use Queueable;

    protected $pasta;

    /**
     * Agora o construtor recebe a pasta corretamente
     */
    public function __construct($pasta)
    {
        $this->pasta = $pasta;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail']; // Salva na central e envia e-mail automático
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Solicitação de exclusão de pasta')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Foi criada uma solicitação de exclusão para a pasta: {$this->pasta->nome}.")
            ->action('Revisar solicitação', route('admin.pendentes'))
            ->line('Aprovação de outro administrador é necessária para concluir a exclusão.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'pasta_id'   => $this->pasta->id,
            'pasta_nome' => $this->pasta->nome,
            'mensagem'   => "Solicitação de exclusão para a pasta: {$this->pasta->nome}",
        ];
    }
}
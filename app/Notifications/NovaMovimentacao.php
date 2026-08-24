<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NovaMovimentacao extends Notification
{
    use Queueable;

    private $titulo;
    private $descricao;
    private $pastaId;

    public function __construct($titulo, $descricao, $pastaId)
    {
        $this->titulo = $titulo;
        $this->descricao = $descricao;
        $this->pastaId = $pastaId;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $rota = ($notifiable->role === 'admin')
            ? route('admin.pastas.show', $this->pastaId)
            : route('client.servico.show', $this->pastaId);

        return (new MailMessage)
            ->subject("Nova movimentação: {$this->titulo}")
            ->greeting("Olá, {$notifiable->name}!")
            ->line($this->descricao)
            ->action('Ver detalhe', $rota)
            ->line('Obrigado por usar o TopoGest!');
    }

    public function toDatabase($notifiable)
    {
        $rota = ($notifiable->role === 'admin') 
            ? route('admin.pastas.show', $this->pastaId) 
            : route('client.servico.show', $this->pastaId);

        return [
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'link' => $rota,
        ];
    }
}
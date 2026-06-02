<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PastaAtualizadaEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $pastaId;
    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct($pastaId, $message = 'Atualizado')
    {
        $this->pastaId = $pastaId;
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        // Usamos PresenceChannel para que os usuários possam ver quem está na pasta,
        // ou apenas um canal que todos com acesso à pasta podem ouvir.
        // Como o Laravel 11 usa PrivateChannel por padrão para rotas autenticadas,
        // podemos usar um canal privado para a pasta.
        return [
            new PrivateChannel('pasta.' . $this->pastaId),
        ];
    }
}

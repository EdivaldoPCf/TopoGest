<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SyncProgressUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $percentage;
    public $current;
    public $total;
    public $last_file;
    public $status;

    /**
     * Create a new event instance.
     */
    public function __construct($percentage, $current, $total, $last_file, $status = 'processing')
    {
        $this->percentage = $percentage;
        $this->current = $current;
        $this->total = $total;
        $this->last_file = $last_file;
        $this->status = $status;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('sync-progress'),
        ];
    }
}

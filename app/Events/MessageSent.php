<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Canal onde será transmitido
     */
    public function broadcastOn()
    {
        // Canal público (pode mudar para PrivateChannel)
        return new Channel('sala.' . $this->message['sala_id']);
    }

    /**
     * Nome do evento no Pusher
     */
    public function broadcastAs()
    {
        return 'message.sent';
    }
}

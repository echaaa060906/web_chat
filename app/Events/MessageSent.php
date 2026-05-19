<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Menentukan channel tempat event harus disiarkan.
     */
    public function broadcastOn(): array
    {
        // 🟢 JIKA CHAT GRUP: Kirim sinyal ke channel privat milik grup tersebut
        if ($this->message->group_id) {
            return [
                new PrivateChannel('group.' . $this->message->group_id),
            ];
        }

        // 👤 JIKA CHAT PERSONAL: Mengirimkan sinyal ke channel privat milik si penerima chat (Fitur Lama)
        return [
            new PrivateChannel('chat.' . $this->message->receiver_id),
        ];
    }

    /**
     * Menentukan nama alias event saat disiarkan ke frontend.
     */
    public function broadcastAs(): string
    {
        return 'MessageSent';
    }
}
<?php

namespace App\Events;

use App\Models\Group;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $group;

    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    /**
     * Menyiarkan event melalui Presence Channel yang sudah aktif digunakan
     */
    public function broadcastOn()
    {
        return new PresenceChannel('online-users');
    }

    /**
     * Nama custom event saat di-listen oleh Laravel Echo
     */
    public function broadcastAs()
    {
        return 'GroupCreated';
    }
}
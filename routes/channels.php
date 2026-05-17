<?php

use Illuminate\Support\Facades\Broadcast;

// 1. Jalur kirim pesan chat privat
Broadcast::channel('chat.{receiverId}', function ($user, $receiverId) {
    return (int) $user->id === (int) $receiverId || (int) $user->id === (int) auth()->id();
});

// 2. Jalur pantau lampu online
Broadcast::channel('online-users', function ($user) {
    return ['id' => $user->id, 'name' => $user->name];
});
<?php

use Illuminate\Support\Facades\Broadcast;

// Channel Presence untuk Indikator Lampu Online
Broadcast::channel('online-users', function ($user) {
    return ['id' => $user->id, 'name' => $user->name];
});

// Channel Private untuk Chat Personal
Broadcast::channel('chat.{receiverId}', function ($user, $receiverId) {
    return (int) $user->id === (int) $receiverId;
});

// Channel Private untuk Chat Kelompok / Grup
Broadcast::channel('group.{groupId}', function ($user, $groupId) {
    return auth()->check(); 
});
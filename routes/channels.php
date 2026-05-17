<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('online-users', function ($user) {
    // Jika user sudah masuk/login, izinkan mereka bergabung ke saluran pantau online ini
    return ['id' => $user->id, 'name' => $user->name];
});
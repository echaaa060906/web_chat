<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController; 
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route Dashboard diarahkan ke ChatController agar bisa membaca data Kontak DAN data Grup sekaligus
Route::get('/dashboard', [ChatController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 👤 Route Chat Personal
    Route::get('/messages/{id}', [ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/messages', [ChatController::class, 'store'])->name('chat.send');

    // 👥 Route Chat Grup
    Route::get('/group-messages/{groupId}', [ChatController::class, 'getGroupMessages'])->name('chat.group_messages');

    // ➕ Route Menambahkan Anggota ke Dalam Grup via Web Halaman
    Route::post('/group-messages/{groupId}/add-member', [ChatController::class, 'addMember'])->name('chat.group_add_member');

    // ✨ Route Mengambil daftar nama seluruh anggota grup secara realtime
    Route::get('/groups/{groupId}/members', [ChatController::class, 'getGroupMembers'])->name('chat.group_members');

    // 🆕 Route Membuat Grup Baru dari Halaman Web
    Route::post('/groups', [ChatController::class, 'createGroup'])->name('chat.create_group');
});

require __DIR__.'/auth.php';
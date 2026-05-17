<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController; // <-- Kuncinya di sini, harus di-import!
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Models\User;

Route::get('/dashboard', function () {
    // Mengambil semua user terdaftar di database, KECUALI user yang sedang login saat ini
    $users = User::where('id', '!=', auth()->id())->get();
    
    // Mengirimkan data user tersebut ke halaman dashboard.blade.php
    return view('dashboard', compact('users'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Kita masukkan lagi ke sini agar aman terlindungi auth
    Route::get('/messages/{receiverId}', [ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/messages', [ChatController::class, 'sendMessage'])->name('chat.send');
});

require __DIR__.'/auth.php';
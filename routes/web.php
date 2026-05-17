<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController; // <-- Kuncinya di sini, harus di-import!
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
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
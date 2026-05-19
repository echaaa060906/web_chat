<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    // Kolom-kolom yang diizinkan untuk diisi (Mass Assignment)
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'group_id',
        'message', // Kolom penampung teks pesan kamu wajib didaftarkan di sini!
    ];

    /**
     * Hubungan relasi ke model User (Pengirim Pesan)
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
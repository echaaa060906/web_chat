<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    // 🟢 KUNCI PERBAIKAN: Laravel wajib tahu 3 kolom ini boleh diisi otomatis
    protected $fillable = ['sender_id', 'receiver_id', 'message'];
}
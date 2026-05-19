<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'created_by'];

    // Relasi: 1 grup memiliki banyak anggota (User)
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    // Relasi: 1 grup berisi banyak pesan (Message)
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use App\Events\MessageSent; // Hubungkan ke file Event Langkah 1

class ChatController extends Controller
{
    // Mengambil riwayat pesan lama
    public function getMessages($receiverId)
    {
        $authId = auth()->id();

        $messages = Message::where(function($query) use ($authId, $receiverId) {
            $query->where('sender_id', $authId)->where('receiver_id', $receiverId);
        })->orWhere(function($query) use ($authId, $receiverId) {
            $query->where('sender_id', $receiverId)->where('receiver_id', $authId);
        })->orderBy('created_at', 'asc')->get();

        return response()->json($messages);
    }

    // Menyimpan dan mengirim pesan baru
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        // Menyimpan pesan ke database (Sudah aman karena $fillable di Message.php sudah kamu perbaiki di foto)
        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);

        // Kirimkan sinyal chat ke server WebSocket Reverb
        try {
            // 🟢 PERBAIKAN: Hapus .toOthers() agar sinyal broadcast tidak tersumbat
            broadcast(new MessageSent($message)); 
        } catch (\Exception $e) {
            // Tetap aman berjalan meski server Reverb sedang offline
        }

        return response()->json(['status' => 'Message sent!', 'message' => $message]);
    }
}
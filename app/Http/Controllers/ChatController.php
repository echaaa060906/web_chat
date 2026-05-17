<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Events\MessageSent;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    // Mengambil semua riwayat pesan antara User Login dengan User Penerima
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

    // Menyimpan pesan baru dan menyiarkannya via WebSocket
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);

        // Memicu Event agar Laravel Reverb menyiarkan pesan ini secara real-time
        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['status' => 'Message sent!', 'message' => $message]);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Group;
use App\Models\Message;
use App\Events\GroupCreated; // Memanggil event baru
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Menampilkan halaman utama dashboard chat dengan data user dan grup.
     */
    public function index()
    {
        $users = User::where('id', '!=', auth()->id())->get();
        
        $groups = Group::where('created_by', auth()->id())
            ->orWhereHas('users', function($q) {
                $q->where('user_id', auth()->id());
            })
            ->get();

        return view('dashboard', compact('users', 'groups'));
    }

    /**
     * Mengambil riwayat pesan chat personal/privat.
     */
    public function getMessages($id)
    {
        $messages = Message::where(function($q) use ($id) {
            $q->where('sender_id', auth()->id())->where('receiver_id', $id);
        })->orWhere(function($q) use ($id) {
            $q->where('sender_id', $id)->where('receiver_id', auth()->id());
        })->get();

        return response()->json($messages);
    }

    /**
     * Mengambil riwayat pesan chat di dalam suatu grup beserta data pengirimnya.
     */
    public function getGroupMessages($groupId)
    {
        $messages = Message::where('group_id', $groupId)
            ->with('sender')
            ->get();

        return response()->json($messages);
    }

    /**
     * Menyimpan dan mengirim pesan baru (Personal & Grup).
     */
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'receiver_id' => 'nullable|exists:users,id',
            'group_id' => 'nullable|exists:groups,id'
        ]);

        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'group_id' => $request->group_id,
            'message' => $request->message
        ]);

        $message->load('sender');

        // Mengirim broadcast pesan jika kamu menggunakan class MessageSent bawaan lama
        if (class_exists('\App\Events\MessageSent')) {
            broadcast(new \App\Events\MessageSent($message))->toOthers();
        }

        return response()->json([
            'status' => 'Pesan terkirim',
            'message' => $message
        ]);
    }

    /**
     * Menambahkan anggota/kontak ke dalam grup lewat dropdown.
     */
    public function addMember(Request $request, $groupId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $group = Group::findOrFail($groupId);
        $group->users()->syncWithoutDetaching([$request->user_id]);

        return response()->json([
            'status' => 'Sukses',
            'message' => 'Anggota berhasil dimasukkan ke dalam grup!'
        ]);
    }

    /**
     * Mengambil daftar nama seluruh anggota grup untuk sub-judul chat.
     */
    public function getGroupMembers($groupId)
    {
        $group = Group::findOrFail($groupId);
        $members = $group->users()->select('users.id', 'users.name')->get();

        return response()->json($members);
    }

    /**
     * Membuat grup baru langsung dari halaman web dan menyiarkannya secara real-time.
     */
    public function createGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        // 1. Simpan grup ke database
        $group = Group::create([
            'name' => $request->name,
            'created_by' => auth()->id()
        ]);

        // 2. Pembuat grup otomatis masuk tabel pivot sebagai anggota pertama
        $group->users()->attach(auth()->id());

        // 3. ✨ BROADCAST REAL-TIME: Kirim data grup baru ke semua user online
        broadcast(new GroupCreated($group))->toOthers();

        return response()->json([
            'status' => 'Sukses',
            'message' => 'Grup baru berhasil dibuat!',
            'group' => $group
        ]);
    }
}
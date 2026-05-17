<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Aplikasi Chat Real-time') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg flex" style="height: 550px;">
                
                <div class="w-1/3 border-r border-gray-200 p-4 overflow-y-auto bg-gray-50">
                    <h3 class="font-bold text-lg mb-4 text-gray-700">Daftar Kontak</h3>
                    <ul>
                        @foreach($users as $user)
                            <li onclick="pilihUser({{ $user->id }}, '{{ $user->name }}')" 
                                class="flex items-center justify-between p-3 mb-2 bg-white border border-gray-200 rounded-lg hover:bg-indigo-50 cursor-pointer transition-all">
                                <span class="font-medium text-gray-800">{{ $user->name }}</span>
                                
                                <span id="status-user-{{ $user->id }}" class="h-3 w-3 rounded-full bg-gray-300 transition-colors" title="Offline"></span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="w-2/3 p-6 flex flex-col justify-between bg-white">
                    <div class="border-b border-gray-200 pb-3 mb-4">
                        <h3 id="chat-with-title" class="font-bold text-lg text-gray-800">Silakan pilih kontak untuk memulai obrolan</h3>
                    </div>

                    <div id="chat-box" class="overflow-y-auto flex-1 p-4 bg-gray-50 border border-gray-200 rounded-lg mb-4 flex flex-col gap-2">
                        <p class="text-gray-500 text-center my-auto">Belum ada obrolan aktif.</p>
                    </div>

                    <div id="form-input-chat" class="flex gap-2 hidden">
                        <input type="text" id="message-input" placeholder="Ketik pesan di sini..." 
                               class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-indigo-500">
                        <button onclick="kirimPesan()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                            Kirim
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        let activeReceiverId = null;
        const currentUserId = "{{ auth()->id() }}";

        // Fungsi A: Ketika salah satu nama kontak diklik
        function pilihUser(id, name) {
            activeReceiverId = id;
            document.getElementById('chat-with-title').innerText = "Ngobrol dengan: " + name;
            document.getElementById('form-input-chat').classList.remove('hidden'); // Munculkan kotak input pesan
            
            // Ambil riwayat chat lama dari database antara kamu dengan user ini
            fetch(`/messages/${id}`)
                .then(res => res.json())
                .then(messages => {
                    const chatBox = document.getElementById('chat-box');
                    chatBox.innerHTML = ''; // Bersihkan tulisan placeholder
                    
                    messages.forEach(msg => {
                        tampilkanPesanDiLayar(msg);
                    });
                    scrollChatKeBawah();
                });
        }

        // Fungsi B: Ketika tombol "Kirim" dipencet
        function kirimPesan() {
            const input = document.getElementById('message-input');
            const messageText = input.value.trim();
            if (!messageText || !activeReceiverId) return;

            // Kirim pesan ke Controller via API POST yang kita buat di Hari 2
            fetch('/messages', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    receiver_id: activeReceiverId,
                    message: messageText
                })
            })
            .then(res => res.json())
            .then(data => {
                tampilkanPesanDiLayar(data.message); // Cetak pesan kita sendiri di layar
                input.value = ''; // Kosongkan kembali kotak input teks
                scrollChatKeBawah();
            });
        }

        // Fungsi C: Membuat balon chat (Kanan jika dari kita, Kiri jika dari orang lain)
        function tampilkanPesanDiLayar(msg) {
            const chatBox = document.getElementById('chat-box');
            const isMe = msg.sender_id == currentUserId;
            
            const msgDiv = document.createElement('div');
            msgDiv.className = `max-w-xs p-3 rounded-lg text-sm ${
                isMe ? 'bg-indigo-600 text-white ml-auto rounded-br-none' : 'bg-gray-200 text-gray-800 mr-auto rounded-bl-none'
            }`;
            msgDiv.innerText = msg.message;
            
            chatBox.appendChild(msgDiv);
        }

        function scrollChatKeBawah() {
            const chatBox = document.getElementById('chat-box');
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        // Fungsi D: Menghubungkan Browser ke Server WebSocket Laravel Reverb
        document.addEventListener('DOMContentLoaded', function () {
            if (currentUserId) {
                
                // 1. DENGARKAN JALUR PRIVAT (Untuk menerima pesan real-time dari orang lain)
                window.Echo.private(`chat.${currentUserId}`)
                    .listen('MessageSent', (e) => {
                        // Jika kebetulan kita lagi buka halaman chat dengan si pengirim, langsung munculkan balon chatnya
                        if (activeReceiverId && e.message.sender_id == activeReceiverId) {
                            tampilkanPesanDiLayar(e.message);
                            scrollChatKeBawah();
                        } else {
                            // Jika kita lagi buka chat dengan orang lain, beri tahu lewat alert biasa
                            alert('Ada pesan masuk dari User ID ' + e.message.sender_id);
                        }
                    });

                // 2. BERGABUNG KE SALURAN PANTAU STATUS ONLINE/OFFLINE
                window.Echo.join('online-users')
                    .here(users => {
                        // Deteksi awal: Siapa saja yang sudah online saat kita pertama masuk, ubah lampunya jadi HIJAU
                        users.forEach(u => ubahStatusLampu(u.id, true));
                    })
                    .joining(u => {
                        // Deteksi real-time: Jika ada user lain baru login/buka web, langsung ubah lampunya jadi HIJAU
                        ubahStatusLampu(u.id, true);
                    })
                    .leaving(u => {
                        // Deteksi real-time: Jika ada user menutup tab/logout, langsung ubah lampunya jadi ABU-ABU
                        ubahStatusLampu(u.id, false);
                    });
            }
        });

        // Fungsi E: Mengubah warna lampu indikator
        function ubahStatusLampu(userId, isOnline) {
            let indicator = document.getElementById(`status-user-${userId}`);
            if (indicator) {
                indicator.className = `h-3 w-3 rounded-full ${isOnline ? 'bg-green-500' : 'bg-gray-300'}`;
                indicator.title = isOnline ? "Online" : "Offline";
            }
        }
    </script>
</x-app-layout>
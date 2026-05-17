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
                        @csrf 
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
            document.getElementById('form-input-chat').classList.remove('hidden'); 
            
            fetch(`/messages/${id}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Gagal memuat pesan.');
                return res.json();
            })
            .then(messages => {
                const chatBox = document.getElementById('chat-box');
                chatBox.innerHTML = ''; 
                
                messages.forEach(msg => {
                    tampilkanPesanDiLayar(msg);
                });
                scrollChatKeBawah();
            })
            .catch(err => console.error(err));
        }

        // Fungsi B: Ketika tombol "Kirim" dipencet atau menekan Enter
        function kirimPesan() {
            const input = document.getElementById('message-input');
            const messageText = input.value.trim();
            if (!messageText || !activeReceiverId) return;

            const tokenElement = document.querySelector('input[name="_token"]') || document.querySelector('meta[name="csrf-token"]');
            const csrfToken = tokenElement ? (tokenElement.value || tokenElement.getAttribute('content')) : '';

            fetch('/messages', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    receiver_id: activeReceiverId,
                    message: messageText
                })
            })
            .then(res => {
                if (!res.ok) throw new Error('Gagal mengirim pesan.');
                return res.json();
            })
            .then(data => {
                tampilkanPesanDiLayar(data.message); 
                input.value = ''; 
                scrollChatKeBawah(); 
            })
            .catch(err => {
                console.error("Error saat mengirim:", err);
                alert("Gagal mengirim pesan. Silakan periksa tab console Anda.");
            });
        }

        // Fungsi C: Membuat balon chat
        function tampilkanPesanDiLayar(msg) {
            const chatBox = document.getElementById('chat-box');
            const isMe = msg.sender_id == currentUserId;
            
            const wrapperDiv = document.createElement('div');
            wrapperDiv.className = `flex w-full ${isMe ? 'justify-end' : 'justify-start'}`;

            const waktuPesan = msg.created_at ? new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            const msgDiv = document.createElement('div');
            msgDiv.className = `max-w-xs p-3 rounded-xl text-sm shadow-sm flex flex-col ${
                isMe ? 'bg-indigo-600 text-white rounded-tr-none' : 'bg-gray-200 text-gray-800 rounded-tl-none'
            }`;
            
            msgDiv.innerHTML = `
                <span class="break-words">${msg.message}</span>
                <span class="text-[10px] mt-1 text-right block ${isMe ? 'text-indigo-200' : 'text-gray-500'}">
                    ${waktuPesan}
                </span>
            `;
            
            wrapperDiv.appendChild(msgDiv);
            chatBox.appendChild(wrapperDiv);
        }

        function scrollChatKeBawah() {
            const chatBox = document.getElementById('chat-box');
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        // Fungsi D: Jalankan Listener WebSocket sejak awal halaman dimuat
        document.addEventListener('DOMContentLoaded', function () {
            const messageInput = document.getElementById('message-input');
            if (messageInput) {
                messageInput.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        kirimPesan();
                    }
                });
            }

            if (currentUserId && window.Echo) {
                // Dengarkan ID diri sendiri secara permanen untuk menangkap sinyal masuk dari lawan chat
                window.Echo.private(`chat.${currentUserId}`)
                    .listen('.MessageSent', (e) => {
                        console.log("Pesan real-time masuk:", e);
                        
                        if (activeReceiverId && e.message.sender_id == activeReceiverId) {
                            tampilkanPesanDiLayar(e.message);
                            scrollChatKeBawah();
                        } else {
                            let kontakSamping = document.getElementById(`status-user-${e.message.sender_id}`);
                            if(kontakSamping) {
                                kontakSamping.className = "h-3 w-3 rounded-full bg-blue-500 animate-pulse";
                                kontakSamping.title = "Ada pesan masuk baru!";
                            }
                        }
                    });

                // Bergabung ke saluran status online
                window.Echo.join('online-users')
                    .here(users => {
                        users.forEach(u => ubahStatusLampu(u.id, true));
                    })
                    .joining(u => {
                        ubahStatusLampu(u.id, true);
                    })
                    .leaving(u => {
                        ubahStatusLampu(u.id, false);
                    });
            }
        });

        function ubahStatusLampu(userId, isOnline) {
            let indicator = document.getElementById(`status-user-${userId}`);
            if (indicator) {
                indicator.className = `h-3 w-3 rounded-full ${isOnline ? 'bg-green-500' : 'bg-gray-300'}`;
                indicator.title = isOnline ? "Online" : "Offline";
            }
        }
    </script>
</x-app-layout>
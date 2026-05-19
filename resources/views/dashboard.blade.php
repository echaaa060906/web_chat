<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Aplikasi Chat Real-time + Group') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg flex" style="height: 550px;">
                
                <div class="w-1/3 border-r border-gray-200 p-4 flex flex-col justify-between bg-gray-50">
                    
                    <div class="overflow-y-auto flex-1">
                        
                        <div class="mb-5 bg-white p-3 border border-gray-200 rounded-lg shadow-sm">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">🆕 Buat Grup Baru</h4>
                            <div class="flex gap-2">
                                <input type="text" id="new-group-name" placeholder="Nama grup baru..." 
                                       class="flex-1 text-xs border border-gray-300 rounded-lg px-2 py-1.5 focus:outline-none focus:border-indigo-500">
                                <button onclick="buatGrupBaru()" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition-colors">
                                    Buat
                                </button>
                            </div>
                        </div>

                        <h3 class="font-bold text-xs uppercase tracking-wider text-gray-500 mb-2">Grup Anda</h3>
                        <ul class="mb-6" id="group-list-container">
                            @forelse($groups as $group)
                                <li onclick="pilihGrup({{ $group->id }}, '{{ $group->name }}')" 
                                    class="flex items-center justify-between p-3 mb-2 bg-indigo-50 border border-indigo-100 rounded-lg hover:bg-indigo-100 cursor-pointer transition-all">
                                    <span class="font-bold text-indigo-900">👥 {{ $group->name }}</span>
                                    <span id="notif-grup-{{ $group->id }}" class="h-2 w-2 rounded-full bg-transparent"></span>
                                </li>
                            @empty
                                <p id="no-group-text" class="text-xs text-gray-400 italic px-2 mb-2">Anda belum bergabung di grup mana pun.</p>
                            @endforelse
                        </ul>

                        <h3 class="font-bold text-xs uppercase tracking-wider text-gray-500 mb-2">Daftar Kontak</h3>
                        <ul>
                            @foreach($users as $user)
                                <li onclick="pilihUser({{ $user->id }}, '{{ $user->name }}')" 
                                    class="flex items-center justify-between p-3 mb-2 bg-white border border-gray-200 rounded-lg hover:bg-indigo-50 cursor-pointer transition-all">
                                    <span class="font-medium text-gray-800">👤 {{ $user->name }}</span>
                                    <span id="status-user-{{ $user->id }}" class="h-3 w-3 rounded-full bg-gray-300 transition-colors" title="Offline"></span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="border-t border-gray-200 pt-4 mt-2">
                        <div class="flex items-center justify-between mb-3 px-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Akun Anda:</span>
                            <span class="text-sm font-bold text-indigo-600">{{ auth()->user()->name }}</span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-center gap-2 bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2.5 rounded-lg text-sm font-semibold transition-colors border border-red-200">
                                Keluar Aplikasi (Log Out)
                            </button>
                        </form>
                    </div>

                </div>

                <div class="w-2/3 p-6 flex flex-col justify-between bg-white">
                    <div class="border-b border-gray-200 pb-3 mb-4">
                        <div class="flex justify-between items-center mb-1">
                            <h3 id="chat-with-title" class="font-bold text-lg text-gray-800">Silakan pilih kontak atau grup untuk memulai</h3>
                            
                            <div id="add-member-area" class="hidden flex gap-2">
                                <select id="select-member" class="text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-1 px-2 text-gray-700">
                                    <option value="">Pilih Kontak...</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                <button onclick="tambahAnggotaKeGrup()" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition-colors shadow-sm">
                                    + Tambah
                                </button>
                            </div>
                        </div>
                        
                        <p id="group-members-list" class="text-xs text-gray-500 hidden"></p>
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
        let activeGroupId = null; 
        const currentUserId = "{{ auth()->id() }}";

        // [Fungsi 1] Pilih User Chat Personal
        function pilihUser(id, name) {
            activeReceiverId = id;
            activeGroupId = null; 
            
            document.getElementById('add-member-area').classList.add('hidden');
            document.getElementById('group-members-list').classList.add('hidden'); 
            document.getElementById('chat-with-title').innerText = "Ngobrol dengan: " + name;
            document.getElementById('form-input-chat').classList.remove('hidden'); 
            
            fetch(`/messages/${id}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(messages => {
                const chatBox = document.getElementById('chat-box');
                chatBox.innerHTML = ''; 
                if (messages.length === 0) {
                    chatBox.innerHTML = '<p class="text-gray-500 text-center my-auto">Belum ada obrolan. Mulai obrolan pertama!</p>';
                } else {
                    messages.forEach(msg => tampilkanPesanDiLayar(msg));
                }
                scrollChatKeBawah();
            });
        }

        // [Fungsi 2] Pilih Grup Chat
        function pilihGrup(id, name) {
            activeGroupId = id;
            activeReceiverId = null; 
            
            document.getElementById('add-member-area').classList.remove('hidden');
            document.getElementById('chat-with-title').innerText = "Grup: " + name;
            document.getElementById('form-input-chat').classList.remove('hidden');
            
            let notifGrup = document.getElementById(`notif-grup-${id}`);
            if(notifGrup) notifGrup.className = "h-2 w-2 rounded-full bg-transparent";

            // AMBIL DATA ANGGOTA GRUP DARI SERVER
            const membersContainer = document.getElementById('group-members-list');
            fetch(`/groups/${id}/members`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(members => {
                if(membersContainer) {
                    membersContainer.classList.remove('hidden');
                    const names = members.map(m => m.name).join(', ');
                    membersContainer.innerHTML = `<strong>Anggota:</strong> ${names}`;
                }
            })
            .catch(err => console.error('Gagal memuat daftar anggota:', err));

            // Ambil histori pesan grup
            fetch(`/group-messages/${id}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(messages => {
                const chatBox = document.getElementById('chat-box');
                chatBox.innerHTML = ''; 
                if (messages.length === 0) {
                    chatBox.innerHTML = '<p class="text-gray-500 text-center my-auto">Belum ada obrolan di grup ini. Mulai obrolan pertama!</p>';
                } else {
                    messages.forEach(msg => tampilkanPesanDiLayar(msg));
                }
                scrollChatKeBawah();
            });
        }

        // [Fungsi 3] Kirim Pesan Dual Mode (Personal / Grup)
        function kirimPesan() {
            const input = document.getElementById('message-input');
            const messageText = input.value.trim();
            if (!messageText || (!activeReceiverId && !activeGroupId)) return;

            const tokenElement = document.querySelector('meta[name="csrf-token"]') || document.querySelector('input[name="_token"]');
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
                    group_id: activeGroupId, 
                    message: messageText
                })
            })
            .then(res => res.json())
            .then(data => {
                tampilkanPesanDiLayar(data.message); 
                input.value = ''; 
                scrollChatKeBawah(); 
            })
            .catch(err => console.error(err));
        }

        // [Fungsi 4] Menampilkan Balon Chat ke Layar
        function tampilkanPesanDiLayar(msg) {
            const chatBox = document.getElementById('chat-box');
            
            const statusKosong = chatBox.querySelector('.text-gray-500');
            if(statusKosong) statusKosong.remove();

            const isMe = msg.sender_id == currentUserId;
            
            const wrapperDiv = document.createElement('div');
            wrapperDiv.className = `flex w-full ${isMe ? 'justify-end' : 'justify-start'}`;

            const waktuPesan = msg.created_at ? new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            const msgDiv = document.createElement('div');
            msgDiv.className = `max-w-xs p-3 rounded-xl text-sm shadow-sm flex flex-col ${
                isMe ? 'bg-indigo-600 text-white rounded-tr-none' : 'bg-gray-200 text-gray-800 rounded-tl-none'
            }`;
            
            const namaPengirim = (!isMe && msg.sender && (activeGroupId || msg.group_id)) 
                ? `<span class="text-[11px] font-bold text-indigo-600 mb-1 block">${msg.sender.name}</span>` 
                : '';

            msgDiv.innerHTML = `
                ${namaPengirim}
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

        // [Fungsi Utama] Menambahkan Anggota Ke Grup
        function tambahAnggotaKeGrup() {
            const select = document.getElementById('select-member');
            const userId = select.value;
            
            if (!userId || !activeGroupId) {
                alert('Silakan pilih salah satu kontak terlebih dahulu!');
                return;
            }

            const tokenElement = document.querySelector('meta[name="csrf-token"]') || document.querySelector('input[name="_token"]');
            const csrfToken = tokenElement ? (tokenElement.value || tokenElement.getAttribute('content')) : '';

            fetch(`/group-messages/${activeGroupId}/add-member`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ user_id: userId })
            })
            .then(res => {
                if (!res.ok) throw new Error('Gagal memproses pendaftaran.');
                return res.json();
            })
            .then(data => {
                alert('Berhasil! Kontak pilihan Anda telah dimasukkan ke dalam grup.');
                select.value = ''; 
                window.location.reload();
            })
            .catch(err => {
                console.error(err);
                alert('Gagal menambahkan anggota.');
            });
        }

        // [Fungsi Baru] Membuat Grup Baru dari Web
        function buatGrupBaru() {
            const inputGrup = document.getElementById('new-group-name');
            const namaGrup = inputGrup.value.trim();

            if (!namaGrup) {
                alert('Nama grup tidak boleh kosong!');
                return;
            }

            const tokenElement = document.querySelector('meta[name="csrf-token"]') || document.querySelector('input[name="_token"]');
            const csrfToken = tokenElement ? (tokenElement.value || tokenElement.getAttribute('content')) : '';

            fetch('/groups', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ name: namaGrup })
            })
            .then(res => {
                if (!res.ok) throw new Error('Gagal membuat grup.');
                return res.json();
            })
            .then(data => {
                alert('Berhasil! ' + data.message);
                inputGrup.value = '';
                window.location.reload();
            })
            .catch(err => {
                console.error(err);
                alert('Gagal membuat grup.');
            });
        }

        // [Fungsi 5] Radio Listener WebSocket (Echo)
        document.addEventListener('DOMContentLoaded', function () {
            const messageInput = document.getElementById('message-input');
            if (messageInput) {
                messageInput.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') kirimPesan();
                });
            }

            if (currentUserId && window.Echo) {
                window.Echo.private(`chat.${currentUserId}`)
                    .listen('.MessageSent', (e) => {
                        if (activeReceiverId && e.message.sender_id == activeReceiverId) {
                            tampilkanPesanDiLayar(e.message);
                            scrollChatKeBawah();
                        } else {
                            let kontakSamping = document.getElementById(`status-user-${e.message.sender_id}`);
                            if(kontakSamping) kontakSamping.className = "h-3 w-3 rounded-full bg-blue-500 animate-pulse";
                        }
                    });

                @foreach($groups as $g)
                    window.Echo.private(`group.{{ $g->id }}`)
                        .listen('.MessageSent', (e) => {
                            if (activeGroupId && e.message.group_id == activeGroupId) {
                                if (e.message.sender_id != currentUserId) {
                                    tampilkanPesanDiLayar(e.message);
                                    scrollChatKeBawah();
                                }
                            } else {
                                let notifGrup = document.getElementById(`notif-grup-${e.message.group_id}`);
                                if(notifGrup) notifGrup.className = "h-2 w-2 rounded-full bg-blue-500 animate-pulse";
                            }
                        });
                @endforeach

                window.Echo.join('online-users')
                    .here(users => users.forEach(u => ubahStatusLampu(u.id, true)))
                    .joining(u => ubahStatusLampu(u.id, true))
                    .leaving(u => ubahStatusLampu(u.id, false))
                    
                    // ✨ MENANGKAP SIARAN GRUP BARU SECARA REAL-TIME TANPA REFRESH
                    .listen('.GroupCreated', (e) => {
                        console.log("Menerima objek grup baru secara real-time:", e.group);
                        
                        const groupContainer = document.getElementById('group-list-container');
                        const noGroupText = document.getElementById('no-group-text');
                        
                        if (noGroupText) noGroupText.remove();

                        // Sisipkan item grup baru ke dalam list menu kiri secara instan
                        const li = document.createElement('li');
                        li.setAttribute('onclick', `pilihGrup(${e.group.id}, '${e.group.name}')`);
                        li.className = "flex items-center justify-between p-3 mb-2 bg-indigo-50 border border-indigo-100 rounded-lg hover:bg-indigo-100 cursor-pointer transition-all";
                        li.innerHTML = `
                            <span class="font-bold text-indigo-900">👥 ${e.group.name}</span>
                            <span id="notif-grup-${e.group.id}" class="h-2 w-2 rounded-full bg-transparent"></span>
                        `;
                        
                        groupContainer.appendChild(li);

                        // Daftarkan jalur WebSocket chat baru secara dinamis untuk grup yang baru terbuat ini
                        window.Echo.private(`group.${e.group.id}`)
                            .listen('.MessageSent', (eventChat) => {
                                if (activeGroupId && eventChat.message.group_id == activeGroupId) {
                                    if (eventChat.message.sender_id != currentUserId) {
                                        tampilkanPesanDiLayar(eventChat.message);
                                        scrollChatKeBawah();
                                    }
                                } else {
                                    let notifGrup = document.getElementById(`notif-grup-${eventChat.message.group_id}`);
                                    if(notifGrup) notifGrup.className = "h-2 w-2 rounded-full bg-blue-500 animate-pulse";
                                }
                            });
                    });
            }
        });

        function ubahStatusLampu(userId, isOnline) {
            let indicator = document.getElementById(`status-user-${userId}`);
            if (indicator) indicator.className = `h-3 w-3 rounded-full ${isOnline ? 'bg-green-500' : 'bg-gray-300'}`;
        }
    </script>
</x-app-layout>
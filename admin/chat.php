<?php
require_once __DIR__ . '/_guard.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FIRAJAYA — Chat CS</title>
  <script src="https://cdn.tailwindcss.com/3.4.17"></script>
  <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
  <script>
    tailwind.config = {
      theme: { extend: { fontFamily: { sans: ['Geist','Inter','sans-serif'] } } }
    }
  </script>
  <link rel="stylesheet" href="/css/style.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700;800;900&display=swap">
  <style>
    .row-hover { transition: all .15s ease; }
    .row-hover:hover { background: #f0f7f1; }
    .row-active { background: #ecfdf5; }
    #chatScrollArea::-webkit-scrollbar { width: 6px; }
    #chatScrollArea::-webkit-scrollbar-thumb { background: #d4d4d4; border-radius: 3px; }
  </style>
</head>
<body class="bg-neutral-100 h-screen overflow-hidden">

  <div id="toastContainer" class="fixed top-4 right-4 z-[9999] flex flex-col gap-2"></div>

  <div class="flex h-screen">

<aside class="w-[72px] bg-white border-r border-neutral-200/60 flex flex-col items-center py-4 gap-1 shrink-0 z-30">
  <div class="w-11 h-11 bg-emerald-500 rounded-xl flex items-center justify-center mb-6 shadow-lg shadow-emerald-500/25">
    <iconify-icon icon="solar:shop-bold" width="22" class="text-white"></iconify-icon>
  </div>
  <a href="dashboard.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400" title="Kasir">
    <iconify-icon icon="solar:calculator-minimalistic-bold" width="22"></iconify-icon>
  </a>
  <a href="produk.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400" title="Produk">
    <iconify-icon icon="solar:box-minimalistic-bold" width="22"></iconify-icon>
  </a>
  <a href="laporan.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400" title="Laporan">
    <iconify-icon icon="solar:chart-bold" width="22"></iconify-icon>
  </a>
  <a href="riwayat.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400" title="Riwayat">
    <iconify-icon icon="solar:clock-circle-bold" width="22"></iconify-icon>
  </a>
  <a href="absensi.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400" title="Absensi">
    <iconify-icon icon="solar:calendar-mark-bold" width="22"></iconify-icon>
  </a>
  <a href="stok.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400" title="Stok">
    <iconify-icon icon="solar:layers-minimalistic-bold" width="22"></iconify-icon>
  </a>
  <a href="pesan.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400 relative" title="Pesan">
    <iconify-icon icon="solar:letter-bold" width="22"></iconify-icon>
    <span id="sidebarBadge" class="hidden absolute top-0 right-0 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">0</span>
  </a>
  <a href="chat.php" class="sidebar-icon active w-11 h-11 rounded-xl flex items-center justify-center relative" title="Chat CS">
    <iconify-icon icon="solar:chat-round-dots-bold" width="22"></iconify-icon>
    <span id="sidebarChatBadge" class="hidden absolute top-0 right-0 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">0</span>
  </a>
<div class="flex-1"></div>
  <a href="/api/auth_logout.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400 hover:text-red-500" title="Logout">
    <iconify-icon icon="solar:logout-2-bold" width="22"></iconify-icon>
  </a>
  <a href="settings.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400" title="Pengaturan">
    <iconify-icon icon="solar:settings-bold" width="22"></iconify-icon>
  </a>
</aside>

    <!-- Content -->
    <div class="flex-1 flex flex-col overflow-hidden">

      <nav class="h-16 bg-white/80 backdrop-blur-xl border-b border-neutral-200/60 flex items-center justify-between px-6 shrink-0 z-20">
        <div class="flex items-center gap-3">
          <h1 class="text-lg font-bold text-neutral-900 tracking-tight">FIRAJAYA</h1>
          <span class="text-[10px] font-bold uppercase tracking-widest text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">Chat CS</span>
        </div>
        <div class="flex items-center gap-4">
          <span class="text-sm font-semibold text-neutral-700"><?php echo htmlspecialchars($userName); ?></span>
        </div>
      </nav>

      <main class="flex-1 overflow-hidden flex">

        <!-- Daftar thread (kotak masuk) -->
        <div class="w-[300px] shrink-0 bg-white border-r border-neutral-200/60 flex flex-col overflow-hidden">
          <div class="p-3 border-b border-neutral-100">
            <p class="text-xs font-bold uppercase tracking-wider text-neutral-400 px-1">Percakapan</p>
          </div>
          <div id="threadList" class="flex-1 overflow-y-auto">
            <div class="text-center py-12 text-neutral-400 text-sm">Memuat...</div>
          </div>
        </div>

        <!-- Panel chat -->
        <div class="flex-1 flex flex-col bg-neutral-50">
          <div id="chatEmptyState" class="flex-1 flex items-center justify-center">
            <div class="text-center text-neutral-400">
              <iconify-icon icon="solar:chat-round-dots-linear" width="48" class="mb-2"></iconify-icon>
              <p class="font-semibold">Pilih percakapan untuk mulai membalas</p>
            </div>
          </div>

          <div id="chatActiveState" class="hidden flex-1 flex flex-col overflow-hidden">
            <div class="bg-white border-b border-neutral-200/60 px-5 py-3 flex items-center gap-3">
              <div class="w-9 h-9 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-700 font-bold text-xs" id="chatHeaderAvatar"></div>
              <div>
                <p class="font-bold text-neutral-800 text-sm" id="chatHeaderName"></p>
                <p class="text-xs text-neutral-400" id="chatHeaderUsername"></p>
              </div>
            </div>
            <div id="chatScrollArea" class="flex-1 overflow-y-auto p-5 flex flex-col gap-2"></div>
            <div class="bg-white border-t border-neutral-200/60 p-3 flex gap-2">
              <input id="chatReplyInput" type="text" placeholder="Tulis balasan..." class="flex-1 px-4 py-2.5 rounded-xl border border-neutral-200 text-sm outline-none focus:ring-2 focus:ring-emerald-500">
              <button id="chatReplySend" class="w-10 h-10 rounded-xl bg-emerald-600 hover:bg-emerald-700 flex items-center justify-center transition-all">
                <iconify-icon icon="solar:send-bold" width="18" class="text-white"></iconify-icon>
              </button>
            </div>
          </div>
        </div>

      </main>
    </div>
  </div>

  <script>
    let activeThreadUserId = null;
    let lastChatId = 0;
    let pollInterval = null;

    async function apiFetch(url) {
      try {
        const sep = url.includes('?') ? '&' : '?';
        const res = await fetch(url + sep + '_t=' + Date.now());
        const json = await res.json().catch(() => null);
        if (json) return json;
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return { success: false, message: 'Response tidak valid' };
      } catch (e) {
        return { success: false, message: e.message };
      }
    }

    function escapeHtml(str) {
      const div = document.createElement('div');
      div.textContent = str || '';
      return div.innerHTML;
    }

    async function loadThreadList() {
      const json = await apiFetch('/api/get_chat_list.php');
      const container = document.getElementById('threadList');

      if (!json.success || !json.data || json.data.length === 0) {
        container.innerHTML = `<div class="text-center py-12 text-neutral-400 text-sm px-4">
          <iconify-icon icon="solar:chat-round-line-linear" width="32" class="text-neutral-300 mb-2"></iconify-icon>
          <p>Belum ada percakapan</p>
        </div>`;
        return;
      }

      container.innerHTML = '';
      json.data.forEach(t => {
        const time = new Date(t.last_time).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        const initial = t.nama_lengkap.charAt(0).toUpperCase();
        const isActive = activeThreadUserId === t.id_user;
        const preview = t.last_sender === 'kasir' ? `Anda: ${t.last_message}` : t.last_message;
        const unreadBadge = t.unread_count > 0
          ? `<span class="min-w-[18px] h-[18px] px-1 bg-emerald-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">${t.unread_count}</span>`
          : '';

        const row = document.createElement('div');
        row.className = `row-hover ${isActive ? 'row-active' : ''} flex items-center gap-3 px-4 py-3 cursor-pointer border-b border-neutral-50`;
        row.onclick = () => openThread(t.id_user, t.nama_lengkap, t.username);
        row.innerHTML = `
          <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-700 font-bold flex items-center justify-center shrink-0">${initial}</div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between">
              <p class="font-semibold text-sm text-neutral-800 truncate">${escapeHtml(t.nama_lengkap)}</p>
              <span class="text-[11px] text-neutral-400 shrink-0">${time}</span>
            </div>
            <div class="flex items-center justify-between gap-2">
              <p class="text-xs text-neutral-500 truncate">${escapeHtml(preview)}</p>
              ${unreadBadge}
            </div>
          </div>
        `;
        container.appendChild(row);
      });
    }

    function renderChatBubble(msg) {
      const isMe = msg.pengirim === 'kasir';
      const time = new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
      const bubble = document.createElement('div');
      bubble.style.maxWidth = '70%';
      bubble.style.alignSelf = isMe ? 'flex-end' : 'flex-start';
      bubble.innerHTML = `
        <div class="${isMe ? 'bg-emerald-600 text-white' : 'bg-white text-neutral-800'} px-3.5 py-2 rounded-2xl text-sm shadow-sm">${escapeHtml(msg.isi_pesan)}</div>
        <div class="text-[10px] text-neutral-400 mt-1 ${isMe ? 'text-right' : 'text-left'}">${time}</div>
      `;
      return bubble;
    }

    async function openThread(id_user, nama, username) {
      activeThreadUserId = id_user;
      lastChatId = 0;

      document.getElementById('chatEmptyState').classList.add('hidden');
      document.getElementById('chatActiveState').classList.remove('hidden');
      document.getElementById('chatActiveState').classList.add('flex');
      document.getElementById('chatHeaderAvatar').textContent = nama.charAt(0).toUpperCase();
      document.getElementById('chatHeaderName').textContent = nama;
      document.getElementById('chatHeaderUsername').textContent = '@' + username;
      document.getElementById('chatScrollArea').innerHTML = '';

      await loadChatMessages(true);
      loadThreadList(); // refresh supaya badge unread di list hilang

      if (pollInterval) clearInterval(pollInterval);
      pollInterval = setInterval(() => loadChatMessages(false), 4000);
    }

    async function loadChatMessages(scrollDown) {
      if (!activeThreadUserId) return;
      const json = await apiFetch(`/api/get_chat_messages.php?id_user=${activeThreadUserId}&since_id=${lastChatId}`);
      if (!json.success) return;

      const container = document.getElementById('chatScrollArea');
      json.data.forEach(msg => {
        container.appendChild(renderChatBubble(msg));
        lastChatId = Math.max(lastChatId, msg.id_chat);
      });
      if (scrollDown || json.data.length > 0) {
        container.scrollTop = container.scrollHeight;
      }
    }

    async function sendReply() {
      const input = document.getElementById('chatReplyInput');
      const isi_pesan = input.value.trim();
      if (isi_pesan === '' || !activeThreadUserId) return;

      input.value = '';
      try {
        const res = await fetch('/api/send_chat.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ isi_pesan, id_user: activeThreadUserId })
        });
        const json = await res.json();
        if (json.success) {
          await loadChatMessages(true);
          loadThreadList();
        }
      } catch (e) {
        input.value = isi_pesan;
      }
    }

    document.getElementById('chatReplySend').addEventListener('click', sendReply);
    document.getElementById('chatReplyInput').addEventListener('keydown', (e) => {
      if (e.key === 'Enter') sendReply();
    });

    async function loadUnreadBadge() {
      try {
        const json = await apiFetch('/api/get_unread_count.php');
        const badge = document.getElementById('sidebarBadge');
        if (json.success && json.unread_count > 0) {
          badge.textContent = json.unread_count > 99 ? '99+' : json.unread_count;
          badge.classList.remove('hidden');
        } else if (badge) badge.classList.add('hidden');
      } catch (e) {}
    }

    async function loadChatBadge() {
      try {
        const json = await apiFetch('/api/get_chat_unread_count.php');
        const badge = document.getElementById('sidebarChatBadge');
        if (json.success && json.unread_count > 0) {
          badge.textContent = json.unread_count > 99 ? '99+' : json.unread_count;
          badge.classList.remove('hidden');
        } else if (badge) badge.classList.add('hidden');
      } catch (e) {}
    }

    loadThreadList();
    loadUnreadBadge();
    loadChatBadge();
    setInterval(loadThreadList, 5000);
    setInterval(loadChatBadge, 5000);
  </script>
</body>
</html>
<?php
require_once __DIR__ . '/_guard.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FIRAJAYA — Pesan Masuk</title>
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
  </style>
</head>
<body class="bg-neutral-100 h-screen overflow-hidden">

  <div id="toastContainer" class="fixed top-4 right-4 z-[9999] flex flex-col gap-2"></div>

  <!-- Detail Modal -->
  <div id="detailModal" class="fixed inset-0 z-[9990] hidden items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeDetailModal()"></div>
    <div class="modal-content relative bg-white rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden max-h-[85vh] overflow-y-auto">
      <div class="bg-gradient-to-r from-emerald-500 to-emerald-700 px-6 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center">
              <iconify-icon icon="solar:letter-bold" width="20" class="text-white"></iconify-icon>
            </div>
            <h2 class="text-lg font-bold text-white">Detail Pesan</h2>
          </div>
          <button onclick="closeDetailModal()" class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-white/70 hover:bg-white/20 hover:text-white transition-all">
            <iconify-icon icon="solar:close-circle-linear" width="20"></iconify-icon>
          </button>
        </div>
      </div>
      <div id="detailContent" class="p-6"></div>
    </div>
  </div>

  <!-- MAIN LAYOUT -->
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
  <a href="pesan.php" class="sidebar-icon active w-11 h-11 rounded-xl flex items-center justify-center relative" title="Pesan">
    <iconify-icon icon="solar:letter-bold" width="22"></iconify-icon>
    <span id="sidebarBadge" class="hidden absolute top-0 right-0 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">0</span>
  </a>
   <a href="chat.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400 relative" title="Chat CS">
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

      <!-- Navbar -->
      <nav class="h-16 bg-white/80 backdrop-blur-xl border-b border-neutral-200/60 flex items-center justify-between px-6 shrink-0 z-20">
        <div class="flex items-center gap-3">
          <h1 class="text-lg font-bold text-neutral-900 tracking-tight">FIRAJAYA</h1>
          <span class="text-[10px] font-bold uppercase tracking-widest text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">Pesan Masuk</span>
        </div>
        <div class="flex items-center gap-4">
          <span class="text-sm font-semibold text-neutral-700"><?php echo htmlspecialchars($userName); ?></span>
        </div>
      </nav>

      <!-- Main Content -->
      <main class="flex-1 overflow-y-auto p-5 space-y-4">

        <div class="bg-white rounded-2xl border border-neutral-200/60 overflow-hidden shadow-sm">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-neutral-100 bg-neutral-50/50">
                  <th class="text-left px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider w-10"></th>
                  <th class="text-left px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Pengirim</th>
                  <th class="text-left px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Subjek</th>
                  <th class="text-left px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Tanggal</th>
                  <th class="text-center px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Aksi</th>
                </tr>
              </thead>
              <tbody id="pesanTableBody">
                <tr><td colspan="5" class="text-center py-12 text-neutral-400">Memuat data...</td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <div id="pagination" class="flex justify-center gap-2"></div>

      </main>
    </div>
  </div>

  <script>
    const API_BASE = '';

    async function apiFetch(url) {
      try {
        const sep = url.includes('?') ? '&' : '?';
        const res = await fetch(API_BASE + url + sep + '_t=' + Date.now());
        const json = await res.json().catch(() => null);
        if (json) return json;
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return { success: false, message: 'Response tidak valid' };
      } catch (e) {
        return { success: false, message: e.message };
      }
    }

    function showToast(msg, type = 'success') {
      const el = document.createElement('div');
      const bg = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
      el.className = `${bg} text-white px-4 py-3 rounded-xl shadow-lg text-sm font-medium animate-pulse`;
      el.textContent = msg;
      document.getElementById('toastContainer').appendChild(el);
      setTimeout(() => el.remove(), 3000);
    }

    function escapeHtml(str) {
      const div = document.createElement('div');
      div.textContent = str || '';
      return div.innerHTML;
    }

    async function loadUnreadBadge() {
      const json = await apiFetch('/api/get_unread_count.php');
      const badge = document.getElementById('sidebarBadge');
      if (json.success && json.unread_count > 0) {
        badge.textContent = json.unread_count > 99 ? '99+' : json.unread_count;
        badge.classList.remove('hidden');
      } else {
        badge.classList.add('hidden');
      }
    }

    async function loadMessages(page = 1) {
      const json = await apiFetch(`/api/get_messages.php?page=${page}`);
      const tbody = document.getElementById('pesanTableBody');

      if (!json.success || !json.data || json.data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-12 text-neutral-400">
          <div class="flex flex-col items-center">
            <iconify-icon icon="solar:letter-unread-linear" width="40" class="text-neutral-300 mb-2"></iconify-icon>
            <p class="font-semibold">Belum ada pesan</p>
          </div>
        </td></tr>`;
        document.getElementById('pagination').innerHTML = '';
        return;
      }

      tbody.innerHTML = '';
      json.data.forEach(p => {
        const tanggal = new Date(p.created_at).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        const isUnread = p.status === 'belum_dibaca';
        const dot = isUnread ? '<span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>' : '';
        const rowCls = isUnread ? 'row-hover border-b border-neutral-50 font-semibold' : 'row-hover border-b border-neutral-50 text-neutral-500';
        tbody.innerHTML += `
        <tr class="${rowCls} cursor-pointer" onclick="viewMessage(${p.id_pesan})">
          <td class="px-4 py-3">${dot}</td>
          <td class="px-4 py-3">
            <p class="text-neutral-800">${escapeHtml(p.nama)}</p>
            <p class="text-[11px] text-neutral-400 font-normal">${escapeHtml(p.email)}</p>
          </td>
          <td class="px-4 py-3 text-neutral-700">${escapeHtml(p.subjek)}</td>
          <td class="px-4 py-3 text-neutral-400 text-xs font-normal">${tanggal}</td>
          <td class="px-4 py-3 text-center">
            <button onclick="event.stopPropagation(); viewMessage(${p.id_pesan})" class="text-xs font-semibold text-emerald-600 hover:underline">Baca</button>
          </td>
        </tr>`;
      });

      renderPagination(json.page, json.pages);
    }

    function renderPagination(page, pages) {
      const el = document.getElementById('pagination');
      if (pages <= 1) { el.innerHTML = ''; return; }
      let html = '';
      for (let i = 1; i <= pages; i++) {
        html += `<button onclick="loadMessages(${i})" class="w-9 h-9 rounded-lg text-sm font-semibold ${i === page ? 'bg-emerald-500 text-white' : 'bg-white border border-neutral-200 text-neutral-600 hover:bg-neutral-50'}">${i}</button>`;
      }
      el.innerHTML = html;
    }

    async function viewMessage(id) {
      const json = await apiFetch(`/api/get_messages.php`);
      const pesan = (json.data || []).find(p => p.id_pesan === id);
      const content = document.getElementById('detailContent');

      if (!pesan) {
        content.innerHTML = `<p class="text-sm text-red-500">Pesan tidak ditemukan</p>`;
      } else {
        const tanggal = new Date(pesan.created_at).toLocaleString('id-ID');
        content.innerHTML = `
          <div class="mb-4 pb-4 border-b border-neutral-100">
            <p class="font-bold text-neutral-800">${escapeHtml(pesan.nama)}</p>
            <p class="text-xs text-neutral-400">${escapeHtml(pesan.email)} &middot; ${tanggal}</p>
          </div>
          <p class="font-semibold text-neutral-700 mb-2">${escapeHtml(pesan.subjek)}</p>
          <p class="text-sm text-neutral-600 whitespace-pre-wrap leading-relaxed">${escapeHtml(pesan.isi_pesan)}</p>
        `;

        if (pesan.status === 'belum_dibaca') {
          await fetch(API_BASE + '/api/mark_message_read.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_pesan: id })
          });
          loadMessages();
          loadUnreadBadge();
        }
      }

      const modal = document.getElementById('detailModal');
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    function closeDetailModal() {
      const modal = document.getElementById('detailModal');
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }

    loadMessages();
    loadUnreadBadge();
  </script>
</body>
</html>
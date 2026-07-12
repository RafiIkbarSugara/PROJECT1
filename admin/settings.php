<?php
require_once __DIR__ . '/_guard.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FIRAJAYA — Pengaturan</title>
  <script src="https://cdn.tailwindcss.com/3.4.17"></script>
  <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
  <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Geist','Inter','sans-serif']}}}}</script>
  <link rel="stylesheet" href="/css/style.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700;800;900&display=swap">
  <style>.input-group label{display:block;font-size:14px;font-weight:600;color:#404040;margin-bottom:6px}.input-group input,.input-group textarea{width:100%;padding:12px 16px;background:#fafafa;border:1px solid #e5e5e5;border-radius:12px;font-size:14px;transition:all .2s}.input-group input:focus,.input-group textarea:focus{outline:none;border-color:#059669;box-shadow:0 0 0 3px rgba(5,150,105,.1)}</style>
</head>
<body class="bg-neutral-100 h-screen overflow-hidden">

  <div id="toastContainer" class="fixed top-4 right-4 z-[9999] flex flex-col gap-2"></div>

  <div class="flex h-screen">
    <!-- Sidebar -->
    <aside class="w-[72px] bg-white border-r border-neutral-200/60 flex flex-col items-center py-4 gap-1 shrink-0 z-30">
      <div class="w-11 h-11 bg-emerald-500 rounded-xl flex items-center justify-center mb-6 shadow-lg shadow-emerald-500/25"><iconify-icon icon="solar:shop-bold" width="22" class="text-white"></iconify-icon></div>
      <a href="dashboard.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400" title="Kasir"><iconify-icon icon="solar:calculator-minimalistic-bold" width="22"></iconify-icon></a>
      <a href="produk.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400" title="Produk"><iconify-icon icon="solar:box-minimalistic-bold" width="22"></iconify-icon></a>
      <a href="laporan.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400" title="Laporan"><iconify-icon icon="solar:chart-bold" width="22"></iconify-icon></a>
      <a href="riwayat.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400" title="Riwayat"><iconify-icon icon="solar:clock-circle-bold" width="22"></iconify-icon></a>
      <a href="absensi.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400" title="Absensi"><iconify-icon icon="solar:calendar-mark-bold" width="22"></iconify-icon></a>
      <a href="stok.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400" title="Stok"><iconify-icon icon="solar:layers-minimalistic-bold" width="22"></iconify-icon></a>
      <a href="pesan.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400 relative" title="Pesan"><iconify-icon icon="solar:letter-bold" width="22"></iconify-icon><span id="sidebarBadge" class="hidden absolute top-0 right-0 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">0</span></a>
      <a href="chat.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400 relative" title="Chat CS"><iconify-icon icon="solar:chat-round-dots-bold" width="22"></iconify-icon><span id="sidebarChatBadge" class="hidden absolute top-0 right-0 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">0</span></a>
      <div class="flex-1"></div>
      <a href="/api/auth_logout.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400 hover:text-red-500" title="Logout"><iconify-icon icon="solar:logout-2-bold" width="22"></iconify-icon></a>
      <a href="settings.php" class="sidebar-icon active w-11 h-11 rounded-xl flex items-center justify-center" title="Pengaturan"><iconify-icon icon="solar:settings-bold" width="22"></iconify-icon></a>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Navbar -->
      <nav class="h-16 bg-white/80 backdrop-blur-xl border-b border-neutral-200/60 flex items-center justify-between px-6 shrink-0 z-20">
        <div class="flex items-center gap-3"><h1 class="text-lg font-bold text-neutral-900 tracking-tight">FIRAJAYA</h1><span class="text-[10px] font-bold uppercase tracking-widest text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">Pengaturan</span></div>
        <div class="flex items-center gap-4"><div class="text-right hidden sm:block"><p class="text-xs text-neutral-400"><?php echo htmlspecialchars($userName); ?></p><p class="text-sm font-semibold text-neutral-700"><?php echo $userRole; ?></p></div></div>
      </nav>

      <!-- Content -->
      <main class="flex-1 overflow-y-auto p-6 flex items-start justify-center">
        <div class="w-full max-w-2xl">
          <div class="bg-white rounded-3xl border border-neutral-200/60 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-emerald-500 to-emerald-700 px-6 py-5">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center"><iconify-icon icon="solar:settings-bold" width="22" class="text-white"></iconify-icon></div>
                <div><h2 class="text-lg font-bold text-white">Pengaturan Toko</h2><p class="text-xs text-white/70">Ubah informasi dan tarif toko</p></div>
              </div>
            </div>

            <form id="settingsForm" onsubmit="saveSettings(event)" class="p-6 space-y-5">
              <div class="input-group">
                <label>Nama Toko</label>
                <input type="text" id="setNamaToko" placeholder="Nama toko Anda" required>
              </div>
              
              <div class="input-group">
                <label>Alamat Toko</label>
                <textarea id="setAlamat" rows="2" placeholder="Alamat lengkap toko"></textarea>
              </div>

              <div class="input-group">
                <label>Nomor Telepon</label>
                <input type="text" id="setTelepon" placeholder="0812-xxxx-xxxx">
              </div>

              <div class="input-group">
                <label>Presentase Pajak (%)</label>
                <input type="number" id="setPajak" step="0.1" min="0" max="100" placeholder="0" required>
              </div>

              <button type="submit" id="saveBtn" class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-emerald-700 text-white font-bold text-sm flex items-center justify-center gap-2 hover:shadow-lg hover:shadow-emerald-500/20 hover:-translate-y-0.5 active:translate-y-0 transition-all">
                <iconify-icon icon="solar:diskette-bold" width="18"></iconify-icon>
                Simpan Perubahan
              </button>
            </form>
          </div>

          <!-- Manajemen Kasir -->
          <div class="bg-white rounded-3xl border border-neutral-200/60 shadow-sm overflow-hidden mt-6">
            <div class="bg-gradient-to-r from-emerald-500 to-emerald-700 px-6 py-5">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center"><iconify-icon icon="solar:user-plus-bold" width="22" class="text-white"></iconify-icon></div>
                <div><h2 class="text-lg font-bold text-white">Manajemen Kasir</h2><p class="text-xs text-white/70">Tambah atau hapus akun kasir</p></div>
              </div>
            </div>

            <form id="kasirForm" onsubmit="addKasir(event)" class="p-6 space-y-5 border-b border-neutral-100">
              <div class="input-group">
                <label>Nama Lengkap</label>
                <input type="text" id="kasirNama" placeholder="Nama kasir" required>
              </div>
              <div class="input-group">
                <label>Username</label>
                <input type="text" id="kasirUsername" placeholder="Minimal 4 karakter" required minlength="4" maxlength="50">
              </div>
              <div class="input-group">
                <label>Password</label>
                <input type="password" id="kasirPassword" placeholder="Minimal 8 karakter, huruf & angka" required minlength="8">
                <p class="text-xs text-neutral-400 mt-1.5">Password akan otomatis dienkripsi (hash), tidak pernah disimpan sebagai teks biasa.</p>
              </div>
              <button type="submit" id="addKasirBtn" class="w-full py-3.5 rounded-2xl bg-neutral-900 text-white font-bold text-sm flex items-center justify-center gap-2 hover:-translate-y-0.5 active:translate-y-0 transition-all">
                <iconify-icon icon="solar:user-plus-bold" width="18"></iconify-icon>
                Tambah Kasir
              </button>
            </form>

            <div class="p-6">
              <h3 class="text-sm font-bold text-neutral-700 mb-3">Daftar Kasir</h3>
              <div id="kasirList" class="space-y-2">
                <p class="text-sm text-neutral-400">Memuat data...</p>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script>
    const API_BASE = '';
    async function apiFetch(url, opts = {}) {
      try { const sep = url.includes('?') ? '&' : '?'; const res = await fetch(API_BASE + url + sep + '_t=' + Date.now(), opts); const json = await res.json().catch(() => null); if (json) return json; if (!res.ok) throw new Error(`HTTP ${res.status}`); return { success: false, message: 'Response tidak valid' }; } catch (e) { return { success: false, message: e.message }; }
    }

    async function loadSettings() {
      const json = await apiFetch('/api/get_settings.php');
      if (json.success) {
        const s = json.data;
        document.getElementById('setNamaToko').value = s.nama_toko || '';
        document.getElementById('setAlamat').value = s.alamat || '';
        document.getElementById('setTelepon').value = s.telepon || '';
        document.getElementById('setPajak').value = s.pajak_persen || 0;
      } else {
        showToast('Gagal memuat pengaturan', 'error');
      }
    }

    async function saveSettings(e) {
      e.preventDefault();
      const btn = document.getElementById('saveBtn');
      btn.disabled = true; btn.innerHTML = 'Menyimpan...';

      const payload = {
        nama_toko: document.getElementById('setNamaToko').value.trim(),
        alamat: document.getElementById('setAlamat').value.trim(),
        telepon: document.getElementById('setTelepon').value.trim(),
        pajak_persen: document.getElementById('setPajak').value
      };

      const json = await apiFetch('/api/update_settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      if (json.success) {
        showToast('Pengaturan berhasil disimpan!', 'success');
      } else {
        showToast(json.message || 'Gagal menyimpan', 'error');
      }
      btn.disabled = false; btn.innerHTML = '<iconify-icon icon="solar:diskette-bold" width="18"></iconify-icon> Simpan Perubahan';
    }

    function showToast(msg, type='success') { const c=document.getElementById('toastContainer'),t=document.createElement('div'),ic=type==='success'?'<iconify-icon icon="solar:check-circle-bold" width="18" class="text-emerald-500"></iconify-icon>':'<iconify-icon icon="solar:close-circle-bold" width="18" class="text-red-400"></iconify-icon>'; t.className='toast-enter glass-strong flex items-center gap-2 px-4 py-3 rounded-xl border border-neutral-200/60 shadow-lg text-sm font-medium text-neutral-700 min-w-[220px]'; t.innerHTML=`${ic} ${msg}`; c.appendChild(t); setTimeout(()=>{t.classList.remove('toast-enter');t.classList.add('toast-exit');setTimeout(()=>t.remove(),300);},2500); }

    loadSettings();

    // ===== Manajemen Kasir =====
    async function loadKasirList() {
      const container = document.getElementById('kasirList');
      const json = await apiFetch('/api/get_kasir.php');
      if (!json.success) {
        container.innerHTML = `<p class="text-sm text-red-500">${json.message || 'Gagal memuat data kasir'}</p>`;
        return;
      }
      if (!json.data.length) {
        container.innerHTML = '<p class="text-sm text-neutral-400">Belum ada akun kasir.</p>';
        return;
      }
      container.innerHTML = json.data.map(k => `
        <div class="flex items-center justify-between bg-neutral-50 rounded-xl px-4 py-3 border border-neutral-200/60">
          <div>
            <p class="text-sm font-semibold text-neutral-800">${escapeHtml(k.nama_lengkap)}</p>
            <p class="text-xs text-neutral-400">@${escapeHtml(k.username)}</p>
          </div>
          <button onclick="deleteKasir(${k.id_user}, '${escapeHtml(k.nama_lengkap)}')" class="w-8 h-8 flex items-center justify-center rounded-lg text-neutral-400 hover:text-red-500 hover:bg-red-50 transition-all" title="Hapus kasir">
            <iconify-icon icon="solar:trash-bin-trash-bold" width="18"></iconify-icon>
          </button>
        </div>
      `).join('');
    }

    function escapeHtml(str) {
      const div = document.createElement('div');
      div.textContent = str;
      return div.innerHTML;
    }

    async function addKasir(e) {
      e.preventDefault();
      const btn = document.getElementById('addKasirBtn');
      btn.disabled = true; btn.innerHTML = 'Menyimpan...';

      const payload = {
        nama_lengkap: document.getElementById('kasirNama').value.trim(),
        username: document.getElementById('kasirUsername').value.trim(),
        password: document.getElementById('kasirPassword').value
      };

      const json = await apiFetch('/api/add_kasir.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      if (json.success) {
        showToast('Akun kasir berhasil dibuat!', 'success');
        document.getElementById('kasirForm').reset();
        loadKasirList();
      } else {
        showToast(json.message || 'Gagal membuat akun kasir', 'error');
      }
      btn.disabled = false; btn.innerHTML = '<iconify-icon icon="solar:user-plus-bold" width="18"></iconify-icon> Tambah Kasir';
    }

    async function deleteKasir(id, nama) {
      if (!confirm(`Hapus akun kasir "${nama}"? Tindakan ini tidak bisa dibatalkan.`)) return;

      const json = await apiFetch('/api/delete_kasir.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_user: id })
      });

      if (json.success) {
        showToast('Akun kasir berhasil dihapus', 'success');
        loadKasirList();
      } else {
        showToast(json.message || 'Gagal menghapus akun kasir', 'error');
      }
    }

    loadKasirList();

    async function loadUnreadBadge() {
      try {
        const res = await fetch('/api/get_unread_count.php?_t=' + Date.now());
        const json = await res.json();
        const badge = document.getElementById('sidebarBadge');
        if (json.success && json.unread_count > 0) {
          badge.textContent = json.unread_count > 99 ? '99+' : json.unread_count;
          badge.classList.remove('hidden');
        } else if (badge) {
          badge.classList.add('hidden');
        }
      } catch (e) { /* abaikan kalau gagal, badge tetap hidden */ }
    }
    loadUnreadBadge();

    async function loadChatBadge() {
      try {
        const res = await fetch('/api/get_chat_unread_count.php?_t=' + Date.now());
        const json = await res.json();
        const badge = document.getElementById('sidebarChatBadge');
        if (json.success && json.unread_count > 0) {
          badge.textContent = json.unread_count > 99 ? '99+' : json.unread_count;
          badge.classList.remove('hidden');
        } else if (badge) {
          badge.classList.add('hidden');
        }
      } catch (e) { /* abaikan kalau gagal, badge tetap hidden */ }
    }
    loadChatBadge();
  </script>
</body>
</html>
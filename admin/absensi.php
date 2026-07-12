<?php
require_once __DIR__ . '/_guard.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FIRAJAYA — Absensi Kasir</title>
  <script src="https://cdn.tailwindcss.com/3.4.17"></script>
  <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Geist','Inter','sans-serif'] }
        }
      }
    }
  </script>
  <link rel="stylesheet" href="/css/style.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700;800;900&display=swap">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
  <style>
    .row-hover { transition: all .15s ease; }
    .row-hover:hover { background: #f0f7f1; }
    .stat-card { transition: all .2s ease; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,.08); }
    .absen-btn { transition: all .15s ease; }
    .absen-btn:not(:disabled):hover { transform: translateY(-2px); }
    .absen-btn:disabled { opacity: .45; cursor: not-allowed; }
    input[type="date"]::-webkit-calendar-picker-indicator { cursor: pointer; opacity: .6; }
    input[type="date"]::-webkit-calendar-picker-indicator:hover { opacity: 1; }
  </style>
</head>
<body class="bg-neutral-100 h-screen overflow-hidden">

  <!-- Toast Container -->
  <div id="toastContainer" class="fixed top-4 right-4 z-[9999] flex flex-col gap-2"></div>

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
  <a href="absensi.php" class="sidebar-icon active w-11 h-11 rounded-xl flex items-center justify-center" title="Absensi">
    <iconify-icon icon="solar:calendar-mark-bold" width="22"></iconify-icon>
  </a>
  <a href="stok.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400" title="Stok">
    <iconify-icon icon="solar:layers-minimalistic-bold" width="22"></iconify-icon>
  </a>
  <a href="pesan.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400 relative" title="Pesan">
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
          <span class="text-[10px] font-bold uppercase tracking-widest text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">Absensi</span>
        </div>
        <div class="flex items-center gap-4">
          <div class="text-right hidden sm:block">
            <p class="text-xs text-neutral-400" id="currentDate"></p>
            <p class="text-sm font-semibold text-neutral-700" id="currentTime"></p>
          </div>
          <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold text-sm"><?= strtoupper(substr($userName,0,1)) ?></div>
        </div>
      </nav>

      <!-- Main Content -->
      <main class="flex-1 overflow-y-auto p-5 space-y-5">

        <!-- Kartu Absen Hari Ini -->
        <div class="bg-white rounded-3xl border border-neutral-200/60 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-emerald-500 to-emerald-700 px-6 py-5">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center"><iconify-icon icon="solar:calendar-mark-bold" width="22" class="text-white"></iconify-icon></div>
              <div>
                <h2 class="text-lg font-bold text-white">Absensi Hari Ini</h2>
                <p class="text-xs text-white/70"><?= htmlspecialchars($userName) ?></p>
              </div>
            </div>
          </div>

          <div class="p-6">
            <div class="grid grid-cols-2 gap-4 mb-6">
              <div class="stat-card bg-neutral-50 rounded-2xl p-4 border border-neutral-200/60">
                <p class="text-xs text-neutral-400 mb-1">Jam Masuk</p>
                <p id="statusJamMasuk" class="text-xl font-bold text-neutral-800">—</p>
              </div>
              <div class="stat-card bg-neutral-50 rounded-2xl p-4 border border-neutral-200/60">
                <p class="text-xs text-neutral-400 mb-1">Jam Pulang</p>
                <p id="statusJamPulang" class="text-xl font-bold text-neutral-800">—</p>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <button id="btnAbsenMasuk" onclick="absenMasuk()" class="absen-btn py-4 rounded-2xl bg-gradient-to-r from-emerald-500 to-emerald-700 text-white font-bold text-sm flex items-center justify-center gap-2">
                <iconify-icon icon="solar:login-3-bold" width="18"></iconify-icon> Absen Masuk
              </button>
              <button id="btnAbsenPulang" onclick="absenPulang()" class="absen-btn py-4 rounded-2xl bg-neutral-900 text-white font-bold text-sm flex items-center justify-center gap-2">
                <iconify-icon icon="solar:logout-3-bold" width="18"></iconify-icon> Absen Pulang
              </button>
            </div>

            <div class="mt-4 flex items-start gap-2 bg-amber-50 border border-amber-200/60 rounded-xl px-4 py-3">
              <iconify-icon icon="solar:lock-keyhole-minimalistic-bold" width="16" class="text-amber-600 mt-0.5 shrink-0"></iconify-icon>
              <p class="text-xs text-amber-700 leading-relaxed">Data absensi bersifat <strong>permanen</strong>. Setelah tercatat, jam masuk &amp; jam pulang tidak bisa diubah atau dihapus oleh siapa pun, termasuk lewat database langsung. Kalau ada kesalahan (misal salah pencet), gunakan tombol <strong>"Koreksi"</strong> di tabel riwayat — ini menambahkan catatan penjelasan tanpa mengubah data aslinya.</p>
            </div>
          </div>
        </div>

        <!-- Riwayat Absensi -->
        <div class="bg-white rounded-3xl border border-neutral-200/60 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-neutral-100 flex items-center justify-between flex-wrap gap-3">
            <h3 class="text-sm font-bold text-neutral-700">Riwayat Absensi Semua Kasir</h3>
            <div class="flex items-center gap-2">
              <input type="date" id="dariFilter" class="text-xs border border-neutral-200 rounded-lg px-2.5 py-1.5">
              <span class="text-xs text-neutral-400">s/d</span>
              <input type="date" id="sampaiFilter" class="text-xs border border-neutral-200 rounded-lg px-2.5 py-1.5">
              <button onclick="loadRiwayat()" class="text-xs font-semibold bg-emerald-500 text-white px-3 py-1.5 rounded-lg hover:bg-emerald-600 transition-all">Filter</button>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="text-left text-neutral-400 text-xs uppercase tracking-wider border-b border-neutral-100">
                  <th class="px-6 py-3 font-semibold">Kasir</th>
                  <th class="px-6 py-3 font-semibold">Tanggal</th>
                  <th class="px-6 py-3 font-semibold">Jam Masuk</th>
                  <th class="px-6 py-3 font-semibold">Jam Pulang</th>
                  <th class="px-6 py-3 font-semibold">Durasi Kerja</th>
                  <th class="px-6 py-3 font-semibold">Status</th>
                  <th class="px-6 py-3 font-semibold"></th>
                </tr>
              </thead>
              <tbody id="riwayatBody">
                <tr><td colspan="7" class="px-6 py-8 text-center text-neutral-400">Memuat data...</td></tr>
              </tbody>
            </table>
          </div>
        </div>

      </main>
    </div>
  </div>

  <script>
    const API_BASE = '';

    async function apiFetch(url, options = {}) {
      try {
        const sep = url.includes('?') ? '&' : '?';
        const res = await fetch(API_BASE + url + sep + '_t=' + Date.now(), options);
        const json = await res.json().catch(() => null);
        if (json) return json;
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return { success: false, message: 'Response tidak valid' };
      } catch (e) {
        return { success: false, message: e.message };
      }
    }

    function showToast(message, type = 'success') {
      const c = document.getElementById('toastContainer'), t = document.createElement('div');
      const icon = type === 'success'
        ? '<iconify-icon icon="solar:check-circle-bold" width="18" class="text-emerald-500"></iconify-icon>'
        : '<iconify-icon icon="solar:close-circle-bold" width="18" class="text-red-400"></iconify-icon>';
      t.className = 'flex items-center gap-2 px-4 py-3 rounded-xl border border-neutral-200/60 shadow-lg bg-white text-sm font-medium text-neutral-700 min-w-[220px]';
      t.innerHTML = `${icon} ${message}`;
      c.appendChild(t);
      setTimeout(() => t.remove(), 2800);
    }

    function jamSaja(datetimeStr) {
      if (!datetimeStr) return '—';
      return datetimeStr.split(' ')[1]?.substring(0, 5) ?? '—';
    }

    function formatDurasi(menit) {
      if (menit === null || menit === undefined) return '—';
      const j = Math.floor(menit / 60), m = menit % 60;
      return `${j}j ${m}m`;
    }

    // ===== Status hari ini =====
    async function loadStatus() {
      const json = await apiFetch('/api/get_absensi_status.php');
      const btnMasuk = document.getElementById('btnAbsenMasuk');
      const btnPulang = document.getElementById('btnAbsenPulang');

      if (!json.success) {
        showToast(json.message || 'Gagal memuat status absensi', 'error');
        return;
      }

      const d = json.data;
      document.getElementById('statusJamMasuk').textContent = jamSaja(d.jam_masuk);
      document.getElementById('statusJamPulang').textContent = jamSaja(d.jam_pulang);

      btnMasuk.disabled = d.sudah_absen_masuk;
      btnPulang.disabled = !d.sudah_absen_masuk || d.sudah_absen_pulang;
    }

    async function absenMasuk() {
      const btn = document.getElementById('btnAbsenMasuk');
      btn.disabled = true;
      const json = await apiFetch('/api/absen_masuk.php', { method: 'POST' });
      if (json.success) {
        showToast('Absen masuk berhasil dicatat!', 'success');
        loadStatus(); loadRiwayat();
      } else {
        showToast(json.message || 'Gagal absen masuk', 'error');
        btn.disabled = false;
      }
    }

    async function absenPulang() {
      const btn = document.getElementById('btnAbsenPulang');
      btn.disabled = true;
      const json = await apiFetch('/api/absen_pulang.php', { method: 'POST' });
      if (json.success) {
        showToast('Absen pulang berhasil dicatat!', 'success');
        loadStatus(); loadRiwayat();
      } else {
        showToast(json.message || 'Gagal absen pulang', 'error');
        btn.disabled = false;
      }
    }

    // ===== Riwayat =====
    async function loadRiwayat() {
      const tbody = document.getElementById('riwayatBody');
      const dari = document.getElementById('dariFilter').value;
      const sampai = document.getElementById('sampaiFilter').value;

      tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-neutral-400">Memuat data...</td></tr>`;

      const json = await apiFetch(`/api/get_absensi.php?dari=${dari}&sampai=${sampai}`);
      if (!json.success) {
        tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-red-400">${json.message || 'Gagal memuat riwayat'}</td></tr>`;
        return;
      }
      if (!json.data.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-neutral-400">Belum ada data absensi pada rentang ini.</td></tr>`;
        return;
      }

      tbody.innerHTML = json.data.map(r => `
        <tr class="row-hover border-b border-neutral-50">
          <td class="px-6 py-3 font-semibold text-neutral-700">${r.nama_kasir}</td>
          <td class="px-6 py-3 text-neutral-500">${r.tanggal}</td>
          <td class="px-6 py-3 text-neutral-700">${jamSaja(r.jam_masuk)}</td>
          <td class="px-6 py-3 text-neutral-700">${jamSaja(r.jam_pulang)}</td>
          <td class="px-6 py-3 text-neutral-500">${formatDurasi(r.durasi_menit)}</td>
          <td class="px-6 py-3">${statusBadge(r)}</td>
          <td class="px-6 py-3 text-right">
            <button onclick="koreksiAbsensi(${r.id_absensi})" class="text-xs font-semibold text-neutral-400 hover:text-emerald-600 transition-all flex items-center gap-1 ml-auto" title="Tambah catatan koreksi (data asli tidak berubah)">
              <iconify-icon icon="solar:pen-2-bold" width="14"></iconify-icon> Koreksi
            </button>
          </td>
        </tr>
      `).join('');
    }

    function statusBadge(r) {
      if (!r.jumlah_koreksi || r.jumlah_koreksi == 0) {
        return `<span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full"><iconify-icon icon="solar:check-circle-bold" width="13"></iconify-icon> Valid</span>`;
      }
      const tip = `Dikoreksi oleh ${r.koreksi_oleh}: "${r.koreksi_alasan}" (${jumlahKoreksiText(r.jumlah_koreksi)})`;
      return `<span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full cursor-help" title="${tip.replace(/"/g, '&quot;')}"><iconify-icon icon="solar:danger-triangle-bold" width="13"></iconify-icon> Dikoreksi</span>`;
    }

    function jumlahKoreksiText(n) {
      return n > 1 ? `${n}x koreksi` : '1x koreksi';
    }

    async function koreksiAbsensi(id_absensi) {
      const alasan = prompt('Tulis alasan koreksi (data asli TIDAK akan diubah/dihapus, ini hanya catatan tambahan):');
      if (alasan === null) return; // dibatalkan
      if (!alasan.trim()) { showToast('Alasan koreksi wajib diisi', 'error'); return; }

      const json = await apiFetch('/api/koreksi_absensi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_absensi, alasan: alasan.trim() })
      });

      if (json.success) {
        showToast('Catatan koreksi berhasil ditambahkan', 'success');
        loadRiwayat();
      } else {
        showToast(json.message || 'Gagal menambahkan koreksi', 'error');
      }
    }

    // ===== Clock =====
    function updateClock() {
      const now = new Date(), days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'], months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
      document.getElementById('currentDate').textContent = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]}`;
      document.getElementById('currentTime').textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }

    // ===== Init =====
    // PENTING: jangan pakai toISOString() untuk tanggal "hari ini" — fungsi itu
    // mengonversi ke UTC, sehingga di jam-jam awal hari (WIB = UTC+7) tanggalnya
    // bisa mundur 1 hari dan bikin filter riwayat tidak menemukan data hari ini.
    function todayLocalStr(offsetDays = 0) {
      const d = new Date();
      d.setDate(d.getDate() + offsetDays);
      const yyyy = d.getFullYear();
      const mm = String(d.getMonth() + 1).padStart(2, '0');
      const dd = String(d.getDate()).padStart(2, '0');
      return `${yyyy}-${mm}-${dd}`;
    }
    const today = todayLocalStr();
    const firstOfMonth = today.slice(0, 8) + '01';
    document.getElementById('dariFilter').value = firstOfMonth;
    document.getElementById('sampaiFilter').value = today;

    loadStatus();
    loadRiwayat();
    updateClock();
    setInterval(updateClock, 1000);
  </script>
</body>
</html>

<?php
require_once __DIR__ . '/_guard.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FIRAJAYA — Riwayat Transaksi</title>
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
    .quick-btn { transition: all .15s ease; }
    .quick-btn:hover { background: #059669; color: white; border-color: #059669; }
    .quick-btn.active { background: #059669; color: white; border-color: #059669; }
    input[type="date"]::-webkit-calendar-picker-indicator {
      cursor: pointer;
      opacity: 0.6;
    }
    input[type="date"]::-webkit-calendar-picker-indicator:hover {
      opacity: 1;
    }
  </style>
</head>
<body class="bg-neutral-100 h-screen overflow-hidden">

  <!-- Receipt Print -->
  <div id="receiptPrint" class="hidden font-mono text-xs"></div>

  <!-- Toast Container -->
  <div id="toastContainer" class="fixed top-4 right-4 z-[9999] flex flex-col gap-2"></div>

  <!-- Detail Modal -->
  <div id="detailModal" class="fixed inset-0 z-[9990] hidden items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeDetailModal()"></div>
    <div class="modal-content relative bg-white rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden max-h-[85vh] overflow-y-auto">
      <div class="bg-gradient-to-r from-emerald-500 to-emerald-700 px-6 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center">
              <iconify-icon icon="solar:document-text-bold" width="20" class="text-white"></iconify-icon>
            </div>
            <h2 class="text-lg font-bold text-white">Detail Transaksi</h2>
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
  <a href="riwayat.php" class="sidebar-icon active w-11 h-11 rounded-xl flex items-center justify-center" title="Riwayat">
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
          <span class="text-[10px] font-bold uppercase tracking-widest text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">Riwayat</span>
        </div>
        <div class="flex items-center gap-4">
          <div class="text-right hidden sm:block">
            <p class="text-xs text-neutral-400" id="currentDate"></p>
            <p class="text-sm font-semibold text-neutral-700" id="currentTime"></p>
          </div>
        </div>
      </nav>

      <!-- Main Content -->
      <main class="flex-1 overflow-y-auto p-5 space-y-4">

        <!-- Quick Filter Buttons -->
        <div class="flex flex-wrap gap-2">
          <button onclick="quickFilter('today')" id="btnToday" class="quick-btn px-4 py-2 rounded-xl bg-white border border-neutral-200/80 text-sm font-semibold text-neutral-600 flex items-center gap-1.5">
            <iconify-icon icon="solar:sun-bold" width="16"></iconify-icon>
            Hari Ini
          </button>
          <button onclick="quickFilter('yesterday')" id="btnYesterday" class="quick-btn px-4 py-2 rounded-xl bg-white border border-neutral-200/80 text-sm font-semibold text-neutral-600 flex items-center gap-1.5">
            <iconify-icon icon="solar:moon-bold" width="16"></iconify-icon>
            Kemarin
          </button>
          <button onclick="quickFilter('week')" id="btnWeek" class="quick-btn px-4 py-2 rounded-xl bg-white border border-neutral-200/80 text-sm font-semibold text-neutral-600 flex items-center gap-1.5">
            <iconify-icon icon="solar:calendar-bold" width="16"></iconify-icon>
            7 Hari
          </button>
          <button onclick="quickFilter('month')" id="btnMonth" class="quick-btn px-4 py-2 rounded-xl bg-white border border-neutral-200/80 text-sm font-semibold text-neutral-600 flex items-center gap-1.5">
            <iconify-icon icon="solar:calendar-minimalistic-bold" width="16"></iconify-icon>
            30 Hari
          </button>
          <button onclick="quickFilter('all')" id="btnAll" class="quick-btn active px-4 py-2 rounded-xl bg-white border border-neutral-200/80 text-sm font-semibold text-neutral-600 flex items-center gap-1.5">
            <iconify-icon icon="solar:inbox-bold" width="16"></iconify-icon>
            Semua
          </button>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl border border-neutral-200/60 p-4 shadow-sm">
          <div class="flex flex-wrap gap-3 items-end">
            <!-- Search -->
            <div class="relative flex-1 min-w-[200px]">
              <label class="block text-xs font-semibold text-neutral-500 mb-1.5">Cari Transaksi</label>
              <div class="relative">
                <iconify-icon icon="solar:magnifer-linear" width="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
                <input type="text" id="searchInput" placeholder="Nomor transaksi..." class="w-full pl-9 pr-4 py-2.5 bg-neutral-50 rounded-xl border border-neutral-200 text-sm" oninput="onSearchDebounce()">
              </div>
            </div>
            <!-- Dari Tanggal -->
            <div class="min-w-[150px]">
              <label class="block text-xs font-semibold text-neutral-500 mb-1.5">Dari Tanggal</label>
              <input type="date" id="dariFilter" class="w-full px-3 py-2.5 bg-neutral-50 rounded-xl border border-neutral-200 text-sm cursor-pointer" onchange="onDateChange()">
            </div>
            <!-- Sampai Tanggal -->
            <div class="min-w-[150px]">
              <label class="block text-xs font-semibold text-neutral-500 mb-1.5">Sampai Tanggal</label>
              <input type="date" id="sampaiFilter" class="w-full px-3 py-2.5 bg-neutral-50 rounded-xl border border-neutral-200 text-sm cursor-pointer" onchange="onDateChange()">
            </div>
            <!-- Reset -->
            <button onclick="clearFilters()" class="px-4 py-2.5 rounded-xl bg-neutral-100 border border-neutral-200 text-sm font-semibold text-neutral-600 hover:bg-neutral-200 transition-all flex items-center gap-1.5">
              <iconify-icon icon="solar:refresh-linear" width="16"></iconify-icon>
              Reset
            </button>
          </div>
          <!-- Active Filter Info -->
          <div id="activeFilterInfo" class="hidden mt-3 pt-3 border-t border-neutral-100 flex items-center gap-2">
            <iconify-icon icon="solar:filter-bold" width="14" class="text-emerald-500"></iconify-icon>
            <span class="text-xs text-neutral-500" id="filterText">Menampilkan semua transaksi</span>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <div class="stat-card bg-white rounded-2xl border border-neutral-200/60 p-4">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center">
                <iconify-icon icon="solar:document-text-bold" width="16" class="text-emerald-500"></iconify-icon>
              </div>
              <span class="text-xs text-neutral-400">Transaksi</span>
            </div>
            <p class="text-2xl font-bold text-neutral-900" id="sumCount">0</p>
          </div>
          <div class="stat-card bg-white rounded-2xl border border-neutral-200/60 p-4">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center">
                <iconify-icon icon="solar:wallet-bold" width="16" class="text-emerald-500"></iconify-icon>
              </div>
              <span class="text-xs text-neutral-400">Pendapatan</span>
            </div>
            <p class="text-2xl font-bold text-emerald-600" id="sumRevenue">Rp 0</p>
          </div>
          <div class="stat-card bg-white rounded-2xl border border-neutral-200/60 p-4">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                <iconify-icon icon="solar:chart-2-bold" width="16" class="text-blue-500"></iconify-icon>
              </div>
              <span class="text-xs text-neutral-400">Rata-rata</span>
            </div>
            <p class="text-2xl font-bold text-blue-600" id="sumAverage">Rp 0</p>
          </div>
          <div class="stat-card bg-white rounded-2xl border border-neutral-200/60 p-4">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center">
                <iconify-icon icon="solar:box-minimalistic-bold" width="16" class="text-amber-500"></iconify-icon>
              </div>
              <span class="text-xs text-neutral-400">Terjual</span>
            </div>
            <p class="text-2xl font-bold text-amber-600" id="sumItems">0</p>
          </div>
        </div>

        <!-- Transaction Table -->
        <div class="bg-white rounded-2xl border border-neutral-200/60 overflow-hidden shadow-sm">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-neutral-100 bg-neutral-50/50">
                  <th class="text-left px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">No. Transaksi</th>
                  <th class="text-left px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Waktu</th>
                  <th class="text-right px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Subtotal</th>
                  <th class="text-right px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Diskon</th>
                  <th class="text-right px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Pajak</th>
                  <th class="text-right px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Total</th>
                  <th class="text-right px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Bayar</th>
                  <th class="text-right px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Kembalian</th>
                  <th class="text-center px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Kasir</th>
                  <th class="text-center px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Aksi</th>
                </tr>
              </thead>
              <tbody id="transactionTable">
                <tr>
                  <td colspan="10" class="text-center py-12 text-neutral-400">
                    <div class="flex flex-col items-center">
                      <iconify-icon icon="solar:clock-circle-linear" width="40" class="text-neutral-300 mb-2"></iconify-icon>
                      <p class="font-semibold">Memuat data...</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between">
          <p class="text-sm text-neutral-400" id="paginationInfo">-</p>
          <div class="flex gap-2" id="paginationButtons"></div>
        </div>

      </main>
    </div>
  </div>

  <script>
    const API_BASE = '';
    let searchTimer = null;
    let currentPage = 1;
    let activeQuickFilter = 'all';

    // =================== DATE HELPERS ===================
    function formatDate(date) {
      const y = date.getFullYear();
      const m = String(date.getMonth() + 1).padStart(2, '0');
      const d = String(date.getDate()).padStart(2, '0');
      return `${y}-${m}-${d}`;
    }
    function getToday() { return formatDate(new Date()); }
    function getYesterday() {
      const d = new Date(); d.setDate(d.getDate() - 1); return formatDate(d);
    }
    function getDaysAgo(n) {
      const d = new Date(); d.setDate(d.getDate() - n + 1); return formatDate(d);
    }

    // =================== API ===================
    async function apiFetch(url) {
      try {
        const sep = url.includes('?') ? '&' : '?';
        const res = await fetch(API_BASE + url + sep + '_t=' + Date.now());
        const json = await res.json().catch(() => null);
        if (json) return json;
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return { success: false, message: 'Response tidak valid' };
      } catch (e) {
        return { success: false, message: e.message };
      }
    }

    // =================== QUICK FILTER ===================
    function quickFilter(type) {
      activeQuickFilter = type;
      document.querySelectorAll('.quick-btn').forEach(b => b.classList.remove('active'));
      const dariInput = document.getElementById('dariFilter');
      const sampaiInput = document.getElementById('sampaiFilter');
      switch(type) {
        case 'today':
          dariInput.value = getToday(); sampaiInput.value = getToday();
          document.getElementById('btnToday').classList.add('active'); break;
        case 'yesterday':
          dariInput.value = getYesterday(); sampaiInput.value = getYesterday();
          document.getElementById('btnYesterday').classList.add('active'); break;
        case 'week':
          dariInput.value = getDaysAgo(7); sampaiInput.value = getToday();
          document.getElementById('btnWeek').classList.add('active'); break;
        case 'month':
          dariInput.value = getDaysAgo(30); sampaiInput.value = getToday();
          document.getElementById('btnMonth').classList.add('active'); break;
        case 'all':
          dariInput.value = ''; sampaiInput.value = '';
          document.getElementById('btnAll').classList.add('active'); break;
      }
      updateFilterInfo(); loadTransactions(1);
    }

    function onDateChange() {
      document.querySelectorAll('.quick-btn').forEach(b => b.classList.remove('active'));
      activeQuickFilter = 'custom';
      updateFilterInfo(); loadTransactions(1);
    }

    function updateFilterInfo() {
      const dari = document.getElementById('dariFilter').value;
      const sampai = document.getElementById('sampaiFilter').value;
      const search = document.getElementById('searchInput').value.trim();
      const infoEl = document.getElementById('activeFilterInfo');
      const textEl = document.getElementById('filterText');
      let parts = [];
      if (dari && sampai) {
        const dFrom = new Date(dari).toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'});
        const dTo = new Date(sampai).toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'});
        parts.push(`Tanggal: ${dFrom} — ${dTo}`);
      } else if (dari) {
        parts.push(`Dari: ${new Date(dari).toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'})}`);
      } else if (sampai) {
        parts.push(`Sampai: ${new Date(sampai).toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'})}`);
      }
      if (search) parts.push(`Pencarian: "${search}"`);
      if (parts.length > 0) { textEl.textContent = parts.join(' | '); infoEl.classList.remove('hidden'); }
      else { textEl.textContent = 'Menampilkan semua transaksi'; infoEl.classList.add('hidden'); }
    }

    // =================== LOAD TRANSACTIONS ===================
    async function loadTransactions(page = 1) {
      currentPage = page;
      const search = document.getElementById('searchInput').value.trim();
      const dari = document.getElementById('dariFilter').value;
      const sampai = document.getElementById('sampaiFilter').value;
      let url = `/api/get_transactions.php?page=${page}`;
      if (search) url += `&search=${encodeURIComponent(search)}`;
      if (dari) url += `&dari=${dari}`;
      if (sampai) url += `&sampai=${sampai}`;
      const json = await apiFetch(url);
      if (json.success) {
        renderTable(json.data); renderPagination(json.page, json.pages, json.total); updateSummary(json.data, json.summary, json.total);
      } else {
        document.getElementById('transactionTable').innerHTML = `<tr><td colspan="10" class="text-center py-12 text-red-400"><div class="flex flex-col items-center"><iconify-icon icon="solar:close-circle-linear" width="40" class="mb-2"></iconify-icon><p class="font-semibold">Gagal memuat data</p><p class="text-xs text-neutral-400 mt-1">${json.message||'Cek koneksi database'}</p></div></td></tr>`;
      }
    }

    // =================== RENDER TABLE ===================
    function renderTable(data) {
      const tbody = document.getElementById('transactionTable');
      if (!data || data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="10" class="text-center py-12 text-neutral-400"><div class="flex flex-col items-center"><iconify-icon icon="solar:clock-circle-linear" width="40" class="text-neutral-300 mb-2"></iconify-icon><p class="font-semibold">Belum ada transaksi</p><p class="text-xs">Data transaksi akan muncul setelah checkout</p></div></td></tr>`;
        return;
      }
      tbody.innerHTML = '';
      data.forEach((t) => {
        const tr = document.createElement('tr');
        tr.className = 'row-hover border-b border-neutral-50';
        const tgl = new Date(t.tanggal);
        const tglStr = tgl.toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'});
        const jamStr = tgl.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});
        tr.innerHTML = `
          <td class="px-4 py-3"><span class="font-semibold text-emerald-700 text-xs bg-emerald-50 px-2 py-1 rounded-lg">${t.no_transaksi}</span></td>
          <td class="px-4 py-3"><span class="text-neutral-700 text-xs">${tglStr}</span><span class="text-neutral-400 text-xs ml-1">${jamStr}</span></td>
          <td class="px-4 py-3 text-right text-xs text-neutral-600">Rp ${Number(t.subtotal).toLocaleString('id-ID')}</td>
          <td class="px-4 py-3 text-right text-xs text-red-400">- Rp ${Number(t.diskon_rupiah).toLocaleString('id-ID')}</td>
          <td class="px-4 py-3 text-right text-xs text-neutral-600">Rp ${Number(t.pajak_rupiah).toLocaleString('id-ID')}</td>
          <td class="px-4 py-3 text-right font-bold text-emerald-600 text-xs">Rp ${Number(t.total).toLocaleString('id-ID')}</td>
          <td class="px-4 py-3 text-right text-xs text-neutral-700">Rp ${Number(t.bayar).toLocaleString('id-ID')}</td>
          <td class="px-4 py-3 text-right text-xs text-neutral-500">Rp ${Number(t.kembalian).toLocaleString('id-ID')}</td>
          <td class="px-4 py-3 text-center text-xs text-neutral-500">${t.nama_kasir||'-'}</td>
          <td class="px-4 py-3 text-center">
            <div class="flex items-center justify-center gap-1">
              <button onclick="viewDetail(${t.id_transaksi})" class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-100 transition-all" title="Detail"><iconify-icon icon="solar:eye-bold" width="14"></iconify-icon></button>
              <button onclick="reprintReceipt(${t.id_transaksi})" class="w-7 h-7 rounded-lg bg-neutral-100 text-neutral-500 flex items-center justify-center hover:bg-neutral-200 transition-all" title="Cetak Ulang"><iconify-icon icon="solar:printer-minimalistic-bold" width="14"></iconify-icon></button>
            </div>
          </td>`;
        tbody.appendChild(tr);
      });
    }

    // =================== PAGINATION ===================
    function renderPagination(page, pages, total) {
      document.getElementById('paginationInfo').textContent = `Halaman ${page} dari ${pages} (${total} transaksi)`;
      const container = document.getElementById('paginationButtons');
      container.innerHTML = '';
      if (pages <= 1) return;
      if (page > 1) { const b=document.createElement('button'); b.className='px-3 py-1.5 rounded-lg bg-white border border-neutral-200 text-sm font-semibold text-neutral-600 hover:bg-neutral-50 transition-all'; b.textContent='← Prev'; b.onclick=()=>loadTransactions(page-1); container.appendChild(b); }
      let start=Math.max(1,page-2), end=Math.min(pages,start+4); start=Math.max(1,end-4);
      for(let i=start;i<=end;i++){ const b=document.createElement('button'); b.className=i===page?'px-3 py-1.5 rounded-lg bg-emerald-500 text-white text-sm font-semibold':'px-3 py-1.5 rounded-lg bg-white border border-neutral-200 text-sm font-semibold text-neutral-600 hover:bg-neutral-50 transition-all'; b.textContent=i; b.onclick=()=>loadTransactions(i); container.appendChild(b); }
      if(page<pages){ const b=document.createElement('button'); b.className='px-3 py-1.5 rounded-lg bg-white border border-neutral-200 text-sm font-semibold text-neutral-600 hover:bg-neutral-50 transition-all'; b.textContent='Next →'; b.onclick=()=>loadTransactions(page+1); container.appendChild(b); }
    }

    // =================== SUMMARY ===================
    function updateSummary(data, summary, total) {
      const r=Number(summary?.total_pendapatan||0), items=Number(summary?.total_item||0), avg=total>0?Math.round(r/total):0;
      document.getElementById('sumCount').textContent=total;
      document.getElementById('sumRevenue').textContent=`Rp ${r.toLocaleString('id-ID')}`;
      document.getElementById('sumAverage').textContent=`Rp ${avg.toLocaleString('id-ID')}`;
      document.getElementById('sumItems').textContent=items.toLocaleString('id-ID');
    }

    // =================== DETAIL ===================
    async function viewDetail(id) {
      const json=await apiFetch(`/api/get_transaction_detail.php?id=${id}`);
      if(!json.success){showToast('Gagal memuat detail','error');return;}
      const t=json.data, tgl=new Date(t.tanggal);
      let itemRows='';
      t.items.forEach(item=>{ itemRows+=`<tr class="border-b border-neutral-100"><td class="py-2 text-neutral-800">${item.nama_produk}</td><td class="py-2 text-center text-neutral-600">${item.qty}</td><td class="py-2 text-right text-neutral-600">Rp ${Number(item.harga).toLocaleString('id-ID')}</td><td class="py-2 text-right font-semibold text-neutral-800">Rp ${Number(item.subtotal).toLocaleString('id-ID')}</td></tr>`; });
      document.getElementById('detailContent').innerHTML=`<div class="space-y-4"><div class="grid grid-cols-2 gap-3 text-sm"><div><p class="text-neutral-400 text-xs">No. Transaksi</p><p class="font-bold text-neutral-800">${t.no_transaksi}</p></div><div><p class="text-neutral-400 text-xs">Waktu</p><p class="font-bold text-neutral-800">${tgl.toLocaleDateString('id-ID')} ${tgl.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'})}</p></div><div><p class="text-neutral-400 text-xs">Kasir</p><p class="font-semibold text-neutral-700">${t.nama_kasir||'-'}</p></div><div><p class="text-neutral-400 text-xs">Item</p><p class="font-semibold text-neutral-700">${t.items.length} produk</p></div></div><div class="border-t border-neutral-100 pt-3"><table class="w-full text-sm"><thead><tr class="border-b border-neutral-200"><th class="py-2 text-left text-xs font-semibold text-neutral-500">Produk</th><th class="py-2 text-center text-xs font-semibold text-neutral-500">Qty</th><th class="py-2 text-right text-xs font-semibold text-neutral-500">Harga</th><th class="py-2 text-right text-xs font-semibold text-neutral-500">Sub</th></tr></thead><tbody>${itemRows}</tbody></table></div><div class="border-t border-neutral-100 pt-3 space-y-1.5 text-sm"><div class="flex justify-between"><span class="text-neutral-500">Subtotal</span><span class="text-neutral-700">Rp ${Number(t.subtotal).toLocaleString('id-ID')}</span></div><div class="flex justify-between"><span class="text-neutral-500">Diskon</span><span class="text-red-400">- Rp ${Number(t.diskon_rupiah).toLocaleString('id-ID')}</span></div><div class="flex justify-between"><span class="text-neutral-500">Pajak (11%)</span><span class="text-neutral-700">Rp ${Number(t.pajak_rupiah).toLocaleString('id-ID')}</span></div><div class="flex justify-between font-bold text-base border-t border-neutral-200 pt-2 mt-2"><span>Total</span><span class="text-emerald-600">Rp ${Number(t.total).toLocaleString('id-ID')}</span></div><div class="flex justify-between"><span class="text-neutral-500">Bayar</span><span class="text-neutral-700">Rp ${Number(t.bayar).toLocaleString('id-ID')}</span></div><div class="flex justify-between"><span class="text-neutral-500">Kembalian</span><span class="text-emerald-600 font-semibold">Rp ${Number(t.kembalian).toLocaleString('id-ID')}</span></div></div></div>`;
      const modal=document.getElementById('detailModal'); modal.classList.remove('hidden'); modal.classList.add('flex');
    }
    function closeDetailModal(){document.getElementById('detailModal').classList.add('hidden');document.getElementById('detailModal').classList.remove('flex');}

    // =================== REPRINT ===================
    async function reprintReceipt(id) {
      const json=await apiFetch(`/api/get_transaction_detail.php?id=${id}`);
      if(!json.success){showToast('Gagal','error');return;}
      const t=json.data, now=new Date(t.tanggal);
      
      let rows='';
      t.items.forEach(item=>{ 
          rows+=`<div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:2px">
          <div style="flex:1">${item.nama_produk}</div>
          <div style="width:30px;text-align:center">${item.qty}</div>
          <div style="width:70px;text-align:right">${Number(item.subtotal).toLocaleString('id-ID')}</div>
          </div>`; 
      });

      const el=document.getElementById('receiptPrint');
      el.innerHTML=`
        <div style="text-align:center;margin-bottom:8px">
            <div style="font-size:14px;font-weight:bold;letter-spacing:1px">FIRAJAYA SEMBAKO</div>
            <div style="font-size:10px;margin-top:2px">Jl. Contoh No.123, Jakarta</div>
            <div style="font-size:10px">Telp: 0812-3456-7890</div>
        </div>
        <hr style="border-top:1px dashed #000;margin:6px 0">
        <div style="display:flex;justify-content:space-between;font-size:10px">
            <span>No: ${t.no_transaksi}</span>
            <span>${now.toLocaleDateString('id-ID')} ${now.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'})}</span>
        </div>
        <div style="font-size:10px;margin-bottom:4px">Kasir: ${t.nama_kasir||'-'}</div>
        <hr style="border-top:1px dashed #000;margin:6px 0">
        
        <div style="display:flex;justify-content:space-between;font-size:10px;font-weight:bold;margin-bottom:4px;border-bottom:1px solid #000;padding-bottom:2px">
            <div style="flex:1">Item</div>
            <div style="width:30px;text-align:center">Qty</div>
            <div style="width:70px;text-align:right">Sub</div>
        </div>
        ${rows}
        
        <hr style="border-top:1px dashed #000;margin:6px 0">
        <div style="font-size:11px">
            <div style="display:flex;justify-content:space-between"><span>Subtotal</span><span>Rp ${Number(t.subtotal).toLocaleString('id-ID')}</span></div>
            <div style="display:flex;justify-content:space-between"><span>Diskon</span><span>Rp ${Number(t.diskon_rupiah).toLocaleString('id-ID')}</span></div>
            <div style="display:flex;justify-content:space-between"><span>Pajak</span><span>Rp ${Number(t.pajak_rupiah).toLocaleString('id-ID')}</span></div>
        </div>
        <hr style="border-top:1px dashed #000;margin:4px 0">
        <div style="display:flex;justify-content:space-between;font-weight:bold;font-size:14px;margin:4px 0">
            <span>TOTAL</span>
            <span>Rp ${Number(t.total).toLocaleString('id-ID')}</span>
        </div>
        <div style="font-size:11px;margin-top:4px">
            <div style="display:flex;justify-content:space-between"><span>Bayar</span><span>Rp ${Number(t.bayar).toLocaleString('id-ID')}</span></div>
            <div style="display:flex;justify-content:space-between;font-weight:bold"><span>Kembalian</span><span>Rp ${Number(t.kembalian).toLocaleString('id-ID')}</span></div>
        </div>
        <hr style="border-top:1px dashed #000;margin:8px 0">
        <div style="text-align:center;font-size:10px">
            <p style="margin:2px 0">Terima kasih atas kunjungan Anda!</p>
            <p style="margin:2px 0">*** CETAK ULANG ***</p>
            <p style="margin:2px 0">Barang yang sudah dibeli tidak ditukar</p>
        </div>`;
        
      el.classList.remove('hidden'); 
      window.print(); 
      setTimeout(() => { el.classList.add('hidden'); }, 500);
    }
    // =================== SEARCH ===================
    function onSearchDebounce(){clearTimeout(searchTimer);searchTimer=setTimeout(()=>{updateFilterInfo();loadTransactions(1);},300);}
    function clearFilters(){
      document.getElementById('searchInput').value=''; document.getElementById('dariFilter').value=''; document.getElementById('sampaiFilter').value='';
      activeQuickFilter='all'; document.querySelectorAll('.quick-btn').forEach(b=>b.classList.remove('active'));
      document.getElementById('btnAll').classList.add('active'); document.getElementById('activeFilterInfo').classList.add('hidden');
      loadTransactions(1);
    }

    // =================== TOAST ===================
    function showToast(message,type='success'){
      const c=document.getElementById('toastContainer'),t=document.createElement('div');
      const icon=type==='success'?'<iconify-icon icon="solar:check-circle-bold" width="18" class="text-emerald-500"></iconify-icon>':'<iconify-icon icon="solar:close-circle-bold" width="18" class="text-red-400"></iconify-icon>';
      t.className='toast-enter glass-strong flex items-center gap-2 px-4 py-3 rounded-xl border border-neutral-200/60 shadow-lg text-sm font-medium text-neutral-700 min-w-[220px]';
      t.innerHTML=`${icon} ${message}`; c.appendChild(t);
      setTimeout(()=>{t.classList.remove('toast-enter');t.classList.add('toast-exit');setTimeout(()=>t.remove(),300);},2500);
    }

    // =================== CLOCK ===================
    function updateClock(){
      const now=new Date(),days=['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'],months=['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
      const d=document.getElementById('currentDate'),t=document.getElementById('currentTime');
      if(d)d.textContent=`${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]}`;
      if(t)t.textContent=now.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
    }

    // =================== KEYBOARD ===================
    document.addEventListener('keydown',(e)=>{if(e.key==='Escape')closeDetailModal();});

    // =================== INIT ===================
    loadTransactions(1); updateClock(); setInterval(updateClock,1000);

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
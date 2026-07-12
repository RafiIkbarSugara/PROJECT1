<?php
require_once __DIR__ . '/_guard.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FIRAJAYA — Manajemen Stok</title>
  <script src="https://cdn.tailwindcss.com/3.4.17"></script>
  <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
  <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Geist','Inter','sans-serif']}}}}</script>
  <link rel="stylesheet" href="/css/style.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700;800;900&display=swap">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
  <style>.stat-card{transition:all .2s ease}.stat-card:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,.08)}.stock-progress{transition:width 0.8s cubic-bezier(0.16,1,0.3,1)}</style>
</head>
<body class="bg-neutral-100 h-screen overflow-hidden">

  <div id="toastContainer" class="fixed top-4 right-4 z-[9999] flex flex-col gap-2"></div>

  <!-- Restock Modal -->
  <div id="restockModal" class="fixed inset-0 z-[9990] hidden items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeRestockModal()"></div>
    <div class="modal-content relative bg-white rounded-3xl max-w-sm w-full shadow-2xl p-6">
      <div class="flex items-center gap-3 mb-5"><div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center"><iconify-icon icon="solar:box-minimalistic-bold" width="24" class="text-emerald-500"></iconify-icon></div><div><h3 class="text-lg font-bold text-neutral-900">Tambah Stok</h3><p class="text-sm text-neutral-400" id="restockProductName">-</p></div></div>
      <div class="mb-3"><div class="flex justify-between text-sm mb-1"><span class="text-neutral-500">Stok saat ini</span><span class="font-bold text-neutral-700" id="restockCurrentStok">0</span></div></div>
      <div class="mb-5"><label class="block text-sm font-semibold text-neutral-700 mb-1.5">Tambah Stok</label><div class="relative"><iconify-icon icon="solar:add-square-linear" width="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon><input type="number" id="restockInput" min="1" placeholder="0" class="w-full pl-11 pr-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-base font-bold text-neutral-800 placeholder:text-neutral-300" oninput="updateRestockPreview()"></div><div class="flex justify-between text-sm mt-2 px-1"><span class="text-neutral-500">Stok baru</span><span class="font-bold text-emerald-600" id="restockNewStok">0</span></div></div>
      <div class="flex gap-3"><button onclick="closeRestockModal()" class="flex-1 py-2.5 rounded-xl border border-neutral-200 text-neutral-600 font-semibold text-sm hover:bg-neutral-50 transition-all">Batal</button><button onclick="submitRestock()" id="restockSubmitBtn" class="flex-1 py-2.5 rounded-xl bg-emerald-500 text-white font-semibold text-sm hover:bg-emerald-600 transition-all flex items-center justify-center gap-1.5"><iconify-icon icon="solar:check-circle-bold" width="16"></iconify-icon> Tambah</button></div>
    </div>
  </div>

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
  <a href="stok.php" class="sidebar-icon active w-11 h-11 rounded-xl flex items-center justify-center" title="Stok">
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
    <div class="flex-1 flex flex-col overflow-hidden">
      <nav class="h-16 bg-white/80 backdrop-blur-xl border-b border-neutral-200/60 flex items-center justify-between px-6 shrink-0 z-20">
        <div class="flex items-center gap-3"><h1 class="text-lg font-bold text-neutral-900 tracking-tight">FIRAJAYA</h1><span class="text-[10px] font-bold uppercase tracking-widest text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">Stok</span></div>
        <div class="flex items-center gap-4"><div class="text-right hidden sm:block"><p class="text-xs text-neutral-400" id="currentDate"></p><p class="text-sm font-semibold text-neutral-700" id="currentTime"></p></div></div>
      </nav>

      <main class="flex-1 overflow-y-auto p-5 space-y-4">
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <div class="stat-card bg-white rounded-2xl border border-neutral-200/60 p-4"><div class="flex items-center gap-2 mb-2"><div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center"><iconify-icon icon="solar:box-minimalistic-bold" width="16" class="text-blue-500"></iconify-icon></div><span class="text-xs text-neutral-400">Total Produk</span></div><p class="text-2xl font-bold text-neutral-900" id="sumTotal">0</p></div>
          <div class="stat-card bg-white rounded-2xl border border-neutral-200/60 p-4"><div class="flex items-center gap-2 mb-2"><div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center"><iconify-icon icon="solar:check-circle-bold" width="16" class="text-emerald-500"></iconify-icon></div><span class="text-xs text-neutral-400">Stok Aman</span></div><p class="text-2xl font-bold text-emerald-600" id="sumSafe">0</p></div>
          <div class="stat-card bg-white rounded-2xl border border-neutral-200/60 p-4"><div class="flex items-center gap-2 mb-2"><div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center"><iconify-icon icon="solar:danger-triangle-bold" width="16" class="text-amber-500"></iconify-icon></div><span class="text-xs text-neutral-400">Stok Rendah</span></div><p class="text-2xl font-bold text-amber-600" id="sumLow">0</p></div>
          <div class="stat-card bg-white rounded-2xl border border-neutral-200/60 p-4"><div class="flex items-center gap-2 mb-2"><div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center"><iconify-icon icon="solar:close-circle-bold" width="16" class="text-red-500"></iconify-icon></div><span class="text-xs text-neutral-400">Stok Habis</span></div><p class="text-2xl font-bold text-red-600" id="sumOut">0</p></div>
        </div>

        <!-- Filter -->
        <div class="flex flex-wrap gap-3 items-center">
          <div class="relative flex-1 min-w-[200px] max-w-md">
            <iconify-icon icon="solar:magnifer-linear" width="18" class="absolute left-4 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
            <input type="text" id="searchInput" placeholder="Cari produk..." class="w-full pl-11 pr-4 py-3 bg-white rounded-2xl border border-neutral-200/80 text-sm shadow-sm" oninput="onSearchDebounce()">
          </div>
          <div class="flex gap-2">
            <button onclick="filterStock('all')" id="btnAll" class="quick-btn active px-4 py-2.5 rounded-xl bg-white border border-neutral-200/80 text-sm font-semibold text-neutral-600">Semua</button>
            <button onclick="filterStock('low')" id="btnLow" class="quick-btn px-4 py-2.5 rounded-xl bg-white border border-neutral-200/80 text-sm font-semibold text-neutral-600">Rendah</button>
            <button onclick="filterStock('out')" id="btnOut" class="quick-btn px-4 py-2.5 rounded-xl bg-white border border-neutral-200/80 text-sm font-semibold text-neutral-600">Habis</button>
          </div>
        </div>

        <!-- Stock Table -->
        <div class="bg-white rounded-2xl border border-neutral-200/60 overflow-hidden shadow-sm">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-neutral-100 bg-neutral-50/50">
                  <th class="text-left px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Produk</th>
                  <th class="text-left px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Kategori</th>
                  <th class="text-center px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Stok Saat Ini</th>
                  <th class="text-left px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider hidden md:table-cell">Visualisasi</th>
                  <th class="text-center px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Status</th>
                  <th class="text-center px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Aksi</th>
                </tr>
              </thead>
              <tbody id="stockTableBody">
                <tr><td colspan="6" class="text-center py-12 text-neutral-400"><iconify-icon icon="solar:layers-minimalistic-linear" width="40" class="text-neutral-300 mb-2"></iconify-icon><p class="font-semibold">Memuat data...</p></td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script>
    const API_BASE = '';
    let allProducts = [];
    let currentStockFilter = 'all';
    let searchTimer = null;
    let restockProductId = null;
    let restockProductStok = 0;

    async function apiFetch(url, opts = {}) {
      try { const sep = url.includes('?') ? '&' : '?'; const res = await fetch(API_BASE + url + sep + '_t=' + Date.now(), opts); const json = await res.json().catch(() => null); if (json) return json; if (!res.ok) throw new Error(`HTTP ${res.status}`); return { success: false, message: 'Response tidak valid' }; } catch (e) { return { success: false, message: e.message }; }
    }

    async function init() {
      const json = await apiFetch('/api/get_products.php');
      if (json.success) { allProducts = json.data; renderSummary(); renderTable(); }
    }

    function renderSummary() {
      const total = allProducts.length;
      const safe = allProducts.filter(p => p.stok > 10).length;
      const low = allProducts.filter(p => p.stok > 0 && p.stok <= 10).length;
      const out = allProducts.filter(p => p.stok <= 0).length;
      document.getElementById('sumTotal').textContent = total;
      document.getElementById('sumSafe').textContent = safe;
      document.getElementById('sumLow').textContent = low;
      document.getElementById('sumOut').textContent = out;
    }

    function renderTable() {
      const tbody = document.getElementById('stockTableBody');
      let filtered = [...allProducts];
      const search = document.getElementById('searchInput')?.value.toLowerCase().trim() || '';
      if (search) filtered = filtered.filter(p => p.nama_produk.toLowerCase().includes(search));

      if (currentStockFilter === 'low') filtered = filtered.filter(p => p.stok > 0 && p.stok <= 10);
      if (currentStockFilter === 'out') filtered = filtered.filter(p => p.stok <= 0);

      if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-12 text-neutral-400"><iconify-icon icon="solar:layers-minimalistic-linear" width="40" class="text-neutral-300 mb-2"></iconify-icon><p class="font-semibold">Tidak ada produk</p></td></tr>`;
        return;
      }

      tbody.innerHTML = '';
      filtered.forEach(p => {
        const maxVisual = 200; // Angka stok maksimal untuk bar 100%
        const widthPct = Math.min(100, Math.max(0, (p.stok / maxVisual) * 100));
        let barColor = 'bg-emerald-500';
        let statusBadge = '<span class="text-[11px] font-bold px-2 py-1 rounded-lg bg-emerald-50 text-emerald-600">Aman</span>';
        
        if (p.stok <= 0) {
          barColor = 'bg-red-500'; statusBadge = '<span class="text-[11px] font-bold px-2 py-1 rounded-lg bg-red-50 text-red-600">Habis</span>';
        } else if (p.stok <= 10) {
          barColor = 'bg-amber-500'; statusBadge = '<span class="text-[11px] font-bold px-2 py-1 rounded-lg bg-amber-50 text-amber-600">Rendah</span>';
        }

        const safeName = (p.nama_produk || '').replace(/'/g, "\\'");
        tbody.innerHTML += `
        <tr class="row-hover border-b border-neutral-50 ${p.stok <= 0 ? 'opacity-60' : ''}">
          <td class="px-4 py-3"><p class="font-semibold text-neutral-800">${p.nama_produk}</p><p class="text-[11px] text-neutral-400">${p.satuan || 'pcs'}</p></td>
          <td class="px-4 py-3 text-xs text-neutral-600">${p.nama_kategori}</td>
          <td class="px-4 py-3 text-center font-bold text-lg text-neutral-900">${p.stok}</td>
          <td class="px-4 py-3 hidden md:table-cell"><div class="w-full bg-neutral-100 rounded-full h-2.5"><div class="stock-progress ${barColor} h-2.5 rounded-full" style="width: ${widthPct}%"></div></div></td>
          <td class="px-4 py-3 text-center">${statusBadge}</td>
          <td class="px-4 py-3 text-center">
            <button onclick="openRestockModal(${p.id_produk}, '${safeName}', ${p.stok})" class="px-4 py-2 rounded-xl bg-emerald-50 text-emerald-600 text-xs font-semibold hover:bg-emerald-100 transition-all flex items-center gap-1 mx-auto"><iconify-icon icon="solar:add-square-bold" width="14"></iconify-icon> Restock</button>
          </td>
        </tr>`;
      });
    }

    // Filter Buttons
    function filterStock(type) {
      currentStockFilter = type;
      document.querySelectorAll('.quick-btn').forEach(b => b.classList.remove('active'));
      if(type==='all') document.getElementById('btnAll').classList.add('active');
      if(type==='low') document.getElementById('btnLow').classList.add('active');
      if(type==='out') document.getElementById('btnOut').classList.add('active');
      renderTable();
    }

    function onSearchDebounce() { clearTimeout(searchTimer); searchTimer = setTimeout(() => renderTable(), 300); }

    // Restock Functions
    function openRestockModal(id, name, stok) {
      restockProductId = id; restockProductStok = parseInt(stok) || 0;
      document.getElementById('restockProductName').textContent = name;
      document.getElementById('restockCurrentStok').textContent = stok;
      document.getElementById('restockInput').value = '';
      document.getElementById('restockNewStok').textContent = stok;
      const modal = document.getElementById('restockModal'); modal.classList.remove('hidden'); modal.classList.add('flex');
      setTimeout(() => document.getElementById('restockInput').focus(), 100);
    }
    function closeRestockModal() { const modal = document.getElementById('restockModal'); modal.classList.add('hidden'); modal.classList.remove('flex'); restockProductId = null; }
    function updateRestockPreview() { const tambah = parseInt(document.getElementById('restockInput').value) || 0; document.getElementById('restockNewStok').textContent = restockProductStok + tambah; }

    async function submitRestock() {
      if (!restockProductId) return; const tambah = parseInt(document.getElementById('restockInput').value) || 0;
      if (tambah <= 0) { showToast('Jumlah harus > 0', 'error'); return; }
      const btn = document.getElementById('restockSubmitBtn'); btn.disabled = true; btn.innerHTML = 'Menyimpan...';
      try {
        const res = await fetch(API_BASE + '/api/update_stock.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id_produk: restockProductId, tambah_stok: tambah }) });
        const result = await res.json();
        if (result.success) { showToast(result.message, 'success'); closeRestockModal(); init(); }
        else { showToast(result.message || 'Gagal', 'error'); }
      } catch (e) { showToast('Error', 'error'); } finally { btn.disabled = false; btn.innerHTML = '<iconify-icon icon="solar:check-circle-bold" width="16"></iconify-icon> Tambah'; }
    }

    function showToast(msg, type='success') { const c=document.getElementById('toastContainer'),t=document.createElement('div'),ic=type==='success'?'<iconify-icon icon="solar:check-circle-bold" width="18" class="text-emerald-500"></iconify-icon>':'<iconify-icon icon="solar:close-circle-bold" width="18" class="text-red-400"></iconify-icon>'; t.className='toast-enter glass-strong flex items-center gap-2 px-4 py-3 rounded-xl border border-neutral-200/60 shadow-lg text-sm font-medium text-neutral-700 min-w-[220px]'; t.innerHTML=`${ic} ${msg}`; c.appendChild(t); setTimeout(()=>{t.classList.remove('toast-enter');t.classList.add('toast-exit');setTimeout(()=>t.remove(),300);},2500); }
    function updateClock(){const n=new Date(),d=['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'],m=['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];const dd=document.getElementById('currentDate'),tt=document.getElementById('currentTime');if(dd)dd.textContent=`${d[n.getDay()]}, ${n.getDate()} ${m[n.getMonth()]}`;if(tt)tt.textContent=n.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit',second:'2-digit'})}
    
    // Cek jika ada param ?restock=ID dari halaman produk
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('restock')) { init().then(() => { const rId = urlParams.get('restock'); const p = allProducts.find(x => x.id_produk == rId); if(p) openRestockModal(p.id_produk, p.nama_produk, p.stok); }); }
    else { init(); }

    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeRestockModal(); if(e.key==='Enter' && restockProductId) submitRestock(); });
    updateClock(); setInterval(updateClock, 1000);

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
<?php
session_start();
if (!isset($_SESSION['customer_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}
 $userName = $_SESSION['admin_name'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FIRAJAYA — Riwayat Pesanan Saya</title>
  <script src="https://cdn.tailwindcss.com/3.4.17"></script>
  <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
  <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Geist','Inter','sans-serif']}}}}</script>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700;800;900&display=swap">
  <style>
    body { font-family: 'Geist', 'Inter', sans-serif; }
    .nav-glass { background: rgba(255,255,255,0.75); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border-bottom: 1px solid rgba(229,229,229,0.5); }
    .row-hover { transition: all .15s ease; }
    .row-hover:hover { background: #f0f7f1; }
  </style>
</head>
<body class="bg-neutral-50 text-neutral-800 antialiased">

  <div id="toastContainer" class="fixed top-4 right-4 z-[9999] flex flex-col gap-2"></div>

  <!-- ====== NAVBAR ====== -->
  <header class="nav-glass fixed top-0 left-0 right-0 z-50">
    <div class="max-w-5xl mx-auto flex items-center justify-between px-6 py-3">
      <a href="index.php" class="flex items-center gap-2.5">
        <div class="w-9 h-9 bg-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-600/20">
          <iconify-icon icon="solar:shop-bold" width="20" class="text-white"></iconify-icon>
        </div>
        <span class="text-lg font-extrabold text-neutral-900 tracking-tight">FIRAJAYA</span>
      </a>
      <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-neutral-500">
        <a href="index.php#menu" class="hover:text-emerald-600 transition-colors">Katalog</a>
        <a href="riwayat_saya.php" class="text-emerald-600 font-semibold">Riwayat Pesanan</a>
        <a href="about.php" class="hover:text-emerald-600 transition-colors">Tentang Kami</a>
        <a href="contact.php" class="hover:text-emerald-600 transition-colors">Kontak</a>
      </nav>
      <div class="flex items-center gap-3">
        <div class="hidden md:flex items-center gap-2 pl-4 border-l border-neutral-200">
          <div class="w-8 h-8 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-700 font-bold text-xs"><?php echo strtoupper(substr($userName, 0, 1)); ?></div>
          <span class="text-sm font-semibold text-neutral-800"><?php echo htmlspecialchars($userName); ?></span>
          <a href="api/auth_logout.php" class="p-1.5 text-neutral-400 hover:text-red-500 transition-colors" title="Keluar">
            <iconify-icon icon="solar:logout-2-bold" width="18"></iconify-icon>
          </a>
        </div>
      </div>
    </div>
  </header>

  <!-- ====== DETAIL MODAL ====== -->
  <div id="detailModal" class="fixed inset-0 z-[9990] hidden items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeDetailModal()"></div>
    <div class="modal-content relative bg-white rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden max-h-[85vh] overflow-y-auto">
      <div class="bg-gradient-to-r from-emerald-500 to-emerald-700 px-6 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center">
              <iconify-icon icon="solar:document-text-bold" width="20" class="text-white"></iconify-icon>
            </div>
            <h2 class="text-lg font-bold text-white">Detail Pesanan</h2>
          </div>
          <button onclick="closeDetailModal()" class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-white/70 hover:bg-white/20 hover:text-white transition-all">
            <iconify-icon icon="solar:close-circle-linear" width="20"></iconify-icon>
          </button>
        </div>
      </div>
      <div id="detailContent" class="p-6"></div>
    </div>
  </div>

  <main class="pt-24 pb-12 max-w-5xl mx-auto px-4">
    <h1 class="text-2xl font-extrabold text-neutral-900 tracking-tight mb-1">Riwayat Pesanan Saya</h1>
    <p class="text-sm text-neutral-500 mb-6">Daftar transaksi yang pernah kamu buat di FIRAJAYA.</p>

    <div class="bg-white rounded-2xl border border-neutral-200/60 overflow-hidden shadow-sm">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-neutral-100 bg-neutral-50/50">
              <th class="text-left px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">No Transaksi</th>
              <th class="text-left px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Tanggal</th>
              <th class="text-right px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Total</th>
              <th class="text-center px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Metode</th>
              <th class="text-center px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Status</th>
              <th class="text-center px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody id="ordersTableBody">
            <tr><td colspan="6" class="text-center py-12 text-neutral-400">Memuat data...</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div id="pagination" class="flex justify-center gap-2 mt-5"></div>
  </main>

  <script>
    const API_BASE = '';
    let currentPage = 1;

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

    function statusBadge(status) {
      const map = {
        lunas:   'bg-emerald-50 text-emerald-600',
        pending: 'bg-amber-50 text-amber-600',
        gagal:   'bg-red-50 text-red-500',
      };
      const cls = map[status] || 'bg-neutral-100 text-neutral-500';
      return `<span class="text-xs font-semibold px-2 py-1 rounded-lg ${cls}">${(status || '-').toUpperCase()}</span>`;
    }

    function metodeBadge(metode) {
      return metode === 'midtrans'
        ? '<span class="text-xs font-medium text-neutral-500">Midtrans</span>'
        : '<span class="text-xs font-medium text-neutral-500">Tunai</span>';
    }

    async function loadOrders(page = 1) {
      currentPage = page;
      const json = await apiFetch(`/api/get_my_orders.php?page=${page}`);
      const tbody = document.getElementById('ordersTableBody');

      if (!json.success || !json.data || json.data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-12 text-neutral-400">
          <div class="flex flex-col items-center">
            <iconify-icon icon="solar:bag-cross-linear" width="40" class="text-neutral-300 mb-2"></iconify-icon>
            <p class="font-semibold">Belum ada pesanan</p>
          </div>
        </td></tr>`;
        document.getElementById('pagination').innerHTML = '';
        return;
      }

      tbody.innerHTML = '';
      json.data.forEach(o => {
        const tanggal = new Date(o.tanggal).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        tbody.innerHTML += `
        <tr class="row-hover border-b border-neutral-50">
          <td class="px-4 py-3 font-semibold text-neutral-800">${o.no_transaksi}</td>
          <td class="px-4 py-3 text-neutral-500">${tanggal}</td>
          <td class="px-4 py-3 text-right font-bold text-neutral-800">Rp ${Number(o.total).toLocaleString('id-ID')}</td>
          <td class="px-4 py-3 text-center">${metodeBadge(o.metode_bayar)}</td>
          <td class="px-4 py-3 text-center">${statusBadge(o.status_bayar)}</td>
          <td class="px-4 py-3 text-center">
            <button onclick="viewDetail(${o.id_transaksi})" class="text-xs font-semibold text-emerald-600 hover:underline">Lihat Detail</button>
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
        html += `<button onclick="loadOrders(${i})" class="w-9 h-9 rounded-lg text-sm font-semibold ${i === page ? 'bg-emerald-500 text-white' : 'bg-white border border-neutral-200 text-neutral-600 hover:bg-neutral-50'}">${i}</button>`;
      }
      el.innerHTML = html;
    }

    async function viewDetail(id) {
      const json = await apiFetch(`/api/get_transaction_detail.php?id=${id}`);
      const content = document.getElementById('detailContent');

      if (!json.success) {
        content.innerHTML = `<p class="text-sm text-red-500">${json.message || 'Gagal memuat detail'}</p>`;
      } else {
        const t = json.data;
        const tanggal = new Date(t.tanggal).toLocaleString('id-ID');
        let rows = '';
        (t.items || []).forEach(it => {
          rows += `<div class="flex justify-between text-sm py-1.5 border-b border-neutral-50">
            <span class="text-neutral-600">${it.nama_produk} <span class="text-neutral-400">x${it.qty}</span></span>
            <span class="font-semibold text-neutral-800">Rp ${Number(it.subtotal).toLocaleString('id-ID')}</span>
          </div>`;
        });
        content.innerHTML = `
          <div class="text-sm text-neutral-500 mb-4">
            <p>No: <span class="font-semibold text-neutral-800">${t.no_transaksi}</span></p>
            <p>${tanggal}</p>
          </div>
          <div class="space-y-0 mb-4">${rows}</div>
          <div class="border-t border-dashed border-neutral-200 pt-3 space-y-1 text-sm">
            <div class="flex justify-between"><span class="text-neutral-500">Subtotal</span><span>Rp ${Number(t.subtotal).toLocaleString('id-ID')}</span></div>
            <div class="flex justify-between"><span class="text-neutral-500">Pajak</span><span>Rp ${Number(t.pajak_rupiah).toLocaleString('id-ID')}</span></div>
            <div class="flex justify-between font-bold text-base text-emerald-600"><span>Total</span><span>Rp ${Number(t.total).toLocaleString('id-ID')}</span></div>
          </div>
        `;
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

    loadOrders();
  </script>

  <!-- Chat Widget CS -->
  <div id="chatWidgetRoot"></div>
  <script src="js/chat-widget.js"></script>
</body>
</html>
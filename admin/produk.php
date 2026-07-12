<?php
require_once __DIR__ . '/_guard.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FIRAJAYA — Manajemen Produk</title>
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
  </style>
</head>

<div id="editProductModal"
class="fixed inset-0 hidden z-[9999] items-center justify-center bg-black/40">

<div class="bg-white rounded-2xl w-full max-w-xl p-6">

<h2 class="text-xl font-bold mb-5">
Edit Produk
</h2>

<form id="editProductForm">

<input type="hidden" name="id_produk" id="edit_id_produk">

<div class="mb-3">
<label>Nama Produk</label>
<input
id="edit_nama_produk"
name="nama_produk"
class="w-full border rounded-lg p-2"
required>
</div>

<div class="mb-3">
<label>Deskripsi</label>
<textarea
id="edit_deskripsi"
name="deskripsi"
class="w-full border rounded-lg p-2"></textarea>
</div>

<div class="grid grid-cols-2 gap-3">

<div>
<label>Harga</label>
<input
type="number"
id="edit_harga"
name="harga"
class="w-full border rounded-lg p-2">
</div>

<div>
<label>Stok</label>
<input
type="number"
id="edit_stok"
name="stok"
class="w-full border rounded-lg p-2">
</div>

</div>

<div class="grid grid-cols-2 gap-3 mt-3">

<div>

<label>Kategori</label>

<select
id="edit_kategori"
name="id_kategori"
class="w-full border rounded-lg p-2">
</select>

</div>

<div>

<label>Satuan</label>

<input
id="edit_satuan"
name="satuan"
class="w-full border rounded-lg p-2">

</div>

</div>

<div class="mt-3">

<label>Barcode</label>

<input
id="edit_barcode"
name="barcode"
class="w-full border rounded-lg p-2">

</div>

<div class="mt-3">

<label>Gambar Baru</label>

<input
type="file"
id="edit_gambar"
name="gambar"
accept="image/*">

</div>

<div class="flex justify-end gap-3 mt-6">

<button
type="button"
onclick="closeEditModal()"
class="px-5 py-2 bg-gray-200 rounded-lg">

Batal

</button>

<button
type="submit"
class="px-5 py-2 bg-emerald-500 text-white rounded-lg">

Simpan

</button>

</div>

</form>

</div>

</div>

<body class="bg-neutral-100 h-screen overflow-hidden">

  <!-- Toast Container -->
  <div id="toastContainer" class="fixed top-4 right-4 z-[9999] flex flex-col gap-2"></div>

  <!-- ====== MODALS (Sama seperti di index.php) ====== -->
  <!-- Add Product Modal -->
  <div id="addProductModal" class="fixed inset-0 z-[9990] hidden items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeAddProductModal()"></div>
    <div class="modal-content relative bg-white rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden max-h-[90vh] overflow-y-auto">
      <div class="bg-gradient-to-r from-emerald-500 to-emerald-700 px-6 py-5">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center"><iconify-icon icon="solar:add-square-bold" width="22" class="text-white"></iconify-icon></div>
            <div><h2 class="text-lg font-bold text-white">Tambah Produk Baru</h2><p class="text-xs text-white/70">Isi data produk sembako</p></div>
          </div>
          <button onclick="closeAddProductModal()" class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-white/70 hover:bg-white/20 hover:text-white transition-all"><iconify-icon icon="solar:close-circle-linear" width="20"></iconify-icon></button>
        </div>
      </div>
      <form id="addProductForm" onsubmit="handleAddProduct(event)" class="p-6 space-y-4">
        <div><label class="block text-sm font-semibold text-neutral-700 mb-1.5">Nama Produk <span class="text-red-400">*</span></label><div class="relative"><iconify-icon icon="solar:box-minimalistic-linear" width="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon><input type="text" name="nama_produk" required placeholder="Contoh: Beras Premium" class="w-full pl-11 pr-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm"></div></div>
        <div><label class="block text-sm font-semibold text-neutral-700 mb-1.5">Deskripsi <span class="text-red-400">*</span></label><textarea name="deskripsi" required rows="2" placeholder="Deskripsi singkat produk, akan tampil di toko pelanggan" class="w-full px-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm resize-none"></textarea></div>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block text-sm font-semibold text-neutral-700 mb-1.5">Harga (Rp) <span class="text-red-400">*</span></label><div class="relative"><span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-neutral-400">Rp</span><input type="number" name="harga" required min="1" placeholder="0" class="w-full pl-11 pr-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm"></div></div>
          <div><label class="block text-sm font-semibold text-neutral-700 mb-1.5">Stok <span class="text-red-400">*</span></label><div class="relative"><iconify-icon icon="solar:layers-minimalistic-linear" width="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon><input type="number" name="stok" required min="0" placeholder="0" class="w-full pl-11 pr-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm"></div></div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block text-sm font-semibold text-neutral-700 mb-1.5">Kategori <span class="text-red-400">*</span></label><div class="relative"><select name="id_kategori" id="formKategori" required class="w-full pl-4 pr-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm appearance-none"><option value="">— Pilih —</option></select></div></div>
          <div><label class="block text-sm font-semibold text-neutral-700 mb-1.5">Satuan</label><div class="relative"><iconify-icon icon="solar:ruler-linear" width="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon><select name="satuan" class="w-full pl-11 pr-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm appearance-none"><option value="pcs">Pcs</option><option value="kg">Kg</option><option value="liter">Liter</option><option value="bks">Bungkus</option><option value="btl">Botol</option><option value="dos">Dus</option></select></div></div>
        </div>
        <div><label class="block text-sm font-semibold text-neutral-700 mb-1.5">Barcode <span class="text-neutral-400 font-normal">(opsional)</span></label><div class="relative"><iconify-icon icon="solar:barcode-linear" width="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon><input type="text" name="barcode" placeholder="Contoh: 8991234" class="w-full pl-11 pr-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm"></div></div>
        <div><label class="block text-sm font-semibold text-neutral-700 mb-1.5">Gambar Produk <span class="text-neutral-400 font-normal">(maks 2MB)</span></label><div id="imgPreviewContainer" class="img-preview-container rounded-2xl bg-neutral-50 p-4 text-center cursor-pointer" onclick="document.getElementById('formGambar').click()"><input type="file" name="gambar" id="formGambar" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden" onchange="previewImage(this)"><div id="imgPlaceholder" class="flex flex-col items-center gap-2 py-4"><div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center"><iconify-icon icon="solar:camera-add-bold" width="28" class="text-emerald-400"></iconify-icon></div><p class="text-sm font-medium text-neutral-500">Klik untuk upload gambar</p><p class="text-xs text-neutral-400">JPG, PNG, WebP, GIF</p></div><img id="imgPreview" src="" alt="Preview" class="hidden max-h-40 mx-auto rounded-xl object-contain"></div></div>
        <button type="submit" id="addProductBtn" class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-emerald-700 text-white font-bold text-sm flex items-center justify-center gap-2 hover:shadow-lg hover:shadow-emerald-500/20 hover:-translate-y-0.5 active:translate-y-0 transition-all"><iconify-icon icon="solar:diskette-bold" width="18"></iconify-icon> Simpan Produk</button>
      </form>
    </div>
  </div>

  <!-- Delete Modal -->
  <div id="deleteConfirmModal" class="fixed inset-0 z-[9990] hidden items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
    <div class="modal-content relative bg-white rounded-3xl max-w-sm w-full shadow-2xl p-6 text-center">
      <div class="w-16 h-16 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-4"><iconify-icon icon="solar:trash-bin-minimalistic-bold" width="32" class="text-red-400"></iconify-icon></div>
      <h3 class="text-lg font-bold text-neutral-900 mb-1">Hapus Produk?</h3>
      <p class="text-sm text-neutral-500 mb-5">Produk <strong id="deleteProductName" class="text-neutral-700"></strong> akan dihapus permanen.</p>
      <div class="flex gap-3">
        <button onclick="closeDeleteModal()" class="flex-1 py-2.5 rounded-xl border border-neutral-200 text-neutral-600 font-semibold text-sm hover:bg-neutral-50 transition-all">Batal</button>
        <button onclick="executeDeleteProduct()" id="confirmDeleteBtn" class="flex-1 py-2.5 rounded-xl bg-red-500 text-white font-semibold text-sm hover:bg-red-600 transition-all">Ya, Hapus</button>
      </div>
    </div>
  </div>

  <!-- Kelola Kategori Modal -->
  <div id="kategoriModal" class="fixed inset-0 z-[9990] hidden items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeKategoriModal()"></div>
    <div class="modal-content relative bg-white rounded-3xl max-w-md w-full shadow-2xl overflow-hidden max-h-[85vh] flex flex-col">
      <div class="bg-gradient-to-r from-emerald-500 to-emerald-700 px-6 py-5 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center"><iconify-icon icon="solar:folder-bold" width="22" class="text-white"></iconify-icon></div>
          <h2 class="text-lg font-bold text-white">Kelola Kategori</h2>
        </div>
        <button onclick="closeKategoriModal()" class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-white/70 hover:bg-white/20 hover:text-white transition-all"><iconify-icon icon="solar:close-circle-linear" width="20"></iconify-icon></button>
      </div>

      <form onsubmit="submitAddKategori(event)" class="p-5 flex gap-2 border-b border-neutral-100 shrink-0">
        <input type="text" id="kategoriNamaInput" placeholder="Nama kategori baru..." required maxlength="50" class="flex-1 px-4 py-2.5 bg-neutral-50 rounded-xl border border-neutral-200 text-sm">
        <button type="submit" id="addKategoriBtn" class="px-4 py-2.5 bg-emerald-500 text-white font-semibold text-sm rounded-xl hover:bg-emerald-600 transition-all flex items-center gap-1.5 shrink-0">
          <iconify-icon icon="solar:add-circle-bold" width="18"></iconify-icon> Tambah
        </button>
      </form>

      <div id="kategoriListContainer" class="p-5 overflow-y-auto space-y-2">
        <p class="text-sm text-neutral-400 text-center py-4">Memuat...</p>
      </div>
    </div>
  </div>

  <!-- ====== MAIN LAYOUT ====== -->
  <div class="flex h-screen">

<aside class="w-[72px] bg-white border-r border-neutral-200/60 flex flex-col items-center py-4 gap-1 shrink-0 z-30">
  <div class="w-11 h-11 bg-emerald-500 rounded-xl flex items-center justify-center mb-6 shadow-lg shadow-emerald-500/25">
    <iconify-icon icon="solar:shop-bold" width="22" class="text-white"></iconify-icon>
  </div>
  <a href="dashboard.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400" title="Kasir">
    <iconify-icon icon="solar:calculator-minimalistic-bold" width="22"></iconify-icon>
  </a>
  <a href="produk.php" class="sidebar-icon active w-11 h-11 rounded-xl flex items-center justify-center" title="Produk">
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
          <span class="text-[10px] font-bold uppercase tracking-widest text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">Produk</span>
        </div>
        <div class="flex items-center gap-4">
          <div class="text-right hidden sm:block"><p class="text-xs text-neutral-400" id="currentDate"></p><p class="text-sm font-semibold text-neutral-700" id="currentTime"></p></div>
        </div>
      </nav>

      <!-- Main Content -->
      <main class="flex-1 overflow-y-auto p-5 space-y-4">
        
        <!-- Top Bar: Search & Add -->
        <div class="flex flex-wrap gap-3 items-center justify-between">
          <div class="relative flex-1 min-w-[250px] max-w-md">
            <iconify-icon icon="solar:magnifer-linear" width="18" class="absolute left-4 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
            <input type="text" id="searchInput" placeholder="Cari nama produk atau barcode..." class="w-full pl-11 pr-4 py-3 bg-white rounded-2xl border border-neutral-200/80 text-sm shadow-sm" oninput="onSearchDebounce()">
          </div>
          <button onclick="openKategoriModal()" class="flex items-center gap-2 px-5 py-3 bg-white text-neutral-600 font-semibold text-sm rounded-2xl border border-neutral-200/80 shadow-sm hover:border-emerald-300 hover:text-emerald-600 transition-all">
            <iconify-icon icon="solar:folder-bold" width="20"></iconify-icon> Kelola Kategori
          </button>
          <button onclick="openAddProductModal()" class="flex items-center gap-2 px-5 py-3 bg-emerald-500 text-white font-semibold text-sm rounded-2xl shadow-lg shadow-emerald-500/20 hover:bg-emerald-600 hover:shadow-emerald-500/30 hover:-translate-y-0.5 active:translate-y-0 transition-all">
            <iconify-icon icon="solar:add-square-bold" width="20"></iconify-icon> Tambah Produk
          </button>
        </div>

        <!-- Category Filter -->
        <div id="categoryContainer" class="flex gap-2 overflow-x-auto pb-1"></div>

        <!-- Product Table -->
        <div class="bg-white rounded-2xl border border-neutral-200/60 overflow-hidden shadow-sm">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-neutral-100 bg-neutral-50/50">
                  <th class="text-left px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider w-12"></th>
                  <th class="text-left px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Nama Produk</th>
                  <th class="text-left px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Kategori</th>
                  <th class="text-right px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Harga</th>
                  <th class="text-center px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Stok</th>
                  <th class="text-left px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Barcode</th>
                  <th class="text-center px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Status</th>
                  <th class="text-center px-4 py-3 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Aksi</th>
                </tr>
              </thead>
              <tbody id="productTableBody">
                <tr><td colspan="8" class="text-center py-12 text-neutral-400"><div class="flex flex-col items-center"><iconify-icon icon="solar:box-minimalistic-linear" width="40" class="text-neutral-300 mb-2"></iconify-icon><p class="font-semibold">Memuat data...</p></div></td></tr>
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
    let categories = [];
    let currentCategory = 'all';
    let searchTimer = null;

    // API Helper
    async function apiFetch(url, opts = {}) {
      try {
        const sep = url.includes('?') ? '&' : '?';
        const res = await fetch(API_BASE + url + sep + '_t=' + Date.now(), opts);
        // Selalu coba baca body JSON dulu, walau status bukan 200 — server
        // kita mengirim pesan error yang berguna di body (mis. 400/404/500),
        // jadi jangan dibuang sebelum dibaca.
        const json = await res.json().catch(() => null);
        if (json) return json;
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return { success: false, message: 'Response tidak valid' };
      } catch (e) { return { success: false, message: e.message }; }
    }

    // Init
    async function init() {
      categories = await fetchCategories();
      renderCategories();
      allProducts = await fetchProducts();
      renderTable(allProducts);
    }

    async function fetchCategories() {
      const json = await apiFetch('/api/get_categories.php');
      return json.success ? json.data : [];
    }

    async function fetchProducts() {
      let url = '/api/get_products.php?include_inactive=1&';
      if (currentCategory !== 'all') url += `kategori=${currentCategory}&`;
      const search = document.getElementById('searchInput')?.value.trim();
      if (search) url += `search=${encodeURIComponent(search)}&`;
      const json = await apiFetch(url);
      return json.success ? json.data : [];
    }

    // Render Kategori
    function renderCategories() {
      const c = document.getElementById('categoryContainer');
      c.innerHTML = `<button class="cat-btn active whitespace-nowrap px-4 py-2.5 rounded-xl text-sm font-semibold border border-transparent transition-all" data-category="all" onclick="setCategory('all',this)">Semua</button>`;
      categories.forEach(cat => {
        c.innerHTML += `<button class="cat-btn whitespace-nowrap px-4 py-2.5 rounded-xl text-sm font-semibold bg-white text-neutral-600 border border-neutral-200/80 hover:border-emerald-300 transition-all" data-category="${cat.id_kategori}" onclick="setCategory(${cat.id_kategori}, this)">${cat.nama_kategori}</button>`;
      });
    }

    // ===== Kelola Kategori =====
    function openKategoriModal() {
      document.getElementById('kategoriModal').classList.remove('hidden');
      document.getElementById('kategoriModal').classList.add('flex');
      renderKategoriList();
    }
    function closeKategoriModal() {
      document.getElementById('kategoriModal').classList.add('hidden');
      document.getElementById('kategoriModal').classList.remove('flex');
    }

    function renderKategoriList() {
      const box = document.getElementById('kategoriListContainer');
      if (!categories.length) {
        box.innerHTML = '<p class="text-sm text-neutral-400 text-center py-4">Belum ada kategori. Tambahkan lewat form di atas.</p>';
        return;
      }
      box.innerHTML = categories.map(cat => `
        <div class="flex items-center justify-between bg-neutral-50 rounded-xl px-4 py-3 border border-neutral-200/60">
          <span class="text-sm font-medium text-neutral-700">${cat.nama_kategori}</span>
          <button onclick="deleteKategori(${cat.id_kategori}, '${cat.nama_kategori.replace(/'/g, "\\'")}')" class="w-8 h-8 flex items-center justify-center rounded-lg text-neutral-400 hover:text-red-500 hover:bg-red-50 transition-all" title="Hapus kategori">
            <iconify-icon icon="solar:trash-bin-trash-bold" width="16"></iconify-icon>
          </button>
        </div>
      `).join('');
    }

    async function submitAddKategori(e) {
      e.preventDefault();
      const input = document.getElementById('kategoriNamaInput');
      const btn = document.getElementById('addKategoriBtn');
      const nama = input.value.trim();
      if (!nama) return;

      btn.disabled = true;
      const json = await apiFetch('/api/add_kategori.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nama_kategori: nama })
      });

      if (json.success) {
        showToast('Kategori berhasil ditambahkan', 'success');
        input.value = '';
        categories = await fetchCategories();
        renderCategories();
        renderKategoriList();
      } else {
        showToast(json.message || 'Gagal menambahkan kategori', 'error');
      }
      btn.disabled = false;
    }

    async function deleteKategori(id, nama) {
      if (!confirm(`Hapus kategori "${nama}"?`)) return;

      const json = await apiFetch('/api/delete_kategori.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_kategori: id })
      });

      if (json.success) {
        showToast('Kategori berhasil dihapus', 'success');
        categories = await fetchCategories();
        renderCategories();
        renderKategoriList();
      } else {
        showToast(json.message || 'Gagal menghapus kategori', 'error');
      }
    }

    async function setCategory(cat, btn) {
      currentCategory = cat;
      document.querySelectorAll('.cat-btn').forEach(b => { b.classList.remove('active'); b.classList.add('bg-white','text-neutral-600','border','border-neutral-200/80'); });
      btn.classList.add('active'); btn.classList.remove('bg-white','text-neutral-600','border','border-neutral-200/80');
      allProducts = await fetchProducts();
      renderTable(allProducts);
    }

    function onSearchDebounce() {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(async () => { allProducts = await fetchProducts(); renderTable(allProducts); }, 300);
    }

    // Render Table
    function renderTable(products) {
      const tbody = document.getElementById('productTableBody');
      if (!products || products.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-12 text-neutral-400"><div class="flex flex-col items-center"><iconify-icon icon="solar:box-minimalistic-linear" width="40" class="text-neutral-300 mb-2"></iconify-icon><p class="font-semibold">Produk tidak ditemukan</p></div></td></tr>`;
        return;
      }
      tbody.innerHTML = '';
      products.forEach(p => {
        const stokCls = p.stok <= 0 ? 'text-red-500 bg-red-50' : p.stok <= 10 ? 'text-amber-500 bg-amber-50' : 'text-emerald-500 bg-emerald-50';
        const img = p.gambar_url || '/assets/img/firajaya.png';
        const safeName = (p.nama_produk || '').replace(/'/g, "\\'");
        const isNonaktif = p.status === 'nonaktif';
        const rowCls = isNonaktif ? 'row-hover border-b border-neutral-50 opacity-50' : 'row-hover border-b border-neutral-50';
        const statusBadge = isNonaktif
          ? '<span class="text-xs font-semibold px-2 py-1 rounded-lg bg-neutral-100 text-neutral-500">Nonaktif</span>'
          : '<span class="text-xs font-semibold px-2 py-1 rounded-lg bg-emerald-50 text-emerald-600">Aktif</span>';
        const toggleBtn = isNonaktif
          ? `<button onclick="toggleProductStatus(${p.id_produk},'${safeName}','aktif')" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-500 flex items-center justify-center hover:bg-emerald-100 transition-all" title="Aktifkan kembali"><iconify-icon icon="solar:eye-bold" width="16"></iconify-icon></button>`
          : `<button onclick="toggleProductStatus(${p.id_produk},'${safeName}','nonaktif')" class="w-8 h-8 rounded-lg bg-neutral-100 text-neutral-400 flex items-center justify-center hover:bg-neutral-200 transition-all" title="Nonaktifkan"><iconify-icon icon="solar:eye-closed-linear" width="16"></iconify-icon></button>`;
        tbody.innerHTML += `
        <tr class="${rowCls}">
          <td class="px-4 py-3"><img src="${img}" alt="${p.nama_produk}" class="w-10 h-10 rounded-lg object-cover border border-neutral-100" onerror="this.src='/assets/img/firajaya.png'"></td>
          <td class="px-4 py-3"><p class="font-semibold text-neutral-800">${p.nama_produk}</p><p class="text-[11px] text-neutral-400">${p.satuan || 'pcs'}</p></td>
          <td class="px-4 py-3"><span class="text-xs font-semibold px-2 py-1 rounded-lg bg-neutral-100 text-neutral-600">${p.nama_kategori}</span></td>
          <td class="px-4 py-3 text-right font-bold text-neutral-800">Rp ${Number(p.harga).toLocaleString('id-ID')}</td>
          <td class="px-4 py-3 text-center"><span class="text-xs font-bold px-2 py-1 rounded-lg ${stokCls}">${p.stok}</span></td>
          <td class="px-4 py-3 text-neutral-500 text-xs">${p.barcode || '-'}</td>
          <td class="px-4 py-3 text-center">${statusBadge}</td>
          <td class="px-4 py-3 text-center">
            <div class="flex items-center justify-center gap-1">
              <button onclick="window.location.href='stok.php?restock=${p.id_produk}'" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-500 flex items-center justify-center hover:bg-amber-100 transition-all" title="Tambah Stok"><iconify-icon icon="solar:layers-minimalistic-bold" width="16"></iconify-icon></button>
<button onclick="openEditProduct(${p.id_produk})"
class="w-8 h-8 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center hover:bg-blue-100 transition-all"
title="Edit">
    <iconify-icon icon="solar:pen-bold" width="16"></iconify-icon>
</button>
              ${toggleBtn}
              <button onclick="confirmDeleteProduct(${p.id_produk},'${safeName}')" class="w-8 h-8 rounded-lg bg-red-50 text-red-400 flex items-center justify-center hover:bg-red-100 transition-all" title="Hapus Permanen"><iconify-icon icon="solar:trash-bin-minimalistic-bold" width="16"></iconify-icon></button>
            </div>
          </td>
        </tr>`;
      });
    }

    async function toggleProductStatus(id, nama, newStatus) {
      try {
        const res = await fetch(API_BASE + '/api/toggle_product_status.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id_produk: id, status: newStatus })
        });
        const result = await res.json();
        if (result.success) {
          showToast(result.message, 'success');
          allProducts = await fetchProducts();
          renderTable(allProducts);
        } else {
          showToast(result.message || 'Gagal mengubah status', 'error');
        }
      } catch (err) {
        showToast('Error: ' + err.message, 'error');
      }
    }

    // --- Modal & CRUD Functions (Sama seperti di app.js) ---
    function openAddProductModal() {
      const modal = document.getElementById('addProductModal'); modal.classList.remove('hidden'); modal.classList.add('flex');
      const sel = document.getElementById('formKategori'); sel.innerHTML = '<option value="">— Pilih Kategori —</option>';
      categories.forEach(c => { sel.innerHTML += `<option value="${c.id_kategori}">${c.nama_kategori}</option>`; });
      document.getElementById('addProductForm').reset(); document.getElementById('imgPreview').style.display = 'none'; document.getElementById('imgPreviewContainer').classList.remove('has-image');
    }
    function closeAddProductModal() { const modal = document.getElementById('addProductModal'); modal.classList.add('hidden'); modal.classList.remove('flex'); }
    function previewImage(input) { const preview = document.getElementById('imgPreview'), container = document.getElementById('imgPreviewContainer'); if(input.files&&input.files[0]){const file=input.files[0]; if(file.size>2*1024*1024){showToast('Maks 2MB','error');input.value='';return;} const reader=new FileReader(); reader.onload=(e)=>{preview.src=e.target.result;preview.style.display='block';container.classList.add('has-image');}; reader.readAsDataURL(file);} else {preview.style.display='none';container.classList.remove('has-image');} }
    
    async function handleAddProduct(e) {
      e.preventDefault(); const form = document.getElementById('addProductForm'), formData = new FormData(form), btn = document.getElementById('addProductBtn');
      btn.disabled = true; btn.innerHTML = 'Menyimpan...';
      try {
        const res = await fetch(API_BASE + '/api/add_product.php', { method: 'POST', body: formData });
        const result = await res.json();
        if (result.success) { showToast(result.message, 'success'); closeAddProductModal(); allProducts = await fetchProducts(); renderTable(allProducts); }
        else { showToast(result.message || 'Gagal', 'error'); }
      } catch (err) { showToast('Error: ' + err.message, 'error'); } 
      finally { btn.disabled = false; btn.innerHTML = '<iconify-icon icon="solar:diskette-bold" width="18"></iconify-icon> Simpan Produk'; }
    }

    let pendingDeleteId = null;
    function confirmDeleteProduct(id, name) { pendingDeleteId = id; document.getElementById('deleteProductName').textContent = name; const modal = document.getElementById('deleteConfirmModal'); modal.classList.remove('hidden'); modal.classList.add('flex'); }
    function closeDeleteModal() { const modal = document.getElementById('deleteConfirmModal'); modal.classList.add('hidden'); modal.classList.remove('flex'); pendingDeleteId = null; }
    async function executeDeleteProduct() {
      if (!pendingDeleteId) return; const btn = document.getElementById('confirmDeleteBtn'); btn.disabled = true; btn.innerHTML = 'Menghapus...';
      try {
        const result = await apiFetch(`/api/delete_product.php?id=${pendingDeleteId}`, { method: 'DELETE' });
        if (result.success) { showToast('Berhasil dihapus', 'success'); allProducts = await fetchProducts(); renderTable(allProducts); }
        else { showToast(result.message || 'Gagal', 'error'); }
      } catch (err) { showToast('Error', 'error'); } finally { btn.disabled = false; btn.innerHTML = 'Ya, Hapus'; closeDeleteModal(); }
    }

    function showToast(msg, type='success') { const c=document.getElementById('toastContainer'),t=document.createElement('div'),ic=type==='success'?'<iconify-icon icon="solar:check-circle-bold" width="18" class="text-emerald-500"></iconify-icon>':'<iconify-icon icon="solar:close-circle-bold" width="18" class="text-red-400"></iconify-icon>'; t.className='toast-enter glass-strong flex items-center gap-2 px-4 py-3 rounded-xl border border-neutral-200/60 shadow-lg text-sm font-medium text-neutral-700 min-w-[220px]'; t.innerHTML=`${ic} ${msg}`; c.appendChild(t); setTimeout(()=>{t.classList.remove('toast-enter');t.classList.add('toast-exit');setTimeout(()=>t.remove(),300);},2500); }
    function updateClock(){const n=new Date(),d=['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'],m=['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];const dd=document.getElementById('currentDate'),tt=document.getElementById('currentTime');if(dd)dd.textContent=`${d[n.getDay()]}, ${n.getDate()} ${m[n.getMonth()]}`;if(tt)tt.textContent=n.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit',second:'2-digit'})}
    
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { closeAddProductModal(); closeDeleteModal(); } });
    init(); updateClock(); setInterval(updateClock, 1000);

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
    
    
    function closeEditModal(){

document
.getElementById("editProductModal")
.classList.add("hidden");

document
.getElementById("editProductModal")
.classList.remove("flex");

}

function openEditProduct(id){

const p=allProducts.find(x=>x.id_produk==id);

if(!p){

showToast("Produk tidak ditemukan","error");

return;

}


document.getElementById("editProductForm").addEventListener("submit", async function(e){

    e.preventDefault();

    const form = document.getElementById("editProductForm");
    const formData = new FormData(form);

    try{

        const res = await fetch("/api/update_product.php",{
            method:"POST",
            body:formData
        });

        const result = await res.json();

        if(result.success){

            showToast("Produk berhasil diperbarui","success");

            closeEditModal();

            allProducts = await fetchProducts();

            renderTable(allProducts);

        }else{

            showToast(result.message,"error");

        }

    }catch(err){

        showToast(err.message,"error");

    }

});

const modal=document.getElementById("editProductModal");

modal.classList.remove("hidden");

modal.classList.add("flex");

document.getElementById("edit_id_produk").value=p.id_produk;

document.getElementById("edit_nama_produk").value=p.nama_produk;

document.getElementById("edit_deskripsi").value=p.deskripsi||"";

document.getElementById("edit_harga").value=p.harga;

document.getElementById("edit_stok").value=p.stok;

document.getElementById("edit_satuan").value=p.satuan||"pcs";

document.getElementById("edit_barcode").value=p.barcode||"";

const s=document.getElementById("edit_kategori");

s.innerHTML="";

categories.forEach(c=>{

const o=document.createElement("option");

o.value=c.id_kategori;

o.textContent=c.nama_kategori;

if(c.id_kategori==p.id_kategori){

o.selected=true;

}

s.appendChild(o);

});

}
  </script>
  
  
</body>
</html>
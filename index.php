<?php
session_start();
// Pelanggan TIDAK wajib login untuk melihat katalog & memesan (guest browsing).
// Kalau sedang login sebagai 'user' (pelanggan), tetap tampilkan info akunnya.
// Kalau login sebagai 'kasir', jangan sampai nyasar ke sini -> arahkan ke admin.


$isLoggedIn = isset($_SESSION['customer_id']);
$userName = $isLoggedIn ? $_SESSION['customer_name'] : null;
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FIRAJAYA — Belanja Sembako Online</title>
  <script src="https://cdn.tailwindcss.com/3.4.17"></script>
  <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
  <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Geist','Inter','sans-serif']}}}}</script>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700;800;900&display=swap">
  <style>
    body { font-family: 'Geist', 'Inter', sans-serif; }
    .add-btn { transition: all 0.2s ease; }
    .add-btn:hover { background: #059669; color: white; transform: scale(1.05); }
    .nav-glass { background: rgba(255,255,255,0.75); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border-bottom: 1px solid rgba(229,229,229,0.5); }
    .hero-gradient { background: radial-gradient(circle at top right, #d1fae5, #ecfdf5, transparent); }
    @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-20px); } }
    .anim-float { animation: float 6s ease-in-out infinite; }
  </style>
</head>
<body class="bg-white text-neutral-800 antialiased">

  <div id="toastContainer" class="fixed top-4 right-4 z-[9999] flex flex-col gap-2"></div>

  <!-- ====== NAVBAR ====== -->
  <header class="nav-glass fixed top-0 left-0 right-0 z-50">
    <div class="max-w-7xl mx-auto flex items-center justify-between px-6 py-3">
      <a href="#hero" class="flex items-center gap-2.5">
        <div class="w-9 h-9 bg-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-600/20">
          <iconify-icon icon="solar:shop-bold" width="20" class="text-white"></iconify-icon>
        </div>
        <span class="text-lg font-extrabold text-neutral-900 tracking-tight">FIRAJAYA</span>
      </a>
      <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-neutral-500">
        <a href="#menu" class="hover:text-emerald-600 transition-colors">Katalog</a>
        <a href="riwayat_saya.php" class="hover:text-emerald-600 transition-colors">Riwayat Pesanan</a>
        <a href="about.php" class="hover:text-emerald-600 transition-colors">Tentang Kami</a>
        <a href="contact.php" class="hover:text-emerald-600 transition-colors">Kontak</a>
      </nav>
      <div class="flex items-center gap-3">
        <button onclick="toggleCart()" class="relative p-2 text-neutral-700 hover:text-emerald-600 transition-colors">
          <iconify-icon icon="solar:cart-large-2-bold" width="24"></iconify-icon>
          <span id="cartBadge" class="hidden absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full text-[10px] text-white font-bold flex items-center justify-center">0</span>
        </button>
        <?php if ($isLoggedIn): ?>
        <div class="hidden md:flex items-center gap-2 pl-4 border-l border-neutral-200">
          <div class="w-8 h-8 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-700 font-bold text-xs"><?php echo strtoupper(substr($userName, 0, 1)); ?></div>
          <span class="text-sm font-semibold text-neutral-800"><?php echo htmlspecialchars($userName); ?></span>
          <a href="api/auth_logout.php" class="p-1.5 text-neutral-400 hover:text-red-500 transition-colors" title="Keluar">
            <iconify-icon icon="solar:logout-2-bold" width="18"></iconify-icon>
          </a>
        </div>
        <?php else: ?>
        <a href="login.php" class="hidden md:flex items-center gap-2 pl-4 ml-1 border-l border-neutral-200 text-sm font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">
          <iconify-icon icon="solar:login-3-bold" width="18"></iconify-icon> Masuk
        </a>
        <?php endif; ?>
        <button onclick="document.getElementById('mobMenu').classList.toggle('hidden')" class="md:hidden p-2"><iconify-icon icon="solar:hamburger-menu-linear" width="24"></iconify-icon></button>
      </div>
    </div>
    <div id="mobMenu" class="hidden md:hidden bg-white border-t px-6 pb-4 space-y-3 pt-2">
      <a href="#menu" class="block py-2 font-medium text-neutral-700">Katalog</a>
      <a href="riwayat_saya.php" class="block py-2 font-medium text-neutral-700">Riwayat Pesanan</a>
      <a href="about.php" class="block py-2 font-medium text-neutral-700">Tentang Kami</a>
      <a href="contact.php" class="block py-2 font-medium text-neutral-700">Kontak</a>
    </div>
  </header>

  <main>
    <!-- ====== HERO SECTION ====== -->
    <section id="hero" class="hero-gradient min-h-screen flex items-center pt-20">
      <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-12 items-center">
        <div class="space-y-6">
          <div class="inline-flex items-center gap-2 bg-emerald-100 text-emerald-700 px-4 py-1.5 rounded-full text-xs font-bold">
            <iconify-icon icon="solar:star-bold" width="14" class="text-yellow-500"></iconify-icon> Belanja Semudah Sentuhan Jari
          </div>
          <h1 class="text-5xl md:text-7xl font-black text-neutral-900 tracking-tighter leading-[1.1]">Kebutuhan Sembako, <span class="text-emerald-600">Dalam Genggaman.</span></h1>
          <p class="text-lg text-neutral-500 max-w-md leading-relaxed">Pilih kebutuhan harian Anda, pesan langsung dari HP, dan bayar mudah langsung di kasir.</p>
          <div class="flex gap-3">
            <a href="#menu" class="px-6 py-3.5 bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/20 hover:bg-emerald-700 hover:-translate-y-0.5 transition-all flex items-center gap-2">
              Belanja Sekarang <iconify-icon icon="solar:arrow-right-linear" width="18"></iconify-icon>
            </a>
            <a href="about.php" class="px-6 py-3.5 bg-white border border-neutral-200 text-neutral-700 font-bold rounded-xl hover:bg-neutral-50 hover:-translate-y-0.5 transition-all">
              Tentang Kami
            </a>
          </div>
        </div>
        
        <!-- HERO CART DINAMIS (GAMBAR ASLI) -->
        <div class="relative hidden md:flex justify-center items-center">
          <div class="absolute w-96 h-96 bg-emerald-200/50 rounded-full blur-3xl"></div>
          <div class="relative bg-white p-8 rounded-3xl shadow-2xl border border-neutral-100 anim-float w-full max-w-sm">
            <div class="space-y-3" id="heroCartItems">
               <div class="text-center py-8 text-neutral-300">
                 <iconify-icon icon="solar:cart-large-2-linear" width="40" class="mb-2"></iconify-icon>
                 <p class="text-sm">Belum ada item</p>
               </div>
            </div>
            <div class="mt-4 pt-4 border-t border-dashed border-neutral-200 flex justify-between font-bold"><span>Total</span><span class="text-xl text-emerald-600" id="heroCartTotal">Rp 0</span></div>
          </div>
        </div>

      </div>
    </section>

    <!-- ====== SOCIAL PROOF ====== -->
    <section class="py-16 bg-neutral-900 text-white">
      <div class="max-w-7xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
        <div><p class="text-4xl font-extrabold text-emerald-400 tracking-tight">1000+</p><p class="text-neutral-400 text-sm mt-1">Produk Tersedia</p></div>
        <div><p class="text-4xl font-extrabold text-emerald-400 tracking-tight">500+</p><p class="text-neutral-400 text-sm mt-1">Pelanggan Aktif</p></div>
        <div><p class="text-4xl font-extrabold text-emerald-400 tracking-tight">4.9★</p><p class="text-neutral-400 text-sm mt-1">Rating Kepuasan</p></div>
        <div><p class="text-4xl font-extrabold text-emerald-400 tracking-tight">< 2mnt</p><p class="text-neutral-400 text-sm mt-1">Waktu Pesan</p></div>
      </div>
    </section>

    <!-- ====== FEATURES ====== -->
    <section class="py-24 bg-neutral-50">
      <div class="max-w-7xl mx-auto px-6 text-center mb-16">
        <span class="text-xs font-bold uppercase tracking-widest text-emerald-600 mb-2 block">Kenapa Pilih Kami?</span>
        <h2 class="text-4xl font-extrabold text-neutral-900 tracking-tight">Belanja Lebih Mudah & Cepat</h2>
      </div>
      <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-3 gap-8">
        <div class="bg-white p-8 rounded-3xl border border-neutral-100 hover:shadow-xl transition-shadow">
          <div class="w-14 h-14 bg-emerald-100 rounded-2xl flex items-center justify-center mb-5"><iconify-icon icon="solar:smartphone-2-bold" width="28" class="text-emerald-600"></iconify-icon></div>
          <h3 class="text-xl font-bold mb-2">Pesan via HP</h3>
          <p class="text-neutral-500 leading-relaxed">Tidak perlu antri di kasir. Pilih produk langsung dari ponselmu kapan saja.</p>
        </div>
        <div class="bg-white p-8 rounded-3xl border border-neutral-100 hover:shadow-xl transition-shadow">
          <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center mb-5"><iconify-icon icon="solar:verified-check-bold" width="28" class="text-blue-600"></iconify-icon></div>
          <h3 class="text-xl font-bold mb-2">Kualitas Terjamin</h3>
          <p class="text-neutral-500 leading-relaxed">Produk segar dan berkualitas langsung dari supplier terpercaya setiap hari.</p>
        </div>
        <div class="bg-white p-8 rounded-3xl border border-neutral-100 hover:shadow-xl transition-shadow">
          <div class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center mb-5"><iconify-icon icon="solar:wallet-money-bold" width="28" class="text-amber-600"></iconify-icon></div>
          <h3 class="text-xl font-bold mb-2">Bayar Aman</h3>
          <p class="text-neutral-500 leading-relaxed">Pemabayaran aman dengan via online (VA, GoPay, QRIS) atau bayar tunai di kasir.</p>
        </div>
      </div>
    </section>

    <!-- ====== KATALOG PRODUK ====== -->
    <section id="menu" class="py-24 bg-white">
      <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-12">
          <span class="text-xs font-bold uppercase tracking-widest text-emerald-600 mb-2 block">Katalog</span>
          <h2 class="text-4xl font-extrabold text-neutral-900 tracking-tight mb-4">Pilih Kebutuhanmu</h2>
          <p class="text-neutral-500 max-w-lg mx-auto">Temukan kebutuhan pokok harianmu dengan harga terjangkau dan proses yang cepat.</p>
        </div>
        <div id="categoryContainer" class="flex gap-2 overflow-x-auto pb-4 mb-8 justify-center"></div>
        <div id="productGrid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-5"></div>
      </div>
    </section>

    <!-- ====== CTA SECTION ====== -->
    <section class="py-24 bg-gradient-to-br from-emerald-600 to-emerald-800 text-white relative overflow-hidden">
      <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
      <div class="max-w-4xl mx-auto px-6 text-center relative z-10">
        <h2 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-6">Siap Belanja Lebih Mudah?</h2>
        <p class="text-emerald-100 text-lg mb-8 max-w-xl mx-auto">Buat pesanan sekarang dan bayar aman langsung di halaman checkout.</p>
        <a href="#menu" class="inline-flex items-center gap-2 px-8 py-4 bg-white text-emerald-700 font-bold rounded-xl shadow-xl hover:-translate-y-1 transition-all">
          Mulai Belanja <iconify-icon icon="solar:arrow-right-linear" width="20"></iconify-icon>
        </a>
      </div>
    </section>
  </main>

  <!-- FOOTER -->
  <footer class="bg-neutral-950 text-neutral-400 pt-16 pb-8">
    <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-4 gap-10 mb-10">
      <div>
        <div class="flex items-center gap-2 mb-4"><div class="w-9 h-9 bg-emerald-600 rounded-xl flex items-center justify-center"><iconify-icon icon="solar:shop-bold" width="20" class="text-white"></iconify-icon></div><span class="text-lg font-extrabold text-white tracking-tight">FIRAJAYA</span></div>
        <p class="text-sm leading-relaxed">Sembako segar dan berkualitas untuk kebutuhan keluarga Indonesia.</p>
      </div>
      <div><h3 class="font-bold text-white mb-4">Navigasi</h3><ul class="space-y-2 text-sm"><li><a href="#menu" class="hover:text-emerald-400 transition-colors">Katalog</a></li><li><a href="riwayat_saya.php" class="hover:text-emerald-400 transition-colors">Riwayat Pesanan</a></li><li><a href="about.php" class="hover:text-emerald-400 transition-colors">Tentang Kami</a></li><li><a href="contact.php" class="hover:text-emerald-400 transition-colors">Kontak</a></li></ul></div>
      <div><h3 class="font-bold text-white mb-4">Kontak</h3><ul class="space-y-2 text-sm"><li>Jl. Contoh No.123</li><li>0812-3456-7890</li><li>info@firajaya.com</li></ul></div>
      <div><h3 class="font-bold text-white mb-4">Sosial Media</h3><div class="flex gap-3"><a href="#" class="w-10 h-10 bg-neutral-800 rounded-xl flex items-center justify-center hover:bg-emerald-600 transition-colors"><iconify-icon icon="mdi:instagram" width="20"></iconify-icon></a><a href="#" class="w-10 h-10 bg-neutral-800 rounded-xl flex items-center justify-center hover:bg-emerald-600 transition-colors"><iconify-icon icon="mdi:whatsapp" width="20"></iconify-icon></a></div></div>
    </div>
    <div class="border-t border-neutral-800 pt-6 max-w-7xl mx-auto px-6 text-center text-xs text-neutral-500">&copy; 2024 FIRAJAYA. All rights reserved.</div>
  </footer>

  <!-- CART PANEL -->
  <div id="cartOverlay" class="fixed inset-0 bg-black/40 z-50 hidden" onclick="toggleCart()"></div>
  <div id="cartPanel" class="fixed right-0 top-0 bottom-0 w-full max-w-sm bg-white shadow-2xl z-50 transform translate-x-full transition-transform duration-300 flex flex-col">
    <div class="p-5 border-b flex justify-between items-center"><h2 class="text-lg font-bold">Keranjang</h2><button onclick="toggleCart()" class="text-neutral-400 hover:text-neutral-700"><iconify-icon icon="solar:close-circle-bold" width="24"></iconify-icon></button></div>
    <div id="cartSelectAllBar" class="hidden px-4 py-2.5 border-b bg-neutral-50 flex items-center gap-2">
      <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this.checked)" class="w-[18px] h-[18px] rounded accent-emerald-600 cursor-pointer">
      <label for="selectAllCheckbox" class="text-sm font-medium text-neutral-600 cursor-pointer">Pilih Semua</label>
    </div>
    <div class="flex-1 overflow-y-auto p-4 space-y-3" id="cartItems"><div class="text-center py-10 text-neutral-400"><iconify-icon icon="solar:cart-large-2-linear" width="40" class="mb-2 opacity-30"></iconify-icon><p class="text-sm">Keranjang kosong</p></div></div>
    <div class="p-4 border-t space-y-3 bg-neutral-50">
       <div class="flex justify-between items-center text-sm text-neutral-500"><span id="selectedCountLabel">0 item dipilih</span></div>
       <div class="flex justify-between font-bold text-lg"><span>Total</span><span id="totalDisplay" class="text-emerald-600">Rp 0</span></div>
       <button onclick="submitOrder()" id="orderBtn" class="w-full py-3.5 rounded-xl bg-emerald-600 text-white font-bold hover:bg-emerald-700 transition-all disabled:opacity-50 shadow-lg shadow-emerald-600/20" disabled>Pesan Sekarang</button>
    </div>
  </div>

    <script>
    const API_BASE = ''; 
    let allProducts = []; 
    let categories = []; 
    let cart = []; 
    let currentCategory = 'all';

    async function apiFetch(u) {
      try {
        const r = await fetch(API_BASE + u + (u.includes('?') ? '&' : '?') + '_t=' + Date.now());
        const json = await r.json().catch(() => null);
        if (json) return json;
        if (!r.ok) throw new Error('HTTP Error');
        return { success: false, message: 'Response tidak valid' };
      } catch (e) {
        console.error('Fetch Error:', e);
        return { success: false };
      }
    }

    async function init() {
      try {
        const catRes = await apiFetch('/api/get_categories.php');
        if (catRes.success) categories = catRes.data || [];
        
        const prodRes = await apiFetch('/api/get_products.php');
        if (prodRes.success) allProducts = prodRes.data || [];
        
        renderCategories();
        renderProducts();
        renderHeroCart();
      } catch (e) {
        console.error('Init Error:', e);
      }
    }

    function renderCategories() {
      const c = document.getElementById('categoryContainer');
      if (!c) return;
      c.innerHTML = `<button class="cat-btn active px-5 py-2.5 rounded-full text-sm font-semibold bg-emerald-600 text-white shadow-sm" onclick="setCat('all', this)">Semua</button>`;
      categories.forEach(cat => {
        c.innerHTML += `<button class="cat-btn px-5 py-2.5 rounded-full text-sm font-semibold bg-neutral-100 text-neutral-700 hover:bg-neutral-200" onclick="setCat(${cat.id_kategori}, this)">${cat.nama_kategori}</button>`;
      });
    }

    function setCat(cat, btn) {
      currentCategory = cat;
      document.querySelectorAll('.cat-btn').forEach(b => {
        b.className = 'cat-btn px-5 py-2.5 rounded-full text-sm font-semibold bg-neutral-100 text-neutral-700 hover:bg-neutral-200';
      });
      btn.className = 'cat-btn active px-5 py-2.5 rounded-full text-sm font-semibold bg-emerald-600 text-white shadow-sm';
      renderProducts();
    }

    function escapeHtml(str) {
      const div = document.createElement('div');
      div.textContent = str || '';
      return div.innerHTML;
    }

    function renderProducts() {
      const grid = document.getElementById('productGrid');
      if (!grid) return;
      let prods = currentCategory === 'all' ? allProducts : allProducts.filter(p => p.id_kategori == currentCategory);
      
      if (prods.length === 0) {
        grid.innerHTML = `<div class="col-span-full text-center py-16 text-neutral-400">Produk belum tersedia</div>`;
        return;
      }

      grid.innerHTML = '';
      prods.forEach(p => {
        const inCart = cart.find(c => c.id_produk === p.id_produk);
        const imgUrl = p.gambar_url || '/assets/img/firajaya.png';
        
        grid.innerHTML += `
        <div class="bg-white rounded-2xl border border-neutral-100 hover:border-emerald-200 hover:shadow-lg transition-all overflow-hidden group">
          <div class="aspect-square bg-neutral-50 overflow-hidden relative">
            <img src="${imgUrl}" alt="${p.nama_produk}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" onerror="this.src='https://placehold.co/400x400/E5E5E5/A3A3A3?text=No+Image'">
            ${p.stok <= 0 ? '<div class="absolute inset-0 bg-white/70 flex items-center justify-center text-xs font-bold text-red-500">Habis</div>' : ''}
          </div>
          <div class="p-4">
            <h3 class="text-sm font-semibold text-neutral-800 truncate mb-1">${p.nama_produk}</h3>
            ${p.deskripsi ? `<p class="text-xs text-neutral-400 line-clamp-2 mb-2" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">${escapeHtml(p.deskripsi)}</p>` : ''}
            <div class="flex items-center justify-between mt-3">
              <span class="text-sm font-bold text-emerald-600">Rp ${Number(p.harga).toLocaleString('id-ID')}</span>
              ${inCart ? `
              <div class="flex items-center gap-2">
                <button onclick="updateCart(${p.id_produk}, -1)" class="w-7 h-7 rounded-lg bg-neutral-100 flex items-center justify-center font-bold text-neutral-600 hover:bg-neutral-200">-</button>
                <span class="text-sm font-bold w-4 text-center">${inCart.qty}</span>
                <button onclick="updateCart(${p.id_produk}, 1)" class="w-7 h-7 rounded-lg bg-emerald-500 text-white flex items-center justify-center font-bold">+</button>
              </div>
              ` : `
              <button onclick="addToCart(${p.id_produk})" class="add-btn w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center" ${p.stok <= 0 ? 'disabled' : ''}>
                <iconify-icon icon="solar:add-circle-bold" width="20"></iconify-icon>
              </button>
              `}
            </div>
          </div>
        </div>`;
      });
    }

    function addToCart(id) {
      const p = allProducts.find(x => x.id_produk === id);
      if (!p || p.stok <= 0) return;
      const existing = cart.find(c => c.id_produk === id);
      if (existing) {
        if (existing.qty < existing.max_stok) existing.qty += 1;
        else { showToast('Stok mencukupi', 'error'); return; }
      } else {
        cart.push({ id_produk: p.id_produk, nama_produk: p.nama_produk, harga: p.harga, qty: 1, max_stok: p.stok, checked: true, catatan: '' });
      }
      showToast(`${p.nama_produk} ditambahkan`);
      updateUI();
    }

    function updateCart(id, delta) {
      const item = cart.find(c => c.id_produk === id);
      if (!item) return;
      item.qty += delta;
      if (item.qty <= 0) cart = cart.filter(c => c.id_produk !== id);
      if (item.qty > item.max_stok) { item.qty = item.max_stok; showToast('Stok mencukupi', 'error'); }
      updateUI();
    }

    function removeFromCart(id) {
      cart = cart.filter(c => c.id_produk !== id);
      updateUI();
    }

    function toggleItemChecked(id, checked) {
      const item = cart.find(c => c.id_produk === id);
      if (item) item.checked = checked;
      updateUI();
    }

    function toggleSelectAll(checked) {
      cart.forEach(c => c.checked = checked);
      updateUI();
    }

    function updateCatatan(id, value) {
      const item = cart.find(c => c.id_produk === id);
      if (item) item.catatan = value;
      // Tidak perlu updateUI() penuh di sini supaya fokus input tidak hilang
      // saat user sedang mengetik (re-render akan reset focus).
    }

    function renderHeroCart() {
      const heroEl = document.getElementById('heroCartItems');
      const heroTotalEl = document.getElementById('heroCartTotal');
      if (!heroEl || !heroTotalEl) return;
      
      if (cart.length === 0) {
        const recommended = allProducts.slice(0, 2);
        if (recommended.length > 0) {
            heroEl.innerHTML = '';
            recommended.forEach(p => {
                const imgUrl = p.gambar_url || '/assets/img/firajaya.png';
                heroEl.innerHTML += `
                <div class="flex items-center gap-3 bg-neutral-50 p-2.5 rounded-xl border border-neutral-100">
                  <div class="w-10 h-10 rounded-lg overflow-hidden shrink-0 bg-neutral-100"><img src="${imgUrl}" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/100x100/E5E5E5/A3A3A3?text=No+Img'"></div>
                  <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-neutral-800 truncate mb-0.5">${p.nama_produk}</h3>
                    <p class="text-[11px] text-neutral-400 mb-1.5">/ ${p.satuan || 'pcs'}</p>
                  </div>
                  <p class="text-xs font-bold text-emerald-600 shrink-0">Rp ${Number(p.harga).toLocaleString('id-ID')}</p>
                </div>`;
            });
            heroTotalEl.innerHTML = `<span class="text-neutral-400 text-sm font-normal">Mulai belanja</span>`;
        } else {
            heroEl.innerHTML = `<div class="text-center py-8 text-neutral-300"><iconify-icon icon="solar:cart-large-2-linear" width="40" class="mb-2"></iconify-icon><p class="text-sm">Belum ada item</p></div>`;
            heroTotalEl.textContent = 'Rp 0';
        }
        return;
      }

      heroEl.innerHTML = '';
      cart.slice(0, 3).forEach(c => {
        const prodData = allProducts.find(p => p.id_produk === c.id_produk);
        const imgUrl = prodData ? (prodData.gambar_url || '/assets/img/firajaya.png') : '/assets/img/firajaya.png';
        
        heroEl.innerHTML += `
        <div class="flex items-center gap-3 bg-neutral-50 p-2.5 rounded-xl border border-neutral-100">
          <div class="w-10 h-10 rounded-lg overflow-hidden shrink-0 bg-neutral-100"><img src="${imgUrl}" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/100x100/E5E5E5/A3A3A3?text=No+Img'"></div>
          <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold truncate">${c.nama_produk}</p>
            <p class="text-[10px] text-neutral-400">${c.qty}x Rp ${Number(c.harga).toLocaleString('id-ID')}</p>
          </div>
          <p class="text-xs font-bold text-emerald-600 shrink-0">Rp ${(c.harga * c.qty).toLocaleString('id-ID')}</p>
        </div>`;
      });

      const total = cart.reduce((s, c) => s + (c.harga * c.qty), 0);
      heroTotalEl.textContent = `Rp ${total.toLocaleString('id-ID')}`;
    }

    function updateUI() {
      renderProducts();
      const badge = document.getElementById('cartBadge');
      const totalItems = cart.reduce((s, c) => s + c.qty, 0);
      if (totalItems > 0) { badge.classList.remove('hidden'); badge.textContent = totalItems; } else { badge.classList.add('hidden'); }

      const selectAllBar = document.getElementById('cartSelectAllBar');
      const cartEl = document.getElementById('cartItems');

      if (cart.length === 0) {
        selectAllBar.classList.add('hidden');
        cartEl.innerHTML = '<div class="text-center py-10 text-neutral-400"><iconify-icon icon="solar:cart-large-2-linear" width="40" class="mb-2 opacity-30"></iconify-icon><p class="text-sm">Keranjang kosong</p></div>';
      } else {
        selectAllBar.classList.remove('hidden');
        const allChecked = cart.every(c => c.checked);
        document.getElementById('selectAllCheckbox').checked = allChecked;

        cartEl.innerHTML = '';
        cart.forEach(c => {
          cartEl.innerHTML += `
          <div class="flex gap-2.5 items-start bg-neutral-50 p-3 rounded-xl border border-neutral-100">
            <input type="checkbox" ${c.checked ? 'checked' : ''} onchange="toggleItemChecked(${c.id_produk}, this.checked)" class="w-[18px] h-[18px] mt-1 rounded accent-emerald-600 cursor-pointer shrink-0">
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between gap-2">
                <h4 class="text-sm font-semibold truncate">${c.nama_produk}</h4>
                <button onclick="removeFromCart(${c.id_produk})" class="text-neutral-300 hover:text-red-500 shrink-0 transition-colors" title="Hapus">
                  <iconify-icon icon="solar:trash-bin-minimalistic-linear" width="17"></iconify-icon>
                </button>
              </div>
              <p class="text-xs text-neutral-500 mb-2">Rp ${Number(c.harga).toLocaleString('id-ID')} x ${c.qty}</p>
              <input type="text" value="${(c.catatan || '').replace(/"/g, '&quot;')}" placeholder="Tambah catatan (opsional)" oninput="updateCatatan(${c.id_produk}, this.value)" class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-neutral-200 outline-none focus:ring-1 focus:ring-emerald-400 mb-2 bg-white">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <button onclick="updateCart(${c.id_produk}, -1)" class="w-7 h-7 rounded-lg bg-white border flex items-center justify-center font-bold text-sm">-</button>
                  <span class="text-sm font-bold w-4 text-center">${c.qty}</span>
                  <button onclick="updateCart(${c.id_produk}, 1)" class="w-7 h-7 rounded-lg bg-white border flex items-center justify-center font-bold text-sm">+</button>
                </div>
                <p class="text-sm font-bold">Rp ${(c.harga * c.qty).toLocaleString('id-ID')}</p>
              </div>
            </div>
          </div>`;
        });
      }

      const selectedItems = cart.filter(c => c.checked);
      const total = selectedItems.reduce((s, c) => s + (c.harga * c.qty), 0);
      document.getElementById('selectedCountLabel').textContent = `${selectedItems.length} item dipilih`;
      document.getElementById('totalDisplay').textContent = `Rp ${total.toLocaleString('id-ID')}`;
      document.getElementById('orderBtn').disabled = selectedItems.length === 0;
      renderHeroCart();
    }

    function toggleCart() {
      document.getElementById('cartPanel').classList.toggle('translate-x-full');
      document.getElementById('cartOverlay').classList.toggle('hidden');
    }

    function submitOrder() {
      const selectedItems = cart.filter(c => c.checked);
      if (selectedItems.length === 0) return;
      localStorage.setItem('firajaya_cart', JSON.stringify(selectedItems));
      window.location.href = 'payment.php';
    }

    function showToast(msg, type = 'success') {
      const c = document.getElementById('toastContainer'), t = document.createElement('div');
      const ic = type === 'success' ? '<iconify-icon icon="solar:check-circle-bold" width="18" class="text-emerald-500"></iconify-icon>' : '<iconify-icon icon="solar:close-circle-bold" width="18" class="text-red-400"></iconify-icon>';
      t.className = 'toast-enter glass-strong flex items-center gap-2 px-4 py-3 rounded-xl border border-neutral-200/60 shadow-lg text-sm font-medium text-neutral-700 min-w-[220px]';
      t.innerHTML = `${ic} ${msg}`;
      c.appendChild(t);
      setTimeout(() => { t.classList.remove('toast-enter'); t.classList.add('toast-exit'); setTimeout(() => t.remove(), 300); }, 2500);
    }

    // Init ketika halaman dimuat
    init();
  </script>

  <!-- Chat Widget CS -->
  <div id="chatWidgetRoot"></div>
  <script src="js/chat-widget.js"></script>
</body>
</html>
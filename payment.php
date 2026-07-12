<?php
require_once __DIR__ . '/config/database.php';
session_start();

if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit;
}
$userName = $_SESSION['customer_name'];

// Ambil alamat default pelanggan jika pernah disimpan sebelumnya
$stmtAddr = $pdo->prepare("SELECT alamat_default, no_telp_default, latitude_default, longitude_default FROM tb_users WHERE id_user = :id");
$stmtAddr->execute([':id' => $_SESSION['customer_id']]);
$savedAddr = $stmtAddr->fetch() ?: ['alamat_default' => '', 'no_telp_default' => '', 'latitude_default' => null, 'longitude_default' => null];

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FIRAJAYA — Checkout</title>
  <script src="https://cdn.tailwindcss.com/3.4.17"></script>
  <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
  <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Geist','Inter','sans-serif']}}}}</script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700;800;900&display=swap">
  <!-- Leaflet (peta OpenStreetMap, gratis tanpa API key) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
  <style>
    body{font-family:'Geist','Inter',sans-serif;}
    .nav-glass{background:rgba(255,255,255,0.85);backdrop-filter:blur(16px);border-bottom:1px solid rgba(229,229,229,0.6);}
    .card{background:#fff;border:1px solid #f0f0f0;border-radius:16px;}
    .card-head{font-size:11px;font-weight:800;letter-spacing:.06em;color:#a3a3a3;text-transform:uppercase;}
    .toast-enter{animation:toastIn .25s ease forwards;}
    .toast-exit{animation:toastOut .25s ease forwards;}
    @keyframes toastIn{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}
    @keyframes toastOut{from{opacity:1}to{opacity:0;transform:translateX(20px)}}
    @keyframes spin{to{transform:rotate(360deg)}}
    .anim-spin{animation:spin 1s linear infinite;}
    .pay-option{transition:all .15s ease;}
    .metode-btn{border-color:#e5e5e5;color:#a3a3a3;background:#fff;transition:all .15s ease;}
    .metode-btn.active{border-color:#059669;color:#059669;background:#ecfdf5;}
  </style>
</head>
<body class="bg-neutral-50 text-neutral-800 antialiased pb-32">

  <div id="toastContainer" class="fixed top-4 right-4 z-[9999] flex flex-col gap-2"></div>

  <header class="nav-glass fixed top-0 left-0 right-0 z-50">
    <div class="max-w-3xl mx-auto flex items-center justify-between px-4 sm:px-6 py-3">
      <a href="index.php" class="flex items-center gap-2.5">
        <div class="w-9 h-9 bg-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-600/20">
          <iconify-icon icon="solar:shop-bold" width="20" class="text-white"></iconify-icon>
        </div>
        <span class="text-lg font-extrabold text-neutral-900 tracking-tight hidden sm:inline">FIRAJAYA</span>
      </a>
      <h1 class="text-sm font-bold text-neutral-800">Checkout</h1>
      <a href="shop.php" class="text-sm font-semibold text-emerald-600 hover:underline flex items-center gap-1">
        <iconify-icon icon="solar:arrow-left-linear" width="16"></iconify-icon>
        <span class="hidden sm:inline">Kembali</span>
      </a>
    </div>
  </header>

  <main class="pt-20 max-w-3xl mx-auto px-4 sm:px-6 space-y-3">

    <!-- ===== Alamat / Metode Pengambilan (ala Shopee) ===== -->
    <section class="card p-4">
      <div class="flex items-center gap-2 mb-3">
        <iconify-icon icon="solar:map-point-bold" width="16" class="text-neutral-400"></iconify-icon>
        <span class="card-head">Pengiriman</span>
      </div>

      <!-- Toggle Pickup vs Delivery -->
      <div class="grid grid-cols-2 gap-2 mb-3">
        <button type="button" onclick="setMetode('pickup')" id="btnPickup"
          class="metode-btn px-3 py-2.5 rounded-xl border-2 text-sm font-bold flex items-center justify-center gap-1.5">
          <iconify-icon icon="solar:shop-bold" width="16"></iconify-icon> Ambil di Toko
        </button>
        <button type="button" onclick="setMetode('delivery')" id="btnDelivery"
          class="metode-btn px-3 py-2.5 rounded-xl border-2 text-sm font-bold flex items-center justify-center gap-1.5">
          <iconify-icon icon="solar:delivery-bold" width="16"></iconify-icon> Dikirim
        </button>
      </div>

      <!-- Info pickup -->
      <div id="pickupInfo" class="flex items-start gap-3">
        <div class="w-9 h-9 rounded-full bg-emerald-50 flex items-center justify-center shrink-0">
          <iconify-icon icon="solar:shop-2-bold" width="18" class="text-emerald-600"></iconify-icon>
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-sm font-bold text-neutral-900"><?php echo htmlspecialchars($userName); ?></p>
          <p class="text-xs text-neutral-500 mt-0.5">Ambil sendiri di toko FIRAJAYA — pesanan diproses kasir setelah pembayaran dikonfirmasi.</p>
        </div>
      </div>

      <!-- Form alamat pengiriman -->
      <div id="deliveryForm" class="hidden space-y-2.5">
        <div>
          <label class="text-xs font-semibold text-neutral-500 block mb-1">Nama Penerima</label>
          <input id="namaPenerima" type="text" placeholder="Nama lengkap penerima"
                 value="<?php echo htmlspecialchars($userName); ?>"
                 class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-neutral-200 outline-none focus:ring-2 focus:ring-emerald-500 bg-neutral-50">
        </div>
        <div>
          <label class="text-xs font-semibold text-neutral-500 block mb-1">No. Telepon</label>
          <input id="noTelpPenerima" type="tel" placeholder="08xxxxxxxxxx"
                 value="<?php echo htmlspecialchars($savedAddr['no_telp_default'] ?? ''); ?>"
                 class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-neutral-200 outline-none focus:ring-2 focus:ring-emerald-500 bg-neutral-50">
        </div>
        <div>
          <div class="flex items-center justify-between mb-1">
            <label class="text-xs font-semibold text-neutral-500">Alamat Lengkap</label>
            <button type="button" onclick="useMyLocation()" id="btnGps"
              class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
              <iconify-icon icon="solar:point-on-map-bold" width="13"></iconify-icon> Gunakan Lokasi Saat Ini
            </button>
          </div>
          <textarea id="alamatLengkap" rows="3" placeholder="Ketik alamat manual, atau pakai tombol GPS di atas"
                    class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-neutral-200 outline-none focus:ring-2 focus:ring-emerald-500 bg-neutral-50 resize-none"><?php echo htmlspecialchars($savedAddr['alamat_default'] ?? ''); ?></textarea>
          <p id="gpsStatus" class="text-[11px] text-neutral-400 mt-1 hidden"></p>
        </div>

        <div>
          <div class="flex items-center justify-between mb-1">
            <label class="text-xs font-semibold text-neutral-500">Titik Lokasi di Peta</label>
            <span class="text-[11px] text-neutral-400">Klik / geser pin untuk sesuaikan</span>
          </div>
          <div id="mapContainer" class="w-full h-52 rounded-xl border border-neutral-200 overflow-hidden bg-neutral-100"></div>
        </div>
        <label class="flex items-center gap-2 text-xs text-neutral-500 pt-1">
          <input id="simpanAlamat" type="checkbox" class="rounded border-neutral-300 text-emerald-600 focus:ring-emerald-500">
          Simpan sebagai alamat utama saya
        </label>
      </div>
    </section>


    <!-- ===== Daftar Produk ===== -->
    <section class="card overflow-hidden">
      <div class="px-4 pt-4 pb-2 flex items-center gap-2">
        <iconify-icon icon="solar:shop-2-bold" width="16" class="text-neutral-400"></iconify-icon>
        <span class="card-head">Pesanan Anda</span>
      </div>
      <div id="orderList" class="divide-y divide-neutral-100"></div>
      <div id="orderEmpty" class="hidden px-4 py-10 text-center text-neutral-300">
        <iconify-icon icon="solar:cart-large-2-linear" width="36" class="mb-2"></iconify-icon>
        <p class="text-sm">Keranjang kosong</p>
      </div>
    </section>

    <!-- ===== Catatan untuk kasir ===== -->
    <section class="card p-4">
      <label class="card-head block mb-2">Catatan untuk Kasir</label>
      <input id="catatanUmum" type="text" maxlength="150" placeholder="Contoh: tolong bungkus terpisah (opsional)"
             class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-neutral-200 outline-none focus:ring-2 focus:ring-emerald-500 bg-neutral-50">
    </section>

    <!-- ===== Metode Pembayaran ===== -->
    <section class="card p-4">
      <span class="card-head block mb-3">Metode Pembayaran</span>
      <div class="pay-option border-2 border-emerald-500 bg-emerald-50 rounded-xl p-3.5 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-white border border-emerald-200 flex items-center justify-center shrink-0">
          <iconify-icon icon="solar:shield-check-bold" width="18" class="text-emerald-600"></iconify-icon>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-bold text-neutral-900">Midtrans Payment Gateway</p>
          <p class="text-[11px] text-neutral-500">QRIS · GoPay · ShopeePay · Virtual Account · Kartu Kredit</p>
        </div>
        <iconify-icon icon="solar:check-circle-bold" width="20" class="text-emerald-600 shrink-0"></iconify-icon>
      </div>
      <p class="text-[11px] text-neutral-400 mt-2.5 px-1">Anda akan memilih channel pembayaran spesifik (QRIS, e-wallet, dll) di jendela pembayaran Midtrans setelah menekan tombol bayar.</p>
    </section>

    <!-- ===== Rincian Pembayaran ===== -->
    <section class="card p-4">
      <span class="card-head block mb-3">Rincian Pembayaran</span>
      <div class="space-y-2 text-sm">
        <div class="flex justify-between text-neutral-500">
          <span>Subtotal Produk</span>
          <span id="subtotalDisplay" class="text-neutral-700 font-medium">Rp 0</span>
        </div>
        <div class="flex justify-between text-neutral-500">
          <span>Biaya Layanan</span>
          <span class="text-emerald-600 font-medium">Gratis</span>
        </div>
        <div class="border-t border-dashed border-neutral-200 pt-2.5 mt-1 flex justify-between items-center">
          <span class="text-sm font-bold text-neutral-800">Total Pembayaran</span>
          <span class="text-lg font-extrabold text-emerald-600" id="totalDisplay">Rp 0</span>
        </div>
      </div>
    </section>
  </main>

  <!-- ===== Sticky bottom bar ala Shopee ===== -->
  <div class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-neutral-200 shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-4">
      <div class="min-w-0">
        <p class="text-[11px] text-neutral-400 leading-tight">Total Pembayaran</p>
        <p class="text-lg font-extrabold text-neutral-900 leading-tight" id="totalDisplayBar">Rp 0</p>
      </div>
      <button onclick="payWithMidtrans()" id="payBtn"
        class="shrink-0 px-6 sm:px-10 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-2xl shadow-lg shadow-emerald-600/25 transition-all active:scale-95 flex items-center justify-center gap-2">
        <iconify-icon icon="solar:shield-check-bold" width="18"></iconify-icon> Bayar Sekarang
      </button>
    </div>
  </div>

  <script>
    let cart = JSON.parse(localStorage.getItem('firajaya_cart') || '[]');
    let metodePengiriman = 'pickup';
    let gpsLat = <?php echo $savedAddr['latitude_default'] !== null ? (float)$savedAddr['latitude_default'] : 'null'; ?>;
    let gpsLng = <?php echo $savedAddr['longitude_default'] !== null ? (float)$savedAddr['longitude_default'] : 'null'; ?>;

    function setMetode(m) {
      metodePengiriman = m;
      document.getElementById('btnPickup').classList.toggle('active', m === 'pickup');
      document.getElementById('btnDelivery').classList.toggle('active', m === 'delivery');
      document.getElementById('pickupInfo').classList.toggle('hidden', m !== 'pickup');
      document.getElementById('deliveryForm').classList.toggle('hidden', m !== 'delivery');
      if (m === 'delivery') {
        // Leaflet perlu container sudah tampil (bukan display:none) baru bisa di-init dengan ukuran benar
        setTimeout(() => {
          initMap();
          map.invalidateSize();
        }, 50);
      }
    }
    setMetode('pickup');

    function useMyLocation() {
      if (!navigator.geolocation) {
        showToast('Perangkat/browser tidak mendukung GPS', 'error');
        return;
      }
      const btn = document.getElementById('btnGps');
      const status = document.getElementById('gpsStatus');
      btn.innerHTML = '<iconify-icon icon="solar:refresh-bold" width="13" class="anim-spin"></iconify-icon> Mencari lokasi...';
      status.classList.remove('hidden');
      status.textContent = 'Meminta izin akses lokasi...';

      navigator.geolocation.getCurrentPosition(async (pos) => {
        gpsLat = pos.coords.latitude;
        gpsLng = pos.coords.longitude;
        status.textContent = 'Lokasi ditemukan, mengambil nama alamat...';

        // Geser pin & pusatkan peta ke lokasi GPS
        if (map && marker) {
          marker.setLatLng([gpsLat, gpsLng]);
          map.setView([gpsLat, gpsLng], 16);
        }

        try {
          const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${gpsLat}&lon=${gpsLng}&zoom=18&addressdetails=1`, {
            headers: { 'Accept-Language': 'id' }
          });
          const data = await res.json();
          const alamatOtomatis = data.display_name || `${gpsLat}, ${gpsLng}`;
          // Isi otomatis, tapi tetap bisa diedit manual oleh pengguna
          document.getElementById('alamatLengkap').value = alamatOtomatis;
          status.textContent = '📍 Alamat terisi dari GPS — silakan sunting jika kurang tepat.';
        } catch (e) {
          document.getElementById('alamatLengkap').value = `Koordinat: ${gpsLat}, ${gpsLng} (isi detail alamat manual)`;
          status.textContent = 'Gagal menerjemahkan lokasi jadi teks alamat, tapi koordinat GPS tetap tersimpan.';
        }
        btn.innerHTML = '<iconify-icon icon="solar:point-on-map-bold" width="13"></iconify-icon> Perbarui Lokasi';
      }, (err) => {
        status.textContent = err.code === 1
          ? 'Izin lokasi ditolak. Anda tetap bisa mengetik alamat manual di bawah.'
          : 'Gagal mendapatkan lokasi. Anda tetap bisa mengetik alamat manual di bawah.';
        btn.innerHTML = '<iconify-icon icon="solar:point-on-map-bold" width="13"></iconify-icon> Gunakan Lokasi Saat Ini';
      }, { enableHighAccuracy: true, timeout: 10000 });
    }

    let map, marker;
    const DEFAULT_CENTER = [-6.2088, 106.8456]; // Jakarta, fallback kalau belum ada lokasi

    function initMap() {
      if (map) return; // sudah pernah di-init, jangan buat ulang
      const startCenter = (gpsLat && gpsLng) ? [gpsLat, gpsLng] : DEFAULT_CENTER;
      map = L.map('mapContainer').setView(startCenter, gpsLat ? 16 : 11);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
      }).addTo(map);

      marker = L.marker(startCenter, { draggable: true }).addTo(map);

      marker.on('dragend', () => {
        const pos = marker.getLatLng();
        setLocationFromMap(pos.lat, pos.lng);
      });
      map.on('click', (e) => {
        marker.setLatLng(e.latlng);
        setLocationFromMap(e.latlng.lat, e.latlng.lng);
      });

      if (gpsLat && gpsLng) marker.setLatLng([gpsLat, gpsLng]);
    }

    async function setLocationFromMap(lat, lng) {
      gpsLat = lat; gpsLng = lng;
      const status = document.getElementById('gpsStatus');
      status.classList.remove('hidden');
      status.textContent = 'Mengambil nama alamat dari titik peta...';
      try {
        const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
          headers: { 'Accept-Language': 'id' }
        });
        const data = await res.json();
        document.getElementById('alamatLengkap').value = data.display_name || `${lat}, ${lng}`;
        status.textContent = '📍 Alamat diperbarui dari peta — silakan sunting jika kurang tepat.';
      } catch (e) {
        status.textContent = 'Titik lokasi tersimpan, tapi gagal menerjemahkan jadi teks alamat. Isi manual jika perlu.';
      }
    }

    function rupiah(n){ return `Rp ${Number(n).toLocaleString('id-ID')}`; }

    function renderOrder() {
      const listEl = document.getElementById('orderList');
      const emptyEl = document.getElementById('orderEmpty');

      if (cart.length === 0) {
        listEl.classList.add('hidden');
        emptyEl.classList.remove('hidden');
        const btn = document.getElementById('payBtn');
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
        return;
      }

      let total = 0;
      listEl.innerHTML = cart.map(c => {
        const sub = c.harga * c.qty;
        total += sub;
        const imgUrl = c.gambar_url || '/assets/img/firajaya.png';
        const catatanHtml = c.catatan
          ? `<p class="text-[11px] text-neutral-400 italic mt-1 flex items-center gap-1"><iconify-icon icon="solar:chat-round-dots-linear" width="12"></iconify-icon>${c.catatan}</p>`
          : '';
        return `
          <div class="px-4 py-3 flex gap-3 items-center">
            <img src="${imgUrl}" onerror="this.src='/assets/img/firajaya.png'" class="w-14 h-14 rounded-xl object-cover bg-neutral-100 border border-neutral-100 shrink-0">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-neutral-800 truncate">${c.nama_produk}</p>
              <p class="text-xs text-neutral-400 mt-0.5">${rupiah(c.harga)} / ${c.satuan || 'pcs'} &nbsp;·&nbsp; x${c.qty}</p>
              ${catatanHtml}
            </div>
            <p class="text-sm font-bold text-neutral-800 shrink-0">${rupiah(sub)}</p>
          </div>`;
      }).join('');

      document.getElementById('subtotalDisplay').textContent = rupiah(total);
      document.getElementById('totalDisplay').textContent = rupiah(total);
      document.getElementById('totalDisplayBar').textContent = rupiah(total);
    }

    // Lengkapi cart dengan gambar produk terbaru (harga & stok tetap divalidasi ulang di server saat bayar)
    async function enrichCartImages() {
      if (cart.length === 0) { renderOrder(); return; }
      try {
        const res = await fetch('/api/get_products.php');
        const data = await res.json();
        if (data.success) {
          const byId = {};
          data.data.forEach(p => byId[p.id_produk] = p);
          cart.forEach(c => {
            const p = byId[c.id_produk];
            if (p) { c.gambar_url = p.gambar_url; c.satuan = p.satuan; }
          });
        }
      } catch (e) { /* gagal ambil gambar bukan hal fatal, tetap lanjut render */ }
      renderOrder();
    }
    enrichCartImages();

    async function payWithMidtrans() {
      if (cart.length === 0) { showToast('Keranjang kosong!', 'error'); return; }

      const catatan = document.getElementById('catatanUmum').value.trim();
      const namaPenerima    = document.getElementById('namaPenerima').value.trim();
      const noTelpPenerima  = document.getElementById('noTelpPenerima').value.trim();
      const alamatLengkap   = document.getElementById('alamatLengkap').value.trim();
      const simpanAlamat    = document.getElementById('simpanAlamat').checked;

      if (metodePengiriman === 'delivery' && (!namaPenerima || !noTelpPenerima || !alamatLengkap)) {
        showToast('Lengkapi nama, no. telepon, dan alamat pengiriman.', 'error');
        return;
      }

      const btn = document.getElementById('payBtn');
      btn.disabled = true;
      btn.innerHTML = '<iconify-icon icon="solar:refresh-bold" width="18" class="anim-spin"></iconify-icon> Memproses...';

      try {
        const res = await fetch('/api/midtrans_get_token.php', {
          method: 'POST', headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({
            items: cart,
            catatan,
            metode_pengiriman: metodePengiriman,
            nama_penerima: namaPenerima,
            no_telp_penerima: noTelpPenerima,
            alamat_pengiriman: alamatLengkap,
            latitude: gpsLat,
            longitude: gpsLng,
            simpan_alamat: simpanAlamat
          })
        });
        const data = await res.json();

        if (data.success && data.token) {
          window.snap.pay(data.token, {
            onSuccess: function(result){
              localStorage.removeItem('firajaya_cart');
              window.location.href = 'index.php?status=success_midtrans';
            },
            onPending: function(result){
              localStorage.removeItem('firajaya_cart');
              window.location.href = 'index.php?status=pending_midtrans';
            },
            onError: function(result){
              showToast('Pembayaran gagal, coba lagi.', 'error');
              resetBtn();
            },
            onClose: function(){
              showToast('Pembayaran dibatalkan.', 'error');
              resetBtn();
            }
          });
        } else {
          showToast(data.message || 'Gagal mendapatkan token', 'error');
          resetBtn();
        }
      } catch(e) {
        showToast('Gagal menghubungi server', 'error');
        resetBtn();
      }
    }

    function resetBtn(){
      const btn = document.getElementById('payBtn');
      btn.disabled = false;
      btn.innerHTML = '<iconify-icon icon="solar:shield-check-bold" width="18"></iconify-icon> Bayar Sekarang';
    }

    function showToast(msg,type='success'){
      const c=document.getElementById('toastContainer'),t=document.createElement('div');
      const ic=type==='success'?'<iconify-icon icon="solar:check-circle-bold" width="18" class="text-emerald-500"></iconify-icon>':'<iconify-icon icon="solar:close-circle-bold" width="18" class="text-red-400"></iconify-icon>';
      t.className='toast-enter bg-white flex items-center gap-2 px-4 py-3 rounded-xl border border-neutral-200 shadow-lg text-sm font-medium text-neutral-700 min-w-[220px]';
      t.innerHTML=`${ic} ${msg}`;
      c.appendChild(t);
      setTimeout(()=>{t.classList.remove('toast-enter');t.classList.add('toast-exit');setTimeout(()=>t.remove(),300);},2500);
    }
  </script>

  <!-- Midtrans Snap JS — SENGAJA diletakkan paling akhir. Snap.js menyisipkan
       Content-Security-Policy ketat ke halaman begitu dia jalan, yang akan
       memblokir script APA PUN (termasuk Tailwind, Leaflet, script kita)
       yang dimuat SETELAHNYA. Dengan naruh di akhir, semua script kita sudah
       selesai jalan duluan sebelum CSP itu aktif. -->
  <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?php echo htmlspecialchars(MIDTRANS_CLIENT_KEY); ?>"></script>
</body>
</html>
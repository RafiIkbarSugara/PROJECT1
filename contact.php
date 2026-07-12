<?php
session_start();
if (!isset($_SESSION['customer_id']) || $_SESSION['role'] !== 'user') { header('Location: login.php'); exit; }
 $userName = $_SESSION['admin_name'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FIRAJAYA — Kontak</title>
  <script src="https://cdn.tailwindcss.com/3.4.17"></script>
  <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
  <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Geist','Inter','sans-serif']}}}}</script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700;800;900&display=swap">
  <style>body{font-family:'Geist','Inter',sans-serif;}.nav-glass{background:rgba(255,255,255,0.75);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border-bottom:1px solid rgba(229,229,229,0.5);}</style>
</head>
<body class="bg-white text-neutral-800 antialiased">
    <header class="nav-glass fixed top-0 left-0 right-0 z-50">
    <div class="max-w-7xl mx-auto flex items-center justify-between px-6 py-3">
      <a href="index.php" class="flex items-center gap-2.5">
        <div class="w-9 h-9 bg-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-600/20">
          <iconify-icon icon="solar:shop-bold" width="20" class="text-white"></iconify-icon>
        </div>
        <span class="text-lg font-extrabold text-neutral-900 tracking-tight">FIRAJAYA</span>
      </a>
      <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-neutral-500">
        <a href="index.php" class="hover:text-emerald-600 transition-colors">Katalog</a>
        <a href="riwayat_saya.php" class="hover:text-emerald-600 transition-colors">Riwayat Pesanan</a>
        <a href="about.php" class="hover:text-emerald-600 transition-colors">Tentang Kami</a>
        <a href="contact.php" class="text-emerald-600 font-semibold">Kontak</a>
      </nav>
      <div class="flex items-center gap-3">
        <a href="index.php" class="relative p-2 text-neutral-700 hover:text-emerald-600 transition-colors"><iconify-icon icon="solar:cart-large-2-bold" width="24"></iconify-icon></a>
        
        <!-- Profil & Tombol Logout -->
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

  <section class="pt-32 pb-20 bg-neutral-50">
    <div class="max-w-7xl mx-auto px-6">
      <div class="text-center mb-16">
        <span class="text-xs font-bold uppercase tracking-widest text-emerald-600 mb-3 block">Hubungi Kami</span>
        <h1 class="text-5xl md:text-6xl font-extrabold text-neutral-900 tracking-tighter leading-tight mb-4">Ada Pertanyaan?</h1>
        <p class="text-lg text-neutral-500 max-w-lg mx-auto">Tim kami siap membantu Anda. Kirim pesan atau kunjungi toko kami langsung.</p>
      </div>
      
      <div class="grid md:grid-cols-3 gap-6 mb-16">
        <div class="bg-white p-8 rounded-3xl border border-neutral-100 text-center hover:shadow-xl transition-shadow">
          <div class="w-14 h-14 bg-emerald-100 rounded-2xl flex items-center justify-center mx-auto mb-5"><iconify-icon icon="solar:map-point-bold" width="28" class="text-emerald-600"></iconify-icon></div>
          <h3 class="font-bold text-lg mb-2">Alamat</h3>
          <p class="text-neutral-500 text-sm">Kampung Baru Desa Pangarengan RT16 RW03 No 54 Kecamatan Rajeg Kabupaten Tangerang Provinsi Banten 15540</p>
        </div>
        <div class="bg-white p-8 rounded-3xl border border-neutral-100 text-center hover:shadow-xl transition-shadow">
          <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-5"><iconify-icon icon="solar:phone-bold" width="28" class="text-blue-600"></iconify-icon></div>
          <h3 class="font-bold text-lg mb-2">Telepon / WA</h3>
          <p class="text-neutral-500 text-sm">+62 858-9107-1360</p>
        </div>
        <div class="bg-white p-8 rounded-3xl border border-neutral-100 text-center hover:shadow-xl transition-shadow">
          <div class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-5"><iconify-icon icon="solar:clock-circle-bold" width="28" class="text-amber-600"></iconify-icon></div>
          <h3 class="font-bold text-lg mb-2">Jam Operasional</h3>
          <p class="text-neutral-500 text-sm">Setiap Hari: 06:00 - 22:00 WIB</p>
        </div>
      </div>

      <div class="max-w-2xl mx-auto bg-white p-8 md:p-10 rounded-3xl border border-neutral-100 shadow-sm">
        <h2 class="text-2xl font-bold mb-6 text-center">Kirim Pesan</h2>
        <div id="formFeedback" class="hidden mb-4 px-4 py-3 rounded-xl text-sm font-medium"></div>
        <form id="contactForm" class="space-y-5">
          <div class="grid md:grid-cols-2 gap-4">
            <input type="text" id="inputNama" placeholder="Nama Lengkap" class="w-full px-4 py-3 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none bg-neutral-50" required>
            <input type="email" id="inputEmail" placeholder="Email" class="w-full px-4 py-3 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none bg-neutral-50" required>
          </div>
          <input type="text" id="inputSubjek" placeholder="Subjek" class="w-full px-4 py-3 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none bg-neutral-50" required>
          <textarea id="inputPesan" rows="5" placeholder="Tulis pesan Anda di sini..." class="w-full px-4 py-3 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none resize-none bg-neutral-50" required></textarea>
          <button type="submit" id="btnSendMessage" class="w-full py-3.5 rounded-xl bg-emerald-600 text-white font-bold hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-600/20">Kirim Pesan</button>
        </form>
      </div>
    </div>
  </section>

  <footer class="bg-neutral-950 text-neutral-400 py-8">
    <div class="max-w-7xl mx-auto px-6 text-center text-sm">&copy; 2024 FIRAJAYA Sembako. All rights reserved.</div>
  </footer>

  <script>
    document.getElementById('contactForm').addEventListener('submit', async function (e) {
      e.preventDefault();

      const btn = document.getElementById('btnSendMessage');
      const feedback = document.getElementById('formFeedback');
      const payload = {
        nama: document.getElementById('inputNama').value.trim(),
        email: document.getElementById('inputEmail').value.trim(),
        subjek: document.getElementById('inputSubjek').value.trim(),
        isi_pesan: document.getElementById('inputPesan').value.trim(),
      };

      btn.disabled = true;
      btn.textContent = 'Mengirim...';
      feedback.classList.add('hidden');

      try {
        const res = await fetch('/api/send_message.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const json = await res.json();

        feedback.classList.remove('hidden');
        if (json.success) {
          feedback.className = 'mb-4 px-4 py-3 rounded-xl text-sm font-medium bg-emerald-50 text-emerald-700';
          feedback.textContent = json.message;
          document.getElementById('contactForm').reset();
        } else {
          feedback.className = 'mb-4 px-4 py-3 rounded-xl text-sm font-medium bg-red-50 text-red-600';
          feedback.textContent = json.message || 'Gagal mengirim pesan';
        }
      } catch (err) {
        feedback.classList.remove('hidden');
        feedback.className = 'mb-4 px-4 py-3 rounded-xl text-sm font-medium bg-red-50 text-red-600';
        feedback.textContent = 'Tidak dapat terhubung ke server. Coba lagi.';
      } finally {
        btn.disabled = false;
        btn.textContent = 'Kirim Pesan';
      }
    });
  </script>

  <!-- Chat Widget CS -->
  <div id="chatWidgetRoot"></div>
  <script src="js/chat-widget.js"></script>
</body>
</html>
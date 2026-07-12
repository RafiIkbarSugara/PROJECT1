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
  <title>FIRAJAYA — Tentang Kami</title>
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
        <a href="about.php" class="text-emerald-600 font-semibold">Tentang Kami</a>
        <a href="contact.php" class="hover:text-emerald-600 transition-colors">Kontak</a>
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
      <div class="max-w-3xl mx-auto text-center mb-16">
        <span class="text-xs font-bold uppercase tracking-widest text-emerald-600 mb-3 block">Cerita Kami</span>
        <h1 class="text-5xl md:text-6xl font-extrabold text-neutral-900 tracking-tighter leading-tight mb-6">Membangun Kehidupan yang Lebih Baik</h1>
        <p class="text-lg text-neutral-500 leading-relaxed">FIRAJAYA bukan sekadar toko sembako. Kami adalah mitra keluarga Indonesia dalam memastikan kebutuhan pokok terpenuhi dengan mudah, terjangkau, dan berkualitas.</p>
      </div>
      <div class="grid md:grid-cols-2 gap-8 items-center">
        <div class="rounded-3xl overflow-hidden shadow-2xl border border-neutral-100"><img src="https://images.unsplash.com/photo-1604719312566-8912e9227c6a?auto=format&fit=crop&w=800&q=80" alt="Toko" class="w-full h-full object-cover"></div>
        <div class="space-y-6">
          <div class="flex gap-4">
            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center shrink-0"><iconify-icon icon="solar:heart-bold" width="24" class="text-emerald-600"></iconify-icon></div>
            <div><h3 class="text-lg font-bold mb-1">Dengan Masyarakat</h3><p class="text-neutral-500 text-sm leading-relaxed">Kami percaya akses terhadap kebutuhan dasar adalah hak semua orang. Itu sebabnya kami menjaga harga tetap terjangkau.</p></div>
          </div>
          <div class="flex gap-4">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center shrink-0"><iconify-icon icon="solar:verified-check-bold" width="24" class="text-blue-600"></iconify-icon></div>
            <div><h3 class="text-lg font-bold mb-1">Kualitas Utama</h3><p class="text-neutral-500 text-sm leading-relaxed">Setiap produk melewati seleksi ketat untuk memastikan kesegaran dan kualitas terbaik sampai ke tangan Anda.</p></div>
          </div>
          <div class="flex gap-4">
            <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center shrink-0"><iconify-icon icon="solar:bolt-bold" width="24" class="text-amber-600"></iconify-icon></div>
            <div><h3 class="text-lg font-bold mb-1">Inovasi Tanpa Henti</h3><p class="text-neutral-500 text-sm leading-relaxed">Sistem pemesanan digital kami dirancang untuk menghemat waktu Anda, tanpa perlu mengantri di kasir.</p></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <footer class="bg-neutral-950 text-neutral-400 py-8">
    <div class="max-w-7xl mx-auto px-6 text-center text-sm">&copy; 2024 FIRAJAYA Sembako. All rights reserved.</div>
  </footer>

  <!-- Chat Widget CS -->
  <div id="chatWidgetRoot"></div>
  <script src="js/chat-widget.js"></script>
</body>
</html>
<?php
session_start();
if (isset($_SESSION['customer_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FIRAJAYA — Daftar Akun</title>
  <script src="https://cdn.tailwindcss.com/3.4.17"></script>
  <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
  <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Geist','Inter','sans-serif']}}}}</script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&display=swap">
  <style>
    body { font-family: 'Geist', 'Inter', sans-serif; }
    @keyframes fadeIn { 0% { opacity: 0; transform: translateY(10px); } 100% { opacity: 1; transform: translateY(0); } }
    .anim-fade-in { animation: fadeIn 0.5s cubic-bezier(0.16,1,0.3,1) forwards; }
    .login-btn { background: linear-gradient(135deg, #059669 0%, #064e3b 100%); transition: all 0.3s ease; }
    .login-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(5,150,105,0.4); }
  </style>
</head>
<body class="bg-neutral-100 min-h-screen flex items-center justify-center p-4">

  <div class="anim-fade-in w-full max-w-sm">
    <div class="bg-white rounded-3xl shadow-xl border border-neutral-200/60 overflow-hidden">
      
      <div class="bg-gradient-to-r from-emerald-500 to-emerald-700 px-6 py-6 text-center">
        <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg">
          <iconify-icon icon="solar:user-plus-bold" width="30" class="text-white"></iconify-icon>
        </div>
        <h1 class="text-xl font-bold text-white tracking-tight">Buat Akun Baru</h1>
        <p class="text-emerald-100 text-sm mt-1">Daftar untuk mulai belanja</p>
      </div>

      <form id="registerForm" onsubmit="handleRegister(event)" class="p-6 space-y-4">
        <div id="msgBox" class="hidden text-sm font-medium px-4 py-3 rounded-xl flex items-center gap-2">
          <iconify-icon icon="solar:close-circle-bold" width="16" id="msgIcon"></iconify-icon>
          <span id="msgText"></span>
        </div>

        <div>
          <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Nama Lengkap</label>
          <input type="text" id="namaInput" required placeholder="Nama Anda" class="w-full px-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
        </div>

        <div>
          <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Username</label>
          <input type="text" id="usernameInput" required placeholder="Minimal 4 karakter" class="w-full px-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
        </div>

        <div>
          <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Password</label>
          <input type="password" id="passwordInput" required placeholder="Minimal 6 karakter" class="w-full px-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
        </div>

        <button type="submit" id="regBtn" class="login-btn w-full py-3.5 rounded-xl text-white font-bold text-sm flex items-center justify-center gap-2 mt-2">
          <iconify-icon icon="solar:user-plus-bold" width="18"></iconify-icon> Daftar Sekarang
        </button>
      </form>

      <div class="px-6 pb-6 text-center">
        <p class="text-sm text-neutral-500">Sudah punya akun? <a href="login.php" class="font-bold text-emerald-600 hover:underline">Masuk di sini</a></p>
      </div>
    </div>
  </div>

  <script>
    async function handleRegister(e) {
      e.preventDefault();
      const btn = document.getElementById('regBtn');
      const msgBox = document.getElementById('msgBox');
      const msgText = document.getElementById('msgText');
      const msgIcon = document.getElementById('msgIcon');

      const nama = document.getElementById('namaInput').value.trim();
      const username = document.getElementById('usernameInput').value.trim();
      const password = document.getElementById('passwordInput').value;

      msgBox.classList.add('hidden');
      btn.disabled = true;
      btn.innerHTML = '<iconify-icon icon="solar:refresh-bold" width="18" class="anim-spin"></iconify-icon> Memproses...';

      try {
        const res = await fetch('api/auth_register.php', {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ nama_lengkap: nama, username, password })
        });
        const data = await res.json();

        // Tampilkan pesan
        msgBox.classList.remove('hidden');
        if (data.success) {
          msgBox.className = 'bg-emerald-50 text-emerald-700 text-sm font-medium px-4 py-3 rounded-xl flex items-center gap-2';
          msgIcon.setAttribute('icon', 'solar:check-circle-bold');
          msgText.textContent = data.message + ' Mengalihkan ke login...';
          
          setTimeout(() => { window.location.href = 'login.php'; }, 2000);
        } else {
          msgBox.className = 'bg-red-50 text-red-600 text-sm font-medium px-4 py-3 rounded-xl flex items-center gap-2';
          msgIcon.setAttribute('icon', 'solar:close-circle-bold');
          msgText.textContent = data.message || 'Gagal mendaftar';
        }
      } catch (err) {
        msgBox.className = 'bg-red-50 text-red-600 text-sm font-medium px-4 py-3 rounded-xl flex items-center gap-2';
        msgIcon.setAttribute('icon', 'solar:close-circle-bold');
        msgText.textContent = 'Tidak dapat terhubung ke server';
        msgBox.classList.remove('hidden');
      } finally {
        btn.disabled = false;
        btn.innerHTML = '<iconify-icon icon="solar:user-plus-bold" width="18"></iconify-icon> Daftar Sekarang';
      }
    }
  </script>
</body>
</html>
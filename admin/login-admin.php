<?php
session_start();

if (isset($_SESSION['admin_id'])) {
    header('Location: /admin/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FIRAJAYA — Login</title>
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
    .tab-active { background: white; color: #059669; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
  </style>
</head>
<body class="bg-neutral-100 min-h-screen flex items-center justify-center p-4">

  <div class="anim-fade-in w-full max-w-sm">
    <div class="bg-white rounded-3xl shadow-xl border border-neutral-200/60 overflow-hidden">
      
      <!-- Header -->
      <div class="bg-gradient-to-r from-emerald-500 to-emerald-700 px-6 py-6 text-center">
        <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg">
          <iconify-icon icon="solar:shop-bold" width="30" class="text-white"></iconify-icon>
        </div>
        <h1 class="text-xl font-bold text-white tracking-tight">FIRAJAYA</h1>
      </div>

      <!-- Tab Pilihan Role -->
      <div class="flex bg-neutral-100 m-4 rounded-xl p-1 gap-1">
        <button type="button" onclick="switchTab('kasir')" id="tabKasir" class="tab-active flex-1 py-2.5 rounded-lg text-sm font-bold transition-all flex items-center justify-center gap-2">
          <iconify-icon icon="solar:calculator-minimalistic-bold" width="18"></iconify-icon> Kasir
        </button>
      </div>

      <!-- Form Login -->
      <form id="loginForm" onsubmit="handleLogin(event)" class="px-6 pb-6 space-y-4">
        <input type="hidden" id="loginRole" value="kasir">
        
        <div id="errorMsg" class="hidden bg-red-50 text-red-600 text-sm font-medium px-4 py-3 rounded-xl flex items-center gap-2">
          <iconify-icon icon="solar:close-circle-bold" width="16"></iconify-icon>
          <span id="errorText">Username atau password salah</span>
        </div>

        <div>
          <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Username</label>
          <div class="relative">
            <iconify-icon icon="solar:user-linear" width="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
            <input type="text" id="usernameInput" required placeholder="Masukkan username" class="w-full pl-11 pr-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Password</label>
          <div class="relative">
            <iconify-icon icon="solar:lock-keyhole-linear" width="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
            <input type="password" id="passwordInput" required placeholder="Masukkan password" class="w-full pl-11 pr-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
          </div>
        </div>

        <button type="submit" id="loginBtn" class="login-btn w-full py-3.5 rounded-xl text-white font-bold text-sm flex items-center justify-center gap-2 mt-2">
          <iconify-icon icon="solar:login-3-bold" width="18"></iconify-icon>
          Masuk
        </button>
      </form>

    </div>
    <p class="text-center text-xs text-neutral-400 mt-6">&copy; 2024 FIRAJAYA POS. All rights reserved.</p>
  </div>

  <script>
    function switchTab(role) {
      document.getElementById('loginRole').value = role;
      const tabKasir = document.getElementById('tabKasir');
      const tabUser = document.getElementById('tabUser');
      
      if (role === 'kasir') {
        tabKasir.className = 'tab-active flex-1 py-2.5 rounded-lg text-sm font-bold transition-all flex items-center justify-center gap-2';
        tabUser.className = 'flex-1 py-2.5 rounded-lg text-sm font-semibold text-neutral-500 transition-all flex items-center justify-center gap-2';
      } else {
        tabUser.className = 'tab-active flex-1 py-2.5 rounded-lg text-sm font-bold transition-all flex items-center justify-center gap-2';
        tabKasir.className = 'flex-1 py-2.5 rounded-lg text-sm font-semibold text-neutral-500 transition-all flex items-center justify-center gap-2';
      }
    }

    async function handleLogin(e) {
      e.preventDefault();
      const btn = document.getElementById('loginBtn');
      const username = document.getElementById('usernameInput').value.trim();
      const password = document.getElementById('passwordInput').value;
      const role = document.getElementById('loginRole').value;
      const errorMsg = document.getElementById('errorMsg');
      const errorText = document.getElementById('errorText');

      errorMsg.classList.add('hidden');
      btn.disabled = true;
      btn.innerHTML = '<iconify-icon icon="solar:refresh-bold" width="18" class="anim-spin"></iconify-icon> Memproses...';

      try {
        const res = await fetch('/api/auth_login.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ username, password, role })
        });
        const data = await res.json();

        if (data.success) {
          window.location.href = data.redirect || 'index.php';
        } else {
          errorText.textContent = data.message || 'Login gagal';
          errorMsg.classList.remove('hidden');
        }
      } catch (err) {
        errorText.textContent = 'Tidak dapat terhubung ke server';
        errorMsg.classList.remove('hidden');
      } finally {
        btn.disabled = false;
        btn.innerHTML = '<iconify-icon icon="solar:login-3-bold" width="18"></iconify-icon> Masuk';
      }
    }
  </script>
</body>
</html>
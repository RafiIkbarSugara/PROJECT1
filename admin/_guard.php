<?php
// ============================================
// admin/_guard.php
// MIDDLEWARE akses halaman admin/kasir.
// WAJIB di-require PALING ATAS di setiap halaman dalam folder /admin/.
//
// Alur:
// 1. Belum login sama sekali            -> redirect ke /login.php
// 2. Sudah login tapi role tidak diizinkan -> tampilkan 403 Forbidden
// 3. Lolos keduanya                     -> lanjut render halaman
//
// Catatan: skema database project ini hanya punya 2 role: 'kasir' dan 'user'.
// Belum ada role 'admin' terpisah. Jadi untuk saat ini 'kasir' dipakai
// sebagai role staff/pemilik warung (setara admin). Kalau nanti perlu
// role 'admin' yang benar-benar terpisah dari 'kasir' (misalnya izin
// berbeda), tinggal tambah value baru di ENUM role tb_users dan daftarkan
// di $allowedRoles di bawah.
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ----- 1. Belum login -----
if (!isset($_SESSION['admin_id'])) {
    header('Location: /admin/login-admin.php');
    exit;
}

// ----- 2. Role tidak diizinkan masuk area admin -----
$allowedRoles = ['kasir']; // <-- tambahkan 'admin' di sini kalau role itu sudah dibuat di DB
if (!in_array($_SESSION['admin_role'], $allowedRoles, true)) {
    http_response_code(403);
    require __DIR__ . '/403.php';
    exit;
}

// ----- 3. Lolos - siapkan variabel umum yang dipakai semua halaman admin -----
$userName = $_SESSION['admin_name'];
$userRole = ucfirst($_SESSION['admin_role']);

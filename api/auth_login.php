<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/rate_limit.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
}

$input = getJsonInput();
if (!$input) {
    jsonResponse(['success' => false, 'message' => 'Data tidak valid'], 400);
}

$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';
$requestedRole = trim($input['role'] ?? 'kasir'); // Role yang dipilih di tab login

if ($username === '' || $password === '') {
    jsonResponse(['success' => false, 'message' => 'Username dan password wajib diisi'], 400);
}

$ip = getClientIp();

try {
    // ===== 1. Cek lockout SEBELUM mengecek password sama sekali =====
    // Ini penting: kalau baru dicek setelah password salah, penyerang tetap
    // bisa mencoba tanpa batas selama lockout belum tercatat.
    $lockout = checkLoginLockout($pdo, $username, $ip);
    if ($lockout['locked']) {
        $pesan = $lockout['reason'] === 'username'
            ? "Terlalu banyak percobaan gagal untuk akun ini. Coba lagi dalam {$lockout['retry_after_min']} menit."
            : "Terlalu banyak percobaan login gagal dari perangkat/jaringan ini. Coba lagi dalam {$lockout['retry_after_min']} menit.";
        jsonResponse(['success' => false, 'message' => $pesan], 429);
    }

    $stmt = $pdo->prepare("SELECT * FROM tb_users WHERE username = :username AND role = :role LIMIT 1");
    $stmt->execute([':username' => $username, ':role' => $requestedRole]);
    $user = $stmt->fetch();

    $loginValid = $user && password_verify($password, $user['password']);

    if (!$loginValid) {
        // Username/password salah ATAU akun ini tidak terdaftar sebagai role
        // yang diminta -- pesannya digabung supaya tidak membocorkan info
        // "username ini terdaftar tapi di role lain" ke penyerang.
        recordLoginAttempt($pdo, $username, $ip, false);
        jsonResponse(['success' => false, 'message' => 'Username atau password salah'], 401);
    }

    // ===== Login berhasil =====
    recordLoginAttempt($pdo, $username, $ip, true);

    session_start();
    session_regenerate_id(true); // cegah session fixation
if ($user['role'] === 'user') {

    // Hapus session admin
    unset(
        $_SESSION['admin_id'],
        $_SESSION['admin_name'],
        $_SESSION['admin_username'],
        $_SESSION['admin_role']
    );

    $_SESSION['customer_id'] = $user['id_user'];
    $_SESSION['customer_name'] = $user['nama_lengkap'];
    $_SESSION['customer_username'] = $user['username'];
    $_SESSION['role'] = 'user';

} else {

    // Hapus session customer
    unset(
        $_SESSION['customer_id'],
        $_SESSION['customer_name'],
        $_SESSION['customer_username']
    );

    $_SESSION['admin_id'] = $user['id_user'];
    $_SESSION['admin_name'] = $user['nama_lengkap'];
    $_SESSION['admin_username'] = $user['username'];
    $_SESSION['admin_role'] = $user['role'];
    $_SESSION['role'] = $user['role'];
}
    $redirect = ($user['role'] === 'user') ? 'index.php' : '/admin/dashboard.php';

    jsonResponse([
        'success' => true,
        'message' => 'Login berhasil',
        'redirect' => $redirect,
        'data' => [
            'nama' => $user['nama_lengkap'],
            'role' => $user['role']
        ]
    ]);

} catch (Exception $e) {
    // Jangan bocorkan detail error database ke client
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan, silakan coba lagi'], 500);
}
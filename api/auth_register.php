<?php
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
}

 $input = getJsonInput();
if (!$input) {
    jsonResponse(['success' => false, 'message' => 'Data tidak valid'], 400);
}

 $nama_lengkap = trim($input['nama_lengkap'] ?? '');
 $username      = trim($input['username'] ?? '');
 $password      = $input['password'] ?? '';

if ($nama_lengkap === '' || $username === '' || $password === '') {
    jsonResponse(['success' => false, 'message' => 'Semua field wajib diisi'], 400);
}
if (strlen($username) < 4) {
    jsonResponse(['success' => false, 'message' => 'Username minimal 4 karakter'], 400);
}
if (strlen($password) < 6) {
    jsonResponse(['success' => false, 'message' => 'Password minimal 6 karakter'], 400);
}

try {
    // Cek username sudah dipakai apa belom (khusus sesama akun pelanggan --
    // username yang sama boleh saja sudah dipakai akun kasir, itu diizinkan)
    $stmt = $pdo->prepare("SELECT id_user FROM tb_users WHERE username = :username AND role = 'user'");
    $stmt->execute([':username' => $username]);
    
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Username sudah digunakan, pilih yang lain'], 409);
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Simpan user baru dengan role 'user' (pelanggan)
    $stmt2 = $pdo->prepare("
        INSERT INTO tb_users (nama_lengkap, username, password, role) 
        VALUES (:nama, :username, :password, 'user')
    ");
    $stmt2->execute([
        ':nama'      => $nama_lengkap,
        ':username'  => $username,
        ':password'  => $hashedPassword
    ]);

    jsonResponse([
        'success' => true, 
        'message' => 'Registrasi berhasil! Silakan login.'
    ]);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Gagal mendaftar: ' . $e->getMessage()], 500);
}
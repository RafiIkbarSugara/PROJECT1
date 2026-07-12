<?php
// ============================================
// POST /api/add_kasir.php
// Tambah akun kasir baru — HANYA bisa diakses oleh
// user yang sudah login dengan role 'kasir'
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();

// ===== 1. Proteksi akses: wajib login sebagai kasir =====
// Ini yang HILANG di banyak endpoint lain (add_product.php, dll).
// Tanpa cek ini, siapa pun bisa memanggil API ini langsung lewat
// Postman/curl walau belum login, dan membuat akun kasir sendiri.
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'kasir') {
    jsonResponse(['success' => false, 'message' => 'Akses ditolak. Silakan login sebagai kasir.'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
}

$input = getJsonInput();
if (!$input) {
    jsonResponse(['success' => false, 'message' => 'Data tidak valid'], 400);
}

$nama_lengkap = trim($input['nama_lengkap'] ?? '');
$username     = trim($input['username'] ?? '');
$password     = $input['password'] ?? '';

// ===== 2. Validasi input =====
if ($nama_lengkap === '' || $username === '' || $password === '') {
    jsonResponse(['success' => false, 'message' => 'Semua field wajib diisi'], 400);
}
if (strlen($nama_lengkap) > 100) {
    jsonResponse(['success' => false, 'message' => 'Nama lengkap terlalu panjang'], 400);
}
if (!preg_match('/^[a-zA-Z0-9_.]{4,50}$/', $username)) {
    jsonResponse(['success' => false, 'message' => 'Username 4-50 karakter, hanya huruf/angka/_ /.'], 400);
}
if (strlen($password) < 8) {
    jsonResponse(['success' => false, 'message' => 'Password minimal 8 karakter'], 400);
}
// Wajib kombinasi huruf & angka supaya tidak mudah ditebak
if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    jsonResponse(['success' => false, 'message' => 'Password harus mengandung huruf dan angka'], 400);
}

try {
    // ===== 3. Cek username sudah dipakai atau belum (khusus sesama akun kasir --
    //          username yang sama boleh saja sudah dipakai akun pelanggan) =====
    $stmt = $pdo->prepare("SELECT id_user FROM tb_users WHERE username = :username AND role = 'kasir'");
    $stmt->execute([':username' => $username]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Username sudah digunakan, pilih yang lain'], 409);
    }

    // ===== 4. Hash password dengan bcrypt (JANGAN PERNAH simpan plain text) =====
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // ===== 5. Simpan dengan role dipaksa 'kasir' di server, =====
    //          BUKAN dari input client — supaya client tidak bisa
    //          kirim role lain lewat manipulasi request.
    $stmt2 = $pdo->prepare("
        INSERT INTO tb_users (nama_lengkap, username, password, role)
        VALUES (:nama, :username, :password, 'kasir')
    ");
    $stmt2->execute([
        ':nama'     => $nama_lengkap,
        ':username' => $username,
        ':password' => $hashedPassword,
    ]);

    jsonResponse([
        'success' => true,
        'message' => 'Akun kasir berhasil dibuat',
        'data' => [
            'id_user'      => $pdo->lastInsertId(),
            'nama_lengkap' => $nama_lengkap,
            'username'     => $username,
            'role'         => 'kasir',
        ]
    ], 201);

} catch (Exception $e) {
    // Jangan bocorkan detail error database (bisa membocorkan struktur tabel)
    jsonResponse(['success' => false, 'message' => 'Gagal membuat akun kasir'], 500);
}
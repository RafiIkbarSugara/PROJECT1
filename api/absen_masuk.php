<?php
// ============================================
// POST /api/absen_masuk.php
// Catat jam masuk kasir yang sedang login.
// Hanya bisa 1x per hari (dijaga UNIQUE KEY + trigger DB).
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'kasir') {
    jsonResponse(['success' => false, 'message' => 'Akses ditolak. Silakan login sebagai kasir.'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
}

$id_user = (int) $_SESSION['admin_id'];
$nama    = $_SESSION['admin_name'];

try {
    // Cek apakah sudah absen masuk hari ini
    $cek = $pdo->prepare("SELECT id_absensi, jam_masuk FROM tb_absensi WHERE id_user = :id AND tanggal = CURDATE()");
    $cek->execute([':id' => $id_user]);
    $existing = $cek->fetch();

    if ($existing) {
        jsonResponse([
            'success' => false,
            'message' => 'Anda sudah absen masuk hari ini pukul ' . date('H:i', strtotime($existing['jam_masuk']))
        ], 409);
    }

    $stmt = $pdo->prepare("
        INSERT INTO tb_absensi (id_user, nama_kasir, tanggal, jam_masuk)
        VALUES (:id_user, :nama, CURDATE(), NOW())
    ");
    $stmt->execute([':id_user' => $id_user, ':nama' => $nama]);

    jsonResponse([
        'success' => true,
        'message' => 'Absen masuk berhasil dicatat',
        'data' => ['jam_masuk' => date('H:i:s')]
    ], 201);

} catch (PDOException $e) {
    // Race condition (dobel klik cepat) akan tertangkap UNIQUE KEY di sini
    if ($e->getCode() == 23000) {
        jsonResponse(['success' => false, 'message' => 'Anda sudah absen masuk hari ini'], 409);
    }
    jsonResponse(['success' => false, 'message' => 'Gagal mencatat absen masuk'], 500);
}

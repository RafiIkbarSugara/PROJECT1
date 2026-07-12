<?php
// ============================================
// POST /api/koreksi_absensi.php
// Menambahkan CATATAN koreksi pada sebuah data absensi.
// TIDAK menghapus / mengubah data asli di tb_absensi.
// Catatan koreksi ini sendiri juga permanen (lihat trigger di DB).
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'kasir') {
    jsonResponse(['success' => false, 'message' => 'Akses ditolak. Silakan login sebagai kasir.'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
}

$input = getJsonInput();
$id_absensi = intval($input['id_absensi'] ?? 0);
$alasan = trim($input['alasan'] ?? '');

if ($id_absensi <= 0) {
    jsonResponse(['success' => false, 'message' => 'Data absensi tidak valid'], 400);
}
if ($alasan === '') {
    jsonResponse(['success' => false, 'message' => 'Alasan koreksi wajib diisi'], 400);
}
if (strlen($alasan) > 255) {
    jsonResponse(['success' => false, 'message' => 'Alasan maksimal 255 karakter'], 400);
}

try {
    $cek = $pdo->prepare("SELECT id_absensi FROM tb_absensi WHERE id_absensi = :id");
    $cek->execute([':id' => $id_absensi]);
    if (!$cek->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Data absensi tidak ditemukan'], 404);
    }

    $stmt = $pdo->prepare("
        INSERT INTO tb_absensi_koreksi (id_absensi, alasan, dikoreksi_oleh, nama_pengoreksi)
        VALUES (:id_absensi, :alasan, :oleh, :nama)
    ");
    $stmt->execute([
        ':id_absensi' => $id_absensi,
        ':alasan'     => $alasan,
        ':oleh'       => $_SESSION['admin_id'],
        ':nama'       => $_SESSION['admin_name'],
    ]);

    jsonResponse(['success' => true, 'message' => 'Catatan koreksi berhasil ditambahkan']);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Gagal mencatat koreksi'], 500);
}

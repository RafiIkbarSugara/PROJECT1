<?php
// ============================================
// GET /api/get_absensi_status.php
// Status absen hari ini untuk kasir yang sedang login
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'kasir') {
    jsonResponse(['success' => false, 'message' => 'Akses ditolak. Silakan login sebagai kasir.'], 403);
}

try {
    $stmt = $pdo->prepare("SELECT jam_masuk, jam_pulang FROM tb_absensi WHERE id_user = :id AND tanggal = CURDATE()");
    $stmt->execute([':id' => $_SESSION['admin_id']]);
    $today = $stmt->fetch();

    jsonResponse([
        'success' => true,
        'data' => [
            'sudah_absen_masuk'  => (bool) $today,
            'sudah_absen_pulang' => (bool) ($today && $today['jam_pulang'] !== null),
            'jam_masuk'          => $today['jam_masuk'] ?? null,
            'jam_pulang'         => $today['jam_pulang'] ?? null,
        ]
    ]);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Gagal mengambil status absensi'], 500);
}

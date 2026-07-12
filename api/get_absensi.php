<?php
// ============================================
// GET /api/get_absensi.php
// Riwayat absensi semua kasir — READ ONLY.
// Filter opsional: ?dari=YYYY-MM-DD&sampai=YYYY-MM-DD
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'kasir') {
    jsonResponse(['success' => false, 'message' => 'Akses ditolak. Silakan login sebagai kasir.'], 403);
}

$dari   = $_GET['dari'] ?? date('Y-m-01');   // default: awal bulan ini
$sampai = $_GET['sampai'] ?? date('Y-m-d');  // default: hari ini

// Validasi format tanggal supaya tidak dipakai untuk injeksi/celah lain
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) {
    jsonResponse(['success' => false, 'message' => 'Format tanggal tidak valid'], 400);
}

try {
    $stmt = $pdo->prepare("
        SELECT a.id_absensi, a.nama_kasir, a.tanggal, a.jam_masuk, a.jam_pulang,
               TIMESTAMPDIFF(MINUTE, a.jam_masuk, a.jam_pulang) AS durasi_menit,
               k.alasan AS koreksi_alasan,
               k.nama_pengoreksi AS koreksi_oleh,
               k.dibuat_pada AS koreksi_pada,
               (SELECT COUNT(*) FROM tb_absensi_koreksi k2 WHERE k2.id_absensi = a.id_absensi) AS jumlah_koreksi
        FROM tb_absensi a
        LEFT JOIN tb_absensi_koreksi k
               ON k.id_absensi = a.id_absensi
              AND k.id_koreksi = (SELECT MAX(id_koreksi) FROM tb_absensi_koreksi WHERE id_absensi = a.id_absensi)
        WHERE a.tanggal BETWEEN :dari AND :sampai
        ORDER BY a.tanggal DESC, a.jam_masuk DESC
    ");
    $stmt->execute([':dari' => $dari, ':sampai' => $sampai]);
    $data = $stmt->fetchAll();

    jsonResponse(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Gagal mengambil riwayat absensi'], 500);
}

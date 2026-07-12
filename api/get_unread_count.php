<?php
// ============================================
// GET /api/get_unread_count.php
// Endpoint ringan khusus untuk badge notifikasi di sidebar
// kasir — dipanggil di setiap halaman kasir (index, produk,
// laporan, riwayat, stok, settings, pesan).
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'kasir') {
    jsonResponse(['success' => false, 'message' => 'Akses ditolak'], 403);
}

try {
    $unreadCount = $pdo->query("SELECT COUNT(*) FROM tb_pesan WHERE status = 'belum_dibaca'")->fetchColumn();

    jsonResponse([
        'success'      => true,
        'unread_count' => (int)$unreadCount,
    ]);

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Gagal mengambil jumlah pesan: ' . $e->getMessage()
    ], 500);
}
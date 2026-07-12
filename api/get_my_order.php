<?php
// ============================================
// GET /api/get_my_orders.php
// Riwayat pesanan milik pelanggan yang login (role 'user')
// Params: ?page=1
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();
if (!isset($_SESSION['customer_id']) || $_SESSION['role'] !== 'user') {
    jsonResponse(['success' => false, 'message' => 'Akses ditolak'], 403);
}

$idUser = $_SESSION['customer_id'];

try {
    $page    = max(1, intval($_GET['page'] ?? 1));
    $perPage = 10;
    $offset  = ($page - 1) * $perPage;

    // Hitung total pesanan milik user ini
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM tb_transaksi WHERE id_user = :id_user");
    $stmtCount->execute([':id_user' => $idUser]);
    $total = $stmtCount->fetchColumn();

    // Ambil data, hanya milik user yang login
    $stmt = $pdo->prepare("
        SELECT
            id_transaksi,
            no_transaksi,
            tanggal,
            subtotal,
            pajak_rupiah,
            total,
            metode_bayar,
            status_bayar
        FROM tb_transaksi
        WHERE id_user = :id_user
        ORDER BY tanggal DESC
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute([':id_user' => $idUser]);
    $orders = $stmt->fetchAll();

    jsonResponse([
        'success' => true,
        'data'    => $orders,
        'total'   => $total,
        'page'    => $page,
        'pages'   => ceil($total / $perPage),
    ]);

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Gagal mengambil riwayat pesanan: ' . $e->getMessage()
    ], 500);
}
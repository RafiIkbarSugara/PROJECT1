<?php
// ============================================
// GET /api/get_transaction_detail.php?id=1
// Ambil detail transaksi beserta items
// Pelanggan (role 'user') hanya bisa lihat transaksi miliknya sendiri.
// Kasir tetap bisa lihat semua (dipakai di riwayat.php).
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();

 $id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    jsonResponse(['success' => false, 'message' => 'ID tidak valid'], 400);
}

try {
    // Ambil header transaksi
    $stmt = $pdo->prepare("SELECT * FROM tb_transaksi WHERE id_transaksi = :id");
    $stmt->execute([':id' => $id]);
    $transaksi = $stmt->fetch();

    if (!$transaksi) {
        jsonResponse(['success' => false, 'message' => 'Transaksi tidak ditemukan'], 404);
    }

    // Batasi akses: pelanggan cuma boleh lihat transaksi miliknya sendiri
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
        if ((int)$transaksi['id_user'] !== (int)$_SESSION['admin_id']) {
            jsonResponse(['success' => false, 'message' => 'Anda tidak punya akses ke transaksi ini'], 403);
        }
    }

    // Ambil detail items
    $stmt2 = $pdo->prepare("
        SELECT id_detail, id_produk, nama_produk, harga, qty, subtotal
        FROM tb_detail_transaksi 
        WHERE id_transaksi = :id
        ORDER BY id_detail ASC
    ");
    $stmt2->execute([':id' => $id]);
    $items = $stmt2->fetchAll();

    $transaksi['items'] = $items;

    jsonResponse([
        'success' => true,
        'data'    => $transaksi
    ]);

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Gagal mengambil detail: ' . $e->getMessage()
    ], 500);
}
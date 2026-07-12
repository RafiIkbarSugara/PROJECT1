<?php
// ============================================
// DELETE /api/delete_product.php?id=1
// Hapus produk beserta gambarnya
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'kasir') {
    jsonResponse(['success' => false, 'message' => 'Akses ditolak. Silakan login sebagai kasir.'], 403);
}

try {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        jsonResponse(['success' => false, 'message' => 'ID produk tidak valid'], 400);
    }

    // Ambil nama gambar sebelum hapus
    $stmt = $pdo->prepare("SELECT gambar FROM tb_produk WHERE id_produk = :id");
    $stmt->execute([':id' => $id]);
    $produk = $stmt->fetch();

    if (!$produk) {
        jsonResponse(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);
    }

    // Hapus dari database
    $stmt2 = $pdo->prepare("DELETE FROM tb_produk WHERE id_produk = :id");
    $stmt2->execute([':id' => $id]);

    // Hapus file gambar jika ada
    if ($produk['gambar']) {
        $filePath = __DIR__ . '/../uploads/produk/' . $produk['gambar'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    jsonResponse([
        'success' => true,
        'message' => 'Produk berhasil dihapus'
    ]);

} catch (Exception $e) {
    // Jika ada foreign key constraint (produk sudah dipakai di riwayat transaksi)
    if ($e->getCode() == 23000) {
        jsonResponse([
            'success' => false,
            'can_deactivate' => true,
            'message' => 'Produk tidak bisa dihapus karena sudah ada di riwayat transaksi. Gunakan "Nonaktifkan" agar produk tersembunyi dari kasir tanpa menghapus riwayatnya.'
        ], 400);
    }
    jsonResponse([
        'success' => false,
        'message' => 'Gagal menghapus: ' . $e->getMessage()
    ], 500);
}
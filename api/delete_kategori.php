<?php
// ============================================
// POST /api/delete_kategori.php
// Hapus kategori — ditolak kalau masih ada produk yang memakainya
// (dijaga foreign key ON DELETE RESTRICT di database).
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
$id_kategori = intval($input['id_kategori'] ?? 0);

if ($id_kategori <= 0) {
    jsonResponse(['success' => false, 'message' => 'Kategori tidak valid'], 400);
}

try {
    // Cek dulu apakah masih dipakai produk, supaya pesan errornya jelas
    // (bukan pesan teknis foreign key mentah dari MySQL)
    $cek = $pdo->prepare("SELECT COUNT(*) AS jumlah FROM tb_produk WHERE id_kategori = :id");
    $cek->execute([':id' => $id_kategori]);
    $jumlahProduk = (int) $cek->fetch()['jumlah'];

    if ($jumlahProduk > 0) {
        jsonResponse([
            'success' => false,
            'message' => "Tidak bisa dihapus, masih ada $jumlahProduk produk memakai kategori ini. Pindahkan/hapus produknya dulu."
        ], 409);
    }

    $stmt = $pdo->prepare("DELETE FROM tb_kategori WHERE id_kategori = :id");
    $stmt->execute([':id' => $id_kategori]);

    jsonResponse(['success' => true, 'message' => 'Kategori berhasil dihapus']);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Gagal menghapus kategori'], 500);
}
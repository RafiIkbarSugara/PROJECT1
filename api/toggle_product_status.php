<?php
// ============================================
// POST /api/toggle_product_status.php
// Ganti status produk antara 'aktif' <-> 'nonaktif'.
// Tidak menghapus data — alternatif aman untuk produk
// yang sudah pernah dipakai di riwayat transaksi (yang
// tidak bisa di-DELETE karena FOREIGN KEY RESTRICT).
// Body JSON: { "id_produk": 1, "status": "nonaktif" }
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
if (!$input) {
    jsonResponse(['success' => false, 'message' => 'Data tidak valid'], 400);
}

$id_produk = intval($input['id_produk'] ?? 0);
$status    = trim($input['status'] ?? '');

if ($id_produk <= 0) {
    jsonResponse(['success' => false, 'message' => 'ID produk tidak valid'], 400);
}
if (!in_array($status, ['aktif', 'nonaktif'], true)) {
    jsonResponse(['success' => false, 'message' => "Status harus 'aktif' atau 'nonaktif'"], 400);
}

try {
    $stmt = $pdo->prepare("SELECT id_produk, nama_produk FROM tb_produk WHERE id_produk = :id");
    $stmt->execute([':id' => $id_produk]);
    $produk = $stmt->fetch();

    if (!$produk) {
        jsonResponse(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);
    }

    $stmt2 = $pdo->prepare("UPDATE tb_produk SET status = :status WHERE id_produk = :id");
    $stmt2->execute([':status' => $status, ':id' => $id_produk]);

    $pesan = $status === 'nonaktif'
        ? "{$produk['nama_produk']} dinonaktifkan — tersembunyi dari kasir & toko, data tetap aman"
        : "{$produk['nama_produk']} diaktifkan kembali";

    jsonResponse([
        'success' => true,
        'message' => $pesan,
        'status'  => $status
    ]);

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Gagal mengubah status: ' . $e->getMessage()
    ], 500);
}
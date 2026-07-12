<?php
// ============================================
// GET /api/get_products.php
// Ambil daftar produk aktif
// Params: ?kategori=1  ?search=beras  ?include_inactive=1
//
// Default: hanya produk berstatus 'aktif' (dipakai kasir & toko).
// include_inactive=1: tampilkan SEMUA status (dipakai produk.php
// untuk keperluan manajemen — bisa lihat & aktifkan kembali
// produk yang dinonaktifkan).
// ============================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../config/database.php';

try {
    $includeInactive = isset($_GET['include_inactive']) && $_GET['include_inactive'] === '1';
    $where  = $includeInactive ? ["1=1"] : ["p.status = 'aktif'"];
    $params = [];

    // Filter kategori
    if (isset($_GET['kategori']) && $_GET['kategori'] !== 'all' && $_GET['kategori'] !== '') {
        $where[] = "p.id_kategori = :id_kategori";
        $params[':id_kategori'] = (int)$_GET['kategori'];
    }

    // Filter pencarian
    if (isset($_GET['search']) && $_GET['search'] !== '') {
        $where[] = "(p.nama_produk LIKE :search OR p.barcode LIKE :search2)";
        $params[':search']  = '%' . $_GET['search'] . '%';
        $params[':search2'] = '%' . $_GET['search'] . '%';
    }

    $whereClause = implode(' AND ', $where);

    $stmt = $pdo->prepare("
        SELECT
            p.id_produk,
            p.nama_produk,
            p.deskripsi,
            p.harga,
            p.stok,
            p.gambar,
            p.satuan,
            p.barcode,
            p.status,
            k.nama_kategori,
            k.id_kategori
        FROM tb_produk p
        JOIN tb_kategori k ON p.id_kategori = k.id_kategori
        WHERE $whereClause
        ORDER BY k.urutan ASC, p.nama_produk ASC
    ");
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // Tambahkan URL gambar
foreach ($products as &$p) {
    $p['gambar_url'] = $p['gambar']
        ? '/uploads/produk/' . $p['gambar']
        : '/assets/img/firajaya.png';
}

    jsonResponse([
        'success' => true,
        'data'    => $products,
        'total'   => count($products)
    ]);

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Gagal mengambil produk: ' . $e->getMessage()
    ], 500);
}
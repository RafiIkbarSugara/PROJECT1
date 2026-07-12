<?php
require_once __DIR__ . '/../config/database.php';

session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'kasir') {
    jsonResponse(['success' => false, 'message' => 'Akses ditolak. Silakan login sebagai kasir.'], 403);
}

 $input = getJsonInput();

if (!$input) {
    jsonResponse(['success' => false, 'message' => 'Data tidak valid'], 400);
}

 $id_produk   = intval($input['id_produk'] ?? 0);
 $tambah_stok = intval($input['tambah_stok'] ?? 0);

if ($id_produk <= 0) {
    jsonResponse(['success' => false, 'message' => 'ID produk tidak valid'], 400);
}

if ($tambah_stok <= 0) {
    jsonResponse(['success' => false, 'message' => 'Jumlah stok harus lebih dari 0'], 400);
}

try {
    $stmt = $pdo->prepare("SELECT id_produk, nama_produk, stok FROM tb_produk WHERE id_produk = :id");
    $stmt->execute([':id' => $id_produk]);
    $produk = $stmt->fetch();

    if (!$produk) {
        jsonResponse(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);
    }

    $stmt2 = $pdo->prepare("
        UPDATE tb_produk 
        SET stok = stok + :tambah 
        WHERE id_produk = :id
    ");
    $stmt2->execute([
        ':tambah' => $tambah_stok,
        ':id'     => $id_produk
    ]);

    $stmt3 = $pdo->prepare("SELECT stok FROM tb_produk WHERE id_produk = :id");
    $stmt3->execute([':id' => $id_produk]);
    $newStok = $stmt3->fetchColumn();

    jsonResponse([
        'success'   => true,
        'message'   => "Stok {$produk['nama_produk']} bertambah +{$tambah_stok}",
        'stok_lama' => $produk['stok'],
        'stok_baru' => $newStok
    ]);

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Gagal update stok: ' . $e->getMessage()
    ], 500);
}
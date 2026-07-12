<?php
// ============================================
// POST /api/add_kategori.php
// Tambah kategori produk baru.
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();

if (!isset($_SESSION['admin_id'])) {
    jsonResponse([
        'success' => false,
        'message' => 'Silakan login terlebih dahulu.'
    ], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
}

$input = getJsonInput();
$nama_kategori = trim($input['nama_kategori'] ?? '');

if ($nama_kategori === '') {
    jsonResponse(['success' => false, 'message' => 'Nama kategori wajib diisi'], 400);
}
if (strlen($nama_kategori) > 50) {
    jsonResponse(['success' => false, 'message' => 'Nama kategori maksimal 50 karakter'], 400);
}

try {
    // Cegah nama kategori dobel (case-insensitive)
    $cek = $pdo->prepare("SELECT id_kategori FROM tb_kategori WHERE LOWER(nama_kategori) = LOWER(:nama)");
    $cek->execute([':nama' => $nama_kategori]);
    if ($cek->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Kategori dengan nama ini sudah ada'], 409);
    }

    // Kategori baru ditaruh di urutan paling akhir
    $maxUrutan = (int) $pdo->query("SELECT COALESCE(MAX(urutan), 0) FROM tb_kategori")->fetchColumn();

    $stmt = $pdo->prepare("INSERT INTO tb_kategori (nama_kategori, urutan) VALUES (:nama, :urutan)");
    $stmt->execute([':nama' => $nama_kategori, ':urutan' => $maxUrutan + 1]);

    jsonResponse([
        'success' => true,
        'message' => 'Kategori berhasil ditambahkan',
        'data' => ['id_kategori' => $pdo->lastInsertId(), 'nama_kategori' => $nama_kategori]
    ], 201);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Gagal menambahkan kategori'], 500);
}
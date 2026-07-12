<?php
// ============================================
// POST /api/add_product.php
// Tambah produk baru + upload gambar
// Menggunakan FormData (multipart/form-data)
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();

if (!isset($_SESSION['admin_id'])) {
    jsonResponse([
        'success' => false,
        'message' => 'Silakan login terlebih dahulu.'
    ], 403);
}

// Hanya terima method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
}

try {
    // ========== Ambil & validasi input ==========
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $deskripsi   = trim($_POST['deskripsi'] ?? '');
    $harga       = intval($_POST['harga'] ?? 0);
    $stok        = intval($_POST['stok'] ?? 0);
    $id_kategori = intval($_POST['id_kategori'] ?? 0);
    $satuan      = trim($_POST['satuan'] ?? 'pcs');
    $barcode     = trim($_POST['barcode'] ?? '') ?: null;

    // Validasi wajib
    if ($nama_produk === '') {
        jsonResponse(['success' => false, 'message' => 'Nama produk wajib diisi'], 400);
    }
    if ($deskripsi === '') {
        jsonResponse(['success' => false, 'message' => 'Deskripsi produk wajib diisi'], 400);
    }
    if ($harga <= 0) {
        jsonResponse(['success' => false, 'message' => 'Harga harus lebih dari 0'], 400);
    }
    if ($stok < 0) {
        jsonResponse(['success' => false, 'message' => 'Stok tidak boleh negatif'], 400);
    }
    if ($id_kategori <= 0) {
        jsonResponse(['success' => false, 'message' => 'Kategori wajib dipilih'], 400);
    }

    // ========== Upload gambar ==========
    $gambarName = null;

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file     = $_FILES['gambar'];
        $maxSize  = 2 * 1024 * 1024; // 2 MB
        $allowed  = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        // Validasi ukuran
        if ($file['size'] > $maxSize) {
            jsonResponse(['success' => false, 'message' => 'Ukuran gambar maksimal 2 MB'], 400);
        }

        // Validasi tipe
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed)) {
            jsonResponse(['success' => false, 'message' => 'Format gambar harus JPG, PNG, WebP, atau GIF'], 400);
        }

        // Buat nama file unik
        $ext        = pathinfo($file['name'], PATHINFO_EXTENSION);
        $gambarName = uniqid('produk_') . '.' . $ext;

        // Pastikan folder uploads/produk ada
        $uploadDir = __DIR__ . '/../uploads/produk/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Pindahkan file
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $gambarName)) {
            jsonResponse(['success' => false, 'message' => 'Gagal menyimpan gambar'], 500);
        }
    }

    // ========== Simpan ke database ==========
    $stmt = $pdo->prepare("
        INSERT INTO tb_produk (nama_produk, deskripsi, harga, stok, id_kategori, gambar, satuan, barcode)
        VALUES (:nama_produk, :deskripsi, :harga, :stok, :id_kategori, :gambar, :satuan, :barcode)
    ");
    $stmt->execute([
        ':nama_produk' => $nama_produk,
        ':deskripsi'   => $deskripsi,
        ':harga'       => $harga,
        ':stok'        => $stok,
        ':id_kategori' => $id_kategori,
        ':gambar'      => $gambarName,
        ':satuan'      => $satuan,
        ':barcode'     => $barcode,
    ]);

    $idProduk = $pdo->lastInsertId();

    // Ambil data produk yang baru ditambahkan
    $stmt2 = $pdo->prepare("
        SELECT p.*, k.nama_kategori
        FROM tb_produk p
        JOIN tb_kategori k ON p.id_kategori = k.id_kategori
        WHERE p.id_produk = :id
    ");
    $stmt2->execute([':id' => $idProduk]);
    $newProduct = $stmt2->fetch();

    // Tambahkan URL gambar
    $newProduct['gambar_url'] = $newProduct['gambar']
        ? '/uploads/produk/' . $newProduct['gambar']
        : null;

    jsonResponse([
        'success' => true,
        'message' => 'Produk berhasil ditambahkan',
        'data'    => $newProduct
    ], 201);

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Gagal menambahkan produk: ' . $e->getMessage()
    ], 500);
}
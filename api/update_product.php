<?php
// ============================================
// POST /api/update_product.php
// Edit produk + upload gambar baru (opsional)
// Menggunakan FormData (multipart/form-data)
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();

if (!isset($_SESSION['admin_id'])) {
    jsonResponse([
        'success' => false,
        'message' => 'Silakan login.'
    ], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'success' => false,
        'message' => 'Method tidak diizinkan'
    ], 405);
}

try {
    $id_produk   = intval($_POST['id_produk'] ?? 0);
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $deskripsi   = trim($_POST['deskripsi'] ?? '') ?: null;
    $harga       = intval($_POST['harga'] ?? 0);
    $stok        = intval($_POST['stok'] ?? 0);
    $id_kategori = intval($_POST['id_kategori'] ?? 0);
    $satuan      = trim($_POST['satuan'] ?? 'pcs');
    $barcode     = trim($_POST['barcode'] ?? '') ?: null;
    $hapus_gambar = isset($_POST['hapus_gambar']) && $_POST['hapus_gambar'] === '1';

    // Validasi
    if ($id_produk <= 0) {
        jsonResponse(['success' => false, 'message' => 'ID produk tidak valid'], 400);
    }

    if ($nama_produk === '') {
        jsonResponse(['success' => false, 'message' => 'Nama produk wajib diisi'], 400);
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

    // Cek produk
    $stmt = $pdo->prepare("
        SELECT gambar
        FROM tb_produk
        WHERE id_produk = :id
    ");
    $stmt->execute([':id' => $id_produk]);

    $produk = $stmt->fetch();

    if (!$produk) {
        jsonResponse([
            'success' => false,
            'message' => 'Produk tidak ditemukan'
        ], 404);
    }

    $gambarName = $produk['gambar'];

    // Hapus gambar lama
    if ($hapus_gambar && $produk['gambar']) {
        $filePath = __DIR__ . '/../uploads/produk/' . $produk['gambar'];

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $gambarName = null;
    }

    // Upload gambar baru
    if (
        isset($_FILES['gambar']) &&
        $_FILES['gambar']['error'] === UPLOAD_ERR_OK
    ) {
        $file = $_FILES['gambar'];

        $maxSize = 2 * 1024 * 1024;
        $allowed = [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif'
        ];

        if ($file['size'] > $maxSize) {
            jsonResponse([
                'success' => false,
                'message' => 'Ukuran gambar maksimal 2 MB'
            ], 400);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed)) {
            jsonResponse([
                'success' => false,
                'message' => 'Format gambar harus JPG, PNG, WebP, atau GIF'
            ], 400);
        }

        // Hapus gambar lama
        if ($produk['gambar']) {
            $oldPath = __DIR__ . '/../uploads/produk/' . $produk['gambar'];

            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $gambarName = uniqid('produk_') . '.' . $ext;

        $uploadDir = __DIR__ . '/../uploads/produk/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!move_uploaded_file(
            $file['tmp_name'],
            $uploadDir . $gambarName
        )) {
            jsonResponse([
                'success' => false,
                'message' => 'Gagal menyimpan gambar'
            ], 500);
        }
    }

    // Update database
    $stmt = $pdo->prepare("
        UPDATE tb_produk SET
            nama_produk = :nama_produk,
            deskripsi = :deskripsi,
            harga = :harga,
            stok = :stok,
            id_kategori = :id_kategori,
            gambar = :gambar,
            satuan = :satuan,
            barcode = :barcode
        WHERE id_produk = :id
    ");

    $stmt->execute([
        ':nama_produk' => $nama_produk,
        ':deskripsi' => $deskripsi,
        ':harga' => $harga,
        ':stok' => $stok,
        ':id_kategori' => $id_kategori,
        ':gambar' => $gambarName,
        ':satuan' => $satuan,
        ':barcode' => $barcode,
        ':id' => $id_produk
    ]);

    // Ambil data terbaru
    $stmt2 = $pdo->prepare("
        SELECT
            p.*,
            k.nama_kategori
        FROM tb_produk p
        JOIN tb_kategori k
            ON p.id_kategori = k.id_kategori
        WHERE p.id_produk = :id
    ");

    $stmt2->execute([
        ':id' => $id_produk
    ]);

    $updated = $stmt2->fetch();

    $updated['gambar_url'] = $updated['gambar']
        ? '/uploads/produk/' . $updated['gambar']
        : null;

    jsonResponse([
        'success' => true,
        'message' => 'Produk berhasil diperbarui',
        'data' => $updated
    ]);

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Gagal update: ' . $e->getMessage()
    ], 500);
}
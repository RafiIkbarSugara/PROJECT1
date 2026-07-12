<?php
// ============================================
// POST /api/process_checkout.php
// Proses transaksi (JSON body)
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();

// ===== WAJIB login sebagai kasir sebelum bisa memproses transaksi =====
// (sebelumnya endpoint ini bisa dipanggil tanpa login sama sekali)
if (!isset($_SESSION['admin_id'])) {
    jsonResponse(['success' => false, 'message' => 'Silakan login sebagai kasir terlebih dahulu.'], 403);
}

 $input = getJsonInput();

if (!$input || empty($input['items'])) {
    jsonResponse(['success' => false, 'message' => 'Data transaksi kosong'], 400);
}

 $items         = $input['items'];
 $diskon_persen = floatval($input['diskon_persen'] ?? 0);
 $pajak_persen  = floatval($input['pajak_persen'] ?? 11);
 $bayar         = intval($input['bayar'] ?? 0);
 $nama_kasir    = trim($_SESSION['admin_name']);
 $id_user       = $_SESSION['admin_id']; // kolom FK di tb_transaksi -> tb_users

try {
    $pdo->beginTransaction();

    // ===== Ambil harga ASLI dari database, jangan percaya harga dari client =====
    // (sebelumnya harga diambil langsung dari input request, sehingga bisa
    // direkayasa dari sisi client sebelum dikirim ke server)
    $idsProduk = array_map(fn($item) => intval($item['id_produk'] ?? 0), $items);
    $stmtHarga = $pdo->prepare("SELECT id_produk, nama_produk, harga FROM tb_produk WHERE id_produk = :id");
    $hargaAsli = [];
    foreach (array_unique($idsProduk) as $idProduk) {
        $stmtHarga->execute([':id' => $idProduk]);
        $row = $stmtHarga->fetch();
        if (!$row) {
            throw new Exception("Produk dengan ID $idProduk tidak ditemukan");
        }
        $hargaAsli[$idProduk] = $row;
    }

    // ===== Hitung (pakai harga dari database, bukan dari input) =====
    $subtotal = 0;
    foreach ($items as &$item) {
        $idProduk = intval($item['id_produk'] ?? 0);
        $item['harga'] = (int) $hargaAsli[$idProduk]['harga'];
        $item['nama_produk'] = $hargaAsli[$idProduk]['nama_produk'];
        $subtotal += $item['harga'] * intval($item['qty']);
    }
    unset($item);
    $diskon_rupiah = round($subtotal * $diskon_persen / 100);
    $after_diskon  = $subtotal - $diskon_rupiah;
    $pajak_rupiah  = round($after_diskon * $pajak_persen / 100);
    $total         = $after_diskon + $pajak_rupiah;
    $kembalian     = $bayar - $total;

    if ($bayar < $total) {
        jsonResponse(['success' => false, 'message' => 'Uang bayar kurang'], 400);
    }

    // ===== No transaksi =====
    $prefix = 'TRX' . date('Ymd');
    $stmtNo = $pdo->prepare("
        SELECT COUNT(*) + 1 AS next_num
        FROM tb_transaksi
        WHERE no_transaksi LIKE :prefix
    ");
    $stmtNo->execute([':prefix' => $prefix . '%']);
    $nextNum   = $stmtNo->fetchColumn();
    $noTrx     = $prefix . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

    // ===== Simpan transaksi =====
    $stmt = $pdo->prepare("
        INSERT INTO tb_transaksi
            (no_transaksi, subtotal, diskon_persen, diskon_rupiah,
             pajak_persen, pajak_rupiah, total, bayar, kembalian, nama_kasir, id_user)
        VALUES
            (:no_trx, :subtotal, :diskon_persen, :diskon_rupiah,
             :pajak_persen, :pajak_rupiah, :total, :bayar, :kembalian, :nama_kasir, :id_user)
    ");
    $stmt->execute([
        ':no_trx'        => $noTrx,
        ':subtotal'      => $subtotal,
        ':diskon_persen' => $diskon_persen,
        ':diskon_rupiah' => $diskon_rupiah,
        ':pajak_persen'  => $pajak_persen,
        ':pajak_rupiah'  => $pajak_rupiah,
        ':total'         => $total,
        ':bayar'         => $bayar,
        ':kembalian'     => $kembalian,
        ':nama_kasir'    => $nama_kasir,
        ':id_user'       => $id_user,
    ]);
    $idTrx = $pdo->lastInsertId();

    // ===== Simpan detail & update stok =====
    $stmtDetail = $pdo->prepare("
        INSERT INTO tb_detail_transaksi
            (id_transaksi, id_produk, nama_produk, harga, qty, subtotal)
        VALUES (:id_trx, :id_produk, :nama_produk, :harga, :qty, :subtotal)
    ");
    $stmtStok = $pdo->prepare("
        UPDATE tb_produk SET stok = stok - :qty
        WHERE id_produk = :id_produk AND stok >= :qty2
    ");

    foreach ($items as $item) {
        $itemSub = intval($item['harga']) * intval($item['qty']);

        $stmtDetail->execute([
            ':id_trx'      => $idTrx,
            ':id_produk'   => $item['id_produk'],
            ':nama_produk' => $item['nama_produk'],
            ':harga'       => $item['harga'],
            ':qty'         => $item['qty'],
            ':subtotal'    => $itemSub,
        ]);

        $stmtStok->execute([
            ':qty'       => $item['qty'],
            ':id_produk' => $item['id_produk'],
            ':qty2'      => $item['qty'],
        ]);

        if ($stmtStok->rowCount() === 0) {
            throw new Exception("Stok '{$item['nama_produk']}' tidak mencukupi");
        }
    }

    $pdo->commit();

    jsonResponse([
        'success'      => true,
        'message'      => 'Transaksi berhasil',
        'no_transaksi' => $noTrx,
        'id_transaksi' => $idTrx,
        'subtotal'     => $subtotal,
        'diskon_rupiah'=> $diskon_rupiah,
        'pajak_rupiah' => $pajak_rupiah,
        'total'        => $total,
        'bayar'        => $bayar,
        'kembalian'    => $kembalian,
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    jsonResponse([
        'success' => false,
        'message' => 'Transaksi gagal: ' . $e->getMessage()
    ], 500);
}
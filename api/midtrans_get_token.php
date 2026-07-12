<?php
require_once __DIR__ . '/../config/database.php';

session_start();



if (!isset($_SESSION['customer_id'])) {
    jsonResponse(['success' => false, 'message' => 'Akses ditolak'], 403);
}

 $input = getJsonInput();
if (!$input) jsonResponse(['success' => false, 'message' => 'Data tidak valid'], 400);

 $items = $input['items'] ?? [];
if (empty($items)) jsonResponse(['success' => false, 'message' => 'Keranjang kosong'], 400);

// ===== Data pengiriman (opsional, default ambil di toko) =====
 $metode_pengiriman = ($input['metode_pengiriman'] ?? 'pickup') === 'delivery' ? 'delivery' : 'pickup';
 $nama_penerima     = trim($input['nama_penerima'] ?? '');
 $no_telp_penerima  = trim($input['no_telp_penerima'] ?? '');
 $alamat_pengiriman = trim($input['alamat_pengiriman'] ?? '');
 $simpan_alamat     = !empty($input['simpan_alamat']);
 $latitude          = isset($input['latitude']) && $input['latitude'] !== null ? floatval($input['latitude']) : null;
 $longitude         = isset($input['longitude']) && $input['longitude'] !== null ? floatval($input['longitude']) : null;

if ($metode_pengiriman === 'delivery') {
    if ($nama_penerima === '' || $no_telp_penerima === '' || $alamat_pengiriman === '') {
        jsonResponse(['success' => false, 'message' => 'Nama penerima, no. telepon, dan alamat wajib diisi untuk pengiriman'], 400);
    }
}

try {
    $pdo->beginTransaction();

    // ===== Ambil harga ASLI dari database, jangan percaya harga dari client =====
    // (sebelumnya harga & nama produk diambil langsung dari input request,
    // sehingga customer bisa mengubah harga di localStorage/DevTools sebelum
    // membayar via Midtrans)
    $idsProduk = array_map(fn($item) => intval($item['id_produk'] ?? 0), $items);
    $stmtHarga = $pdo->prepare("SELECT id_produk, nama_produk, harga FROM tb_produk WHERE id_produk = :id");
    $hargaAsli = [];
    foreach (array_unique($idsProduk) as $idProduk) {
        $stmtHarga->execute([':id' => $idProduk]);
        $row = $stmtHarga->fetch();
        if (!$row) {
            jsonResponse(['success' => false, 'message' => "Produk dengan ID $idProduk tidak ditemukan"], 400);
        }
        $hargaAsli[$idProduk] = $row;
    }

    // 1. Hitung total & siapkan item details (pakai harga dari database)
    $subtotal = 0;
    $item_details = [];
    foreach ($items as &$item) {
        $idProduk = intval($item['id_produk'] ?? 0);
        $item['harga'] = (int) $hargaAsli[$idProduk]['harga'];
        $item['nama_produk'] = $hargaAsli[$idProduk]['nama_produk'];

        $sub = $item['harga'] * intval($item['qty']);
        $subtotal += $sub;
        $item_details[] = [
            'id'       => (string)$item['id_produk'],
            'price'    => $item['harga'],
            'quantity' => intval($item['qty']),
            'name'     => substr($item['nama_produk'], 0, 50) // Max 50 char for Midtrans
        ];
    }
    unset($item);

    $pajak_persen = 0;
    $pajak_rupiah = round($subtotal * $pajak_persen / 100);
    $total = $subtotal + $pajak_rupiah;

    // 2. Buat No Transaksi Unik (Midtrans butuh order_id unik)
    $prefix = 'TRX' . date('Ymd');
    $stmtNo = $pdo->prepare("SELECT COUNT(*) + 1 AS next_num FROM tb_transaksi WHERE no_transaksi LIKE :prefix");
    $stmtNo->execute([':prefix' => $prefix . '%']);
    $nextNum = $stmtNo->fetchColumn();
    $noTrx = $prefix . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

    // 3. Simpan ke database dengan status pending
    $stmt = $pdo->prepare("
        INSERT INTO tb_transaksi 
        (no_transaksi, subtotal, pajak_persen, pajak_rupiah, total, bayar, kembalian, nama_kasir, id_user, metode_bayar, status_bayar,
         metode_pengiriman, nama_penerima, no_telp_penerima, alamat_pengiriman, latitude, longitude)
        VALUES 
        (:no_trx, :subtotal, :pajak_persen, :pajak_rupiah, :total, 0, 0, 'Self-Service', :id_user, 'midtrans', 'pending',
         :metode_pengiriman, :nama_penerima, :no_telp_penerima, :alamat_pengiriman, :latitude, :longitude)
    ");
    $stmt->execute([
        ':no_trx' => $noTrx, ':subtotal' => $subtotal,
        ':pajak_persen' => $pajak_persen, ':pajak_rupiah' => $pajak_rupiah, ':total' => $total,
        ':id_user' => $_SESSION['customer_id'],
        ':metode_pengiriman' => $metode_pengiriman,
        ':nama_penerima' => $nama_penerima ?: null,
        ':no_telp_penerima' => $no_telp_penerima ?: null,
        ':alamat_pengiriman' => $alamat_pengiriman ?: null,
        ':latitude' => $latitude,
        ':longitude' => $longitude,
    ]);
    $idTrx = $pdo->lastInsertId();

    // Simpan sebagai alamat default pelanggan, jika diminta
    if ($metode_pengiriman === 'delivery' && $simpan_alamat) {
        $stmtAddr = $pdo->prepare("UPDATE tb_users SET alamat_default = :alamat, no_telp_default = :telp, latitude_default = :lat, longitude_default = :lng WHERE id_user = :id");
        $stmtAddr->execute([
            ':alamat' => $alamat_pengiriman,
            ':telp'   => $no_telp_penerima,
            ':lat'    => $latitude,
            ':lng'    => $longitude,
            ':id'     => $_SESSION['customer_id'],
        ]);
    }

    // Simpan detail
    $stmtDetail = $pdo->prepare("INSERT INTO tb_detail_transaksi (id_transaksi, id_produk, nama_produk, harga, qty, subtotal) VALUES (:id_trx, :id_produk, :nama_produk, :harga, :qty, :subtotal)");
    foreach ($items as $item) {
        $itemSub = intval($item['harga']) * intval($item['qty']);
        $stmtDetail->execute([
            ':id_trx' => $idTrx, ':id_produk' => $item['id_produk'],
            ':nama_produk' => $item['nama_produk'], ':harga' => $item['harga'],
            ':qty' => $item['qty'], ':subtotal' => $itemSub
        ]);
    }

    $pdo->commit();

    // 4. Request Token ke Midtrans via CURL
    $params = [
        'transaction_details' => [
            'order_id'     => $noTrx,
            'gross_amount' => $total
        ],
        'item_details' => $item_details,
'customer_details' => [
    'first_name' => $metode_pengiriman === 'delivery' && $nama_penerima ? $nama_penerima : $_SESSION['customer_name'],
    'phone'      => $no_telp_penerima ?: null,
    'email' => $_SESSION['customer_username'] . '@example.com',
    'shipping_address' => $metode_pengiriman === 'delivery' ? [
        'first_name' => $nama_penerima,
        'phone'      => $no_telp_penerima,
        'address'    => $alamat_pengiriman,
    ] : null
        ]
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => "https://app.sandbox.midtrans.com/snap/v1/transactions",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => "",
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => "POST",
        CURLOPT_POSTFIELDS     => json_encode($params),
        CURLOPT_HTTPHEADER     => [
            "Content-Type: application/json",
            "Accept: application/json",
            "Authorization: Basic " . base64_encode(MIDTRANS_SERVER_KEY . ":")
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        jsonResponse(['success' => false, 'message' => "Gagal koneksi ke Midtrans: #$err"], 500);
    } else {
        $midtransResp = json_decode($response, true);
        if (isset($midtransResp['token'])) {
            jsonResponse([
                'success' => true,
                'token'   => $midtransResp['token'],
                'order_id' => $noTrx
            ]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Gagal mendapatkan token Midtrans', 'midtrans_error' => $midtransResp], 400);
        }
    }

} catch (Exception $e) {
    $pdo->rollBack();
    jsonResponse(['success' => false, 'message' => 'Error DB: ' . $e->getMessage()], 500);
}
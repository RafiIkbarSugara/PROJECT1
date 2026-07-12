<?php
require_once __DIR__ . '/../config/database.php';

// Midtrans mengirim POST JSON
 $raw = file_get_contents('php://input');
 $notification = json_decode($raw, true);

if (!$notification) exit;

 $transaction_status = $notification['transaction_status'] ?? '';
 $order_id           = $notification['order_id'] ?? '';
 $fraud_status       = $notification['fraud_status'] ?? 'accept';

// Validasi Signature Key
 $signature_key = $notification['signature_key'] ?? '';
 $hash = hash('sha512', $order_id . $notification['status_code'] . $notification['gross_amount'] . MIDTRANS_SERVER_KEY);

if ($signature_key !== $hash) {
    http_response_code(403);
    exit('Invalid signature');
}

try {
    if ($transaction_status == 'capture') {
        if ($fraud_status == 'challenge') {
            $status_bayar = 'pending';
        } else if ($fraud_status == 'accept') {
            $status_bayar = 'lunas';
        }
    } else if ($transaction_status == 'settlement') {
        $status_bayar = 'lunas';
    } else if ($transaction_status == 'cancel' || $transaction_status == 'deny' || $transaction_status == 'expire') {
        $status_bayar = 'gagal';
    } else if ($transaction_status == 'pending') {
        $status_bayar = 'pending';
    }

    if (isset($status_bayar)) {
        $stmt = $pdo->prepare("UPDATE tb_transaksi SET status_bayar = :status WHERE no_transaksi = :order_id");
        $stmt->execute([':status' => $status_bayar, ':order_id' => $order_id]);
    }

    // Beritahu Midtrans bahwa notifikasi diterima
    http_response_code(200);
    echo "OK";

} catch (Exception $e) {
    http_response_code(500);
}
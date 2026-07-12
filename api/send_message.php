<?php
// ============================================
// POST /api/send_message.php
// Simpan pesan dari form Kontak (contact.php)
// Body JSON: { nama, email, subjek, isi_pesan }
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
}

$input = getJsonInput();
if (!$input) {
    jsonResponse(['success' => false, 'message' => 'Data tidak valid'], 400);
}

$nama      = trim($input['nama'] ?? '');
$email     = trim($input['email'] ?? '');
$subjek    = trim($input['subjek'] ?? '');
$isi_pesan = trim($input['isi_pesan'] ?? '');
$id_user   = $_SESSION['customer_id'] ?? null;

if ($nama === '' || $email === '' || $subjek === '' || $isi_pesan === '') {
    jsonResponse(['success' => false, 'message' => 'Semua field wajib diisi'], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Format email tidak valid'], 400);
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO tb_pesan (id_user, nama, email, subjek, isi_pesan)
        VALUES (:id_user, :nama, :email, :subjek, :isi_pesan)
    ");
    $stmt->execute([
        ':id_user'   => $id_user,
        ':nama'      => $nama,
        ':email'     => $email,
        ':subjek'    => $subjek,
        ':isi_pesan' => $isi_pesan,
    ]);

    jsonResponse([
        'success' => true,
        'message' => 'Pesan berhasil dikirim. Terima kasih telah menghubungi kami!'
    ]);

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Gagal mengirim pesan: ' . $e->getMessage()
    ], 500);
}
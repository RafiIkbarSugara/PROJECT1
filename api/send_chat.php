<?php
// ============================================
// POST /api/send_chat.php
// Kirim pesan chat. Bisa dipakai pelanggan (role 'user') atau
// kasir (role 'kasir').
// Body JSON pelanggan: { isi_pesan }              -> id_user = dirinya sendiri
// Body JSON kasir:     { isi_pesan, id_user }      -> id_user = thread tujuan
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();

if (!isset($_SESSION['admin_id'])) {
    jsonResponse(['success' => false, 'message' => 'Anda harus login'], 401);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
}

$input     = getJsonInput();
$isi_pesan = trim($input['isi_pesan'] ?? '');

if ($isi_pesan === '') {
    jsonResponse(['success' => false, 'message' => 'Pesan tidak boleh kosong'], 400);
}

$role = $_SESSION['role'];

if ($role === 'user') {
    // Pelanggan selalu kirim ke thread miliknya sendiri
    $id_user  = $_SESSION['admin_id'];
    $pengirim = 'user';
} elseif ($role === 'kasir') {
    // Kasir wajib sebutkan thread pelanggan mana yang dibalas
    $id_user = intval($input['id_user'] ?? 0);
    if ($id_user <= 0) {
        jsonResponse(['success' => false, 'message' => 'id_user (thread tujuan) wajib diisi'], 400);
    }
    $pengirim = 'kasir';
} else {
    jsonResponse(['success' => false, 'message' => 'Role tidak dikenali'], 403);
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO tb_chat (id_user, pengirim, isi_pesan)
        VALUES (:id_user, :pengirim, :isi_pesan)
    ");
    $stmt->execute([
        ':id_user'   => $id_user,
        ':pengirim'  => $pengirim,
        ':isi_pesan' => $isi_pesan,
    ]);

    jsonResponse([
        'success'  => true,
        'id_chat'  => $pdo->lastInsertId(),
        'pengirim' => $pengirim,
        'isi_pesan'=> $isi_pesan,
        'created_at' => date('Y-m-d H:i:s'),
    ]);

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Gagal mengirim pesan: ' . $e->getMessage()
    ], 500);
}
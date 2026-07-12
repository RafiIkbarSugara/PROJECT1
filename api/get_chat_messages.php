<?php
// ============================================
// GET /api/get_chat_messages.php
// Ambil pesan dalam 1 thread chat. Dipakai untuk polling.
//
// Pelanggan (role 'user'): otomatis ambil thread miliknya sendiri.
// Kasir (role 'kasir'): wajib kirim ?id_user=X untuk pilih thread.
//
// Params: ?since_id=10     -> hanya pesan dengan id_chat > since_id
//         ?mark_read=0     -> jangan tandai dibaca (dipakai polling
//                             badge di background saat panel TERTUTUP,
//                             supaya badge notifikasi tidak hilang
//                             sebelum pesan benar-benar dibuka/dibaca)
//         Default mark_read=1 (saat thread/panel sedang dibuka)
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();
if (!isset($_SESSION['admin_id'])) {
    jsonResponse(['success' => false, 'message' => 'Anda harus login'], 401);
}

$role = $_SESSION['role'];

if ($role === 'user') {
    $id_user = $_SESSION['admin_id'];
} elseif ($role === 'kasir') {
    $id_user = intval($_GET['id_user'] ?? 0);
    if ($id_user <= 0) {
        jsonResponse(['success' => false, 'message' => 'id_user wajib diisi'], 400);
    }
} else {
    jsonResponse(['success' => false, 'message' => 'Role tidak dikenali'], 403);
}

$since_id  = intval($_GET['since_id'] ?? 0);
$markRead  = ($_GET['mark_read'] ?? '1') !== '0';

try {
    if ($since_id > 0) {
        $stmt = $pdo->prepare("
            SELECT id_chat, id_user, pengirim, isi_pesan, status, created_at
            FROM tb_chat
            WHERE id_user = :id_user AND id_chat > :since_id
            ORDER BY id_chat ASC
        ");
        $stmt->execute([':id_user' => $id_user, ':since_id' => $since_id]);
    } else {
        $stmt = $pdo->prepare("
            SELECT id_chat, id_user, pengirim, isi_pesan, status, created_at
            FROM tb_chat
            WHERE id_user = :id_user
            ORDER BY id_chat ASC
        ");
        $stmt->execute([':id_user' => $id_user]);
    }
    $messages = $stmt->fetchAll();

    // Tandai pesan dari lawan bicara sebagai sudah dibaca — HANYA kalau
    // thread/panel benar-benar sedang dibuka (mark_read=1, default).
    // Polling background saat panel tertutup pakai mark_read=0 supaya
    // badge notifikasi tidak hilang sebelum pesan benar-benar dibaca.
    if ($markRead) {
        $lawanBicara = $role === 'user' ? 'kasir' : 'user';
        $stmtRead = $pdo->prepare("
            UPDATE tb_chat SET status = 'sudah_dibaca'
            WHERE id_user = :id_user AND pengirim = :lawan AND status = 'belum_dibaca'
        ");
        $stmtRead->execute([':id_user' => $id_user, ':lawan' => $lawanBicara]);
    }

    jsonResponse([
        'success' => true,
        'data'    => $messages,
    ]);

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Gagal mengambil pesan: ' . $e->getMessage()
    ], 500);
}
<?php
// ============================================
// GET /api/get_chat_unread_count.php
// Badge notifikasi chat — dipakai DUA arah:
// - Kasir (role 'kasir'): total pesan dari SEMUA pelanggan yang belum dibaca
// - Pelanggan (role 'user'): jumlah balasan kasir di thread-nya yang belum dibaca
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();
if (!isset($_SESSION['admin_id'])) {
    jsonResponse(['success' => false, 'message' => 'Anda harus login'], 401);
}

$role = $_SESSION['role'];

try {
    if ($role === 'kasir') {
        $count = $pdo->query("
            SELECT COUNT(*) FROM tb_chat WHERE pengirim = 'user' AND status = 'belum_dibaca'
        ")->fetchColumn();
    } elseif ($role === 'user') {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM tb_chat
            WHERE id_user = :id_user AND pengirim = 'kasir' AND status = 'belum_dibaca'
        ");
        $stmt->execute([':id_user' => $_SESSION['admin_id']]);
        $count = $stmt->fetchColumn();
    } else {
        jsonResponse(['success' => false, 'message' => 'Role tidak dikenali'], 403);
    }

    jsonResponse([
        'success'      => true,
        'unread_count' => (int)$count,
    ]);

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Gagal mengambil jumlah chat: ' . $e->getMessage()
    ], 500);
}
<?php
// ============================================
// GET /api/get_chat_list.php
// Daftar semua thread chat (kotak masuk kasir) — 1 baris per
// pelanggan, menampilkan pesan terakhir & jumlah belum dibaca.
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'kasir') {
    jsonResponse(['success' => false, 'message' => 'Akses ditolak'], 403);
}

try {
    // Ambil 1 pesan terakhir per id_user, plus jumlah belum dibaca dari pelanggan
    $stmt = $pdo->query("
        SELECT
            u.id_user,
            u.nama_lengkap,
            u.username,
            last_msg.isi_pesan AS last_message,
            last_msg.pengirim AS last_sender,
            last_msg.created_at AS last_time,
            (SELECT COUNT(*) FROM tb_chat c2
             WHERE c2.id_user = u.id_user AND c2.pengirim = 'user' AND c2.status = 'belum_dibaca'
            ) AS unread_count
        FROM tb_users u
        JOIN (
            SELECT c1.id_user, c1.isi_pesan, c1.pengirim, c1.created_at
            FROM tb_chat c1
            WHERE c1.id_chat = (
                SELECT MAX(c3.id_chat) FROM tb_chat c3 WHERE c3.id_user = c1.id_user
            )
        ) last_msg ON last_msg.id_user = u.id_user
        ORDER BY last_msg.created_at DESC
    ");
    $threads = $stmt->fetchAll();

    $totalUnread = $pdo->query("
        SELECT COUNT(*) FROM tb_chat WHERE pengirim = 'user' AND status = 'belum_dibaca'
    ")->fetchColumn();

    jsonResponse([
        'success'      => true,
        'data'         => $threads,
        'unread_count' => (int)$totalUnread,
    ]);

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Gagal mengambil daftar chat: ' . $e->getMessage()
    ], 500);
}
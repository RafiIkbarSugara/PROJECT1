<?php
// ============================================
// GET /api/get_messages.php
// Daftar pesan dari contact.php, untuk dibaca kasir.
// Params: ?page=1
// Selalu mengembalikan unread_count (dipakai badge sidebar).
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'kasir') {
    jsonResponse(['success' => false, 'message' => 'Akses ditolak'], 403);
}

try {
    $page    = max(1, intval($_GET['page'] ?? 1));
    $perPage = 15;
    $offset  = ($page - 1) * $perPage;

    $total = $pdo->query("SELECT COUNT(*) FROM tb_pesan")->fetchColumn();

    $stmt = $pdo->query("
        SELECT id_pesan, id_user, nama, email, subjek, isi_pesan, status, created_at
        FROM tb_pesan
        ORDER BY created_at DESC
        LIMIT $perPage OFFSET $offset
    ");
    $pesan = $stmt->fetchAll();

    $unreadCount = $pdo->query("SELECT COUNT(*) FROM tb_pesan WHERE status = 'belum_dibaca'")->fetchColumn();

    jsonResponse([
        'success'       => true,
        'data'          => $pesan,
        'total'         => $total,
        'page'          => $page,
        'pages'         => ceil($total / $perPage),
        'unread_count'  => (int)$unreadCount,
    ]);

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Gagal mengambil pesan: ' . $e->getMessage()
    ], 500);
}
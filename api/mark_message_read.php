<?php
// ============================================
// POST /api/mark_message_read.php
// Tandai pesan sebagai sudah dibaca.
// Body JSON: { "id_pesan": 1 }
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'kasir') {
    jsonResponse(['success' => false, 'message' => 'Akses ditolak'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
}

$input = getJsonInput();
$id_pesan = intval($input['id_pesan'] ?? 0);

if ($id_pesan <= 0) {
    jsonResponse(['success' => false, 'message' => 'ID pesan tidak valid'], 400);
}

try {
    $stmt = $pdo->prepare("UPDATE tb_pesan SET status = 'sudah_dibaca' WHERE id_pesan = :id");
    $stmt->execute([':id' => $id_pesan]);

    jsonResponse(['success' => true, 'message' => 'Pesan ditandai sudah dibaca']);

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Gagal menandai pesan: ' . $e->getMessage()
    ], 500);
}
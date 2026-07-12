<?php
// ============================================
// POST /api/delete_kasir.php
// Hapus akun kasir — dengan pengaman:
// 1. Wajib login sebagai kasir
// 2. Tidak boleh hapus akun sendiri (hindari kelupaan tanpa akses)
// 3. Tidak boleh hapus kasir terakhir (hindari lockout total)
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'kasir') {
    jsonResponse(['success' => false, 'message' => 'Akses ditolak. Silakan login sebagai kasir.'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
}

$input = getJsonInput();
$id_user = intval($input['id_user'] ?? 0);

if ($id_user <= 0) {
    jsonResponse(['success' => false, 'message' => 'ID kasir tidak valid'], 400);
}

// ===== Cegah hapus akun sendiri =====
if ($id_user === (int) $_SESSION['admin_id']) {
    jsonResponse(['success' => false, 'message' => 'Tidak bisa menghapus akun Anda sendiri'], 400);
}

try {
    // Pastikan target memang kasir (bukan akun pelanggan)
    $stmt = $pdo->prepare("SELECT id_user FROM tb_users WHERE id_user = :id AND role = 'kasir'");
    $stmt->execute([':id' => $id_user]);
    if (!$stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Akun kasir tidak ditemukan'], 404);
    }

    // ===== Cegah kasir terakhir terhapus (hindari lockout total) =====
    $count = $pdo->query("SELECT COUNT(*) AS total FROM tb_users WHERE role = 'kasir'")->fetch();
    if ($count['total'] <= 1) {
        jsonResponse(['success' => false, 'message' => 'Tidak bisa menghapus kasir terakhir yang tersisa'], 400);
    }

    $del = $pdo->prepare("DELETE FROM tb_users WHERE id_user = :id AND role = 'kasir'");
    $del->execute([':id' => $id_user]);

    jsonResponse(['success' => true, 'message' => 'Akun kasir berhasil dihapus']);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Gagal menghapus akun kasir'], 500);
}

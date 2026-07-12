<?php
// ============================================
// GET /api/get_kasir.php
// Ambil daftar akun kasir (khusus role kasir yang login)
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();

// ===== Proteksi: hanya kasir yang sudah login yang boleh akses =====
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'kasir') {
    jsonResponse(['success' => false, 'message' => 'Akses ditolak. Silakan login sebagai kasir.'], 403);
}

try {
    // Jangan pernah SELECT kolom password ke response
    $stmt = $pdo->query("
        SELECT id_user, nama_lengkap, username, role, created_at
        FROM tb_users
        WHERE role = 'kasir'
        ORDER BY created_at DESC
    ");
    $kasirList = $stmt->fetchAll();

    jsonResponse(['success' => true, 'data' => $kasirList]);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Gagal mengambil data kasir'], 500);
}

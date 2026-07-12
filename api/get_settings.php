<?php
require_once __DIR__ . '/../config/database.php';

session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'kasir') {
    jsonResponse(['success' => false, 'message' => 'Akses ditolak. Silakan login sebagai kasir.'], 403);
}

try {
    $stmt = $pdo->query("SELECT nama_pengaturan, nilai FROM tb_pengaturan");
    $rows = $stmt->fetchAll();
    
    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['nama_pengaturan']] = $row['nilai'];
    }
    
    jsonResponse(['success' => true, 'data' => $settings]);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Gagal mengambil pengaturan: ' . $e->getMessage()], 500);
}
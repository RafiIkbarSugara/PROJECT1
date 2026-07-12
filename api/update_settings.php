<?php
require_once __DIR__ . '/../config/database.php';

session_start();
// Hanya admin yang boleh mengubah pengaturan (opsional, uncomment jika mau)
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//     jsonResponse(['success' => false, 'message' => 'Akses ditolak'], 403);
// }

 $input = getJsonInput();
if (!$input) {
    jsonResponse(['success' => false, 'message' => 'Data tidak valid'], 400);
}

try {
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("
        INSERT INTO tb_pengaturan (nama_pengaturan, nilai) 
        VALUES (:name, :value) 
        ON DUPLICATE KEY UPDATE nilai = :value2
    ");
    
    foreach ($input as $key => $value) {
        $stmt->execute([
            ':name' => $key,
            ':value' => $value,
            ':value2' => $value
        ]);
    }
    
    $pdo->commit();
    jsonResponse(['success' => true, 'message' => 'Pengaturan berhasil disimpan']);
} catch (Exception $e) {
    $pdo->rollBack();
    jsonResponse(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
}
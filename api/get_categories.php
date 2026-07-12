<?php
// ============================================
// GET /api/get_categories.php
// ============================================

require_once __DIR__ . '/../config/database.php';

try {
    $stmt = $pdo->query("
        SELECT id_kategori, nama_kategori, urutan
        FROM tb_kategori
        ORDER BY urutan ASC
    ");
    $categories = $stmt->fetchAll();

    jsonResponse([
        'success' => true,
        'data'    => $categories
    ]);

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Gagal mengambil kategori: ' . $e->getMessage()
    ], 500);
}
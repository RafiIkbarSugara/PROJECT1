<?php
// ============================================
// KasirKu POS — Konfigurasi Database
// ============================================


 $DB_HOST = 'localhost';
 $DB_NAME = 'kasirku_db';
 $DB_USER = 'root';
 $DB_PASS = '';        // ← Sesuaikan password MySQL Anda

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // Jika koneksi gagal, kirim JSON error
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Koneksi database gagal: ' . $e->getMessage()
    ]);
    exit;
}

// =================== MIDTRANS CONFIG ===================
define('MIDTRANS_SERVER_KEY', 'Mid-server-E0Umimf0W6L_Vr797_0f_1IJ');
define('MIDTRANS_CLIENT_KEY', 'Mid-client-PUqQ7xl0mjmRJ_XR');
define('MIDTRANS_IS_PRODUCTION', false); // Ubah jadi true kalau sudah tayang

/** Kirim response JSON dan hentikan eksekusi */
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Baca input JSON dari request body */
function getJsonInput() {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true);
}
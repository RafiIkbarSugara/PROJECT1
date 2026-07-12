<?php
session_start();
header('Content-Type: application/json');


$adaSesi = isset($_SESSION['customer_id']) || isset($_SESSION['admin_id']);

unset(
    $_SESSION['customer_id'],
    $_SESSION['customer_name'],
    $_SESSION['customer_username'],
    $_SESSION['admin_id'],
    $_SESSION['admin_name'],
    $_SESSION['admin_username'],
    $_SESSION['admin_role'],
    $_SESSION['role']
);
session_destroy();

echo json_encode([
    'success' => true,
    'message' => $adaSesi ? 'Logout berhasil' : 'Tidak ada sesi aktif'
]);
exit;
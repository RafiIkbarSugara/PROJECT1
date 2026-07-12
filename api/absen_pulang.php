<?php
// ============================================
// POST /api/absen_pulang.php
// Catat jam pulang kasir yang sedang login.
// Hanya bisa mengisi jam_pulang jika masih NULL (sekali saja).
// Trigger di database juga mengunci ini sebagai lapisan kedua.
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'kasir') {
    jsonResponse(['success' => false, 'message' => 'Akses ditolak. Silakan login sebagai kasir.'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
}

$id_user = (int) $_SESSION['admin_id'];

try {
    $cek = $pdo->prepare("SELECT id_absensi, jam_masuk, jam_pulang FROM tb_absensi WHERE id_user = :id AND tanggal = CURDATE()");
    $cek->execute([':id' => $id_user]);
    $today = $cek->fetch();

    if (!$today) {
        jsonResponse(['success' => false, 'message' => 'Anda belum absen masuk hari ini'], 400);
    }

    if ($today['jam_pulang'] !== null) {
        jsonResponse([
            'success' => false,
            'message' => 'Anda sudah absen pulang hari ini pukul ' . date('H:i', strtotime($today['jam_pulang']))
        ], 409);
    }

    // Update ini HANYA valid selama jam_pulang masih NULL — begitu
    // WHERE tidak cocok lagi (sudah terisi), UPDATE tidak akan mengenai baris manapun.
    // Trigger trg_absensi_lock_update di database jadi pengaman tambahan
    // kalau ada percobaan update dari luar alur normal ini.
    $stmt = $pdo->prepare("
        UPDATE tb_absensi
        SET jam_pulang = NOW()
        WHERE id_user = :id AND tanggal = CURDATE() AND jam_pulang IS NULL
    ");
    $stmt->execute([':id' => $id_user]);

    if ($stmt->rowCount() === 0) {
        jsonResponse(['success' => false, 'message' => 'Absen pulang gagal, kemungkinan sudah tercatat sebelumnya'], 409);
    }

    jsonResponse([
        'success' => true,
        'message' => 'Absen pulang berhasil dicatat',
        'data' => ['jam_pulang' => date('H:i:s')]
    ]);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Gagal mencatat absen pulang'], 500);
}

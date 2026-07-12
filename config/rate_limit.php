<?php
// ============================================
// config/rate_limit.php
// Helper proteksi brute force untuk login.
// Mengunci sementara berdasarkan USERNAME (mencegah brute force
// 1 akun tertentu) dan berdasarkan IP (mencegah 1 sumber mencoba
// banyak username sekaligus / credential stuffing).
// ============================================

// ---- Ambang batas, silakan disesuaikan ----
define('LOGIN_MAX_ATTEMPTS_USERNAME', 5);   // maks percobaan gagal per username
define('LOGIN_WINDOW_USERNAME_MIN', 15);    // dalam rentang berapa menit
define('LOGIN_LOCKOUT_USERNAME_MIN', 15);   // dikunci berapa menit

define('LOGIN_MAX_ATTEMPTS_IP', 20);        // maks percobaan gagal per IP (semua username)
define('LOGIN_WINDOW_IP_MIN', 15);
define('LOGIN_LOCKOUT_IP_MIN', 30);

function getClientIp(): string {
    // Untuk deployment di belakang reverse proxy/load balancer, sesuaikan
    // dengan header yang benar-benar bisa dipercaya dari infrastruktur Anda.
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * Cek apakah username atau IP sedang dalam status lockout.
 * Return: ['locked' => bool, 'reason' => 'username'|'ip'|null, 'retry_after_min' => int]
 */
function checkLoginLockout(PDO $pdo, string $username, string $ip): array {
    // ----- Cek lockout per username -----
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS jumlah
        FROM tb_login_attempts
        WHERE username = :username
          AND berhasil = 0
          AND waktu > (NOW() - INTERVAL " . LOGIN_WINDOW_USERNAME_MIN . " MINUTE)
    ");
    $stmt->execute([':username' => $username]);
    $jumlahUser = (int) $stmt->fetchColumn();

    if ($jumlahUser >= LOGIN_MAX_ATTEMPTS_USERNAME) {
        return ['locked' => true, 'reason' => 'username', 'retry_after_min' => LOGIN_LOCKOUT_USERNAME_MIN];
    }

    // ----- Cek lockout per IP (lintas username, indikasi credential stuffing) -----
    $stmt2 = $pdo->prepare("
        SELECT COUNT(*) AS jumlah
        FROM tb_login_attempts
        WHERE ip_address = :ip
          AND berhasil = 0
          AND waktu > (NOW() - INTERVAL " . LOGIN_WINDOW_IP_MIN . " MINUTE)
    ");
    $stmt2->execute([':ip' => $ip]);
    $jumlahIp = (int) $stmt2->fetchColumn();

    if ($jumlahIp >= LOGIN_MAX_ATTEMPTS_IP) {
        return ['locked' => true, 'reason' => 'ip', 'retry_after_min' => LOGIN_LOCKOUT_IP_MIN];
    }

    return ['locked' => false, 'reason' => null, 'retry_after_min' => 0];
}

/** Catat satu percobaan login (berhasil atau gagal) */
function recordLoginAttempt(PDO $pdo, string $username, string $ip, bool $berhasil): void {
    $stmt = $pdo->prepare("
        INSERT INTO tb_login_attempts (username, ip_address, berhasil)
        VALUES (:username, :ip, :berhasil)
    ");
    $stmt->execute([':username' => $username, ':ip' => $ip, ':berhasil' => $berhasil ? 1 : 0]);

    // Bersih-bersih ringan: buang catatan lebih dari 1 hari (1 dari ~50 request saja,
    // supaya tidak membebani setiap request tapi tabel tetap terjaga ukurannya)
    if (random_int(1, 50) === 1) {
        $pdo->exec("DELETE FROM tb_login_attempts WHERE waktu < (NOW() - INTERVAL 1 DAY)");
    }
}

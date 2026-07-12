-- ============================================
-- FIRAJAYA POS — FULL DATABASE SCHEMA
-- Dibuat: 2026-07-04
-- Ini adalah kumpulan LENGKAP semua tabel yang dipakai aplikasi:
-- users, pengaturan, kategori, produk, transaksi, pesan, chat,
-- absensi (+koreksi), dan proteksi login (rate limit).
--
-- CARA PAKAI:
-- - Database BARU/KOSONG   -> jalankan file ini dari awal sampai akhir, aman.
-- - Database SUDAH ADA ISI -> juga aman dijalankan ulang (semua pakai
--   CREATE TABLE IF NOT EXISTS / INSERT IGNORE), tidak akan menghapus
--   atau menimpa data yang sudah ada.
-- - TIDAK ADA data dummy/contoh produk & kategori di sini — silakan
--   input sendiri lewat aplikasi setelah database siap.
-- ============================================

CREATE DATABASE IF NOT EXISTS kasirku_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE kasirku_db;

-- ============================================
-- TABEL USERS (login & register)
-- ============================================
CREATE TABLE IF NOT EXISTS tb_users (
    id_user      INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    username     VARCHAR(50)  NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,
    role         ENUM('kasir','user') NOT NULL DEFAULT 'user',
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABEL PENGATURAN (settings.php)
-- ============================================
CREATE TABLE IF NOT EXISTS tb_pengaturan (
    nama_pengaturan VARCHAR(50)  NOT NULL PRIMARY KEY,
    nilai           VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABEL KATEGORI
-- ============================================
CREATE TABLE IF NOT EXISTS tb_kategori (
    id_kategori   INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50)  NOT NULL,
    urutan        INT          DEFAULT 0,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABEL PRODUK
-- ============================================
CREATE TABLE IF NOT EXISTS tb_produk (
    id_produk   INT AUTO_INCREMENT PRIMARY KEY,
    nama_produk VARCHAR(100) NOT NULL,
    deskripsi   TEXT         DEFAULT NULL,
    harga       INT          NOT NULL,
    stok        INT          NOT NULL DEFAULT 0,
    id_kategori INT          NOT NULL,
    gambar      VARCHAR(255) DEFAULT NULL,
    satuan      VARCHAR(20)  DEFAULT 'pcs',
    barcode     VARCHAR(50)  DEFAULT NULL,
    status      ENUM('aktif','nonaktif') DEFAULT 'aktif',
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kategori) REFERENCES tb_kategori(id_kategori)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABEL TRANSAKSI
-- (metode_bayar & status_bayar dipakai oleh fitur Midtrans)
-- ============================================
CREATE TABLE IF NOT EXISTS tb_transaksi (
    id_transaksi  INT AUTO_INCREMENT PRIMARY KEY,
    no_transaksi  VARCHAR(20)   NOT NULL UNIQUE,
    tanggal       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    subtotal      INT           NOT NULL DEFAULT 0,
    diskon_persen DECIMAL(5,2)  NOT NULL DEFAULT 0,
    diskon_rupiah INT           NOT NULL DEFAULT 0,
    pajak_persen  DECIMAL(5,2)  NOT NULL DEFAULT 0,
    pajak_rupiah  INT           NOT NULL DEFAULT 0,
    total         INT           NOT NULL DEFAULT 0,
    bayar         INT           NOT NULL DEFAULT 0,
    kembalian     INT           NOT NULL DEFAULT 0,
    nama_kasir    VARCHAR(50)   DEFAULT 'Kasir',
    metode_bayar  ENUM('tunai','midtrans') NOT NULL DEFAULT 'tunai',
    status_bayar  ENUM('lunas','pending','gagal') NOT NULL DEFAULT 'lunas',
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABEL DETAIL TRANSAKSI
-- ============================================
CREATE TABLE IF NOT EXISTS tb_detail_transaksi (
    id_detail    INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi INT          NOT NULL,
    id_produk    INT          NOT NULL,
    nama_produk  VARCHAR(100) NOT NULL,
    harga        INT          NOT NULL,
    qty          INT          NOT NULL DEFAULT 1,
    subtotal     INT          NOT NULL DEFAULT 0,
    FOREIGN KEY (id_transaksi) REFERENCES tb_transaksi(id_transaksi)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_produk) REFERENCES tb_produk(id_produk)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- DATA AWAL: PENGATURAN TOKO
-- ============================================
INSERT IGNORE INTO tb_pengaturan (nama_pengaturan, nilai) VALUES
('nama_toko',     'FIRAJAYA'),
('alamat',        ''),
('telepon',       ''),
('pajak_persen',  '11');

-- ============================================
-- DATA AWAL: KATEGORI & PRODUK
-- ============================================
-- Sengaja TIDAK diisi data contoh/dummy di sini — kategori & produk
-- diinput sendiri lewat aplikasi (halaman Produk -> "Kelola Kategori"
-- untuk kategori, tombol "Tambah Produk" untuk produk).

-- ============================================
-- TABEL PESAN (form Kontak - contact.php)
-- ============================================
CREATE TABLE IF NOT EXISTS tb_pesan (
    id_pesan   INT AUTO_INCREMENT PRIMARY KEY,
    id_user    INT DEFAULT NULL,
    nama       VARCHAR(100) NOT NULL,
    email      VARCHAR(100) NOT NULL,
    subjek     VARCHAR(150) NOT NULL,
    isi_pesan  TEXT NOT NULL,
    status     ENUM('belum_dibaca','sudah_dibaca') NOT NULL DEFAULT 'belum_dibaca',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES tb_users(id_user) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABEL CHAT (live chat kasir <-> pelanggan)
-- ============================================
CREATE TABLE IF NOT EXISTS tb_chat (
    id_chat    INT AUTO_INCREMENT PRIMARY KEY,
    id_user    INT NOT NULL,
    pengirim   ENUM('user','kasir') NOT NULL,
    isi_pesan  TEXT NOT NULL,
    status     ENUM('belum_dibaca','sudah_dibaca') NOT NULL DEFAULT 'belum_dibaca',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES tb_users(id_user) ON DELETE CASCADE,
    INDEX idx_chat_user (id_user, id_chat)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- AKUN KASIR DEFAULT — TIDAK di-generate di sini
-- ============================================
-- Saya sengaja TIDAK menyertakan INSERT akun kasir di file ini, karena
-- kolom password butuh hash bcrypt asli dari password_hash() PHP — kalau
-- saya tulis hash palsu/asal, login akan gagal tanpa pesan error yang jelas.
--
-- Cara membuat akun kasir pertama (pilih salah satu):
--
-- 1) PALING MUDAH: gunakan halaman register.php yang sudah ada di project,
--    lalu di phpMyAdmin jalankan query ini untuk menaikkan role-nya jadi kasir
--    (register.php selalu membuat role 'user'):
--      UPDATE tb_users SET role = 'kasir' WHERE username = 'username_kamu';
--
-- 2) MANUAL via PHP: jalankan script berikut sekali lewat browser/CLI PHP
--    untuk dapat hash yang valid, lalu copy hasilnya ke query INSERT:
--      <?php echo password_hash('kasir123', PASSWORD_DEFAULT); ?>
--    Hasilnya (selalu beda tiap generate, itu normal) baru dipakai di:
--      INSERT INTO tb_users (nama_lengkap, username, password, role)
--      VALUES ('Kasir Utama', 'kasir1', '<HASIL_HASH_DI_SINI>', 'kasir');
-- ============================================
-- SISTEM ABSENSI KASIR
-- Jalankan script ini di phpMyAdmin / MySQL client
-- ============================================

CREATE TABLE IF NOT EXISTS tb_absensi (
    id_absensi   INT AUTO_INCREMENT PRIMARY KEY,
    id_user      INT NOT NULL,
    nama_kasir   VARCHAR(100) NOT NULL,
    tanggal      DATE NOT NULL,
    jam_masuk    DATETIME NOT NULL,
    jam_pulang   DATETIME DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_tanggal (id_user, tanggal),
    FOREIGN KEY (id_user) REFERENCES tb_users(id_user) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- PENGUNCIAN DI LEVEL DATABASE
-- Trigger ini berjalan otomatis di MySQL, jadi berlaku
-- bahkan kalau seseorang mengedit lewat phpMyAdmin/SQL
-- langsung, bukan cuma lewat aplikasi.
-- ============================================

DELIMITER $$

-- 1) Data absensi tidak boleh dihapus sama sekali, oleh siapa pun
DROP TRIGGER IF EXISTS trg_absensi_no_delete$$
CREATE TRIGGER trg_absensi_no_delete
BEFORE DELETE ON tb_absensi
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Data absensi bersifat permanen dan tidak boleh dihapus.';
END$$

-- 2) Data absensi hanya boleh diubah SATU KALI, yaitu untuk mengisi
--    jam_pulang dari NULL -> terisi. Setelah itu (dan untuk semua kolom
--    lain: jam_masuk, tanggal, id_user) tidak boleh diubah lagi.
DROP TRIGGER IF EXISTS trg_absensi_lock_update$$
CREATE TRIGGER trg_absensi_lock_update
BEFORE UPDATE ON tb_absensi
FOR EACH ROW
BEGIN
    IF NOT (OLD.id_user <=> NEW.id_user)
       OR NOT (OLD.tanggal <=> NEW.tanggal)
       OR NOT (OLD.jam_masuk <=> NEW.jam_masuk)
       OR NOT (OLD.nama_kasir <=> NEW.nama_kasir) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Pemilik, tanggal, nama kasir, dan jam masuk tidak bisa diubah.';
    END IF;

    IF OLD.jam_pulang IS NOT NULL AND NOT (OLD.jam_pulang <=> NEW.jam_pulang) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Jam pulang sudah tercatat dan tidak bisa diubah lagi.';
    END IF;
END$$

DELIMITER ;

-- ============================================
-- FITUR KOREKSI ABSENSI (append-only, permanen)
-- Data asli di tb_absensi TETAP TIDAK PERNAH diubah/dihapus.
-- Tabel ini hanya menambahkan CATATAN koreksi di atasnya.
-- Jalankan setelah database_absensi.sql
-- ============================================

CREATE TABLE IF NOT EXISTS tb_absensi_koreksi (
    id_koreksi      INT AUTO_INCREMENT PRIMARY KEY,
    id_absensi      INT NOT NULL,
    alasan          VARCHAR(255) NOT NULL,
    dikoreksi_oleh  INT NOT NULL,
    nama_pengoreksi VARCHAR(100) NOT NULL,
    dibuat_pada     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_absensi) REFERENCES tb_absensi(id_absensi),
    FOREIGN KEY (dikoreksi_oleh) REFERENCES tb_users(id_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER $$

-- Catatan koreksi tidak boleh dihapus
DROP TRIGGER IF EXISTS trg_koreksi_no_delete$$
CREATE TRIGGER trg_koreksi_no_delete
BEFORE DELETE ON tb_absensi_koreksi
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Catatan koreksi bersifat permanen dan tidak boleh dihapus.';
END$$

-- Catatan koreksi tidak boleh diedit setelah dibuat
DROP TRIGGER IF EXISTS trg_koreksi_no_update$$
CREATE TRIGGER trg_koreksi_no_update
BEFORE UPDATE ON tb_absensi_koreksi
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Catatan koreksi bersifat permanen dan tidak boleh diubah.';
END$$

DELIMITER ;

-- ============================================
-- PROTEKSI BRUTE FORCE LOGIN
-- Mencatat setiap percobaan login (berhasil/gagal)
-- untuk dasar rate limiting & lockout sementara.
-- ============================================

CREATE TABLE IF NOT EXISTS tb_login_attempts (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50) NOT NULL,
    ip_address  VARCHAR(45) NOT NULL,
    berhasil    TINYINT(1) NOT NULL DEFAULT 0,
    waktu       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username_waktu (username, waktu),
    INDEX idx_ip_waktu (ip_address, waktu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
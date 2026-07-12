<?php
// ============================================
// GET /api/get_sales_report.php
// Laporan penjualan: ringkasan, grafik, produk terlaris
// Params: ?dari=-  ?sampai=-
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'kasir') {
    jsonResponse(['success' => false, 'message' => 'Akses ditolak. Silakan login sebagai kasir.'], 403);
}

try {
    $where  = ["1=1"];
    $params = [];

    if (isset($_GET['dari']) && $_GET['dari'] !== '') {
        $where[] = "DATE(tanggal) >= :dari";
        $params[':dari'] = $_GET['dari'];
    }
    if (isset($_GET['sampai']) && $_GET['sampai'] !== '') {
        $where[] = "DATE(tanggal) <= :sampai";
        $params[':sampai'] = $_GET['sampai'];
    }

    $whereClause = implode(' AND ', $where);

    // Filter kategori khusus untuk Produk Terlaris (opsional)
    $kategoriFilter = isset($_GET['kategori']) && $_GET['kategori'] !== '' && $_GET['kategori'] !== 'all'
        ? (int)$_GET['kategori']
        : null;

    // ===== Ringkasan =====
    $stmtSum = $pdo->prepare("
        SELECT 
            COUNT(*) as total_transaksi,
            COALESCE(SUM(total), 0) as total_pendapatan,
            COALESCE(SUM(bayar), 0) as total_bayar,
            COALESCE(SUM(subtotal), 0) as total_subtotal,
            COALESCE(SUM(diskon_rupiah), 0) as total_diskon,
            COALESCE(SUM(pajak_rupiah), 0) as total_pajak
        FROM tb_transaksi
        WHERE $whereClause
    ");
    $stmtSum->execute($params);
    $summary = $stmtSum->fetch();

    // Total item terjual
    $stmtItems = $pdo->prepare("
        SELECT COALESCE(SUM(d.qty), 0) as total_item
        FROM tb_detail_transaksi d
        JOIN tb_transaksi t ON d.id_transaksi = t.id_transaksi
        WHERE $whereClause
    ");
    $stmtItems->execute($params);
    $totalItems = $stmtItems->fetchColumn();

    // Rata-rata per transaksi
    $avgPerTrx = $summary['total_transaksi'] > 0 
        ? round($summary['total_pendapatan'] / $summary['total_transaksi']) 
        : 0;

    // ===== Grafik Harian =====
    $stmtChart = $pdo->prepare("
        SELECT 
            DATE(tanggal) as tanggal,
            COUNT(*) as jumlah_transaksi,
            COALESCE(SUM(total), 0) as total_harian
        FROM tb_transaksi
        WHERE $whereClause
        GROUP BY DATE(tanggal)
        ORDER BY DATE(tanggal) ASC
    ");
    $stmtChart->execute($params);
    $chartData = $stmtChart->fetchAll();

    // ===== Produk Terlaris =====
    // JOIN ke tb_produk & tb_kategori supaya bisa difilter per kategori.
    // LEFT JOIN dipakai (bukan INNER) supaya produk yang sudah dihapus
    // tetap muncul di riwayat (nama_produk tetap ada dari snapshot detail),
    // hanya saja tidak akan lolos filter kategori karena id_kategori NULL.
    $bestWhere = $whereClause;
    $bestParams = $params;
    if ($kategoriFilter !== null) {
        $bestWhere .= " AND p.id_kategori = :kategoriFilter";
        $bestParams[':kategoriFilter'] = $kategoriFilter;
    }

    $stmtBest = $pdo->prepare("
        SELECT 
            d.nama_produk,
            SUM(d.qty) as total_qty,
            SUM(d.subtotal) as total_penjualan,
            COUNT(DISTINCT d.id_transaksi) as jumlah_transaksi
        FROM tb_detail_transaksi d
        JOIN tb_transaksi t ON d.id_transaksi = t.id_transaksi
        LEFT JOIN tb_produk p ON d.id_produk = p.id_produk
        WHERE $bestWhere
        GROUP BY d.nama_produk
        ORDER BY total_qty DESC
        LIMIT 10
    ");
    $stmtBest->execute($bestParams);
    $bestProducts = $stmtBest->fetchAll();

    // ===== Penjualan per Kategori =====
    $stmtCat = $pdo->prepare("
        SELECT 
            k.nama_kategori,
            SUM(d.qty) as total_qty,
            SUM(d.subtotal) as total_penjualan
        FROM tb_detail_transaksi d
        JOIN tb_transaksi t ON d.id_transaksi = t.id_transaksi
        JOIN tb_produk p ON d.id_produk = p.id_produk
        JOIN tb_kategori k ON p.id_kategori = k.id_kategori
        WHERE $whereClause
        GROUP BY k.id_kategori
        ORDER BY total_penjualan DESC
    ");
    $stmtCat->execute($params);
    $categorySales = $stmtCat->fetchAll();

    jsonResponse([
        'success' => true,
        'data' => [
            'summary' => [
                'total_transaksi'  => (int)$summary['total_transaksi'],
                'total_pendapatan' => (int)$summary['total_pendapatan'],
                'total_subtotal'   => (int)$summary['total_subtotal'],
                'total_diskon'     => (int)$summary['total_diskon'],
                'total_pajak'      => (int)$summary['total_pajak'],
                'total_item'       => (int)$totalItems,
                'rata_rata'        => (int)$avgPerTrx,
            ],
            'chart'          => $chartData,
            'best_products'  => $bestProducts,
            'category_sales' => $categorySales,
        ]
    ]);

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Gagal mengambil laporan: ' . $e->getMessage()
    ], 500);
}
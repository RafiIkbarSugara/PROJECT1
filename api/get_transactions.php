<?php
// ============================================
// GET /api/get_transactions.php
// Params: ?dari=2025-01-01  ?sampai=2025-01-31  ?search=TRX  ?page=1
// ============================================

require_once __DIR__ . '/../config/database.php';

session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'kasir') {
    jsonResponse(['success' => false, 'message' => 'Akses ditolak. Silakan login sebagai kasir.'], 403);
}

try {
    $where  = ["1=1"];
    $params = [];

    // Filter tanggal dari
    if (isset($_GET['dari']) && $_GET['dari'] !== '') {
        $where[] = "DATE(tanggal) >= :dari";
        $params[':dari'] = $_GET['dari'];
    }

    // Filter tanggal sampai
    if (isset($_GET['sampai']) && $_GET['sampai'] !== '') {
        $where[] = "DATE(tanggal) <= :sampai";
        $params[':sampai'] = $_GET['sampai'];
    }

    // Filter pencarian no transaksi
    if (isset($_GET['search']) && $_GET['search'] !== '') {
        $where[] = "no_transaksi LIKE :search";
        $params[':search'] = '%' . $_GET['search'] . '%';
    }

    $whereClause = implode(' AND ', $where);

    // Pagination
    $page    = max(1, intval($_GET['page'] ?? 1));
    $perPage = 15;
    $offset  = ($page - 1) * $perPage;

    // Hitung total
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM tb_transaksi WHERE $whereClause");
    $stmtCount->execute($params);
    $total = $stmtCount->fetchColumn();

    // Ambil data
    $stmt = $pdo->prepare("
        SELECT 
            id_transaksi,
            no_transaksi,
            tanggal,
            subtotal,
            diskon_rupiah,
            pajak_rupiah,
            total,
            bayar,
            kembalian,
            nama_kasir
        FROM tb_transaksi
        WHERE $whereClause
        ORDER BY tanggal DESC
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);
    $transactions = $stmt->fetchAll();

    // Ringkasan
    $stmtSum = $pdo->prepare("
        SELECT 
            COALESCE(SUM(total), 0) as total_pendapatan,
            COALESCE(SUM(bayar), 0) as total_bayar
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

    jsonResponse([
        'success' => true,
        'data'    => $transactions,
        'total'   => $total,
        'page'    => $page,
        'pages'   => ceil($total / $perPage),
        'summary' => [
            'total_pendapatan' => $summary['total_pendapatan'],
            'total_bayar'      => $summary['total_bayar'],
            'total_item'       => $totalItems
        ]
    ]);

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Gagal mengambil transaksi: ' . $e->getMessage()
    ], 500);
}
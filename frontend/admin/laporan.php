<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/Database.php';
require_once __DIR__ . '/../../backend/models/Laporan.php';

$db = (new Database())->getConnection();
$laporanModel = new Laporan($db);

$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate   = $_GET['end_date'] ?? date('Y-m-d');

$ringkasan = $laporanModel->getRingkasan($startDate, $endDate);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Omzet - KantinKu</title>
    <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
        }
    </style>
</head>
<body class="bg-light py-4">

    <div class="container" style="max-width: 800px;">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <a href="index.php" class="btn btn-outline-secondary btn-sm fw-semibold">&larr; Kembali ke Dashboard</a>
            <button onclick="window.print()" class="btn btn-success btn-sm fw-semibold">🖨️ Cetak Laporan</button>
        </div>

        <!-- Filter Form -->
        <div class="card border-0 shadow-sm mb-4 no-print">
            <div class="card-body">
                <form method="GET" action="laporan.php" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Tanggal Awal</label>
                        <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Tanggal Akhir</label>
                        <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-food-primary w-100 fw-semibold">Filter Laporan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Print View Header -->
        <div class="card border-0 shadow-sm p-4">
            <div class="text-center mb-4">
                <h3 class="fw-bold mb-1">REKAPITULASI LAPORAN OMZET KANTINKU</h3>
                <p class="text-muted small">Periode: <?= htmlspecialchars($startDate) ?> s.d. <?= htmlspecialchars($endDate) ?></p>
            </div>

            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Indikator Keuangan</th>
                        <th class="text-end">Jumlah / Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Total Transaksi Berhasil</td>
                        <td class="text-end fw-bold"><?= number_format($ringkasan['total_transaksi']) ?> Transaksi</td>
                    </tr>
                    <tr>
                        <td>Total Menu Terjual</td>
                        <td class="text-end fw-bold"><?= number_format($ringkasan['total_produk_terjual']) ?> Porsi</td>
                    </tr>
                    <tr class="table-success">
                        <td class="fw-bold">Total Omzet (Pendapatan Lunas)</td>
                        <td class="text-end fw-bold fs-5 text-success">Rp <?= number_format($ringkasan['total_pendapatan'], 0, ',', '.') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
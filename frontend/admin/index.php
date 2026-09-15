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

$ringkasan = $laporanModel->getRingkasan();
$topProduk = $laporanModel->getProdukTerlaris(5);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - KantinKu</title>
    <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-success" href="#">KantinKu - Administrator</a>
            <div class="d-flex align-items-center gap-3">
                <span class="small fw-semibold text-muted">Admin: <?= htmlspecialchars($_SESSION['user']['nama_lengkap']) ?></span>
                <a href="../auth/logout.php" class="btn btn-outline-danger btn-sm fw-semibold">Keluar</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold m-0">Ringkasan Eksekutif</h4>
            <a href="laporan.php" class="btn btn-food-primary fw-semibold">📊 Rekap Laporan Omzet</a>
        </div>

        <!-- Metric Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 rounded-3">
                    <span class="text-muted small fw-semibold">Total Pendapatan (Lunas)</span>
                    <h3 class="fw-bold text-success mt-1 mb-0">Rp <?= number_format($ringkasan['total_pendapatan'], 0, ',', '.') ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 rounded-3">
                    <span class="text-muted small fw-semibold">Total Transaksi</span>
                    <h3 class="fw-bold text-dark mt-1 mb-0"><?= number_format($ringkasan['total_transaksi']) ?> Transaksi</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 rounded-3">
                    <span class="text-muted small fw-semibold">Total Menu Terjual</span>
                    <h3 class="fw-bold text-primary mt-1 mb-0"><?= number_format($ringkasan['total_produk_terjual']) ?> Porsi</h3>
                </div>
            </div>
        </div>

        <!-- Top Selling Menu -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold m-0">5 Menu Terlaris</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php if (!empty($topProduk)): ?>
                        <?php foreach ($topProduk as $tp): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <span class="fw-semibold"><?= htmlspecialchars($tp['nama_produk']) ?></span>
                                <span class="badge bg-success rounded-pill"><?= $tp['total_terjual'] ?> Porsi Terjual</span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item text-center text-muted py-3">Belum ada data penjualan.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

</body>
</html>
<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/Database.php';
require_once __DIR__ . '/../../backend/models/Pesanan.php';

$id = (int)($_GET['id'] ?? 0);
$db = (new Database())->getConnection();
$pesananModel = new Pesanan($db);
$order = $pesananModel->getById($id);

if (!$order || $order['user_id'] != $_SESSION['user']['id']) {
    echo "Tiket tidak ditemukan atau akses ditolak.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tiket Ambil Pesanan - KantinKu</title>
    <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 py-4">
    <div class="container" style="max-width: 420px;">
        <div class="card shadow border-0 rounded-3 text-center">
            <div class="card-body p-4">
                <span class="badge bg-success mb-2">Tiket Digital Siswa</span>
                <h4 class="fw-bold mb-1">#<?= htmlspecialchars($order['kode_pesanan']) ?></h4>
                <p class="text-muted small">Tunjukkan tiket ini saat mengambil makanan di loket</p>
                
                <hr class="my-3">
                
                <div class="text-start mb-3">
                    <?php foreach ($order['items'] as $item): ?>
                        <div class="d-flex justify-content-between small mb-1">
                            <span><?= htmlspecialchars($item['nama_produk']) ?> x<?= $item['jumlah'] ?></span>
                            <span class="fw-semibold">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="d-flex justify-content-between fw-bold fs-6 mb-3 pt-2 border-top">
                    <span>Total Pembayaran:</span>
                    <span class="text-success">Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></span>
                </div>

                <div class="alert alert-secondary py-2 small mb-3">
                    Status: <strong><?= strtoupper(str_replace('_', ' ', $order['status'])) ?></strong>
                </div>

                <a href="index.php" class="btn btn-food-primary w-100 fw-semibold">Kembali ke Katalog</a>
            </div>
        </div>
    </div>
</body>
</html>

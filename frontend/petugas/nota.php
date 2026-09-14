<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'petugas') {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/Database.php';
require_once __DIR__ . '/../../backend/models/Pesanan.php';

$id = (int)($_GET['id'] ?? 0);
$db = (new Database())->getConnection();
$pesananModel = new Pesanan($db);
$order = $pesananModel->getById($id);

if (!$order) {
    echo "Pesanan tidak ditemukan.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Kasir - POS KantinKu</title>
    <style>
        @media print {
            @page { size: 80mm auto; margin: 0; }
            body { width: 76mm; margin: 2mm auto; font-family: 'Courier New', Courier, monospace; font-size: 9pt; }
            .no-print { display: none !important; }
        }
        body { width: 76mm; margin: 20px auto; font-family: 'Courier New', Courier, monospace; font-size: 9pt; color: #000; }
        .receipt-card { border: 1px dashed #333; padding: 10px; }
        .text-center { text-align: center; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .item-row { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .btn-print { display: block; width: 100%; padding: 8px; background: #16a34a; color: #fff; text-align: center; text-decoration: none; font-weight: bold; margin-bottom: 10px; border-radius: 4px; }
    </style>
</head>
<body onload="window.print()">
    <a href="javascript:window.print()" class="btn-print no-print">Cetak Struk</a>
    <div class="receipt-card">
        <div class="text-center">
            <h3 style="margin: 0; font-size: 13pt;">KANTINKU DIGITAL</h3>
            <p style="margin: 2px 0;">Struk Resmi Transaksi</p>
        </div>
        <div class="divider"></div>
        <div>No: <strong>#<?= htmlspecialchars($order['kode_pesanan']) ?></strong></div>
        <div>Pemesan: <?= htmlspecialchars($order['nama_pemesan']) ?></div>
        <div>Kasir: <?= htmlspecialchars($_SESSION['user']['nama_lengkap']) ?></div>
        <div class="divider"></div>
        <?php foreach ($order['items'] as $item): ?>
            <div class="item-row">
                <span><?= htmlspecialchars($item['nama_produk']) ?> (x<?= $item['jumlah'] ?>)</span>
                <span>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
            </div>
        <?php endforeach; ?>
        <div class="divider"></div>
        <div class="item-row" style="font-weight: bold;">
            <span>TOTAL:</span>
            <span>Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></span>
        </div>
        <div class="item-row">
            <span>Metode:</span>
            <span><?= strtoupper($order['metode_pembayaran']) ?></span>
        </div>
        <div class="item-row">
            <span>Status Bayar:</span>
            <span><?= strtoupper($order['status_pembayaran']) ?></span>
        </div>
        <div class="divider"></div>
        <div class="text-center" style="margin-top: 8px;">
            <p style="margin: 0;">Terima Kasih!</p>
            <p style="margin: 0; font-size: 8pt;">Selamat Menikmati</p>
        </div>
    </div>
</body>
</html>
<?php
session_start();

// Validasi Otorisasi (RBAC) - Hanya untuk Petugas
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'petugas') {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/Database.php';
require_once __DIR__ . '/../../backend/models/Pesanan.php';

$db = (new Database())->getConnection();
$pesananModel = new Pesanan($db);

// Action Handler Meja Kasir
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $orderId = (int)($_POST['order_id'] ?? 0);

    if ($action === 'update_status') {
        $pesananModel->updateStatus($orderId, $_POST['status'], $_SESSION['user']['id']);
    } elseif ($action === 'update_payment_status') {
        $pesananModel->updatePaymentStatus($orderId, $_POST['status_pembayaran'], $_SESSION['user']['id']);
    }

    header("Location: index.php");
    exit;
}

$orders = $pesananModel->getAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Petugas - KantinKu</title>
    <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-success" href="#">KantinKu - Kasir & Dapur</a>
            <div class="d-flex align-items-center gap-3">
                <span class="small fw-semibold text-muted">Petugas: <?= htmlspecialchars($_SESSION['user']['nama_lengkap']) ?></span>
                <a href="../auth/logout.php" class="btn btn-outline-danger btn-sm fw-semibold">Keluar</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <h4 class="fw-bold mb-4">Monitoring Antrean & POS Kasir</h4>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Pemesan</th>
                                <th>Total</th>
                                <th>Metode</th>
                                <th>Pembayaran</th>
                                <th>Status Pesanan</th>
                                <th>Aksi Jalur Cepat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($orders)): ?>
                                <?php foreach ($orders as $o): ?>
                                    <tr>
                                        <td class="fw-bold">#<?= htmlspecialchars($o['kode_pesanan']) ?></td>
                                        <td><?= htmlspecialchars($o['nama_pemesan']) ?></td>
                                        <td>Rp <?= number_format($o['total_harga'], 0, ',', '.') ?></td>
                                        <td><span class="badge bg-secondary"><?= strtoupper($o['metode_pembayaran']) ?></span></td>
                                        <td>
                                            <form action="index.php" method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="update_payment_status">
                                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                                <select name="status_pembayaran" class="form-select form-select-sm border-0 <?= $o['status_pembayaran'] === 'lunas' ? 'bg-success-subtle text-success fw-bold' : 'bg-warning-subtle text-dark' ?>" onchange="this.form.submit()">
                                                    <option value="belum_bayar" <?= $o['status_pembayaran'] === 'belum_bayar' ? 'selected' : '' ?>>Belum Bayar</option>
                                                    <option value="lunas" <?= $o['status_pembayaran'] === 'lunas' ? 'selected' : '' ?>>Lunas</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td>
                                            <form action="index.php" method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                                <select name="status" class="form-select form-select-sm border-0 fw-semibold" onchange="this.form.submit()">
                                                    <option value="menunggu" <?= $o['status'] === 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                                                    <option value="diproses" <?= $o['status'] === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                                                    <option value="siap_diambil" <?= $o['status'] === 'siap_diambil' ? 'selected' : '' ?>>Siap Diambil</option>
                                                    <option value="selesai" <?= $o['status'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                                    <option value="dibatalkan" <?= $o['status'] === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td>
                                            <?php if ($o['status'] !== 'selesai' && $o['status'] !== 'dibatalkan'): ?>
                                                <form action="index.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                                    <input type="hidden" name="status" value="selesai">
                                                    <button type="submit" class="btn btn-sm btn-success fw-semibold">Selesaikan</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Belum ada pesanan masuk.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
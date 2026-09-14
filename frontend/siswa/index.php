<?php
session_start();

// Validasi Otorisasi (RBAC) - Hanya untuk Siswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/Database.php';
require_once __DIR__ . '/../../backend/models/Produk.php';
require_once __DIR__ . '/../../backend/models/Pesanan.php';

$db = (new Database())->getConnection();
$produkModel = new Produk($db);
$pesananModel = new Pesanan($db);

$error = '';
$success = '';

// Handling Form Checkout Pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'checkout') {
    $metode = $_POST['metode_pembayaran'] ?? 'tunai';
    $itemsRaw = $_POST['items'] ?? [];

    $cartItems = [];
    foreach ($itemsRaw as $pId => $qty) {
        if ((int)$qty > 0) {
            $cartItems[] = [
                'product_id' => (int)$pId,
                'jumlah'     => (int)$qty
            ];
        }
    }

    if (!empty($cartItems)) {
        try {
            $res = $pesananModel->createOrder($_SESSION['user']['id'], $cartItems, $metode);
            $success = "Pesanan berhasil dibuat! Kode: " . $res['kode_pesanan'];
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } else {
        $error = "Keranjang belanja masih kosong.";
    }
}

$produkList = $produkModel->getAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Siswa - KantinKu</title>
    <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-success" href="#">KantinKu - Siswa</a>
            <div class="d-flex align-items-center gap-3">
                <span class="small fw-semibold text-muted">Halo, <?= htmlspecialchars($_SESSION['user']['nama_lengkap']) ?></span>
                <a href="../auth/logout.php" class="btn btn-outline-danger btn-sm fw-semibold">Keluar</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold m-0">Katalog Menu</h4>
            <button class="btn btn-food-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#paymentModal">
                🛒 Keranjang & Checkout
            </button>
        </div>

        <!-- Grid produk -->
        <div class="row g-4">
            <?php foreach ($produkList as $p): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm rounded-3">
                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-warning text-dark align-self-start mb-2"><?= htmlspecialchars($p['nama_kategori']) ?></span>
                            <h5 class="card-title fw-bold text-truncate mb-1"><?= htmlspecialchars($p['nama_produk']) ?></h5>
                            <p class="card-text text-muted small flex-grow-1"><?= htmlspecialchars($p['deskripsi'] ?? '-') ?></p>
                            <div class="mt-3 pt-2 border-top">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-success">Rp <?= number_format($p['harga'], 0, ',', '.') ?></span>
                                    <small class="text-muted">Stok: <?= $p['stok'] ?></small>
                                </div>
                                <input type="number" class="form-control form-control-sm item-qty" data-id="<?= $p['id'] ?>" min="0" max="<?= $p['stok'] ?>" value="0">
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal Checkout Pembayaran -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3 border-0 shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark">Pilih Cara Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="index.php" method="POST" id="checkout-form">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="checkout">
                        <input type="hidden" name="metode_pembayaran" id="hidden-metode-pembayaran" value="tunai">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Metode Pembayaran</label>
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-success text-start active btn-payment" data-method="tunai">
                                    💵 Tunai di Loket Kantin (Bayar saat Ambil)
                                </button>
                                <button type="button" class="btn btn-outline-success text-start btn-payment" data-method="qris">
                                    📱 QRIS Digital (Konfirmasi Otomatis)
                                </button>
                            </div>
                        </div>
                        <div id="cart-form-inputs"></div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="submit" class="btn btn-food-primary w-100 py-2 fw-bold" id="btn-checkout">
                            Konfirmasi & Buat Pesanan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Synchronize keranjang belanja ke form modal
        document.querySelectorAll('.btn-payment').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.btn-payment').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('hidden-metode-pembayaran').value = this.dataset.method;
            });
        });

        document.getElementById('paymentModal').addEventListener('show.bs.modal', function () {
            const container = document.getElementById('cart-form-inputs');
            container.innerHTML = '';
            document.querySelectorAll('.item-qty').forEach(input => {
                if (parseInt(input.value) > 0) {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = `items[${input.dataset.id}]`;
                    hidden.value = input.value;
                    container.appendChild(hidden);
                }
            });
        });
    </script>
</body>
</html>
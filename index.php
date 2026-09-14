<?php
session_start();
require_once __DIR__ . '/backend/config/Database.php';
require_once __DIR__ . '/backend/models/Kategori.php';
require_once __DIR__ . '/backend/models/Produk.php';

// Gateway Router: Auto-redirect jika pengguna sudah login
if (isset($_SESSION['user'])) {
    $r = $_SESSION['user']['role'];
    if ($r === 'admin') { header("Location: frontend/admin/index.php"); exit; }
    if ($r === 'petugas') { header("Location: frontend/petugas/index.php"); exit; }
    if ($r === 'siswa') { header("Location: frontend/siswa/index.php"); exit; }
}

$db = (new Database())->getConnection();
$kategoriModel = new Kategori($db);
$produkModel   = new Produk($db);

$kategoriList = $kategoriModel->getAll();
$produkList   = $produkModel->getAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KantinKu - E-Kantin Sekolah</title>
    <link rel="stylesheet" href="frontend/assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="frontend/assets/css/style.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-success" href="index.php">KantinKu</a>
            <div class="d-flex gap-2">
                <a href="frontend/auth/login.php" class="btn btn-outline-success btn-sm px-3 fw-semibold">Masuk</a>
                <a href="frontend/auth/register.php" class="btn btn-food-primary btn-sm px-3 fw-semibold">Daftar</a>
            </div>
        </div>
    </nav>

    <!-- Hero Banner -->
    <section class="py-5 bg-light text-center border-bottom">
        <div class="container py-4">
            <h1 class="fw-bold display-5 mb-3">Selamat Datang di <span class="text-success">KantinKu</span></h1>
            <p class="lead text-muted mx-auto mb-4" style="max-width: 600px;">
                Pesan menu makanan dan minuman kantin favoritmu secara praktis, bebas antre, dan cepat!
            </p>
            <a href="frontend/auth/register.php" class="btn btn-food-primary btn-lg px-4 fw-semibold shadow-sm">Pesan Sekarang</a>
        </div>
    </section>

    <!-- Etalase Menu -->
    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold m-0">Menu Hari Ini</h3>
                <span class="badge bg-success fs-6"><?= count($produkList) ?> Varian Menu</span>
            </div>

            <!-- Card Grid Produk -->
            <div class="row g-4">
                <?php if (!empty($produkList)): ?>
                    <?php foreach ($produkList as $p): ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="card h-100 border-0 shadow-sm rounded-3">
                                <div class="card-body d-flex flex-column">
                                    <span class="badge bg-warning text-dark align-self-start mb-2"><?= htmlspecialchars($p['nama_kategori']) ?></span>
                                    <h5 class="card-title fw-bold text-truncate mb-1"><?= htmlspecialchars($p['nama_produk']) ?></h5>
                                    <p class="card-text text-muted small flex-grow-1 text-truncate"><?= htmlspecialchars($p['deskripsi'] ?? 'Menu lezat siap santap') ?></p>
                                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                        <span class="fw-bold text-success">Rp <?= number_format($p['harga'], 0, ',', '.') ?></span>
                                        <small class="text-muted">Stok: <?= $p['stok'] ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <p class="text-muted fs-5">Belum ada menu yang tersedia saat ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white py-4 border-top text-center text-muted small">
        <div class="container">
            &copy; 2026 KantinKu. Seluruh hak cipta dilindungi.
        </div>
    </footer>

    <script src="frontend/assets/js/main.js"></script>
</body>
</html>
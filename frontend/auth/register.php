<?php
session_start();
if (isset($_SESSION['user'])) { 
    header("Location: ../../index.php"); 
    exit; 
}

require_once __DIR__ . '/../../backend/config/Database.php';
require_once __DIR__ . '/../../backend/models/User.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $nama     = trim($_POST['nama_lengkap'] ?? '');
    $noHp     = trim($_POST['no_hp'] ?? '');

    if (!empty($username) && !empty($password) && !empty($nama)) {
        try {
            $db = (new Database())->getConnection();
            $userModel = new User($db);
            $userModel->register($username, $password, $nama, $noHp);
            $_SESSION['register_success'] = "Pendaftaran berhasil! Silakan login.";
            header("Location: login.php");
            exit;
        } catch (Exception $e) { 
            $error = $e->getMessage(); 
        }
    } else { 
        $error = "Harap lengkapi seluruh formulir!"; 
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun Siswa - KantinKu</title>
    <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 py-4">
    <div class="container" style="max-width: 480px;">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-4">
                <h4 class="card-title text-center fw-bold mb-1">Daftar Akun KantinKu</h4>
                <p class="text-muted text-center small mb-4">Lengkapi data diri Anda untuk membuat akun siswa</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small" role="alert">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST">
                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label small fw-semibold">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required placeholder="Masukkan nama lengkap">
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label small fw-semibold">Username / NIS</label>
                        <input type="text" class="form-control" id="username" name="username" required placeholder="Masukkan username">
                    </div>
                    <div class="mb-3">
                        <label for="no_hp" class="form-label small fw-semibold">No. HP / WhatsApp</label>
                        <input type="text" class="form-control" id="no_hp" name="no_hp" placeholder="Contoh: 08123456789">
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label small fw-semibold">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Buat password">
                    </div>
                    <button type="submit" class="btn btn-food-primary w-100 py-2 fw-semibold">Daftar Sekarang</button>
                </form>

                <div class="text-center mt-3">
                    <span class="small text-muted">Sudah punya akun? </span>
                    <a href="login.php" class="small text-decoration-none fw-bold">Login di sini</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
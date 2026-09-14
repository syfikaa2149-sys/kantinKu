<?php
session_start();
if (isset($_SESSION['user'])) {
    $r = $_SESSION['user']['role'];
    if ($r === 'admin') header("Location: ../admin/index.php");
    elseif ($r === 'petugas') header("Location: ../petugas/index.php");
    else header("Location: ../siswa/index.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/Database.php';
require_once __DIR__ . '/../../backend/models/User.php';

$error = '';
$success = $_SESSION['register_success'] ?? '';
unset($_SESSION['register_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        $db = (new Database())->getConnection();
        $userModel = new User($db);
        $user = $userModel->login($username, $password);

        if ($user) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id'           => $user['id'],
                'username'     => $user['username'],
                'nama_lengkap' => $user['nama_lengkap'],
                'role'         => $user['role']
            ];

            if ($user['role'] === 'admin') header("Location: ../admin/index.php");
            elseif ($user['role'] === 'petugas') header("Location: ../petugas/index.php");
            else header("Location: ../siswa/index.php");
            exit;
        } else {
            $error = "Username atau kata sandi tidak valid.";
        }
    } else {
        $error = "Harap masukkan username dan kata sandi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KantinKu</title>
    <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 py-4">
    <div class="container" style="max-width: 400px;">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-4">
                <h4 class="card-title text-center fw-bold mb-1">Masuk KantinKu</h4>
                <p class="text-muted text-center small mb-4">Silakan login untuk mengakses layanan kantin</p>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success py-2 small" role="alert">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small" role="alert">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label small fw-semibold">Username / NIS</label>
                        <input type="text" class="form-control" id="username" name="username" required placeholder="Masukkan username">
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label small fw-semibold">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Masukkan password">
                    </div>
                    <button type="submit" class="btn btn-food-primary w-100 py-2 fw-semibold">Masuk</button>
                </form>

                <div class="text-center mt-3">
                    <span class="small text-muted">Belum punya akun? </span>
                    <a href="register.php" class="small text-decoration-none fw-bold">Daftar di sini</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
require_once "../config/Database.php";
require_once "User.php";

try {
    $db = (new Database())->getConnection();
    $user = new User($db);

    // Coba daftarkan 1 akun baru
    $hasil = $user->register("siswa1", "password123", "Siswa Budi", "08123456789");
    
    if ($hasil) {
        echo "Registrasi Akun Berhasil!";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
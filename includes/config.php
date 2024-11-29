<?php
// Pengaturan koneksi database
$host = 'localhost'; // Host database, biasanya 'localhost' atau IP server
$dbname = 'perpustakaan'; // Nama database
$username = 'root'; // Username database
$password = ''; // Password database (kosong jika menggunakan XAMPP default)

// Membuat koneksi ke database menggunakan PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set charset untuk menghindari masalah encoding
    $pdo->exec("SET NAMES 'utf8'");
} catch (PDOException $e) {
    // Menangani error jika koneksi gagal
    die("Connection failed: " . $e->getMessage());
}

// Jika koneksi berhasil, akan mengembalikan objek PDO yang bisa digunakan untuk query
?>

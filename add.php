<?php
include 'includes/header.php'; // Menyertakan header
include 'includes/navbar.php'; // Menyertakan navbar
// Path ke file CSV
$file = 'data/buku2.csv';
$tempFile = 'data/temp_buku.csv'; // File sementara untuk menyimpan data yang tidak terhapus

// Menangani form submit untuk menambah buku
if (isset($_POST['submit'])) {
    // Mengambil data dari form
    $judul = $_POST['judul'];
    $klasifikasi = $_POST['klasifikasi'];
    $nama_klasifikasi = $_POST['nama_klasifikasi'];

    // Membaca data yang sudah ada di file CSV
    $handle = fopen($file, 'a'); // Membuka file untuk ditulis
    if ($handle !== false) {
        // Membuat kode buku otomatis berdasarkan data yang ada
        $kode_buku = generateKodeBuku($file);

        // Menyimpan data buku baru ke dalam file CSV
        fputcsv($handle, [$judul, $klasifikasi, $nama_klasifikasi, $kode_buku]);
        fclose($handle);

        echo '<script>alert("Buku berhasil ditambahkan!"); window.location.href = "add.php";</script>';
    } else {
        echo "Gagal menulis ke file.";
    }
}

// Fungsi untuk menghasilkan kode buku secara otomatis
function generateKodeBuku($file)
{
    // Membaca file CSV untuk mendapatkan kode buku terakhir
    $handle = fopen($file, 'r');
    $lastKodeBuku = '';
    while (($data = fgetcsv($handle)) !== false) {
        $lastKodeBuku = $data[3]; // Mengambil kode buku terakhir
    }
    fclose($handle);

    // Menambahkan angka ke kode buku terakhir (misalnya: B001, B002, ...)
    $prefix = 'KBB-';
    $number = (int)substr($lastKodeBuku, 1); // Mengambil angka setelah prefix 'B'
    $number++;

    return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
}

// Menangani penghapusan berdasarkan kode buku
if (isset($_GET['delete'])) {
    $kodeBuku = $_GET['delete'];

    // Membuka file CSV dan file sementara
    $handle = fopen($file, 'r');
    $tempHandle = fopen($tempFile, 'w');

    if ($handle && $tempHandle) {
        // Membaca dan menyalin baris-baris ke file sementara, kecuali baris dengan kode buku yang ingin dihapus
        while (($data = fgetcsv($handle)) !== false) {
            if ($data[3] !== $kodeBuku) { // Jika kode buku tidak cocok, simpan baris ke file sementara
                fputcsv($tempHandle, $data);
            }
        }

        fclose($handle);
        fclose($tempHandle);

        // Mengganti file asli dengan file sementara (menghapus buku yang tidak terpilih)
        rename($tempFile, $file);

        echo '<script>alert("Buku berhasil dihapus!"); window.location.href = "add.php";</script>';
    } else {
        echo "Gagal membuka file.";
    }
}

// Menangani pencarian buku berdasarkan judul
$results = [];
if (isset($_GET['keyword'])) {
    $keyword = $_GET['keyword'];
    // Membaca file CSV dan mencari data
    $handle = fopen($file, 'r');
    $header = fgetcsv($handle); // Membaca header (baris pertama)

    // Proses baris data berikutnya
    while (($data = fgetcsv($handle)) !== false) {
        $judul = $data[0]; // Judul buku ada di kolom pertama

        // Memeriksa apakah keyword ada dalam judul buku (case-insensitive)
        if (strpos(strtolower($judul), strtolower($keyword)) !== false) {
            $results[] = [
                'judul' => $data[0],
                'klasifikasi' => $data[1],
                'nama_klasifikasi' => $data[2],
                'kode_buku' => $data[3]
            ];
        }
    }
    fclose($handle);
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Koleksi Buku</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #searchBar {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Kelola Koleksi Buku</h1>

        <!-- Form untuk menambah buku -->
        <h3>Tambah Buku Baru</h3>
        <form action="add.php" method="POST">
            <div class="form-group">
                <label for="judul">Judul Buku:</label>
                <input type="text" class="form-control" id="judul" name="judul" required>
            </div>
            <div class="form-group">
                <label for="klasifikasi">Klasifikasi:</label>
                <input type="text" class="form-control" id="klasifikasi" name="klasifikasi" required>
            </div>
            <div class="form-group">
                <label for="nama_klasifikasi">Nama Klasifikasi:</label>
                <input type="text" class="form-control" id="nama_klasifikasi" name="nama_klasifikasi" required>
            </div>
            <button type="submit" class="btn btn-primary" name="submit">Tambah Buku</button>
        </form>

        <!-- Pencarian Buku -->
        <h3 class="mt-5">Cari Buku Berdasarkan Judul</h3>
        <form action="add.php" method="GET">
            <div class="form-group">
                <input type="text" class="form-control" id="searchBar" name="keyword" placeholder="Cari berdasarkan judul buku">
            </div>
            <button type="submit" class="btn btn-primary">Cari</button>
        </form>

        <!-- Tabel Koleksi Buku -->
        <h3 class="mt-5">Daftar Koleksi Buku</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Judul Buku</th>
                    <th>Klasifikasi</th>
                    <th>Nama Klasifikasi</th>
                    <th>Kode Buku</th>
                    <th>Opsi</th>
                </tr>
            </thead>
            <tbody>
                <!-- Menampilkan hasil pencarian jika ada -->
                <?php if (!empty($results)): ?>
                    <?php foreach ($results as $book): ?>
                        <tr>
                            <td><?= $book['judul'] ?></td>
                            <td><?= $book['klasifikasi'] ?></td>
                            <td><?= $book['nama_klasifikasi'] ?></td>
                            <td><?= $book['kode_buku'] ?></td>
                            <td><a href="add.php?delete=<?= $book['kode_buku'] ?>" class="btn btn-danger">Hapus</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Tidak ada buku yang ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS & Popper.js -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

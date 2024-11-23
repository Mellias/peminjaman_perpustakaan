<?php
include_once 'includes/header.php'; // Menyertakan header
 // Menyertakan navbar

// Path ke file CSV
$file = 'data/buku2.csv';
$tempFile = 'data/temp_buku.csv'; // File sementara untuk menyimpan data yang tidak terhapus

// Fungsi untuk menghasilkan kode buku secara otomatis
function generateKodeBuku($file)
{
    $prefix = 'KBB-';
    $number = 1;

    if (file_exists($file) && ($handle = fopen($file, 'r'))) {
        $lastKodeBuku = '';
        while (($data = fgetcsv($handle)) !== false) {
            $lastKodeBuku = $data[3]; // Kode buku ada di kolom ke-4
        }
        fclose($handle);

        if (!empty($lastKodeBuku)) {
            $number = (int)substr($lastKodeBuku, strlen($prefix)) + 1;
        }
    }

    return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
}

// Menangani form submit untuk menambah buku
if (isset($_POST['submit'])) {
    $judul = trim($_POST['judul']);
    $klasifikasi = trim($_POST['klasifikasi']);
    $nama_klasifikasi = trim($_POST['nama_klasifikasi']);

    if ($judul && $klasifikasi && $nama_klasifikasi) {
        $kode_buku = generateKodeBuku($file);

        if ($handle = fopen($file, 'a')) {
            fputcsv($handle, [$judul, $klasifikasi, $nama_klasifikasi, $kode_buku]);
            fclose($handle);

            echo '<script>alert("Buku berhasil ditambahkan!"); window.location.href = "add.php";</script>';
        } else {
            echo '<script>alert("Gagal menulis ke file!");</script>';
        }
    } else {
        echo '<script>alert("Semua kolom harus diisi!");</script>';
    }
}

// Menangani penghapusan berdasarkan kode buku
if (isset($_GET['delete'])) {
    $kodeBuku = $_GET['delete'];

    if (file_exists($file) && ($handle = fopen($file, 'r')) && ($tempHandle = fopen($tempFile, 'w'))) {
        while (($data = fgetcsv($handle)) !== false) {
            if ($data[3] !== $kodeBuku) { // Jika kode buku tidak cocok, simpan baris ke file sementara
                fputcsv($tempHandle, $data);
            }
        }
        fclose($handle);
        fclose($tempHandle);
        rename($tempFile, $file);

        echo '<script>alert("Buku berhasil dihapus!"); window.location.href = "add.php";</script>';
    } else {
        echo '<script>alert("Gagal membuka file!");</script>';
    }
}

// Menangani pencarian buku berdasarkan judul
$results = [];
if (isset($_GET['keyword']) && !empty(trim($_GET['keyword']))) {
    $keyword = trim($_GET['keyword']);

    if ($handle = fopen($file, 'r')) {
        fgetcsv($handle); // Lewati header
        while (($data = fgetcsv($handle)) !== false) {
            if (stripos($data[0], $keyword) !== false) {
                $results[] = $data;
            }
        }
        fclose($handle);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Koleksi Buku</title>
    <!-- Tambahkan Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Tambahkan container-fluid agar navbar responsif -->
    <div class="container-fluid">
        <?php include 'includes/navbar.php'; ?>
    </div>

    <div class="container mt-4">
        <h1 class="mb-4">Kelola Koleksi Buku</h1>

        <!-- Form untuk menambah buku -->
        <h3>Tambah Buku Baru</h3>
        <form action="add.php" method="POST" class="mb-4">
            <div class="mb-3">
                <label for="judul" class="form-label">Judul Buku:</label>
                <input type="text" class="form-control" id="judul" name="judul" required>
            </div>
            <div class="mb-3">
                <label for="klasifikasi" class="form-label">Klasifikasi:</label>
                <input type="text" class="form-control" id="klasifikasi" name="klasifikasi" required>
            </div>
            <div class="mb-3">
                <label for="nama_klasifikasi" class="form-label">Nama Klasifikasi:</label>
                <input type="text" class="form-control" id="nama_klasifikasi" name="nama_klasifikasi" required>
            </div>
            <button type="submit" class="btn btn-primary" name="submit">Tambah Buku</button>
        </form>

        <!-- Pencarian Buku -->
        <h3 class="mt-5">Cari Buku Berdasarkan Judul</h3>
        <form action="add.php" method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" name="keyword" placeholder="Cari berdasarkan judul buku">
                <button type="submit" class="btn btn-primary">Cari</button>
            </div>
        </form>

        <!-- Tabel Koleksi Buku -->
        <h3 class="mt-5">Daftar Koleksi Buku</h3>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <tbody>
                <?php if (!empty($results)): ?>
                    <?php foreach ($results as $data): ?>
                        <tr>
                            <td><?= htmlspecialchars($data[0]) ?></td>
                            <td><?= htmlspecialchars($data[1]) ?></td>
                            <td><?= htmlspecialchars($data[2]) ?></td>
                            <td><?= htmlspecialchars($data[3]) ?></td>
                            <td>
                                <a href="add.php?delete=<?= urlencode($data[3]) ?>" class="btn btn-danger btn-sm">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada buku yang ditemukan.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tambahkan Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

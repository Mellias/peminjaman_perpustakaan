<?php
include_once 'includes/header.php'; // Menyertakan header
include_once 'includes/config.php'; // Menyertakan konfigurasi database

// Menangani form submit untuk menambah data buku dan anggota
if (isset($_POST['submit'])) {
    // Jika file CSV untuk buku di-upload
    if (isset($_FILES['csv_file_buku']) && $_FILES['csv_file_buku']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['csv_file_buku']['tmp_name'];

        // Memastikan file yang di-upload adalah CSV
        $fileType = $_FILES['csv_file_buku']['type'];
        if ($fileType !== 'text/csv') {
            echo '<script>alert("Tolong unggah file CSV yang valid untuk Buku!");</script>';
        } else {
            try {
                // Membuka file CSV untuk buku
                $file = fopen($fileTmpPath, 'r');
                $rowNumber = 0;

                // Membaca file CSV baris per baris
                while (($row = fgetcsv($file)) !== FALSE) {
                    // Lewati baris pertama (header)
                    if ($rowNumber === 0) {
                        $rowNumber++;
                        continue;
                    }

                    // Menyimpan data ke dalam database buku
                    $judul = $row[0]; // Kolom 1 (judul buku)
                    $klasifikasi = $row[1]; // Kolom 2 (klasifikasi)
                    $nama_klasifikasi = $row[2]; // Kolom 3 (nama klasifikasi)
                    $kode_eksamplar = $row[3]; // Kolom 4 (kode eksamplar yang diinginkan)

                    if ($judul && $klasifikasi && $nama_klasifikasi && $kode_eksamplar) {
                        // Insert buku ke database dengan kode eksamplar langsung dari CSV
                        $stmt = $pdo->prepare("INSERT INTO buku (judul, klasifikasi, nama_klasifikasi, kode_buku) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$judul, $klasifikasi, $nama_klasifikasi, $kode_eksamplar]);
                    }

                    $rowNumber++;
                }
                fclose($file);

                echo '<script>alert("Data buku berhasil ditambahkan dari file CSV!"); window.location.href = "add.php";</script>';
            } catch (Exception $e) {
                echo '<script>alert("Terjadi kesalahan saat memproses file CSV: ' . $e->getMessage() . '");</script>';
            }
        }
    }

    // Jika file CSV untuk anggota di-upload
    if (isset($_FILES['csv_file_anggota']) && $_FILES['csv_file_anggota']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['csv_file_anggota']['tmp_name'];
    
        // Memastikan file yang di-upload adalah CSV
        $fileType = $_FILES['csv_file_anggota']['type'];
        if ($fileType !== 'text/csv') {
            echo '<script>alert("Tolong unggah file CSV yang valid untuk Anggota!");</script>';
        } else {
            try {
                // Membuka file CSV untuk anggota
                $file = fopen($fileTmpPath, 'r');
                $rowNumber = 0;
    
                // Membaca file CSV baris per baris
                while (($row = fgetcsv($file)) !== FALSE) {
                    // Lewati baris pertama (header)
                    if ($rowNumber === 0) {
                        $rowNumber++;
                        continue;
                    }
    
                    // Menyimpan data ke dalam database anggota
                    $id_anggota = $row[0]; // Kolom 1 (ID Anggota)
                    $tipe_keanggotaan = $row[1]; // Kolom 2 (Tipe Keanggotaan)
                    
                    // Asumsikan hanya ID Anggota dan Tipe Keanggotaan yang diperlukan
                    if ($id_anggota && $tipe_keanggotaan) {
                        // Insert anggota ke database (tidak perlu nama atau tanggal bergabung)
                        $stmt = $pdo->prepare("INSERT INTO anggota (id_anggota, tipe_keanggotaan) VALUES (?, ?)");
                        $stmt->execute([$id_anggota, $tipe_keanggotaan]);
                    }
    
                    $rowNumber++;
                }
                fclose($file);
    
                echo '<script>alert("Data anggota berhasil ditambahkan dari file CSV!"); window.location.href = "add.php";</script>';
            } catch (Exception $e) {
                echo '<script>alert("Terjadi kesalahan saat memproses file CSV: ' . $e->getMessage() . '");</script>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Koleksi Buku dan Anggota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <?php include 'includes/navbar.php'; ?>
    </div>

    <div class="container mt-4">
        <h1 class="mb-4">Kelola Koleksi Buku dan Anggota</h1>

        <!-- Form untuk mengunggah file buku -->
        <h3>Tambah Buku Dari File CSV</h3>
        <form action="add.php" method="POST" enctype="multipart/form-data" class="mb-4">
            <div class="mb-3">
                <label for="csv_file_buku" class="form-label">Unggah File CSV Buku:</label>
                <input type="file" class="form-control" id="csv_file_buku" name="csv_file_buku" accept=".csv">
            </div>
            <button type="submit" class="btn btn-primary" name="submit">Tambah Buku</button>
        </form>

        <!-- Form untuk mengunggah file anggota -->
        <h3>Tambah Anggota Dari File CSV</h3>
        <form action="add.php" method="POST" enctype="multipart/form-data" class="mb-4">
            <div class="mb-3">
                <label for="csv_file_anggota" class="form-label">Unggah File CSV Anggota:</label>
                <input type="file" class="form-control" id="csv_file_anggota" name="csv_file_anggota" accept=".csv">
            </div>
            <button type="submit" class="btn btn-primary" name="submit">Tambah Anggota</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

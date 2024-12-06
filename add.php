<?php
include_once 'includes/header.php'; // Menyertakan header
include_once 'includes/config.php'; // Menyertakan konfigurasi database

// Fungsi untuk menghasilkan kode buku secara otomatis
function generateKodeBuku($pdo)
{
    $prefix = 'KB-';
    $number = 1;

    // Query untuk mendapatkan kode buku terakhir
    $stmt = $pdo->query("SELECT kode_buku FROM buku ORDER BY kode_buku DESC LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        $lastKodeBuku = $row['kode_buku'];
        if (!empty($lastKodeBuku)) {
            // Mengambil angka dari kode buku terakhir dan menambahkannya
            $lastNumber = (int)substr($lastKodeBuku, strlen($prefix));
            $number = $lastNumber + 1;
        }
    }

    // Menghasilkan kode buku dengan nomor yang dihasilkan
    return $prefix . str_pad($number, 5, '0', STR_PAD_LEFT); // Menambahkan angka dengan padding 5 digit
}

// Menangani form submit untuk menambah buku
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

                    if ($judul && $klasifikasi && $nama_klasifikasi) {
                        // Menghasilkan kode buku otomatis
                        $kode_buku = generateKodeBuku($pdo);

                        // Insert buku ke database
                        $stmt = $pdo->prepare("INSERT INTO buku (judul, klasifikasi, nama_klasifikasi, kode_buku) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$judul, $klasifikasi, $nama_klasifikasi, $kode_buku]);
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

    // Jika file CSV untuk peminjaman di-upload
   // Jika file CSV untuk peminjaman di-upload
        // Jika file CSV untuk peminjaman di-upload
        if (isset($_FILES['csv_file_peminjaman']) && $_FILES['csv_file_peminjaman']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['csv_file_peminjaman']['tmp_name'];

            // Memastikan file yang di-upload adalah CSV
            $fileType = $_FILES['csv_file_peminjaman']['type'];
            if ($fileType !== 'text/csv') {
                echo '<script>alert("Tolong unggah file CSV yang valid untuk Peminjaman!");</script>';
            } else {
                try {
                    // Membuka file CSV untuk peminjaman
                    $file = fopen($fileTmpPath, 'r');
                    $rowNumber = 0;

                    // Membaca file CSV baris per baris
                    while (($row = fgetcsv($file)) !== FALSE) {
                        // Lewati baris pertama (header)
                        if ($rowNumber === 0) {
                            $rowNumber++;
                            continue;
                        }

                        // Menyimpan data ke dalam database peminjaman
                        $id_anggota = $row[0]; // Kolom 1 (ID Anggota)
                        $judul = $row[2]; // Kolom 3 (Judul Buku)
                        $kode_klasifikasi = $row[3]; // Kolom 4 (Kode Klasifikasi)
                        $nama_klasifikasi = $row[4]; // Kolom 5 (Nama Klasifikasi)
                        $tanggal_pinjam = $row[5]; // Kolom 6 (Tanggal Pinjam)
                        $tanggal_kembali = $row[6]; // Kolom 7 (Tanggal Kembali)
                        $bulan_peminjaman = $row[7]; // Kolom 8 (Bulan Peminjaman)
                        $status_peminjaman = $row[8]; // Kolom 9 (Status Peminjaman)

                        if ($id_anggota && $judul && $kode_klasifikasi && $nama_klasifikasi && $tanggal_pinjam && $tanggal_kembali && $bulan_peminjaman && $status_peminjaman) {
                            // Ambil maksimal 4 kata pertama dari judul untuk pencocokan
                            $judulArray = explode(' ', $judul);
                            $judulTerkait = implode(' ', array_slice($judulArray, 0, 4)); // Ambil 4 kata pertama

                            // Query untuk mencari kode buku berdasarkan judul yang cocok (4 kata pertama)
                            $stmt = $pdo->prepare("SELECT kode_buku FROM buku WHERE judul LIKE ? LIMIT 1");
                            $stmt->execute(['%' . $judulTerkait . '%']);
                            $kode_buku = $stmt->fetchColumn();

                            if ($kode_buku) {
                                // Jika ditemukan kode buku, insert data peminjaman ke database
                                $stmt = $pdo->prepare("INSERT INTO peminjaman (id_anggota, kode_buku, judul, kode_klasifikasi, nama_klasifikasi, tanggal_pinjam, tanggal_kembali, bulan_peminjaman, status_peminjaman) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                $stmt->execute([$id_anggota, $kode_buku, $judul, $kode_klasifikasi, $nama_klasifikasi, $tanggal_pinjam, $tanggal_kembali, $bulan_peminjaman, $status_peminjaman]);
                            } else {
                                echo '<script>alert("Kode buku untuk judul "' . $judul . '" tidak ditemukan di database!");</script>';
                            }
                        }

                        $rowNumber++;
                    }
                    fclose($file);

                    echo '<script>alert("Data peminjaman berhasil ditambahkan dari file CSV!"); window.location.href = "add.php";</script>';
                } catch (Exception $e) {
                    echo '<script>alert("Terjadi kesalahan saat memproses file CSV peminjaman: ' . $e->getMessage() . '");</script>';
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
    <title>Kelola Koleksi Buku dan Peminjaman</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <?php include 'includes/navbar.php'; ?>
    </div>

    <div class="container mt-4">
        <h1 class="mb-4">Kelola Koleksi Buku dan Peminjaman</h1>

        <!-- Form untuk mengunggah file buku -->
        <h3>Tambah Buku Dari File CSV</h3>
        <form action="add.php" method="POST" enctype="multipart/form-data" class="mb-4">
            <div class="mb-3">
                <label for="csv_file_buku" class="form-label">Unggah File CSV Buku:</label>
                <input type="file" class="form-control" id="csv_file_buku" name="csv_file_buku" accept=".csv">
            </div>
            <button type="submit" class="btn btn-primary" name="submit">Tambah Buku</button>
        </form>

        <!-- Form untuk mengunggah file peminjaman -->
        <h3>Tambah Peminjaman Dari File CSV</h3>
        <form action="add.php" method="POST" enctype="multipart/form-data" class="mb-4">
            <div class="mb-3">
                <label for="csv_file_peminjaman" class="form-label">Unggah File CSV Peminjaman:</label>
                <input type="file" class="form-control" id="csv_file_peminjaman" name="csv_file_peminjaman" accept=".csv">
            </div>
            <button type="submit" class="btn btn-primary" name="submit">Tambah Peminjaman</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

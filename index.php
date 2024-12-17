<?php
// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "perpustakaan";

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fungsi untuk normalisasi string (untuk pencocokan data yang lebih tepat)
function normalizeString($string) {
    return mb_strtolower(trim(preg_replace('/\s+/', ' ', $string)));
}

// Fungsi untuk normalisasi Z-Score
function zScoreNormalization($data, $column) {
    $values = array_column($data, $column);
    
    // Pastikan nilai-nilai dalam kolom adalah numerik
    $values = array_map('floatval', $values); // Konversi semua nilai menjadi numerik
    
    $mean = array_sum($values) / count($values);
    $variance = array_sum(array_map(function($x) use ($mean) { return pow($x - $mean, 2); }, $values)) / count($values);
    $stddev = sqrt($variance);

    foreach ($data as &$row) {
        $row[$column] = ($row[$column] - $mean) / $stddev; // Z-Score Normalization
    }

    return $data;
}

// Fungsi untuk One-Hot Encoding
function oneHotEncode($data, $column) {
    $uniqueValues = array_unique(array_column($data, $column));
    foreach ($data as &$row) {
        foreach ($uniqueValues as $value) {
            $row[$column . "_" . $value] = ($row[$column] == $value) ? 1 : 0;
        }
    }

    return $data;
}

// Cek apakah form telah di-submit
if (isset($_POST['submit']) && isset($_FILES['csv_file'])) {
    // Pastikan file yang di-upload adalah CSV
    if ($_FILES['csv_file']['type'] !== 'text/csv') {
        echo 'Tolong unggah file CSV!';
        exit;
    }

    // Baca file CSV
    $fileTmpPath = $_FILES['csv_file']['tmp_name'];
    $file = fopen($fileTmpPath, 'r');

    $data = [];
    
    // Lewati header CSV
    fgetcsv($file);

    // Proses setiap baris dalam file CSV
    while (($row = fgetcsv($file)) !== FALSE) {
        // Ambil data dari CSV
        $id_anggota = $row[0];          // ID Anggota
        $tipe_keanggotaan = $row[1];    // Tipe Keanggotaan
        $kode_eksemplar = $row[2];      // Kode Eksemplar
        $judul = $row[3];               // Judul
        $kode_klasifikasi = $row[4];    // Kode Klasifikasi
        $nama_klasifikasi = $row[5];    // Nama Klasifikasi
        $tanggal_pinjam = $row[6];      // Tanggal Pinjam
        $tanggal_kembali = $row[7];     // Tanggal Kembali
        $status_peminjaman = $row[8];   // Status Peminjaman
        $bulan_peminjaman = $row[9];    // Bulan Peminjaman

        // Menambahkan data ke array
        $data[] = [
            'id_anggota' => $id_anggota,
            'tipe_keanggotaan' => $tipe_keanggotaan,
            'kode_eksemplar' => $kode_eksemplar,
            'judul' => $judul,
            'kode_klasifikasi' => $kode_klasifikasi,
            'nama_klasifikasi' => $nama_klasifikasi,
            'tanggal_pinjam' => $tanggal_pinjam,
            'tanggal_kembali' => $tanggal_kembali,
            'status_peminjaman' => $status_peminjaman,
            'bulan_peminjaman' => $bulan_peminjaman
        ];
    }

    fclose($file);

    // Lakukan normalisasi dan encoding
    $data = zScoreNormalization($data, 'kode_klasifikasi'); // Normalisasi klasifikasi
    $data = oneHotEncode($data, 'tipe_keanggotaan');       // One-Hot Encoding untuk tipe keanggotaan
    $data = oneHotEncode($data, 'status_peminjaman');      // One-Hot Encoding untuk status peminjaman

    // Simpan data peminjaman ke database setelah pengolahan
    foreach ($data as $row) {
        $insertSql = "INSERT INTO peminjaman (id_anggota, kode_buku, judul, kode_klasifikasi, nama_klasifikasi, tanggal_pinjam, tanggal_kembali, bulan_peminjaman, status_peminjaman) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("sssssssss", $row['id_anggota'], $row['kode_eksemplar'], $row['judul'], $row['kode_klasifikasi'], $row['nama_klasifikasi'], $row['tanggal_pinjam'], $row['tanggal_kembali'], $row['bulan_peminjaman'], $row['status_peminjaman']);
        $insertStmt->execute();
    }
    
    echo '<script>alert("Data peminjaman berhasil dimasukkan!"); window.location.href = "index.php";</script>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Data Peminjaman</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-5">
    <h1 class="text-center mb-4">Upload Data Peminjaman CSV</h1>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="csv_file" class="form-label">Pilih File CSV</label>
            <input type="file" class="form-control" name="csv_file" id="csv_file" required>
        </div>
        <button type="submit" class="btn btn-primary" name="submit">Upload</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Tutup koneksi
$conn->close();
?>

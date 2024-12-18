<?php
// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "perpustakaan";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function normalizeString($string) {
    return mb_strtolower(trim(preg_replace('/\s+/', ' ', $string)));
}

function zScoreNormalization($data, $column) {
    $values = array_column($data, $column);
    $values = array_map('floatval', $values);
    $mean = array_sum($values) / count($values);
    $variance = array_sum(array_map(function($x) use ($mean) { return pow($x - $mean, 2); }, $values)) / count($values);
    $stddev = sqrt($variance);

    foreach ($data as &$row) {
        $row[$column] = ($row[$column] - $mean) / $stddev;
    }

    return $data;
}

function oneHotEncode($data, $column) {
    $uniqueValues = array_unique(array_column($data, $column));
    foreach ($data as &$row) {
        foreach ($uniqueValues as $value) {
            $row[$column . "_" . $value] = ($row[$column] == $value) ? 1 : 0;
        }
    }
    return $data;
}

if (isset($_POST['submit'])) {
    if (isset($_FILES['csv_file_buku']) && $_FILES['csv_file_buku']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['csv_file_buku']['tmp_name'];
        if ($_FILES['csv_file_buku']['type'] !== 'text/csv') {
            echo '<script>alert("Tolong unggah file CSV yang valid untuk Buku!");</script>';
        } else {
            $file = fopen($fileTmpPath, 'r');
            $rowNumber = 0;
            while (($row = fgetcsv($file)) !== FALSE) {
                if ($rowNumber === 0) {
                    $rowNumber++;
                    continue;
                }
                $judul = $row[0];
                $klasifikasi = $row[1];
                $nama_klasifikasi = $row[2];
                $kode_eksamplar = $row[3];
                if ($judul && $klasifikasi && $nama_klasifikasi && $kode_eksamplar) {
                    $stmt = $conn->prepare("INSERT INTO buku (judul, klasifikasi, nama_klasifikasi, kode_buku) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $judul, $klasifikasi, $nama_klasifikasi, $kode_eksamplar);
                    $stmt->execute();
                }
                $rowNumber++;
            }
            fclose($file);
            echo '<script>alert("Data buku berhasil ditambahkan dari file CSV!");</script>';
        }
    }

    if (isset($_FILES['csv_file_anggota']) && $_FILES['csv_file_anggota']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['csv_file_anggota']['tmp_name'];
        if ($_FILES['csv_file_anggota']['type'] !== 'text/csv') {
            echo '<script>alert("Tolong unggah file CSV yang valid untuk Anggota!");</script>';
        } else {
            $file = fopen($fileTmpPath, 'r');
            $rowNumber = 0;
            while (($row = fgetcsv($file)) !== FALSE) {
                if ($rowNumber === 0) {
                    $rowNumber++;
                    continue;
                }
                $id_anggota = $row[0];
                $tipe_keanggotaan = $row[1];
                if ($id_anggota && $tipe_keanggotaan) {
                    $stmt = $conn->prepare("INSERT INTO anggota (id_anggota, tipe_keanggotaan) VALUES (?, ?)");
                    $stmt->bind_param("ss", $id_anggota, $tipe_keanggotaan);
                    $stmt->execute();
                }
                $rowNumber++;
            }
            fclose($file);
            echo '<script>alert("Data anggota berhasil ditambahkan dari file CSV!");</script>';
        }
    }

    if (isset($_FILES['csv_file_peminjaman']) && $_FILES['csv_file_peminjaman']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['csv_file_peminjaman']['tmp_name'];
        if ($_FILES['csv_file_peminjaman']['type'] !== 'text/csv') {
            echo '<script>alert("Tolong unggah file CSV yang valid untuk Peminjaman!");</script>';
        } else {
            $file = fopen($fileTmpPath, 'r');
            $data = [];
            fgetcsv($file);
            while (($row = fgetcsv($file)) !== FALSE) {
                $data[] = [
                    'id_anggota' => $row[0],
                    'tipe_keanggotaan' => $row[1],
                    'kode_eksemplar' => $row[2],
                    'judul' => $row[3],
                    'kode_klasifikasi' => $row[4],
                    'nama_klasifikasi' => $row[5],
                    'tanggal_pinjam' => $row[6],
                    'tanggal_kembali' => $row[7],
                    'status_peminjaman' => $row[8],
                    'bulan_peminjaman' => $row[9]
                ];
            }
            fclose($file);

            $data = zScoreNormalization($data, 'kode_klasifikasi');
            $data = oneHotEncode($data, 'tipe_keanggotaan');
            $data = oneHotEncode($data, 'status_peminjaman');

            foreach ($data as $row) {
                $stmt = $conn->prepare("INSERT INTO peminjaman (id_anggota, kode_buku, judul, kode_klasifikasi, nama_klasifikasi, tanggal_pinjam, tanggal_kembali, bulan_peminjaman, status_peminjaman) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssssss", $row['id_anggota'], $row['kode_eksemplar'], $row['judul'], $row['kode_klasifikasi'], $row['nama_klasifikasi'], $row['tanggal_pinjam'], $row['tanggal_kembali'], $row['bulan_peminjaman'], $row['status_peminjaman']);
                $stmt->execute();
            }

            echo '<script>alert("Data peminjaman berhasil dimasukkan!");</script>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px; /* Kurangi padding untuk memperkecil kotak */
            max-width: 600px; /* Atur lebar maksimum kotak */
            margin: 20px auto; /* Margin otomatis untuk memposisikan kotak di tengah */
        }

        h1 {
            font-weight: bold;
            color: #333;
        }
        .btn-primary {
            background: linear-gradient(90deg, #007bff, #0056b3);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #0056b3, #007bff);
        }
        .mb-3 label {
            font-weight: 600;
        }
        .form-control {
            box-shadow: none;
            border-radius: 6px;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .file-input-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        .file-input-wrapper input[type="file"] {
            opacity: 0;
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
            cursor: pointer;
        }
        .file-input-wrapper label {
            display: block;
            padding: 8px 15px;
            background: #007bff;
            color: white;
            text-align: center;
            border-radius: 6px;
            cursor: pointer;
        }
        .file-input-wrapper label:hover {
            background: #0056b3;
        }
        .section-title {
            margin-top: 20px;
            font-size: 1.25rem;
            font-weight: bold;
            color: #495057;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-5">
        <h1 class="text-center mb-4">Pengelolaaan Data Perpustakaaan UMRAH</h1>

        <form method="POST" enctype="multipart/form-data">
            <div class="section-title">Data Buku</div>
            <div class="mb-3">
                <label for="csv_file_buku" class="form-label">upload file csv</label>
                <div class="file-input-wrapper">
                    <label for="csv_file_buku">Pilih File</label>
                    <input type="file" class="form-control" name="csv_file_buku" id="csv_file_buku">
                </div>
            </div>

            <div class="section-title">Data Anggota</div>
            <div class="mb-3">
                <label for="csv_file_anggota" class="form-label">upload file csv</label>
                <div class="file-input-wrapper">
                    <label for="csv_file_anggota">Pilih File</label>
                    <input type="file" class="form-control" name="csv_file_anggota" id="csv_file_anggota">
                </div>
            </div>

            <div class="section-title">Data Peminjaman</div>
            <div class="mb-3">
                <label for="csv_file_peminjaman" class="form-label">upload file csv</label>
                <div class="file-input-wrapper">
                    <label for="csv_file_peminjaman">Pilih File</label>
                    <input type="file" class="form-control" name="csv_file_peminjaman" id="csv_file_peminjaman">
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-3" name="submit">
                <i class="fas fa-upload"></i> Upload Data
            </button>
        </form>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

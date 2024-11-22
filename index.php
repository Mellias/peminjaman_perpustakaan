<?php
// Path to the CSV files
$peminjamanFile = 'data/peminjaman2.csv';
$bukuFile = 'data/buku2.csv';

// Function to read and parse the CSV files
if (file_exists($peminjamanFile)) {
    $peminjamanData = array_map('str_getcsv', file($peminjamanFile, FILE_SKIP_EMPTY_LINES));
} else {
    die("File peminjaman tidak ditemukan.");
}

if (file_exists($bukuFile)) {
    $bukuData = array_map('str_getcsv', file($bukuFile, FILE_SKIP_EMPTY_LINES));
} else {
    die("File buku tidak ditemukan.");
}

// Extract headers and rows
$peminjamanHeaders = array_map('trim', $peminjamanData[0]);
$peminjamanRows = array_slice($peminjamanData, 1);

// Temukan indeks kolom "Judul Buku"
$judulIndexPeminjaman = array_search('Judul', $peminjamanHeaders);

if ($judulIndexPeminjaman === false) {
    die("Kolom 'Judul' tidak ditemukan di file peminjaman.");
}

// Normalisasi string untuk mencocokkan judul
function normalizeString($string) {
    return strtolower(trim(preg_replace('/\s+/', ' ', $string)));
}

// Ambil dan normalisasi data peminjaman
$peminjamanNormalized = [];
foreach ($peminjamanRows as $row) {
    $peminjamanNormalized[] = normalizeString($row[$judulIndexPeminjaman]);
}

// Hitung jumlah peminjaman berdasarkan judul buku
$peminjamanCount = array_count_values($peminjamanNormalized);

// Total buku yang dipinjam
$totalBukuDipinjam = count($peminjamanCount);

include 'includes/header.php'; // Menyertakan header
include 'includes/navbar.php'; // Menyertakan navbar
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Buku yang Dipinjam</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 80%;
            border-collapse: collapse;
            margin: 20px auto;
            font-size: 0.9em;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .container {
            margin: 0 auto;
            max-width: 80%;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Data Buku yang Dipinjam</h1>

        <p>Total Buku yang Dipinjam: <?php echo $totalBukuDipinjam; ?></p>

        <h2>Detail Buku yang Dipinjam</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Judul Buku</th>
                    <th>Jumlah Dipinjam</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php foreach ($peminjamanCount as $judul => $jumlah): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($judul); ?></td>
                        <td><?php echo $jumlah; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

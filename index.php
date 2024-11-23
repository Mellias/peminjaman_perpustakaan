<?php
// Path to the CSV files
$peminjamanFile = 'data/peminjaman2.csv';
$bukuFile = 'data/buku2.csv';

// Inisialisasi default
$totalBukuDipinjam = 0; // Jika tidak ada data, total tetap 0
$peminjamanCount = []; // Default array untuk jumlah peminjaman

// Function to read and parse the CSV files
if (file_exists($peminjamanFile)) {
    $peminjamanData = array_map('str_getcsv', file($peminjamanFile, FILE_SKIP_EMPTY_LINES));
    if (!empty($peminjamanData)) {
        // Extract headers and rows
        $peminjamanHeaders = array_map('trim', $peminjamanData[0]);
        $peminjamanRows = array_slice($peminjamanData, 1);

        // Temukan indeks kolom "Judul Buku"
        $judulIndexPeminjaman = array_search('Judul', $peminjamanHeaders);

        if ($judulIndexPeminjaman !== false) {
            // Normalisasi string untuk mencocokkan judul
            function normalizeString($string) {
                return strtolower(trim(preg_replace('/\s+/', ' ', $string)));
            }

            // Ambil dan normalisasi data peminjaman
            $peminjamanNormalized = [];
            foreach ($peminjamanRows as $row) {
                if (count($row) > $judulIndexPeminjaman) { // Pastikan kolom judul ada
                    $peminjamanNormalized[] = normalizeString($row[$judulIndexPeminjaman]);
                }
            }

            // Hitung jumlah peminjaman berdasarkan judul buku
            $peminjamanCount = array_count_values($peminjamanNormalized);

            // Total buku yang dipinjam
            $totalBukuDipinjam = array_sum($peminjamanCount);
        }
    }
}

// Sertakan header dan navbar
include 'includes/header.php';
include 'includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Buku yang Dipinjam</title>
    <!-- Tambahkan Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <!-- Konten Utama -->
    <div class="container mt-5">
        <h1 class="text-center mb-4">Data Buku yang Dipinjam</h1>

        <?php if ($totalBukuDipinjam > 0): ?>
            <!-- Total Buku yang Dipinjam -->
            <div class="alert alert-info text-center">
                <strong>Total Buku yang Dipinjam:</strong> <?php echo $totalBukuDipinjam; ?>
            </div>

            <!-- Tabel Data Buku yang Dipinjam -->
            <h2 class="text-center mb-4">Detail Buku yang Dipinjam</h2>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
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
        <?php else: ?>
            <!-- Pesan Jika Tidak Ada Buku yang Dipinjam -->
            <div class="alert alert-warning text-center">
                <strong>Tidak ada buku yang dipinjam saat ini.</strong>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tambahkan Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

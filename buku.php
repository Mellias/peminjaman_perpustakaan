<?php
// Path ke file CSV
$bukuFile = 'data/buku2.csv';

// Inisialisasi data
$klasifikasiList = []; // Untuk daftar klasifikasi
$bukuData = []; // Untuk menyimpan data buku

// Cek apakah file CSV ada
if (file_exists($bukuFile)) {
    $data = array_map('str_getcsv', file($bukuFile, FILE_SKIP_EMPTY_LINES));

    if (!empty($data)) {
        // Ambil header dan data buku
        $header = array_map('trim', $data[0]);
        $rows = array_slice($data, 1);

        // Temukan indeks kolom Klasifikasi
        $klasifikasiIndex = array_search('nama_klasifikasi', $header);
        if ($klasifikasiIndex === false) {
            die("Kolom 'Klasifikasi' tidak ditemukan di file buku.");
        }

        // Kumpulkan data buku dan daftar klasifikasi unik
        foreach ($rows as $row) {
            if (count($row) > $klasifikasiIndex) {
                $bukuData[] = $row;
                $klasifikasiList[] = $row[$klasifikasiIndex];
            }
        }

        // Hapus duplikat dari daftar klasifikasi
        $klasifikasiList = array_unique($klasifikasiList);
        sort($klasifikasiList); // Urutkan alfabetis
    }
} else {
    die("File buku tidak ditemukan.");
}

// Filter berdasarkan klasifikasi jika dipilih
$filterKlasifikasi = $_GET['klasifikasi'] ?? '';
if ($filterKlasifikasi !== '') {
    $bukuData = array_filter($bukuData, function ($row) use ($filterKlasifikasi, $klasifikasiIndex) {
        return $row[$klasifikasiIndex] === $filterKlasifikasi;
    });
}

// Hitung jumlah total buku setelah filter
$totalBuku = count($bukuData);

// Sertakan header dan navbar
include 'includes/header.php';
include 'includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koleksi Buku</title>
    <!-- Tambahkan Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    
    <!-- Konten Utama -->
    <div class="container mt-5">
        <h1 class="text-center mb-4">Koleksi Buku</h1>

        <!-- Dropdown Filter -->
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-6 mx-auto">
                    <div class="input-group">
                        <label class="input-group-text" for="klasifikasi">Filter Klasifikasi</label>
                        <select name="klasifikasi" id="klasifikasi" class="form-select">
                            <option value="">Tampilkan Semua</option>
                            <?php foreach ($klasifikasiList as $klasifikasi): ?>
                                <option value="<?php echo htmlspecialchars($klasifikasi); ?>" <?php echo $klasifikasi === $filterKlasifikasi ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($klasifikasi); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary">Tampilkan</button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Total Buku -->
        <div class="alert alert-info text-center">
            <strong>Total Buku: <?php echo $totalBuku; ?></strong>
        </div>

        <!-- Tabel Koleksi Buku -->
        <?php if (!empty($bukuData)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <?php foreach ($header as $col): ?>
                                <th><?php echo htmlspecialchars($col); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bukuData as $row): ?>
                            <tr>
                                <?php foreach ($row as $col): ?>
                                    <td><?php echo htmlspecialchars($col); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center">
                <strong>Tidak ada buku yang ditemukan.</strong>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tambahkan Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

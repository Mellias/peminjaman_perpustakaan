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

// Inisialisasi default
$totalBukuDipinjam = 0;
$totalBukuTidakDipinjam = 0;
$peminjamanCount = [];
$allBooks = [];
$booksNeverBorrowed = [];
$klasifikasiCounts = []; // Jumlah buku per klasifikasi

// Ambil data peminjaman
$sqlPeminjaman = "SELECT Judul, Kode_Buku FROM peminjaman";
$resultPeminjaman = $conn->query($sqlPeminjaman);

if ($resultPeminjaman->num_rows > 0) {
    while ($row = $resultPeminjaman->fetch_assoc()) {
        $key = normalizeString($row['Judul']) . '|' . normalizeString($row['Kode_Buku']);
        $peminjamanCount[$key] = isset($peminjamanCount[$key]) ? $peminjamanCount[$key] + 1 : 1;
    }
    $totalBukuDipinjam = array_sum($peminjamanCount);
}

// Ambil data buku
$sqlBuku = "SELECT judul, nama_klasifikasi, kode_buku FROM buku";
$resultBuku = $conn->query($sqlBuku);

if ($resultBuku->num_rows > 0) {
    while ($row = $resultBuku->fetch_assoc()) {
        $key = normalizeString($row['judul']) . '|' . normalizeString($row['kode_buku']);
        $allBooks[$key] = $row['nama_klasifikasi'];

        // Hitung jumlah buku per klasifikasi
        $klasifikasi = $row['nama_klasifikasi'];
        $klasifikasiCounts[$klasifikasi] = isset($klasifikasiCounts[$klasifikasi]) ? $klasifikasiCounts[$klasifikasi] + 1 : 1;
    }
    $booksNeverBorrowed = array_diff(array_keys($allBooks), array_keys($peminjamanCount));
    $totalBukuTidakDipinjam = count($booksNeverBorrowed);
}

// Tangkap pilihan view untuk tabel/grafik
$displayOption = isset($_GET['display']) ? $_GET['display'] : 'tabel';

// Sertakan header dan navbar
include 'includes/header.php';
include 'includes/navbar.php';

// Tangkap pilihan dropdown
$viewOption = isset($_GET['view']) ? $_GET['view'] : 'dipinjam';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Buku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">Data Buku</h1>

    <div class="mb-4 text-center">
        <form method="get" action="">
            <select name="view" class="form-select w-auto d-inline" onchange="this.form.submit()">
                <option value="dipinjam" <?php echo ($viewOption == 'dipinjam') ? 'selected' : ''; ?>>Buku yang Dipinjam</option>
                <option value="tidak_dipinjam" <?php echo ($viewOption == 'tidak_dipinjam') ? 'selected' : ''; ?>>Buku yang Tidak Pernah Dipinjam</option>
            </select>
        </form>
    </div>

    <?php if ($viewOption == 'tidak_dipinjam'): ?>
        <div class="alert alert-info text-center">
            <strong>Total Buku yang Tidak Pernah Dipinjam:</strong> <?php echo $totalBukuTidakDipinjam; ?>
        </div>
        <h2 class="text-center mb-4">Buku yang Tidak Pernah Dipinjam</h2>

        <div class="mb-4 text-center">
            <form method="get" action="">
                <input type="hidden" name="view" value="tidak_dipinjam">
                <select name="display" class="form-select w-auto d-inline" onchange="this.form.submit()">
                    <option value="tabel" <?php echo ($displayOption == 'tabel') ? 'selected' : ''; ?>>Tabel</option>
                    <option value="grafik" <?php echo ($displayOption == 'grafik') ? 'selected' : ''; ?>>Grafik</option>
                </select>
            </form>
        </div>

        <?php if ($displayOption == 'tabel'): ?>
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Judul Buku</th>
                    <th>Nama Klasifikasi</th>
                </tr>
                </thead>
                <tbody>
                <?php $no = 1; ?>
                <?php foreach ($booksNeverBorrowed as $key): ?>
                    <?php list($judul, $kode) = explode('|', $key); ?>
                    <?php $klasifikasi = $allBooks[$key] ?? 'Tidak Diketahui'; ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($judul); ?></td>
                        <td><?php echo htmlspecialchars($klasifikasi); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <canvas id="klasifikasiChart" width="400" height="200"></canvas>
            <script>
                const data = {
                    labels: <?php echo json_encode(array_keys($klasifikasiCounts)); ?>,
                    datasets: [{
                        label: 'Jumlah Buku Tidak Pernah Dipinjam',
                        data: <?php echo json_encode(array_values($klasifikasiCounts)); ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                };

                const config = {
                    type: 'bar',
                    data: data,
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                        }
                    }
                };

                new Chart(
                    document.getElementById('klasifikasiChart'),
                    config
                );
            </script>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-info text-center">
            <strong>Total Buku yang Dipinjam:</strong> <?php echo $totalBukuDipinjam; ?>
        </div>
        <h2 class="text-center mb-4">Buku yang Dipinjam</h2>
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
            <?php foreach ($peminjamanCount as $key => $jumlah): ?>
                <?php list($judul, $kode) = explode('|', $key); ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($judul); ?></td>
                    <td><?php echo $jumlah; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Fungsi untuk normalisasi string
function normalizeString($string) {
    return strtolower(trim(preg_replace('/\s+/', ' ', $string)));
}

// Tutup koneksi
$conn->close();

// Tangkap pilihan view untuk tabel/grafik
$displayOption = isset($_GET['display']) ? $_GET['display'] : 'tabel';

// Sertakan header dan navbar (jika diperlukan)
// include 'includes/header.php';
// include 'includes/navbar.php';

// Tangkap pilihan dropdown
$viewOption = isset($_GET['view']) ? $_GET['view'] : 'dipinjam';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Buku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">Data Buku</h1>

    <div class="mb-4 text-center">
        <form method="get" action="">
            <select name="view" class="form-select w-auto d-inline" onchange="this.form.submit()">
                <option value="dipinjam" <?php echo ($viewOption == 'dipinjam') ? 'selected' : ''; ?>>Buku yang Dipinjam</option>
                <option value="tidak_dipinjam" <?php echo ($viewOption == 'tidak_dipinjam') ? 'selected' : ''; ?>>Buku yang Tidak Pernah Dipinjam</option>
            </select>
        </form>
    </div>

    <?php if ($viewOption == 'tidak_dipinjam'): ?>
        <div class="alert alert-info text-center">
            <strong>Total Buku yang Tidak Pernah Dipinjam:</strong> <?php echo $totalBukuTidakDipinjam; ?>
        </div>
        <h2 class="text-center mb-4">Buku yang Tidak Pernah Dipinjam</h2>

        <div class="mb-4 text-center">
            <form method="get" action="">
                <input type="hidden" name="view" value="tidak_dipinjam">
                <select name="display" class="form-select w-auto d-inline" onchange="this.form.submit()">
                    <option value="tabel" <?php echo ($displayOption == 'tabel') ? 'selected' : ''; ?>>Tabel</option>
                    <option value="grafik" <?php echo ($displayOption == 'grafik') ? 'selected' : ''; ?>>Grafik</option>
                </select>
            </form>
        </div>

        <?php if ($displayOption == 'tabel'): ?>
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Judul Buku</th>
                    <th>Nama Klasifikasi</th>
                </tr>
                </thead>
                <tbody>
                <?php $no = 1; ?>
                <?php foreach ($booksNeverBorrowed as $key): ?>
                    <?php list($judul, $kode) = explode('|', $key); ?>
                    <?php $klasifikasi = $allBooks[$key] ?? 'Tidak Diketahui'; ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($judul); ?></td>
                        <td><?php echo htmlspecialchars($klasifikasi); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <canvas id="klasifikasiChart" width="400" height="200"></canvas>
            <script>
                const data = {
                    labels: <?php echo json_encode(array_keys($klasifikasiCounts)); ?>,
                    datasets: [{
                        label: 'Jumlah Buku Tidak Pernah Dipinjam',
                        data: <?php echo json_encode(array_values($klasifikasiCounts)); ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                };

                const config = {
                    type: 'bar',
                    data: data,
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                        }
                    }
                };

                new Chart(
                    document.getElementById('klasifikasiChart'),
                    config
                );
            </script>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-info text-center">
            <strong>Total Buku yang Dipinjam:</strong> <?php echo $totalBukuDipinjam; ?>
        </div>
        <h2 class="text-center mb-4">Buku yang Dipinjam</h2>
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
            <?php foreach ($peminjamanCount as $key => $jumlah): ?>
                <?php list($judul, $kode) = explode('|', $key); ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($judul); ?></td>
                    <td><?php echo $jumlah; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>

<?php
include 'includes/header.php'; // Menyertakan header
include 'includes/navbar.php'; // Menyertakan navbar

// Koneksi ke database
$servername = "localhost"; // Nama server database (misalnya localhost)
$username = "root"; // Username untuk login ke database
$password = ""; // Password untuk login ke database
$dbname = "perpustakaan"; // Nama database

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Inisialisasi data
$bulanList = [];
$filteredData = [];
$dataPerBulan = [];
$selectedBulan = $_GET['bulan'] ?? ''; // Filter bulan dari dropdown

// Ambil data dari database
$sql = "SELECT * FROM peminjaman";
if ($selectedBulan) {
    $sql .= " WHERE MONTH(tanggal_pinjam) = MONTH(STR_TO_DATE('$selectedBulan', '%M'))";
}
$result = $conn->query($sql);

// Proses data yang diambil dari database
if ($result->num_rows > 0) {
    while($data = $result->fetch_assoc()) {
        $tanggalPeminjaman = $data['tanggal_pinjam']; // Kolom tanggal pinjam
        $bulan = date('F', strtotime($tanggalPeminjaman)); // Mengambil nama bulan

        $bulanList[] = $bulan;

        // Hitung data untuk grafik
        if (!isset($dataPerBulan[$bulan])) {
            $dataPerBulan[$bulan] = 0;
        }
        $dataPerBulan[$bulan]++;

        // Filter data berdasarkan bulan
        $filteredData[] = $data;
    }
} else {
    echo "Tidak ada data ditemukan.";
}

$conn->close();

// Hapus duplikat bulan
$bulanList = array_unique($bulanList);
sort($bulanList); // Urutkan bulan alfabetis
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Peminjaman</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Data Peminjaman</h1>
        <!-- Dropdown Filter Bulan -->
        <div class="mb-4 text-center">
            <form method="GET">
                <label for="bulan" class="form-label">Filter Berdasarkan Bulan:</label>
                <select name="bulan" id="bulan" class="form-select w-auto d-inline-block">
                    <option value="">Tampilkan Semua</option>
                    <?php foreach ($bulanList as $bulan): ?>
                        <option value="<?php echo htmlspecialchars($bulan); ?>" <?php echo $bulan === $selectedBulan ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($bulan); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Tampilkan</button>
            </form>
        </div>

        <!-- Tabel Data Peminjaman -->
        <div id="table-container" style="display: block;">
            <?php if (!empty($filteredData)): ?>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>ID Anggota</th>
                                <th>Kode Buku</th>
                                <th>Judul</th>
                                <th>Kode Klasifikasi</th>
                                <th>Nama Klasifikasi</th>
                                <th>Tanggal Pinjam</th>
                                <th>Tanggal Kembali</th>
                                <th>Bulan Peminjaman</th>
                                <th>Status Peminjaman</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            <?php foreach ($filteredData as $data): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <?php foreach ($data as $value): ?>
                                        <td><?php echo htmlspecialchars($value); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning text-center">Tidak ada data peminjaman untuk bulan ini.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

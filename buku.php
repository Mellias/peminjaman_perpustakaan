<?php
// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "perpustakaan"; // Ganti dengan nama database Anda

$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Inisialisasi data
$klasifikasiList = []; // Untuk daftar klasifikasi
$bukuData = []; // Untuk menyimpan data buku

// Ambil data dari tabel buku
$sql = "SELECT * FROM buku";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Ambil data dari query dan kumpulkan klasifikasi
    while($row = $result->fetch_assoc()) {
        $bukuData[] = $row;
        $klasifikasiList[] = $row['nama_klasifikasi'];
    }
} else {
    echo "0 results";
}

// Hapus duplikat dari daftar klasifikasi
$klasifikasiList = array_unique($klasifikasiList);
sort($klasifikasiList); // Urutkan alfabetis

// Filter berdasarkan klasifikasi jika dipilih
$filterKlasifikasi = $_GET['klasifikasi'] ?? '';
if ($filterKlasifikasi !== '') {
    $bukuData = array_filter($bukuData, function ($row) use ($filterKlasifikasi) {
        return $row['nama_klasifikasi'] === $filterKlasifikasi;
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
        <h1 class="text-center mb-4">KOLEKSI BUKU</h1>

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
                            <!-- Header tabel -->
                            <th>ID</th>
                            <th>Judul</th>
                            <th>Klasifikasi</th>
                            <th>Nama Klasifikasi</th>
                            <th>Kode Buku</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bukuData as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['judul']); ?></td>
                                <td><?php echo htmlspecialchars($row['klasifikasi']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_klasifikasi']); ?></td>
                                <td><?php echo htmlspecialchars($row['kode_buku']); ?></td>
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

<?php
// Tutup koneksi database
$conn->close();
?>

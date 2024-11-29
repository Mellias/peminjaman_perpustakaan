<?php
include_once 'includes/header.php'; // Menyertakan header
include_once 'includes/config.php'; // Menyertakan konfigurasi database

// Fungsi untuk menghasilkan kode buku secara otomatis
function generateKodeBuku($pdo)
{
    $prefix = 'KBB-';
    $number = 1;

    // Query untuk mendapatkan kode buku terakhir
    $stmt = $pdo->query("SELECT kode_buku FROM buku ORDER BY kode_buku DESC LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $lastKodeBuku = $row['kode_buku'];
        if (!empty($lastKodeBuku)) {
            $number = (int)substr($lastKodeBuku, strlen($prefix)) + 1;
        }
    }

    return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
}

// Menangani form submit untuk menambah buku
if (isset($_POST['submit'])) {
    $judul = trim($_POST['judul']);
    $klasifikasi = trim($_POST['klasifikasi']);
    $nama_klasifikasi = trim($_POST['nama_klasifikasi']);

    if ($judul && $klasifikasi && $nama_klasifikasi) {
        $kode_buku = generateKodeBuku($pdo);

        // Insert buku ke database
        $stmt = $pdo->prepare("INSERT INTO buku (judul, klasifikasi, nama_klasifikasi, kode_buku) VALUES (?, ?, ?, ?)");
        $stmt->execute([$judul, $klasifikasi, $nama_klasifikasi, $kode_buku]);

        if ($stmt) {
            echo '<script>alert("Buku berhasil ditambahkan!"); window.location.href = "add.php";</script>';
        } else {
            echo '<script>alert("Gagal menambahkan buku!");</script>';
        }
    } else {
        echo '<script>alert("Semua kolom harus diisi!");</script>';
    }
}

// Menangani penghapusan berdasarkan kode buku
if (isset($_GET['delete'])) {
    $kodeBuku = $_GET['delete'];

    // Hapus buku dari database
    $stmt = $pdo->prepare("DELETE FROM buku WHERE kode_buku = ?");
    $stmt->execute([$kodeBuku]);

    if ($stmt) {
        echo '<script>alert("Buku berhasil dihapus!"); window.location.href = "add.php";</script>';
    } else {
        echo '<script>alert("Gagal menghapus buku!");</script>';
    }
}

// Menangani pencarian buku berdasarkan judul
$results = [];
if (isset($_GET['keyword']) && !empty(trim($_GET['keyword']))) {
    $keyword = trim($_GET['keyword']);

    $stmt = $pdo->prepare("SELECT * FROM buku WHERE judul LIKE ?");
    $searchTerm = "%" . $keyword . "%";
    $stmt->execute([$searchTerm]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Koleksi Buku</title>
    <!-- Tambahkan Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Tambahkan container-fluid agar navbar responsif -->
    <div class="container-fluid">
        <?php include 'includes/navbar.php'; ?>
    </div>

    <div class="container mt-4">
        <h1 class="mb-4">Kelola Koleksi Buku</h1>

        <!-- Form untuk menambah buku -->
        <h3>Tambah Buku Baru</h3>
        <form action="add.php" method="POST" class="mb-4">
            <div class="mb-3">
                <label for="judul" class="form-label">Judul Buku:</label>
                <input type="text" class="form-control" id="judul" name="judul" required>
            </div>
            <div class="mb-3">
                <label for="klasifikasi" class="form-label">Klasifikasi:</label>
                <input type="text" class="form-control" id="klasifikasi" name="klasifikasi" required>
            </div>
            <div class="mb-3">
                <label for="nama_klasifikasi" class="form-label">Nama Klasifikasi:</label>
                <input type="text" class="form-control" id="nama_klasifikasi" name="nama_klasifikasi" required>
            </div>
            <button type="submit" class="btn btn-primary" name="submit">Tambah Buku</button>
        </form>

        <!-- Pencarian Buku -->
        <h3 class="mt-5">Cari Buku Berdasarkan Judul</h3>
        <form action="add.php" method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" name="keyword" placeholder="Cari berdasarkan judul buku">
                <button type="submit" class="btn btn-primary">Cari</button>
            </div>
        </form>

        <!-- Tabel Koleksi Buku -->
        <h3 class="mt-5">Daftar Koleksi Buku</h3>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <tbody>
                <?php if (!empty($results)): ?>
                    <?php foreach ($results as $data): ?>
                        <tr>
                            <td><?= htmlspecialchars($data['judul']) ?></td>
                            <td><?= htmlspecialchars($data['klasifikasi']) ?></td>
                            <td><?= htmlspecialchars($data['nama_klasifikasi']) ?></td>
                            <td><?= htmlspecialchars($data['kode_buku']) ?></td>
                            <td>
                                <a href="add.php?delete=<?= urlencode($data['kode_buku']) ?>" class="btn btn-danger btn-sm">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada buku yang ditemukan.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tambahkan Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Tidak perlu menutup koneksi PDO secara eksplisit, karena PDO menutupnya secara otomatis.
?>

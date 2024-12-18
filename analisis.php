<?php
include 'includes/header.php';
include 'includes/navbar.php';

// Koneksi database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "perpustakaan";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Mendapatkan bulan dari POST (atau default ke Januari jika tidak ada)
$bulan = isset($_POST['bulan']) ? $_POST['bulan'] : 1;

// Mengambil data peminjaman dan anggota berdasarkan bulan
$sql = "SELECT p.id_anggota, a.Tipe_Keanggotaan, 
            DATEDIFF(p.tanggal_kembali, p.tanggal_pinjam) AS durasi_pinjam,
            COUNT(p.kode_buku) AS frekuensi_peminjaman
            FROM peminjaman p
            JOIN anggota a ON p.id_anggota = a.ID_Anggota
            WHERE p.status_peminjaman = 'Telah Kembali'
            AND MONTH(p.tanggal_pinjam) = $bulan
            GROUP BY p.id_anggota";
        
// Menangani pemilihan bulan
if ($bulan == 'semua') {
    // Query untuk menampilkan data dari Januari hingga Juni (semua bulan)
    $sql = "SELECT p.id_anggota, a.Tipe_Keanggotaan, 
                DATEDIFF(p.tanggal_kembali, p.tanggal_pinjam) AS durasi_pinjam,
                COUNT(p.kode_buku) AS frekuensi_peminjaman
            FROM peminjaman p
            JOIN anggota a ON p.id_anggota = a.ID_Anggota
            WHERE p.status_peminjaman = 'Telah Kembali'
            AND MONTH(p.tanggal_pinjam) BETWEEN 1 AND 6
            GROUP BY p.id_anggota";
} else {
    // Query untuk bulan tertentu (Januari - Juni)
    $sql = "SELECT p.id_anggota, a.Tipe_Keanggotaan, 
                DATEDIFF(p.tanggal_kembali, p.tanggal_pinjam) AS durasi_pinjam,
                COUNT(p.kode_buku) AS frekuensi_peminjaman
            FROM peminjaman p
            JOIN anggota a ON p.id_anggota = a.ID_Anggota
            WHERE p.status_peminjaman = 'Telah Kembali'
            AND MONTH(p.tanggal_pinjam) = $bulan
            GROUP BY p.id_anggota";
}

$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id_anggota' => $row['id_anggota'],
            'durasi_pinjam' => $row['durasi_pinjam'],
            'frekuensi_peminjaman' => $row['frekuensi_peminjaman'],
            'Tipe_Keanggotaan' => $row['Tipe_Keanggotaan']
        ];
    }
} else {
    echo "<div class='alert alert-warning'>Data belum tersedia untuk bulan yang dipilih.</div>";
    $data = [];
}

// Fungsi normalisasi data dengan Min-Max Scaling
function minMaxNormalize($data) {
    if (empty($data)) {
        echo "<div class='alert alert-warning'>Tidak ada data yang dapat dinormalisasi.</div>";
        return [];
    }

    $minDurasi = min(array_column($data, 'durasi_pinjam'));
    $maxDurasi = max(array_column($data, 'durasi_pinjam'));

    $minFrekuensi = min(array_column($data, 'frekuensi_peminjaman'));
    $maxFrekuensi = max(array_column($data, 'frekuensi_peminjaman'));

    $normalizedData = [];
    foreach ($data as $row) {
        $normalizedData[] = [
            'id_anggota' => $row['id_anggota'],
            'durasi_pinjam' => ($row['durasi_pinjam'] - $minDurasi) / ($maxDurasi - $minDurasi),
            'frekuensi_peminjaman' => ($row['frekuensi_peminjaman'] - $minFrekuensi) / ($maxFrekuensi - $minFrekuensi),
            'Tipe_Keanggotaan' => $row['Tipe_Keanggotaan']
        ];
    }
    return $normalizedData;
}

$normalizedData = minMaxNormalize($data);

// K-Means Algorithm
function kMeans($data, $K) {
    if (empty($data)) {
        echo "<div class='alert alert-warning'>Tidak ada data untuk dilakukan clustering.</div>";
        return [];
    }

    $centroids = array_slice($data, 0, $K);  // Inisialisasi centroid dengan data pertama
    $iterations = 0;
    $maxIterations = 100;
    $clusters = [];

    do {
        $iterations++;
        $newClusters = array_fill(0, $K, []);

        // Menyusun ulang data ke dalam cluster berdasarkan kedekatan dengan centroid
        foreach ($data as $point) {
            $distances = [];
            foreach ($centroids as $i => $centroid) {
                // Hitung jarak Euclidean
                $distances[$i] = sqrt(pow($point['durasi_pinjam'] - $centroid['durasi_pinjam'], 2) + 
                                      pow($point['frekuensi_peminjaman'] - $centroid['frekuensi_peminjaman'], 2));
            }
            // Tentukan cluster terdekat
            $nearestCentroid = array_search(min($distances), $distances);
            $newClusters[$nearestCentroid][] = $point;
        }

        // Menghitung centroid baru
        $newCentroids = [];
        foreach ($newClusters as $cluster) {
            // Pastikan cluster tidak kosong sebelum menghitung centroid
            if (count($cluster) > 0) {
                $newCentroids[] = [
                    'durasi_pinjam' => array_sum(array_column($cluster, 'durasi_pinjam')) / count($cluster),
                    'frekuensi_peminjaman' => array_sum(array_column($cluster, 'frekuensi_peminjaman')) / count($cluster),
                ];
            } else {
                // Jika cluster kosong, pilih centroid acak atau tetap gunakan centroid sebelumnya
                $newCentroids[] = $centroids[array_rand($centroids)];
            }
        }

        // Jika centroid baru sama dengan centroid lama, berhenti
        if ($newCentroids == $centroids) {
            break;
        }

        // Perbarui centroid dan cluster
        $centroids = $newCentroids;
        $clusters = $newClusters;

    } while ($iterations < $maxIterations);

    return $clusters;
}

$K = 3; // Jumlah cluster yang diinginkan
$clusters = kMeans($normalizedData, $K);

// Menampilkan buku yang paling sering dipinjam
$sql_buku = "SELECT kode_buku, judul, COUNT(*) AS jumlah_peminjaman
             FROM peminjaman
             GROUP BY kode_buku
             ORDER BY jumlah_peminjaman DESC
             LIMIT 5";
$result_buku = $conn->query($sql_buku);

$mostFrequentBooks = [];
if ($result_buku->num_rows > 0) {
    while ($row_buku = $result_buku->fetch_assoc()) {
        $mostFrequentBooks[] = $row_buku;
    }
} else {
    echo "<div class='alert alert-warning'>Belum ada data buku yang dipinjam.</div>";
}

$conn->close();

// Generate Analysis
function generateAnalysis($clusters) {
    if (empty($clusters)) {
        echo "<div class='alert alert-warning'>Tidak ada data cluster untuk dianalisis.</div>";
        return [];
    }

    $analysis = [];
    foreach ($clusters as $index => $cluster) {
        $avgDurasi = array_sum(array_column($cluster, 'durasi_pinjam')) / count($cluster);
        $avgFrekuensi = array_sum(array_column($cluster, 'frekuensi_peminjaman')) / count($cluster);

        $durasiPinjam = array_column($cluster, 'durasi_pinjam');
        sort($durasiPinjam);

        $count = count($durasiPinjam);
        $percentile33 = $durasiPinjam[floor($count * 0.33)];
        $percentile66 = $durasiPinjam[floor($count * 0.70)];

        if ($avgDurasi > $percentile66) {
            $clusterName = "Cluster Peminjaman Lama";
        } elseif ($avgDurasi > $percentile33 && $avgDurasi <= $percentile66) {
            $clusterName = "Cluster Peminjaman Rata-rata";
        } else {
            $clusterName = "Cluster Peminjaman Cepat";
        }

        $tipeKeanggotaan = array_column($cluster, 'Tipe_Keanggotaan');
        $uniqueTipeKeanggotaan = array_unique($tipeKeanggotaan);

        $analysis[] = [
            'cluster' => $clusterName,
            'avgDurasi' => $avgDurasi,
            'avgFrekuensi' => $avgFrekuensi,
            'tipeKeanggotaan' => implode(', ', $uniqueTipeKeanggotaan),
            'description' => $clusterName . " memiliki rata-rata durasi pinjam " . round($avgDurasi, 2) . " hari, dengan frekuensi peminjaman rata-rata sebanyak " . round($avgFrekuensi, 2) . " buku. Tipe keanggotaan: " . implode(', ', $uniqueTipeKeanggotaan)
        ];
    }
    return $analysis;
}

$analysis = generateAnalysis($clusters);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analisis Clustering Peminjaman Buku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
        }
        header {
            background-color: #ffffff;
            color: #333;
            padding: 20px;
            text-align: center;
            font-size: 2em;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .container {
            margin-top: 30px;
        }
        .chart-container {
            position: relative;
            height: 800px;
            margin: 30px 0;
        }
        .analysis {
            margin-top: 30px;
        }
        .card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <header>
        Analisis Clustering Peminjaman Buku
    </header>
    
    <div class="container">
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-3">
                    <label for="bulan">Bulan</label>
                    <select name="bulan" class="form-control">
                        <option value="semua" <?= $bulan == 'semua' ? 'selected' : '' ?>>Semua Bulan</option>
                        <option value="1" <?= $bulan == 1 ? 'selected' : '' ?>>Januari</option>
                        <option value="2" <?= $bulan == 2 ? 'selected' : '' ?>>Februari</option>
                        <option value="3" <?= $bulan == 3 ? 'selected' : '' ?>>Maret</option>
                        <option value="4" <?= $bulan == 4 ? 'selected' : '' ?>>April</option>
                        <option value="5" <?= $bulan == 5 ? 'selected' : '' ?>>Mei</option>
                        <option value="6" <?= $bulan == 6 ? 'selected' : '' ?>>Juni</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary" style="margin-top: 28px;">Terapkan</button>
                </div>
            </div>
        </form>

        <div class="card">
            <div class="card-header">Buku Paling Sering Dipinjam</div>
            <div class="card-body">
                <ul>
                    <?php if (!empty($mostFrequentBooks)): ?>
                        <?php foreach ($mostFrequentBooks as $book): ?>
                            <li><?= $book['judul'] ?> (<?= $book['jumlah_peminjaman'] ?> kali dipinjam)</li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class='alert alert-warning'>Belum ada data buku yang dipinjam.</div>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Analisis Clustering Peminjaman</div>
            <div class="card-body">
                <?php if (!empty($analysis)): ?>
                    <?php foreach ($analysis as $cluster): ?>
                        <h5><?= $cluster['cluster'] ?></h5>
                        <p><?= $cluster['description'] ?></p>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class='alert alert-warning'>Tidak ada data clustering untuk ditampilkan.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="chart-container">
            <canvas id="clusterChart"></canvas>
        </div>
    </div>

    <script>
            var ctx = document.getElementById('clusterChart').getContext('2d');
            var clusterData = {
                labels: <?php echo json_encode(!empty($analysis) ? array_column($analysis, 'cluster') : []); ?>,
                datasets: [{
                    label: 'Frekuensi Peminjaman',
                    data: <?php echo json_encode(!empty($analysis) ? array_column($analysis, 'avgFrekuensi') : []); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            };

            var clusterChart = new Chart(ctx, {
                type: 'bar',
                data: clusterData,
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
    </script>

</body>
</html>

<?php
include 'includes/header.php'; // Menyertakan header
include 'includes/navbar.php'; // Menyertakan navbar

// Koneksi database
$servername = "localhost";
$username = "root";  // ganti dengan username database Anda
$password = "";  // ganti dengan password database Anda
$dbname = "perpustakaan";  // ganti dengan nama database Anda

$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Mengambil data peminjaman
$sql = "SELECT id_anggota, DATEDIFF(tanggal_kembali, tanggal_pinjam) AS durasi_pinjam, COUNT(kode_buku) AS frekuensi_peminjaman 
        FROM peminjaman
        WHERE status_peminjaman = 'Telah Kembali'
        GROUP BY id_anggota";
$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id_anggota' => $row['id_anggota'],
            'durasi_pinjam' => $row['durasi_pinjam'],
            'frekuensi_peminjaman' => $row['frekuensi_peminjaman']
        ];
    }
} else {
    echo "0 results";
}

// Fungsi normalisasi data
function normalize($data) {
    $normalizedData = [];
    $minDurasi = min(array_column($data, 'durasi_pinjam'));
    $maxDurasi = max(array_column($data, 'durasi_pinjam'));
    $minFrekuensi = min(array_column($data, 'frekuensi_peminjaman'));
    $maxFrekuensi = max(array_column($data, 'frekuensi_peminjaman'));
    
    foreach ($data as $row) {
        $normalizedData[] = [
            'id_anggota' => $row['id_anggota'],
            'durasi_pinjam' => ($row['durasi_pinjam'] - $minDurasi) / ($maxDurasi - $minDurasi),
            'frekuensi_peminjaman' => ($row['frekuensi_peminjaman'] - $minFrekuensi) / ($maxFrekuensi - $minFrekuensi),
        ];
    }
    return $normalizedData;
}

$normalizedData = normalize($data);

// Algoritma K-Means (sama dengan yang telah dibahas sebelumnya)
function kMeans($data, $K) {
    // Inisialisasi centroid secara acak
    $centroids = array_slice($data, 0, $K);

    $iterations = 0;
    $maxIterations = 100;
    $clusters = [];

    do {
        $iterations++;
        $newClusters = array_fill(0, $K, []);
        
        // Assign data points ke centroid terdekat
        foreach ($data as $point) {
            $distances = [];
            foreach ($centroids as $i => $centroid) {
                $distances[$i] = sqrt(pow($point['durasi_pinjam'] - $centroid['durasi_pinjam'], 2) + pow($point['frekuensi_peminjaman'] - $centroid['frekuensi_peminjaman'], 2));
            }
            $nearestCentroid = array_search(min($distances), $distances);
            $newClusters[$nearestCentroid][] = $point;
        }

        // Recalculate centroid baru
        $newCentroids = [];
        foreach ($newClusters as $cluster) {
            $newCentroids[] = [
                'durasi_pinjam' => array_sum(array_column($cluster, 'durasi_pinjam')) / count($cluster),
                'frekuensi_peminjaman' => array_sum(array_column($cluster, 'frekuensi_peminjaman')) / count($cluster),
            ];
        }

        if ($newCentroids == $centroids) {
            break;
        }

        $centroids = $newCentroids;
        $clusters = $newClusters;

    } while ($iterations < $maxIterations);

    return $clusters;
}

// Menentukan jumlah cluster (misalnya 2)
$K = 3;
$clusters = kMeans($normalizedData, $K);

// Menutup koneksi database
$conn->close();

// Fungsi untuk memberikan analisis berdasarkan clustering
// Fungsi untuk memberikan analisis berdasarkan clustering dan mengganti nama cluster
function generateAnalysis($clusters) {
    $analysis = [];

    foreach ($clusters as $index => $cluster) {
        // Menghitung rata-rata durasi pinjam dan frekuensi peminjaman dalam cluster
        $avgDurasi = array_sum(array_column($cluster, 'durasi_pinjam')) / count($cluster);
        $avgFrekuensi = array_sum(array_column($cluster, 'frekuensi_peminjaman')) / count($cluster);

        // Menentukan nilai durasi pinjam dalam cluster
        $durasiPinjam = array_column($cluster, 'durasi_pinjam');
        sort($durasiPinjam);  // Urutkan nilai durasi pinjam dari yang terkecil hingga terbesar

        // Menghitung persentil 33 dan 66 sebagai batas untuk kategori
        $count = count($durasiPinjam);
        $percentile33 = $durasiPinjam[floor($count * 0.33)];
        $percentile66 = $durasiPinjam[floor($count * 0.70)];

        // Menentukan nama cluster berdasarkan persentil
        if ($avgDurasi > $percentile66) {
            $clusterName = "Cluster Peminjaman Lama";
        } elseif ($avgDurasi > $percentile33 && $avgDurasi <= $percentile66) {
            $clusterName = "Cluster Peminjaman Rata-rata";
        } else {
            $clusterName = "Cluster Peminjaman Cepat";
        }

        // Menyusun analisis berdasarkan rata-rata durasi pinjam dan frekuensi peminjaman
        $analysis[] = [
            'cluster' => $clusterName,
            'avgDurasi' => $avgDurasi,
            'avgFrekuensi' => $avgFrekuensi,
            'description' => $clusterName . " memiliki rata-rata durasi pinjam " . round($avgDurasi, 2) . " hari, dengan frekuensi peminjaman rata-rata sebanyak " . round($avgFrekuensi, 2) . " buku."
        ];
    }

    return $analysis;
}

// Mengambil analisis dari hasil clustering
$analysis = generateAnalysis($clusters);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analisis Clustering Peminjaman Buku</title>

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEJrT+K6+S3sjRpmzGKHY6gprpZIeGVg5A6xZfokJc9QhNmjDoOBmA4PLaXlt" crossorigin="anonymous">

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
        }
        header {
            background-color: white;
            color: black;
            padding: 15px;
            text-align: center;
        }
        .container {
            margin: 20px auto;
        }
        .chart-container {
            position: relative;
            height: 800px;
            width: 100%;
            margin: 0 auto;
        }
        .analysis {
            margin-top: 20px;
            font-size: 1.1em;
        }
        .analysis p {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<header>
    <h1>Analisis K-Means Clustering Peminjaman Buku</h1>
</header>

<div class="container">
    <div class="chart-container">
        <canvas id="clusteringChart"></canvas>
    </div>

    <!-- Menampilkan analisis -->
    <div class="analysis">
        <?php
        // Menampilkan analisis untuk setiap cluster
        foreach ($analysis as $a) {
            echo "<p><strong>" . $a['cluster'] . ":</strong> " . $a['description'] . "</p>";
        }
        ?>
    </div>
</div>

<!-- Bootstrap JS & Popper -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz4fnFO9gybP+YfFf7lD3pT4xJ6hg5V6o0x+QFlF4aZk3n6EwA3Er/7lg9x" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-cn7l7gDPX6ZKqG3I6g5j8rZjJfk6+dZaW8IN9KwyT+3VpAAe25+LR7xgFE2cmS4V" crossorigin="anonymous"></script>

<script>
    // Data untuk Chart.js
    var clusters = <?php echo json_encode($clusters); ?>;
    var clusterNames = <?php echo json_encode(array_column($analysis, 'cluster')); ?>; // Nama-nama cluster berdasarkan kriteria

    var dataPoints = [];
    var colors = ['#FF5733', '#33FF57', '#3357FF']; // Warna untuk masing-masing cluster
    var datasets = [];

    // Mengonversi data ke format yang bisa diproses oleh Chart.js
    clusters.forEach(function(cluster, index) {
        var clusterData = {
            label: clusterNames[index], // Menggunakan nama cluster yang sesuai
            data: [],
            backgroundColor: colors[index],
            borderColor: colors[index],
            borderWidth: 1
        };

        cluster.forEach(function(member) {
            clusterData.data.push({
                x: member.durasi_pinjam,  // durasi_pinjam
                y: member.frekuensi_peminjaman, // frekuensi_peminjaman
            });
        });

        datasets.push(clusterData);
    });

    var ctx = document.getElementById('clusteringChart').getContext('2d');
    var clusteringChart = new Chart(ctx, {
        type: 'scatter',
        data: {
            datasets: datasets
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top', // Posisi legend di atas grafik
                    labels: {
                        font: {
                            size: 14 // Ukuran font legend
                        }
                    }
                }
            },
            scales: {
                x: {
                    type: 'linear',
                    position: 'bottom',
                    title: {
                        display: true,
                        text: 'Durasi Pinjam (Hari)'  // Menambahkan keterangan sumbu X
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Frekuensi Peminjaman (Buku)'  // Menambahkan keterangan sumbu Y
                    }
                }
            }
        }
    });
</script>

</body>
</html>

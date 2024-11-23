<?php
// Path ke file CSV
$file = 'data/anggota.csv';
include 'includes/header.php'; // Menyertakan header
include 'includes/navbar.php'; // Menyertakan navbar

// Inisialisasi tipe keanggotaan
$tipeKeanggotaanList = [];
$filteredData = [];
$selectedTipe = $_GET['tipe_keanggotaan'] ?? ''; // Tipe keanggotaan yang dipilih dari dropdown

// Cek apakah file CSV ada
if (file_exists($file)) {
    if (($handle = fopen($file, 'r')) !== false) {
        $isHeader = true;
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if ($isHeader) {
                $isHeader = false;
                continue;
            }

            $tipe = $data[1]; // Ambil kolom tipe keanggotaan
            $tipeKeanggotaanList[] = $tipe;

            // Filter data berdasarkan tipe keanggotaan
            if ($selectedTipe === '' || $tipe === $selectedTipe) {
                $filteredData[] = $data;
            }
        }
        fclose($handle);
    }
}

// Hapus duplikat tipe keanggotaan
$tipeKeanggotaanList = array_unique($tipeKeanggotaanList);
sort($tipeKeanggotaanList);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Keanggotaan</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Fungsi untuk mengatur visibilitas tampilan
        function toggleView() {
            const view = document.getElementById("viewSelector").value;
            document.getElementById("tableView").style.display = view === "table" ? "block" : "none";
            document.getElementById("infoGraphicView").style.display = view === "infographic" ? "block" : "none";
        }
    </script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Data Keanggotaan</h1>

        <!-- Dropdown untuk memilih tampilan -->
        <div class="mb-4 text-center">
            <label for="viewSelector" class="form-label">Pilih Tampilan:</label>
            <select id="viewSelector" onchange="toggleView()" class="form-select w-auto d-inline-block">
                <option value="table">Tabel</option>
                <option value="infographic">Infografis</option>
            </select>
        </div>

        <!-- Dropdown tambahan untuk filter tipe keanggotaan -->
        <div class="mb-4 text-center">
            <form method="GET">
                <label for="tipe_keanggotaan" class="form-label">Filter Berdasarkan Tipe Keanggotaan:</label>
                <select name="tipe_keanggotaan" id="tipe_keanggotaan" class="form-select w-auto d-inline-block">
                    <option value="">Tampilkan Semua</option>
                    <?php foreach ($tipeKeanggotaanList as $tipe): ?>
                        <option value="<?php echo htmlspecialchars($tipe); ?>" <?php echo $tipe === $selectedTipe ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tipe); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Tampilkan</button>
            </form>
        </div>

        <!-- Tampilan Tabel -->
        <div id="tableView" style="display: block;">
            <?php if (!empty($filteredData)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>ID Anggota</th>
                                <th>Tipe Keanggotaan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            <?php foreach ($filteredData as $data): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($data[0]); ?></td>
                                    <td><?php echo htmlspecialchars($data[1]); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning text-center">Tidak ada data yang ditemukan.</div>
            <?php endif; ?>
        </div>

        <!-- Tampilan Infografis (Chart.js) -->
        <div id="infoGraphicView" style="display: none;">
            <?php
            // Hitung jumlah anggota berdasarkan tipe keanggotaan
            $membershipCounts = [];
            foreach ($filteredData as $data) {
                $tipe = $data[1];
                if (!isset($membershipCounts[$tipe])) {
                    $membershipCounts[$tipe] = 0;
                }
                $membershipCounts[$tipe]++;
            }

            // Siapkan data untuk Chart.js
            $labels = json_encode(array_keys($membershipCounts));
            $data = json_encode(array_values($membershipCounts));
            ?>

            <!-- Chart.js Grafik -->
            <div id="chartContainer" style="width: 80%; margin: 0 auto;">
                <canvas id="membershipChart"></canvas>
            </div>

            <script type="text/javascript">
                // Data untuk grafik berdasarkan tipe keanggotaan
                const membershipLabels = <?php echo $labels ?? '[]'; ?>;
                const membershipData = <?php echo $data ?? '[]'; ?>;

                // Inisialisasi grafik menggunakan Chart.js
                const ctx = document.getElementById('membershipChart').getContext('2d');
                const membershipChart = new Chart(ctx, {
                    type: 'bar', // Grafik batang
                    data: {
                        labels: membershipLabels, // Label tipe keanggotaan
                        datasets: [{
                            label: 'Jumlah Anggota Berdasarkan Tipe Keanggotaan',
                            data: membershipData, // Data jumlah anggota
                            backgroundColor: 'rgba(54, 162, 235, 0.2)', // Warna latar belakang batang
                            borderColor: 'rgba(54, 162, 235, 1)', // Warna border batang
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(tooltipItem) {
                                        return tooltipItem.raw + ' Anggota';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    text: 'Jumlah Anggota',
                                    display: true
                                }
                            }
                        }
                    }
                });
            </script>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

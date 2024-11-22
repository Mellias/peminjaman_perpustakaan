<?php
include 'includes/header.php'; // Menyertakan header
include 'includes/navbar.php'; // Menyertakan navbar
?>

<div class="container" style="max-width: 80%; margin: 0 auto;">
    <h1>Data Peminjaman</h1>

    <!-- Dropdown untuk memilih tampilan -->
    <label for="viewSelect">Tampilkan data sebagai: </label>
    <select id="viewSelect">
        <option value="table">Tabel</option>
        <option value="chart">Grafik</option>
    </select>

    <!-- Tabel Data Peminjaman -->
    <div id="table-container" style="display: block; margin-top: 20px;">
        <?php
        // Path ke file CSV
        $file = 'data/peminjaman2.csv';

        // Data untuk grafik (klasifikasi per bulan)
        $dataPerKlasifikasi = [];

        // Periksa apakah file ada
        if (file_exists($file)) {
            // Buka file CSV
            if (($handle = fopen($file, 'r')) !== false) {
                echo "<table border='1' cellpadding='5' style='width: 100%;'>";
                $isHeader = true; // Penanda untuk header
                $counter = 1; // Inisialisasi penomoran
                while (($data = fgetcsv($handle, 1000, ',')) !== false) { // Gunakan delimiter ;
                    if ($isHeader) {
                        // Tampilkan header tabel
                        echo "<tr>";
                        echo "<th>No</th>"; // Tambahkan kolom penomoran
                        foreach ($data as $header) {
                            echo "<th>" . htmlspecialchars($header) . "</th>";
                        }
                        echo "</tr>";
                        $isHeader = false;
                    } else {
                        // Ambil bulan dan klasifikasi dari data CSV
                        $tanggalPeminjaman = $data[2]; // Misalnya, kolom ke-3 adalah tanggal peminjaman
                        $klasifikasi = $data[4]; // Misalnya, kolom ke-5 adalah klasifikasi buku

                        // Tentukan bulan berdasarkan tanggal
                        $bulan = date('F', strtotime($tanggalPeminjaman));

                        // Hitung jumlah peminjaman per klasifikasi
                        if (!isset($dataPerKlasifikasi[$klasifikasi])) {
                            $dataPerKlasifikasi[$klasifikasi] = 1;
                        } else {
                            $dataPerKlasifikasi[$klasifikasi]++;
                        }

                        // Tampilkan data peminjaman dalam tabel
                        echo "<tr>";
                        echo "<td>" . $counter++ . "</td>"; // Tambahkan nomor urut
                        foreach ($data as $value) {
                            echo "<td>" . htmlspecialchars($value) . "</td>";
                        }
                        echo "</tr>";
                    }
                }
                echo "</table>";
                fclose($handle);
            } else {
                echo "<p>Tidak dapat membuka file.</p>";
            }
        } else {
            echo "<p>File tidak ditemukan.</p>";
        }
        ?>
    </div>

    <!-- Grafik Data Peminjaman -->
    <div id="chart-container" style="display: none; margin-top: 20px;">
        <canvas id="peminjamanChart"></canvas>
    </div>
</div>

<?php
include 'includes/footer.php'; // Menyertakan footer
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Include Chart.js -->

<script>
    // Data untuk grafik berdasarkan klasifikasi peminjaman
    const dataPerKlasifikasi = <?php echo json_encode($dataPerKlasifikasi); ?>;

    // Menyusun data untuk Chart.js
    const klasifikasiLabels = Object.keys(dataPerKlasifikasi);
    const klasifikasiData = Object.values(dataPerKlasifikasi);

    // Inisialisasi grafik
    const ctx = document.getElementById('peminjamanChart').getContext('2d');
    const peminjamanChart = new Chart(ctx, {
        type: 'bar', // Grafik batang
        data: {
            labels: klasifikasiLabels, // Label klasifikasi
            datasets: [{
                label: 'Jumlah Peminjaman Berdasarkan Klasifikasi',
                data: klasifikasiData, // Data jumlah peminjaman
                backgroundColor: 'rgba(54, 162, 235, 0.2)', // Warna latar belakang batang
                borderColor: 'rgba(54, 162, 235, 1)', // Warna garis border batang
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
                            return tooltipItem.raw + ' Peminjaman';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Fungsi untuk mengubah tampilan antara tabel dan grafik
    document.getElementById('viewSelect').addEventListener('change', function() {
        const selectedView = this.value;
        if (selectedView === 'table') {
            document.getElementById('table-container').style.display = 'block';
            document.getElementById('chart-container').style.display = 'none';
        } else {
            document.getElementById('table-container').style.display = 'none';
            document.getElementById('chart-container').style.display = 'block';
        }
    });
</script>

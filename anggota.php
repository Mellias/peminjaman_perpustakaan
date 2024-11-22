<?php
// Path ke file CSV
$file = 'data/anggota.csv';
include 'includes/header.php'; // Menyertakan header
include 'includes/navbar.php'; // Menyertakan navbar
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Keanggotaan</title>
    
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        function toggleView() {
            const view = document.getElementById("viewSelector").value;
            document.getElementById("tableView").style.display = view === "table" ? "block" : "none";
            document.getElementById("infoGraphicView").style.display = view === "infographic" ? "block" : "none";
        }
    </script>

    <style>
        /* CSS untuk memperkecil ukuran tabel */
        table {
            width: 50%; /* Menyempitkan lebar tabel */
            margin: 0 auto;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 8px 12px;
            text-align: center;
        }
        table th {
            background-color: #f2f2f2;
        }
        /* Menambahkan border pada tabel */
        table, th, td {
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <h1>Data Keanggotaan</h1>

    <!-- Dropdown untuk memilih tampilan -->
    <label for="viewSelector">Pilih Tampilan:</label>
    <select id="viewSelector" onchange="toggleView()">
        <option value="table">Tabel</option>
        <option value="infographic">Infografis</option>
    </select>

    <!-- Tampilan Tabel -->
    <div id="tableView" style="display: block;">
        <?php
        // Periksa apakah file ada
        if (file_exists($file)) {
            // Buka file CSV
            if (($handle = fopen($file, 'r')) !== false) {
                echo "<table>";
                echo "<tr><th>No</th><th>ID Anggota</th><th>Tipe Keanggotaan</th></tr>";
                
                // Loop melalui setiap baris dalam file CSV
                $isHeader = true; // Penanda untuk header
                $rowNumber = 1; // Menambahkan penomoran
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    if ($isHeader) {
                        // Lewati header
                        $isHeader = false;
                        continue;
                    }
                    echo "<tr>";
                    echo "<td>" . $rowNumber . "</td>";  // Menampilkan nomor baris
                    echo "<td>" . htmlspecialchars($data[0]) . "</td>";
                    echo "<td>" . htmlspecialchars($data[1]) . "</td>";
                    echo "</tr>";
                    $rowNumber++; // Menambahkan angka untuk nomor baris berikutnya
                }
                echo "</table>";
                fclose($handle);
            } else {
                echo "Tidak dapat membuka file.";
            }
        } else {
            echo "File tidak ditemukan.";
        }
        ?>
    </div>

    <!-- Tampilan Infografis (Chart.js) -->
    <div id="infoGraphicView" style="display: none;">
    <?php
    if (file_exists($file)) {
        $membershipCounts = [];
        if (($handle = fopen($file, 'r')) !== false) {
            $isHeader = true; // Penanda untuk header
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if ($isHeader) {
                    $isHeader = false;
                    continue;
                }
                $type = $data[1]; // Tipe keanggotaan
                if (!isset($membershipCounts[$type])) {
                    $membershipCounts[$type] = 0;
                }
                $membershipCounts[$type]++;
            }
            fclose($handle);
        }
        
        // Siapkan data untuk Chart.js
        $labels = json_encode(array_keys($membershipCounts));
        $data = json_encode(array_values($membershipCounts));
    }
    ?>

    <!-- Chart.js Grafik -->
    <div id="chartContainer" style="width: 80%; height: 400px; margin: 0 auto;">
        <canvas id="membershipChart"></canvas>
    </div>

    <script type="text/javascript">
        // Data untuk grafik berdasarkan tipe keanggotaan
        const membershipLabels = <?php echo $labels; ?>;
        const membershipData = <?php echo $data; ?>;

        // Inisialisasi grafik menggunakan Chart.js
        const ctx = document.getElementById('membershipChart').getContext('2d');
        const membershipChart = new Chart(ctx, {
            type: 'bar', // Grafik batang
            data: {
                labels: membershipLabels, // Label tipe keanggotaan
                datasets: [{
                    label: 'Jumlah Anggota Berdasarkan Tipe Keanggotaan',
                    data: membershipData, // Data jumlah anggota
                    backgroundColor: 'rgba(75, 192, 192, 0.2)', // Warna latar belakang batang
                    borderColor: 'rgba(75, 192, 192, 1)', // Warna border batang
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

</body>
</html>

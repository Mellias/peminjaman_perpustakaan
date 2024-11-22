<?php
// Path to the cleaned CSV file
$csvFile = 'data/buku2.csv';

// Function to read and parse the CSV file
if (file_exists($csvFile)) {
    $csvData = array_map('str_getcsv', file($csvFile));
} else {
    die("File CSV tidak ditemukan.");
}

// Extract the headers and rows
$headers = array_map('trim', $csvData[0]); // Baris pertama sebagai header
$rows = array_slice($csvData, 1); // Sisanya sebagai data

// Jika ada keyword pencarian, filter data berdasarkan judul
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
if ($keyword) {
    $rows = array_filter($rows, function($row) use ($keyword) {
        return stripos($row[0], $keyword) !== false; // Cek apakah keyword ada dalam judul buku
    });
}

include 'includes/header.php'; // Menyertakan header
include 'includes/navbar.php'; // Menyertakan navbar
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Buku</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .search-container {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>Data Buku</h1>

    <!-- Form Pencarian -->
    <div class="search-container">
        <form method="GET" action="">
            <input type="text" name="keyword" placeholder="Cari berdasarkan judul buku..." value="<?php echo htmlspecialchars($keyword); ?>" />
            <button type="submit">Cari</button>
        </form>
    </div>

    <?php if (!empty($rows)): ?>
        <table>
            <thead>
                <tr>
                    <th>No</th> <!-- Kolom untuk nomor -->
                    <?php foreach ($headers as $header): ?>
                        <th><?php echo htmlspecialchars($header); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; // Inisialisasi variabel penomoran ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?php echo $no++; ?></td> <!-- Tambahkan nomor -->
                        <?php foreach ($row as $cell): ?>
                            <td><?php echo htmlspecialchars($cell); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Tidak ada data yang tersedia atau tidak ada buku yang sesuai dengan pencarian.</p>
    <?php endif; ?>

</body>
</html>

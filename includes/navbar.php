<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Default Title'; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        nav {
            background: linear-gradient(90deg, #3f51b5, #1a237e);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .nav-brand {
            font-size: 1.8rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #ffffff;
            margin-right: 500px;
        }
        .nav-links {
            display: flex;
            gap: 15px;
        }
        nav a {
            color: white;
            text-decoration: none;
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            transition: background-color 0.3s, transform 0.2s, box-shadow 0.2s;
        }
        nav a:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(255, 255, 255, 0.2);
        }
        nav a.active {
            background-color: #e91e63;
            color: white;
            font-weight: bold;
            border: 1px solid #e91e63;
            transform: translateY(0);
        }
        nav a.active:hover {
            background-color: #c2185b;
        }
        .nav-logo img {
            height: 50px;
            width: auto;
            margin-right: 20px;
            transition: transform 0.3s ease;
        }
        .nav-logo img:hover {
            transform: scale(1.1);
        }
        .container {
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-logo">
            <img src="data/umrah.png" alt="Logo">
        </div>
        <div class="nav-brand">Perpustakaaan UMRAH</div>
        <div class="nav-links">
            <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Beranda</a>
            <a href="buku.php" class="<?= basename($_SERVER['PHP_SELF']) == 'buku.php' ? 'active' : '' ?>">Koleksi Buku</a>
            <a href="anggota.php" class="<?= basename($_SERVER['PHP_SELF']) == 'anggota.php' ? 'active' : '' ?>">Daftar Peminjam</a>
            <a href="analisis.php" class="<?= basename($_SERVER['PHP_SELF']) == 'analisis.php' ? 'active' : '' ?>">Analisis</a>
        </div>
    </nav>
    <div class="container">
        <!-- Content here -->
    </div>
</body>
</html>

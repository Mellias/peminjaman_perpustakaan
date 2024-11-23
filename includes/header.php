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
            background-color: #333;
            color: white;
            padding: 10px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            padding-left: 20px;
        }
        nav a {
            color: white;
            margin: 0 10px;
            text-decoration: none;
            display: inline-block;
            padding: 8px 16px;
        }
        nav a:hover {
            text-decoration: underline;
            background-color: #444;
        }
        nav a.active {
            background-color: #e91e63; /* Warna khusus untuk halaman aktif */
            color: white;
            font-weight: bold; /* Penekanan pada halaman aktif */
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

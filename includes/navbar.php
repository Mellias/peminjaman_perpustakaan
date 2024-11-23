<nav>
    <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Beranda</a>
    <a href="buku.php" class="<?= basename($_SERVER['PHP_SELF']) == 'buku.php' ? 'active' : '' ?>">Koleksi Buku</a>
    <a href="anggota.php" class="<?= basename($_SERVER['PHP_SELF']) == 'anggota.php' ? 'active' : '' ?>">Daftar Peminjam</a>
    <a href="peminjaman.php" class="<?= basename($_SERVER['PHP_SELF']) == 'peminjaman.php' ? 'active' : '' ?>">Data Peminjaman</a>
    <a href="add.php" class="<?= basename($_SERVER['PHP_SELF']) == 'add.php' ? 'active' : '' ?>">Pengelolaan Data Buku</a>
</nav>


<style>
    nav a {
    color: white;
    margin: 0 10px;
    text-decoration: none;
    display: inline-block;
    padding: 8px 16px;
    transition: all 0.3s ease;
}

nav a:hover {
    text-decoration: underline;
    background-color: #444;
}

nav a:focus,
nav a:active {
    outline: none;
    box-shadow: 0 0 8px rgba(233, 30, 99, 0.8);
    background-color: #e91e63;
    color: white;
}

nav a.active {
    background-color: #e91e63 !important; /* Warna khusus halaman aktif */
    color: white !important; /* Warna teks */
    font-weight: bold;
}

nav a.active:focus,
nav a.active:active {
    color: white !important;
    background-color: #e91e63 !important;
    box-shadow: none !important;
    outline: none !important;
}


</style>
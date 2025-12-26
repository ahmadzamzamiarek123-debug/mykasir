<?php
// Include file lain biar kode bersih
include 'config.php';
include 'functions.php';

// Mulai session untuk keranjang
session_start();

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Jika form pencarian produk dikirim, ambil berdasarkan cari
if (isset($_POST['cari_produk'])) {
    $nama_produk = $_POST['nama_produk'];
    $query = "SELECT * FROM produk WHERE Nama LIKE '%$nama_produk%' AND Stok > 0";
    $result = $conn->query($query);
} else {
    // Jika belum cari, tampilkan semua produk yang stok > 0
    $query = "SELECT * FROM produk WHERE Stok > 0";
    $result = $conn->query($query);
}

// Jika tambah ke keranjang
if (isset($_POST['tambah_keranjang'])) {
    $id_produk = $_POST['id_produk'];
    $jumlah = $_POST['jumlah'];
    $query = "SELECT * FROM produk WHERE id = $id_produk";
    $produk = $conn->query($query)->fetch_assoc();
    if ($produk && $jumlah <= $produk['Stok']) {
        $_SESSION['keranjang'][] = [
            'id' => $produk['id'],
            'Nama' => $produk['Nama'],
            'Harga' => $produk['Harga'],
            'jumlah' => $jumlah,
            'subtotal' => $produk['Harga'] * $jumlah
        ];
    }
}

// Jika hapus dari keranjang
if (isset($_GET['hapus'])) {
    $index = $_GET['hapus'];
    unset($_SESSION['keranjang'][$index]);
    $_SESSION['keranjang'] = array_values($_SESSION['keranjang']);
}

// Hitung total belanja pakai fungsi
$total_belanja = hitungTotal($_SESSION['keranjang']);

// Jika selesai transaksi
if (isset($_POST['selesai'])) {
    $uang_diterima = $_POST['uang_diterima'];
    $kembalian = $uang_diterima - $total_belanja;
    if ($kembalian >= 0) {
        // Simpan ke laporan
        $conn->query("INSERT INTO transaksi (total_belanja, uang_diterima, kembalian) VALUES ($total_belanja, $uang_diterima, $kembalian)");
        // Kurangi stok
        foreach ($_SESSION['keranjang'] as $item) {
            $conn->query("UPDATE produk SET Stok = Stok - {$item['jumlah']} WHERE id = {$item['id']}");
        }
        // Reset keranjang
        $_SESSION['keranjang'] = [];
        $total_belanja = 0;
        $pesan = "Transaksi selesai! Kembalian: " . formatRupiah($kembalian);
    } else {
        $pesan = "Uang kurang!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyKasir - Dashboard Kasir</title>
    <link rel="stylesheet" href="style.css"> <!-- Pakai CSS terpisah -->
</head>
<body>
    <div class="container">
        <h1>MyKasir - Dashboard Kasir</h1>
        
        <!-- Pencarian Produk (Opsional, untuk filter) -->
        <h2>Cari Produk (Opsional)</h2>
        <form method="POST">
             <div style="text-align: right; margin-bottom: 10px;">
        <a href="logout.php" style="color: #8d6e63; text-decoration: none; font-weight: bold;">Logout / Keluar</a>
            <input type="text" name="nama_produk" placeholder="Masukkan nama produk (atau kosongkan untuk semua)">
            <button type="submit" name="cari_produk">Cari</button>
        </form>
        
        <!-- Daftar Produk (Muncul Otomatis) -->
        <h2>Daftar Produk</h2>
        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <tr><th>Nama</th><th>Harga</th><th>Stok</th><th>Jumlah Beli</th><th>Aksi</th></tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['Nama']; ?></td>
                        <td><?php echo formatRupiah($row['Harga']); ?></td>
                        <td><?php echo $row['Stok']; ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id_produk" value="<?php echo $row['id']; ?>">
                                <input type="number" name="jumlah" min="1" max="<?php echo $row['Stok']; ?>" required>
                        </td>
                        <td>
                                <button type="submit" name="tambah_keranjang">Tambah ke Keranjang</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>Tidak ada produk tersedia.</p>
        <?php endif; ?>
        
        <!-- Keranjang -->
        <h2>Keranjang Belanja</h2>
        <?php if (!empty($_SESSION['keranjang'])): ?>
            <table>
                <tr><th>Nama</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th><th>Aksi</th></tr>
                <?php foreach ($_SESSION['keranjang'] as $index => $item): ?>
                    <tr>
                        <td><?php echo $item['Nama']; ?></td>
                        <td><?php echo formatRupiah($item['Harga']); ?></td>
                        <td><?php echo $item['jumlah']; ?></td>
                        <td><?php echo formatRupiah($item['subtotal']); ?></td>
                        <td><a href="?hapus=<?php echo $index; ?>" style="color:red;">Hapus</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <p class="total">Total Belanja: <?php echo formatRupiah($total_belanja); ?></p>
            
            <!-- Input Bayar -->
            <h2>Input Bayar</h2>
            <form method="POST">
                <input type="number" name="uang_diterima" placeholder="Uang Diterima" required>
                <button type="submit" name="selesai">Selesai / Simpan</button>
            </form>
        <?php else: ?>
            <p>Keranjang kosong.</p>
        <?php endif; ?>
        
        <!-- Pesan -->
        <?php if (isset($pesan)): ?>
            <p class="pesan"><?php echo $pesan; ?></p>
        <?php endif; ?>
    </div>
</body>
</html>

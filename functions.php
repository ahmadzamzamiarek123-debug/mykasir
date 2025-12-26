<?php
// File ini untuk fungsi-fungsi sederhana biar kode utama bersih.

// Fungsi hitung total belanja dari keranjang
function hitungTotal($keranjang) {
    $total = 0;
    foreach ($keranjang as $item) {
        $total += $item['subtotal'];
    }
    return $total;
}

// Fungsi format rupiah (biar tampilan harga bagus)
function formatRupiah($angka) {
    return "Rp " . number_format($angka);
}
?>
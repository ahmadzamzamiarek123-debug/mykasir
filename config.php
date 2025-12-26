<?php
// File ini untuk koneksi ke MySQL. Ganti password jika perlu (default XAMPP kosong).
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mykasir";

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
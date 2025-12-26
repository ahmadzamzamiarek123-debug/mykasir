<?php
// File logout: Reset keranjang dan redirect ke dashboard
session_start(); // Mulai session dulu
session_destroy(); // Hapus semua session (termasuk keranjang)
header("Location: dashboard.php"); // Redirect ke dashboard
exit();
?>
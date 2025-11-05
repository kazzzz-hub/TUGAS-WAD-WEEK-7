<?php
session_start();
include 'koneksi.php';

// Validasi apakah request method adalah GET dan ID tersedia
if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['id'])) {
    $_SESSION['error'] = "Request tidak valid atau ID tidak ditemukan";
    header("Location: tampil_layanan.php");
    exit();
}

// Validasi dan sanitasi ID
$id_layanan = mysqli_real_escape_string($koneksi, $_GET['id']);

// Validasi apakah ID adalah angka
if (!is_numeric($id_layanan)) {
    $_SESSION['error'] = "ID layanan tidak valid";
    header("Location: tampil_layanan.php");
    exit();
}

// Ambil data layanan sebelum dihapus (untuk mendapatkan nama gambar dan notifikasi)
$query_select = "SELECT gambar, nama_layanan FROM layanan WHERE id_layanan = '$id_layanan'";
$result_select = mysqli_query($koneksi, $query_select);

// Cek apakah data layanan ditemukan
if (!$result_select || mysqli_num_rows($result_select) == 0) {
    $_SESSION['error'] = "Layanan tidak ditemukan";
    header("Location: tampil_layanan.php");
    exit();
}

$layanan = mysqli_fetch_assoc($result_select);
$nama_layanan = $layanan['nama_layanan'];
$gambar = $layanan['gambar'];

// Hapus data dari database
$query_delete = "DELETE FROM layanan WHERE id_layanan = '$id_layanan'";

if (mysqli_query($koneksi, $query_delete)) {
    // Jika berhasil dihapus dari database, hapus juga gambar jika ada
    if ($gambar && file_exists('uploads/layanan/' . $gambar)) {
        unlink('uploads/layanan/' . $gambar);
    }
    
    $_SESSION['success'] = "Layanan <strong>'$nama_layanan'</strong> berhasil dihapus";
} else {
    $_SESSION['error'] = "Gagal menghapus layanan: " . mysqli_error($koneksi);
}

// Redirect kembali ke halaman tampil layanan
header("Location: tampil_layanan.php");
exit();
?>
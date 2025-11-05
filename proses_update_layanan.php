<?php
session_start();
include 'koneksi.php';

// Validasi metode request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Metode request tidak valid";
    header("Location: form_edit_layanan.php?id=" . $_POST['id_layanan']);
    exit();
}

// Ambil dan sanitasi data input
$id_layanan = mysqli_real_escape_string($koneksi, $_POST['id_layanan']);
$nama_layanan = mysqli_real_escape_string($koneksi, $_POST['nama_layanan']);
$deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
$harga = mysqli_real_escape_string($koneksi, $_POST['harga']);
$durasi = mysqli_real_escape_string($koneksi, $_POST['durasi']);
$kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
$status = mysqli_real_escape_string($koneksi, $_POST['status']);
$gambar_lama = mysqli_real_escape_string($koneksi, $_POST['gambar_lama']);

// Validasi input
$errors = [];

// Validasi nama layanan
if (empty($nama_layanan)) {
    $errors[] = "Nama layanan tidak boleh kosong";
}

// Validasi deskripsi
if (empty($deskripsi)) {
    $errors[] = "Deskripsi layanan tidak boleh kosong";
}

// Validasi harga
if (empty($harga) || !is_numeric($harga) || $harga <= 0) {
    $errors[] = "Harga harus berupa angka positif";
}

// Validasi durasi
if (empty($durasi) || !is_numeric($durasi) || $durasi <= 0) {
    $errors[] = "Durasi harus berupa angka positif";
}

// Jika ada error, redirect kembali ke form
if (!empty($errors)) {
    $_SESSION['error'] = implode("<br>", $errors);
    header("Location: form_edit_layanan.php?id=" . $id_layanan);
    exit();
}

// Handle upload gambar
$gambar = $gambar_lama;
if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['gambar'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $file_size = $file['size'];
    
    // Validasi tipe file
    if (!in_array($file_extension, $allowed_extensions)) {
        $_SESSION['error'] = "Hanya file gambar JPG, JPEG, PNG, atau GIF yang diizinkan";
        header("Location: form_edit_layanan.php?id=" . $id_layanan);
        exit();
    }
    
    // Validasi ukuran file
    if ($file_size > $max_size) {
        $_SESSION['error'] = "Ukuran file maksimal 2MB";
        header("Location: form_edit_layanan.php?id=" . $id_layanan);
        exit();
    }
    
    // Generate nama file unik
    $gambar = uniqid() . '_' . time() . '.' . $file_extension;
    $upload_dir = 'uploads/layanan/';
    
    // Buat folder jika belum ada
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $upload_dir . $gambar)) {
        // Hapus gambar lama jika ada
        if (!empty($gambar_lama) && file_exists($upload_dir . $gambar_lama)) {
            unlink($upload_dir . $gambar_lama);
        }
    } else {
        $_SESSION['error'] = "Gagal mengupload gambar";
        header("Location: form_edit_layanan.php?id=" . $id_layanan);
        exit();
    }
}

// Update data ke database
$query = "UPDATE layanan SET 
          nama_layanan = '$nama_layanan',
          deskripsi = '$deskripsi',
          harga = '$harga',
          durasi = '$durasi',
          kategori = '$kategori',
          status = '$status',
          gambar = '$gambar',
          updated_at = CURRENT_TIMESTAMP
          WHERE id_layanan = '$id_layanan'";

if (mysqli_query($koneksi, $query)) {
    $_SESSION['success'] = "Data layanan berhasil diupdate";
    header("Location: tampil_layanan.php");
    exit();
} else {
    $_SESSION['error'] = "Gagal update data: " . mysqli_error($koneksi);
    header("Location: form_edit_layanan.php?id=" . $id_layanan);
    exit();
}
?>
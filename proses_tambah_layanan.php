<?php
session_start();
include 'koneksi.php';

// Validasi metode request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Metode request tidak valid";
    header("Location: form_tambah_layanan.php");
    exit();
}

// Ambil dan sanitasi data input
$nama_layanan = mysqli_real_escape_string($koneksi, $_POST['nama_layanan']);
$deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
$harga = mysqli_real_escape_string($koneksi, $_POST['harga']);
$durasi = mysqli_real_escape_string($koneksi, $_POST['durasi']);
$kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
$status = mysqli_real_escape_string($koneksi, $_POST['status']);

// Validasi input
$errors = [];

// Validasi nama layanan
if (empty($nama_layanan)) {
    $errors[] = "Nama layanan tidak boleh kosong";
} elseif (strlen($nama_layanan) > 100) {
    $errors[] = "Nama layanan maksimal 100 karakter";
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
if (empty($durasi) || !is_numeric($durasi) || $durasi < 1) {
    $errors[] = "Durasi harus berupa angka positif minimal 1 hari";
}

// Validasi kategori
if (empty($kategori)) {
    $errors[] = "Kategori harus dipilih";
}

// Validasi status
if (empty($status) || !in_array($status, ['Aktif', 'Tidak Aktif'])) {
    $errors[] = "Status harus dipilih";
}

// Jika ada error, redirect kembali ke form
if (!empty($errors)) {
    $_SESSION['error'] = implode("<br>", $errors);
    header("Location: form_tambah_layanan.php");
    exit();
}

// Handle upload gambar
$gambar = null;
if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['gambar'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
    
    // Get file extension
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Validasi tipe file
    if (!in_array($file_ext, $allowed_extensions)) {
        $_SESSION['error'] = "Hanya file gambar JPG, JPEG, PNG, atau GIF yang diizinkan";
        header("Location: form_tambah_layanan.php");
        exit();
    }
    
    // Validasi ukuran file
    if ($file_size > $max_size) {
        $_SESSION['error'] = "Ukuran file maksimal 2MB";
        header("Location: form_tambah_layanan.php");
        exit();
    }
    
    // Generate nama file unik
    $gambar = uniqid() . '_' . time() . '.' . $file_ext;
    $upload_dir = 'uploads/layanan/';
    
    // Buat folder jika belum ada
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Upload file
    if (!move_uploaded_file($file_tmp, $upload_dir . $gambar)) {
        $_SESSION['error'] = "Gagal mengupload gambar";
        header("Location: form_tambah_layanan.php");
        exit();
    }
}

// Cek duplikasi nama layanan
$check_query = "SELECT COUNT(*) as total FROM layanan WHERE nama_layanan = '$nama_layanan'";
$check_result = mysqli_query($koneksi, $check_query);
$check_data = mysqli_fetch_assoc($check_result);

if ($check_data['total'] > 0) {
    $_SESSION['error'] = "Nama layanan '$nama_layanan' sudah ada dalam database";
    header("Location: form_tambah_layanan.php");
    exit();
}

// Insert data ke database
$query = "INSERT INTO layanan 
          (nama_layanan, deskripsi, harga, durasi, kategori, status, gambar, created_at, updated_at)
          VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "ssdiss", 
    $nama_layanan, 
    $deskripsi, 
    $harga, 
    $durasi, 
    $kategori, 
    $status, 
    $gambar
);

if (mysqli_stmt_execute($stmt)) {
    $id_layanan_baru = mysqli_insert_id($koneksi);
    
    // Log aktivitas (opsional)
    $log_message = "Layanan baru ditambahkan: $nama_layanan (ID: $id_layanan_baru)";
    error_log($log_message);
    
    $_SESSION['success'] = "Data layanan berhasil ditambahkan!";
    header("Location: tampil_layanan.php");
    exit();
} else {
    // Jika gagal insert, hapus gambar yang sudah diupload
    if ($gambar && file_exists($upload_dir . $gambar)) {
        unlink($upload_dir . $gambar);
    }
    
    $_SESSION['error'] = "Gagal menambahkan data: " . mysqli_error($koneksi);
    header("Location: form_tambah_layanan.php");
    exit();
}

// Tutup statement dan koneksi
mysqli_stmt_close($stmt);
mysqli_close($koneksi);
?>
<?php
session_start();
include 'koneksi.php';

// Ambil data layanan berdasarkan ID
if (!isset($_GET['id'])) {
    header("Location: tampil_layanan.php");
    exit();
}

$id_layanan = $_GET['id'];
$query = "SELECT * FROM layanan WHERE id_layanan = '$id_layanan'";
$result = mysqli_query($koneksi, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: tampil_layanan.php");
    exit();
}

$layanan = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Layanan - PT. Selatan</title>
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        textarea {
            height: 100px;
        }
        .btn {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background: #0056b3;
        }
        .error {
            color: red;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Data Layanan</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <form action="proses_update_layanan.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_layanan" value="<?php echo $layanan['id_layanan']; ?>">
            <input type="hidden" name="gambar_lama" value="<?php echo $layanan['gambar']; ?>">
            
            <div class="form-group">
                <label for="nama_layanan">Nama Layanan *</label>
                <input type="text" id="nama_layanan" name="nama_layanan" 
                       value="<?php echo htmlspecialchars($layanan['nama_layanan']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="deskripsi">Deskripsi Layanan *</label>
                <textarea id="deskripsi" name="deskripsi" required><?php echo htmlspecialchars($layanan['deskripsi']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="harga">Harga (Rp) *</label>
                <input type="number" id="harga" name="harga" min="0" step="0.01"
                       value="<?php echo $layanan['harga']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="durasi">Durasi (hari) *</label>
                <input type="number" id="durasi" name="durasi" min="1"
                       value="<?php echo $layanan['durasi']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="kategori">Kategori *</label>
                <select id="kategori" name="kategori" required>
                    <option value="">Pilih Kategori</option>
                    <option value="Web Development" <?php echo $layanan['kategori'] == 'Web Development' ? 'selected' : ''; ?>>Web Development</option>
                    <option value="Mobile Development" <?php echo $layanan['kategori'] == 'Mobile Development' ? 'selected' : ''; ?>>Mobile Development</option>
                    <option value="Cloud Services" <?php echo $layanan['kategori'] == 'Cloud Services' ? 'selected' : ''; ?>>Cloud Services</option>
                    <option value="Consulting" <?php echo $layanan['kategori'] == 'Consulting' ? 'selected' : ''; ?>>Consulting</option>
                    <option value="Maintenance" <?php echo $layanan['kategori'] == 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="status">Status *</label>
                <select id="status" name="status" required>
                    <option value="Aktif" <?php echo $layanan['status'] == 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="Tidak Aktif" <?php echo $layanan['status'] == 'Tidak Aktif' ? 'selected' : ''; ?>>Tidak Aktif</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="gambar">Gambar Layanan</label>
                <?php if ($layanan['gambar']): ?>
                    <div>
                        <img src="uploads/layanan/<?php echo $layanan['gambar']; ?>" 
                             alt="Gambar Layanan" style="max-width: 200px; margin-bottom: 10px;">
                        <br>
                        <small>Gambar saat ini: <?php echo $layanan['gambar']; ?></small>
                    </div>
                <?php endif; ?>
                <input type="file" id="gambar" name="gambar" accept="image/*">
                <small>Kosongkan jika tidak ingin mengubah gambar</small>
            </div>
            
            <button type="submit" class="btn">Update Layanan</button>
            <a href="tampil_layanan.php" style="margin-left: 10px;">Kembali</a>
        </form>
    </div>
</body>
</html>
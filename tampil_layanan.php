<?php
session_start();
include 'koneksi.php';

// Query untuk mengambil data layanan
$query = "SELECT * FROM layanan ORDER BY created_at DESC";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Layanan - PT. Selatan</title>
    <style>
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .notification {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .btn {
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 5px;
            display: inline-block;
            font-size: 14px;
        }
        .btn-add {
            background: #28a745;
            color: white;
            margin-bottom: 20px;
        }
        .btn-add:hover {
            background: #218838;
        }
        .btn-edit {
            background: #007bff;
            color: white;
        }
        .btn-edit:hover {
            background: #0056b3;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background: #c82333;
        }
        .status-aktif {
            color: #28a745;
            font-weight: bold;
        }
        .status-tidak-aktif {
            color: #dc3545;
            font-weight: bold;
        }
        .img-thumbnail {
            max-width: 50px;
            max-height: 50px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Data Layanan PT. Selatan</h2>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="notification success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="notification error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <a href="form_tambah_layanan.php" class="btn btn-add">+ Tambah Layanan Baru</a>
        
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Gambar</th>
                    <th>Nama Layanan</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Durasi</th>
                    <th>Status</th>
                    <th>Tanggal Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>
                            <?php if ($row['gambar']): ?>
                                <img src="uploads/layanan/<?php echo $row['gambar']; ?>" 
                                     alt="Gambar Layanan" class="img-thumbnail"
                                     onerror="this.style.display='none'">
                            <?php else: ?>
                                <span style="color: #6c757d;">No Image</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['nama_layanan']); ?></td>
                        <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                        <td>Rp <?php echo number_format($row['harga'], 2, ',', '.'); ?></td>
                        <td><?php echo $row['durasi']; ?> hari</td>
                        <td>
                            <span class="status-<?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>">
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                        <td>
                            <a href="form_edit_layanan.php?id=<?php echo $row['id_layanan']; ?>" 
                               class="btn btn-edit">Edit</a>
                            <a href="proses_hapus_layanan.php?id=<?php echo $row['id_layanan']; ?>" 
                               class="btn btn-delete" 
                               onclick="return confirmHapus('<?php echo addslashes($row['nama_layanan']); ?>')">
                               Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; color: #6c757d;">
                            Tidak ada data layanan. <a href="form_tambah_layanan.php">Klik di sini</a> untuk menambahkan layanan baru.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php
        // Tampilkan statistik
        $total_query = "SELECT COUNT(*) as total, 
                               SUM(CASE WHEN status = 'Aktif' THEN 1 ELSE 0 END) as aktif,
                               SUM(CASE WHEN status = 'Tidak Aktif' THEN 1 ELSE 0 END) as tidak_aktif
                        FROM layanan";
        $stat_result = mysqli_query($koneksi, $total_query);
        $stat_data = mysqli_fetch_assoc($stat_result);
        ?>
        
        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 4px;">
            <strong>Statistik Layanan:</strong><br>
            Total Layanan: <?php echo $stat_data['total']; ?> | 
            Aktif: <span class="status-aktif"><?php echo $stat_data['aktif']; ?></span> | 
            Tidak Aktif: <span class="status-tidak-aktif"><?php echo $stat_data['tidak_aktif']; ?></span>
        </div>
    </div>

    <script>
    function confirmHapus(namaLayanan) {
        return confirm('Apakah Anda yakin ingin menghapus layanan:\n\"' + namaLayanan + '\"?\n\nTindakan ini tidak dapat dibatalkan!');
    }
    </script>
</body>
</html>

<?php mysqli_close($koneksi); ?>
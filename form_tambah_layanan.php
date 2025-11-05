<?php
session_start();
include 'koneksi.php';

// PROSES TAMBAH DATA JIKA FORM DISUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    // Jika tidak ada error, proses data
    if (empty($errors)) {
        // Handle upload gambar
        $gambar = null;
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['gambar'];
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            $file_name = $file['name'];
            $file_tmp = $file['tmp_name'];
            $file_size = $file['size'];
            
            // Get file extension
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Validasi tipe file
            if (!in_array($file_ext, $allowed_extensions)) {
                $errors[] = "Hanya file gambar JPG, JPEG, PNG, atau GIF yang diizinkan";
            }
            
            // Validasi ukuran file
            if ($file_size > $max_size) {
                $errors[] = "Ukuran file maksimal 2MB";
            }
            
            if (empty($errors)) {
                // Generate nama file unik
                $gambar = uniqid() . '_' . time() . '.' . $file_ext;
                $upload_dir = 'uploads/layanan/';
                
                // Buat folder jika belum ada
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Upload file
                if (!move_uploaded_file($file_tmp, $upload_dir . $gambar)) {
                    $errors[] = "Gagal mengupload gambar";
                }
            }
        }

        // Jika masih tidak ada error, insert ke database
        if (empty($errors)) {
            // Cek duplikasi nama layanan
            $check_query = "SELECT COUNT(*) as total FROM layanan WHERE nama_layanan = '$nama_layanan'";
            $check_result = mysqli_query($koneksi, $check_query);
            $check_data = mysqli_fetch_assoc($check_result);

            if ($check_data['total'] > 0) {
                $errors[] = "Nama layanan '$nama_layanan' sudah ada dalam database";
            } else {
                // SOLUSI: Gunakan query langsung tanpa prepared statement
                if ($gambar) {
                    $query = "INSERT INTO layanan 
                              (nama_layanan, deskripsi, harga, durasi, kategori, status, gambar, created_at, updated_at)
                              VALUES ('$nama_layanan', '$deskripsi', '$harga', '$durasi', '$kategori', '$status', '$gambar', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
                } else {
                    $query = "INSERT INTO layanan 
                              (nama_layanan, deskripsi, harga, durasi, kategori, status, created_at, updated_at)
                              VALUES ('$nama_layanan', '$deskripsi', '$harga', '$durasi', '$kategori', '$status', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
                }

                if (mysqli_query($koneksi, $query)) {
                    $_SESSION['success'] = "Data layanan berhasil ditambahkan!";
                    header("Location: tampil_layanan.php");
                    exit();
                } else {
                    $errors[] = "Gagal menambahkan data: " . mysqli_error($koneksi);
                }
            }
        }
    }
    
    // Jika ada errors, simpan di session untuk ditampilkan
    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}
?>

<!-- BAGIAN HTML TETAP SAMA -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Layanan - PT. Selatan</title>
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0,123,255,0.3);
        }
        textarea {
            height: 120px;
            resize: vertical;
        }
        .btn {
            padding: 12px 25px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-secondary {
            background: #6c757d;
            margin-left: 10px;
            text-decoration: none;
            display: inline-block;
            padding: 12px 25px;
            color: white;
            border-radius: 4px;
        }
        .btn-secondary:hover {
            background: #545b62;
        }
        .error {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
        }
        .success {
            color: #28a745;
            font-size: 14px;
            margin-top: 5px;
        }
        .required {
            color: #dc3545;
        }
        .form-help {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        .preview-image {
            max-width: 200px;
            max-height: 150px;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
            display: none;
        }
        .notification {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .notification.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .notification.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 style="color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px;">Tambah Data Layanan Baru</h2>
        
        <!-- Notifikasi -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="notification error">
                <strong>Error:</strong><br>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="notification success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" id="formLayanan">
            <div class="form-group">
                <label for="nama_layanan">Nama Layanan <span class="required">*</span></label>
                <input type="text" id="nama_layanan" name="nama_layanan" 
                       placeholder="Masukkan nama layanan" required
                       maxlength="100"
                       value="<?php echo isset($_POST['nama_layanan']) ? htmlspecialchars($_POST['nama_layanan']) : ''; ?>">
                <div class="form-help">Maksimal 100 karakter</div>
                <div class="error" id="error_nama"></div>
            </div>
            
            <div class="form-group">
                <label for="deskripsi">Deskripsi Layanan <span class="required">*</span></label>
                <textarea id="deskripsi" name="deskripsi" 
                          placeholder="Masukkan deskripsi lengkap layanan" required><?php echo isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : ''; ?></textarea>
                <div class="form-help">Jelaskan detail dan manfaat layanan</div>
                <div class="error" id="error_deskripsi"></div>
            </div>
            
            <div class="form-group">
                <label for="harga">Harga (Rp) <span class="required">*</span></label>
                <input type="number" id="harga" name="harga" 
                       placeholder="0" min="0" step="0.01" required
                       value="<?php echo isset($_POST['harga']) ? htmlspecialchars($_POST['harga']) : ''; ?>">
                <div class="form-help">Masukkan harga tanpa titik (contoh: 1500000)</div>
                <div class="error" id="error_harga"></div>
            </div>
            
            <div class="form-group">
                <label for="durasi">Durasi (hari) <span class="required">*</span></label>
                <input type="number" id="durasi" name="durasi" 
                       placeholder="0" min="1" required
                       value="<?php echo isset($_POST['durasi']) ? htmlspecialchars($_POST['durasi']) : ''; ?>">
                <div class="form-help">Durasi pengerjaan layanan dalam hari</div>
                <div class="error" id="error_durasi"></div>
            </div>
            
            <div class="form-group">
                <label for="kategori">Kategori <span class="required">*</span></label>
                <select id="kategori" name="kategori" required>
                    <option value="">Pilih Kategori</option>
                    <option value="Web Development" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Web Development') ? 'selected' : ''; ?>>Web Development</option>
                    <option value="Mobile Development" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Mobile Development') ? 'selected' : ''; ?>>Mobile Development</option>
                    <option value="Cloud Services" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Cloud Services') ? 'selected' : ''; ?>>Cloud Services</option>
                    <option value="Consulting" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Consulting') ? 'selected' : ''; ?>>Consulting</option>
                    <option value="Maintenance" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                    <option value="Digital Marketing" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Digital Marketing') ? 'selected' : ''; ?>>Digital Marketing</option>
                    <option value="Data Analytics" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Data Analytics') ? 'selected' : ''; ?>>Data Analytics</option>
                    <option value="UI/UX Design" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'UI/UX Design') ? 'selected' : ''; ?>>UI/UX Design</option>
                </select>
                <div class="error" id="error_kategori"></div>
            </div>
            
            <div class="form-group">
                <label for="status">Status <span class="required">*</span></label>
                <select id="status" name="status" required>
                    <option value="Aktif" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                    <option value="Tidak Aktif" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Tidak Aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
                </select>
                <div class="error" id="error_status"></div>
            </div>
            
            <div class="form-group">
                <label for="gambar">Gambar Layanan</label>
                <input type="file" id="gambar" name="gambar" accept="image/*">
                <div class="form-help">
                    Format: JPG, JPEG, PNG, GIF (Maksimal 2MB)<br>
                    Ukuran disarankan: 800x600 px
                </div>
                <img id="preview" class="preview-image" alt="Preview Gambar">
                <div class="error" id="error_gambar"></div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn">Simpan Layanan</button>
                <a href="tampil_layanan.php" class="btn-secondary">Kembali ke Daftar</a>
                <button type="button" class="btn-secondary" onclick="resetForm()" style="background: #6c757d; border: none; cursor: pointer;">Reset Form</button>
            </div>
        </form>
    </div>

    <script>
        // Preview gambar sebelum upload
        document.getElementById('gambar').addEventListener('change', function(e) {
            const preview = document.getElementById('preview');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });

        // Validasi client-side
        document.getElementById('formLayanan').addEventListener('submit', function(e) {
            let valid = true;
            const errors = {};

            // Validasi nama layanan
            const nama = document.getElementById('nama_layanan').value.trim();
            if (!nama) {
                errors.nama = 'Nama layanan harus diisi';
                valid = false;
            } else if (nama.length > 100) {
                errors.nama = 'Nama layanan maksimal 100 karakter';
                valid = false;
            }

            // Validasi deskripsi
            const deskripsi = document.getElementById('deskripsi').value.trim();
            if (!deskripsi) {
                errors.deskripsi = 'Deskripsi layanan harus diisi';
                valid = false;
            }

            // Validasi harga
            const harga = document.getElementById('harga').value;
            if (!harga || harga <= 0) {
                errors.harga = 'Harga harus lebih dari 0';
                valid = false;
            }

            // Validasi durasi
            const durasi = document.getElementById('durasi').value;
            if (!durasi || durasi < 1) {
                errors.durasi = 'Durasi minimal 1 hari';
                valid = false;
            }

            // Validasi kategori
            const kategori = document.getElementById('kategori').value;
            if (!kategori) {
                errors.kategori = 'Kategori harus dipilih';
                valid = false;
            }

            // Validasi gambar
            const gambar = document.getElementById('gambar').files[0];
            if (gambar) {
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                const maxSize = 2 * 1024 * 1024; // 2MB
                
                if (!allowedTypes.includes(gambar.type)) {
                    errors.gambar = 'Format file harus JPG, JPEG, PNG, atau GIF';
                    valid = false;
                }
                
                if (gambar.size > maxSize) {
                    errors.gambar = 'Ukuran file maksimal 2MB';
                    valid = false;
                }
            }

            // Tampilkan error
            document.querySelectorAll('.error').forEach(el => el.textContent = '');
            
            if (!valid) {
                e.preventDefault();
                for (const [key, value] of Object.entries(errors)) {
                    document.getElementById(`error_${key}`).textContent = value;
                }
            }
        });

        // Fungsi reset form
        function resetForm() {
            if (confirm('Apakah Anda yakin ingin mengosongkan semua field?')) {
                document.getElementById('formLayanan').reset();
                document.getElementById('preview').style.display = 'none';
                document.querySelectorAll('.error').forEach(el => el.textContent = '');
            }
        }
    </script>
</body>
</html>
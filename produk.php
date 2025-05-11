<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'kasir';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

session_start();
if (!isset($_SESSION['UserID']) || $_SESSION['Level'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Handle CREATE
if (isset($_POST['create'])) {

    $namaProduk = $_POST['NamaProduk'] ?? '';
    $harga = $_POST['Harga'] ?? '';
    $stok = $_POST['Stok'] ?? '';
    $img = $_FILES['img']['name'];
    $tmp = $_FILES['img']['tmp_name'];

    $uploadDir = "uploads/";
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $extension = strtolower(pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed)) {
        echo "<script>alert('Tipe file tidak diizinkan!');</script>";
        return;
    }
    
    $imgName = time() . '_' . uniqid() . '.' . $extension;
    $uploadPath = $uploadDir . $imgName;

    if (!empty($namaProduk) && !empty($harga) && !empty($stok) && !empty($img)) {
        if (move_uploaded_file($tmp, $uploadPath)) {
            $sql = "INSERT INTO produk (NamaProduk, Harga, img, Stok) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdsi", $namaProduk, $harga, $imgName, $stok);
            if ($stmt->execute()) {
                echo "<script>alert('Berhasil input'); window.location.href='produk.php';</script>";
            } else {
                echo "<script>alert('Query gagal: " . $stmt->error . "');</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Gagal upload gambar');</script>";
        }
    } else {
        echo "<script>alert('Semua field harus diisi!');</script>";
    }
}



// Handle UPDATE
if (isset($_POST['update'])) {
    $produkId = $_POST['ProdukID'] ?? '';
    $namaProduk = $_POST['NamaProduk'] ?? '';
    $harga = $_POST['Harga'] ?? '';
    $stok = $_POST['Stok'] ?? '';

    // Cek apakah ada file yang diupload
    $gambarBaru = $_FILES['img']['name'] ?? '';
    $tmp = $_FILES['img']['tmp_name'] ?? '';

    if (!empty($produkId)) {
        if (!empty($gambarBaru)) {
            // Rename nama file agar unik
            $ext = pathinfo($gambarBaru, PATHINFO_EXTENSION);
            $namaFileBaru = time() . '_' . uniqid() . '.' . $ext;
            $uploadPath = 'uploads/' . $namaFileBaru;

            // Pindahkan file ke folder uploads
            if (move_uploaded_file($tmp, $uploadPath)) {
                // Update termasuk gambar
                $sql = "UPDATE produk SET NamaProduk=?, Harga=?, Stok=?, img=? WHERE ProdukID=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sdisi", $namaProduk, $harga, $stok, $namaFileBaru, $produkId);
            } else {
                echo "❌ Gagal upload gambar!";
                exit;
            }
        } else {
            // Update tanpa mengubah gambar
            $sql = "UPDATE produk SET NamaProduk=?, Harga=?, Stok=? WHERE ProdukID=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdii", $namaProduk, $harga, $stok, $produkId);
        }

        $stmt->execute();
        $stmt->close();
        header("Location: produk.php");
        exit;
    }
}


// Handle DELETE
if (isset($_GET['delete'])) {
    $produkId = $_GET['delete'];

    try {
        $stmt = $conn->prepare("DELETE FROM produk WHERE ProdukID=?");
        $stmt->bind_param("i", $produkId);
        $stmt->execute();
        $stmt->close();

        header("Location: produk.php");
        exit();
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1451) {
            echo "<script>
                alert('Tidak bisa menghapus produk karena sudah digunakan dalam transaksi!');
                window.location.href = 'produk.php';
            </script>";
            exit();
        } else {
            echo "Error: " . $e->getMessage();
        }
    }
}

//handle cari
if (isset($_GET['cari']) && $_GET['cari'] !== '') {
    $keyword = mysqli_real_escape_string($conn, $_GET['cari']);
    $query = "SELECT * FROM produk WHERE NamaProduk LIKE '%$keyword%'";
} else {
    $query = "SELECT * FROM produk";
}

$result = mysqli_query($conn, $query);
$pelanggan = mysqli_fetch_all($result, MYSQLI_ASSOC);






// Menampilkan data
$result = mysqli_query($conn, $query);
$pelanggan = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Gunakan hasil pencarian (atau semua data jika tidak mencari)
$produk = $pelanggan;
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Manage Stok</title>
	<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,600;0,900;1,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
	<link href="css/produk.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="img/jam.png">
</head>
<body>
	<div class="sideMenu" id="side-menu">
    <a class="closebtn" href="javascript:void(0)" onclick="closeNav()">×</a>
<div class="main-menu">
  <h2>
    <i class="bi bi-clock"></i>
    <div id="clock" style="font-size: 24px; font-weight: bold;"></div>
  </h2>

  <a href="index.php"><i class="bi bi-house-door-fill"></i> Beranda</a>
  <a href="penjualan.php"><i class="bi bi-cash-coin"></i> Transaksi</a>
  <a href="pelanggan.php"><i class="bi bi-people-fill"></i> Pelanggan</a>

  <?php if ($_SESSION['Level'] === 'admin'): ?>
    <a href="laporan.php"><i class="bi bi-bar-chart-fill"></i> Laporan</a>
    <a href="crud_user.php"><i class="bi bi-person-gear"></i> User</a>
    <a href="produk.php"><i class="bi bi-box-seam"></i> Stok</a>
  <?php endif; ?>

  <a href="logout.php"><i class="bi bi-door-open-fill"></i> Log out</a>
</div>

		</div>
	</div>
	<div id="content-area">
    <div class="header" id="main-header" style="background-color: #1f293a; padding: 15px; position: fixed; top: 0; left: 0; width: 100%; z-index: 999;">
  <span onclick="openNav()" style="font-size:30px; cursor:pointer; display: inline-block; width: 100%; color:white; text-align: left;">
    ☰ Cosette Time Atelier
  </span>
</div>
		<div class="content-text">
       

<!-- Form Tambah Data -->
<h3>Form Stok Barang</h3>

    <!-- Form Tambah Data -->
    <form action="" method="post" enctype="multipart/form-data">
    <input type="hidden" name="create" value="1">
    <input type="text" name="NamaProduk" placeholder="Nama Produk" required>
    <input type="number" name="Harga" placeholder="Harga" required>
    <input type="number" name="Stok" placeholder="Stok" required><br>
    <label for="img" style="margin:10px;">Input foto produk:</label>
    <input type="file" name="img" accept="image/*" required><br>
    <button type="submit" class="btn btn-success" style="margin-top:10px;">
        <i class="bi bi-box-seam"></i> Tambah Produk
    </button>
</form>
<div class="d-flex justify-content-center mb-3">
    <form method="GET" action="" class="d-flex" style="max-width: 500px; width: 100%;">
        <input type="text" class="form-control me-2" name="cari" placeholder="Cari nama produk..." value="<?= isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : '' ?>">
        <button class="btn btn-primary me-1" type="submit">Cari</button>
        <a href="pelanggan.php" class="btn btn-secondary">Reset</a>
    </form>
</div>




    <!-- Tabel Data Produk -->
    <table class="table table-bordered">
    <thead>
        <tr class="table-primary text-center">
            <th>ProdukID</th>
            <th>Gambar</th>
            <th>Nama Produk</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($produk as $item) : ?>
            <tr class="text-center">
                <td><?= htmlspecialchars($item['ProdukID']) ?></td>

                <!-- Tampilkan gambar jika ada -->
                <td>
                    <?php if (!empty($item['img'])) : ?>
                        <img src="uploads/<?= htmlspecialchars($item['img']) ?>" alt="gambar produk" width="60" height="60" style="object-fit:cover; border-radius:5px;">
                    <?php else : ?>
                        <span class="text-muted">No image</span>
                    <?php endif; ?>
                </td>

                <td><?= htmlspecialchars($item['NamaProduk']) ?></td>
                <td>Rp <?= number_format($item['Harga'], 2, ',', '.') ?></td>
                <td><?= htmlspecialchars($item['Stok']) ?></td>
                <td>
                    <!-- Form Edit dengan file upload -->
                    <form action="" method="post" enctype="multipart/form-data" style="display:inline-block;">
                        <input type="hidden" name="update" value="1">
                        <input type="hidden" name="ProdukID" value="<?= htmlspecialchars($item['ProdukID']) ?>">

                        <input type="text" name="NamaProduk" value="<?= htmlspecialchars($item['NamaProduk']) ?>" required>
                        <input type="number" name="Harga" value="<?= htmlspecialchars($item['Harga']) ?>" 
                        step="any" required>

                        <input type="number" name="Stok" value="<?= htmlspecialchars($item['Stok']) ?>" required>

                        <!-- Tambahkan input file -->
                        <input type="file" name="img" accept="image/*">

                        <button type="submit" class="btn btn-warning btn-sm">✏️ Edit</button>
                    </form>

                    <!-- Tombol Hapus -->
                    <a href="?delete=<?= htmlspecialchars($item['ProdukID']) ?>" 
                       onclick="return confirm('Hapus produk ini?')" 
                       class="btn btn-danger btn-sm">🗑 Hapus</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

	</div>
	</div>
	<script>
	function openNav() {
  document.getElementById("side-menu").style.width = "300px";
  document.getElementById("content-area").style.marginLeft = "300px";
  document.getElementById("main-header").style.marginLeft = "300px";
  document.getElementById("main-header").style.width = "calc(100% - 300px)";
}

function closeNav() {
  document.getElementById("side-menu").style.width = "0";
  document.getElementById("content-area").style.marginLeft = "0";
  document.getElementById("main-header").style.marginLeft = "0";
  document.getElementById("main-header").style.width = "100%";
}
    function updateClock() {
    const now = new Date();
    const jam = String(now.getHours()).padStart(2, '0');
    const menit = String(now.getMinutes()).padStart(2, '0');
    const detik = String(now.getSeconds()).padStart(2, '0');
    const waktu = `${jam}:${menit}:${detik}`;
    
    document.getElementById('clock').textContent = waktu;
  }

  // Panggil updateClock setiap 1 detik
  setInterval(updateClock, 1000);

  // Jalankan pertama kali supaya langsung tampil
  updateClock();
	</script>
</body>
</html>
            

<body>
    
</body>
</html>

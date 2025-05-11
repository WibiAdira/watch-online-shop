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
if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

// Tambah atau Edit Pelanggan
if (isset($_POST['save'])) {
    $id = $_POST['PelangganID'] ?? '';
    $nama = $_POST['NamaPelanggan'] ?? '';
    $alamat = $_POST['Alamat'] ?? '';
    $nomor = $_POST['NomorTelepon'] ?? '';

    if ($id) {
        $stmt = $conn->prepare("UPDATE pelanggan SET NamaPelanggan=?, Alamat=?, NomorTelepon=? WHERE PelangganID=?");
        $stmt->bind_param("sssi", $nama, $alamat, $nomor, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO pelanggan (NamaPelanggan, Alamat, NomorTelepon) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nama, $alamat, $nomor);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: pelanggan.php");
    exit();
}

// Hapus Pelanggan
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    try {
        $stmt = $conn->prepare("DELETE FROM pelanggan WHERE PelangganID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        header("Location: pelanggan.php");
        exit();
    } catch (mysqli_sql_exception $e) {
        // Cek jika error karena foreign key constraint
        if ($e->getCode() == 1451) {
            echo "<script>
                alert('Tidak bisa menghapus pelanggan karena sudah digunakan dalam transaksi!');
                window.location.href = 'pelanggan.php';
            </script>";
            exit();
        } else {
            // Jika error lain, tampilkan error-nya
            echo "Error: " . $e->getMessage();
        }
    }
}



// Ambil Data Pelanggan
$pelangganResult = $conn->query("SELECT * FROM pelanggan");
$pelanggan = $pelangganResult->fetch_all(MYSQLI_ASSOC);

// Ambil Data untuk Edit
$editData = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM pelanggan WHERE PelangganID=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editData = $result->fetch_assoc();
    $stmt->close();
}

// Ambil data dari database, dengan pencarian jika ada
if (isset($_GET['cari']) && $_GET['cari'] !== '') {
    $keyword = mysqli_real_escape_string($conn, $_GET['cari']);
    $query = "SELECT * FROM pelanggan WHERE NamaPelanggan LIKE '%$keyword%'";
} else {
    $query = "SELECT * FROM pelanggan";
}

$result = mysqli_query($conn, $query);
$pelanggan = mysqli_fetch_all($result, MYSQLI_ASSOC);



$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
	<title>Tambah pelanggan
        
    </title>
	<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,600;0,900;1,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
	<link href="css/pelanggan.css" rel="stylesheet">
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
<h3>Form Manajemen pelanggan</h3>

<form action="" method="post">
            <input type="text" name="NamaPelanggan" placeholder="Nama Pelanggan" required>
            <input type="text" name="Alamat" placeholder="Alamat" required>
            <input type="number" name="NomorTelepon" placeholder="Nomor Telepon" oninput="limitNumberInput(event)" maxlength="13" onkeydown="return restrictInput(event)" required>
            <div class="d-flex justify-content-center">
            <button type="submit" style="margin-top:10px;" class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm" name="save"> <i class="bi bi-person-plus-fill"></i>Tambah Pelanggan</button>
            </div>
        </form>
       <!-- Form Pencarian -->
<div class="d-flex justify-content-center mb-3">
    <form method="GET" action="" class="d-flex" style="max-width: 500px; width: 100%;">
        <input type="text" class="form-control me-2" name="cari" placeholder="Cari nama pelanggan..." value="<?= isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : '' ?>">
        <button class="btn btn-primary me-1" type="submit">Cari</button>
        <a href="pelanggan.php" class="btn btn-secondary">Reset</a>
    </form>
</div>



    <table class="table table-bordered">
        <thead>
            <tr class="table-primary text-center">
                <th>ID</th>
                <th>Nama</th>
                <th>Alamat</th>
                <th>Nomor Telepon</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pelanggan as $p) : ?>
                <tr>
                    <form action="" method="post">
                        <td><?= htmlspecialchars($p['PelangganID']) ?></td>
                        <td><input type="text" name="NamaPelanggan" value="<?= htmlspecialchars($p['NamaPelanggan']) ?>" required></td>
                        <td><input type="text" name="Alamat" value="<?= htmlspecialchars($p['Alamat']) ?>" required></td>
                        <td><input type="text" name="NomorTelepon" value="<?= htmlspecialchars($p['NomorTelepon']) ?>" required></td>
                        <td>
                            <input type="hidden" name="PelangganID" value="<?= $p['PelangganID'] ?>">
                            <button type="submit" class="btn btn-warning btn-sm" name="save">✏️ Edit</button>
                            <a href="?delete=<?= $p['PelangganID'] ?>" onclick="return confirm('Hapus pelanggan ini?')" class="btn btn-danger btn-sm">🗑 Hapus</a>
                        </td>
                    </form>
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

function restrictInput(e) {
    // Mencegah karakter e, E, +, dan -
    if (["e", "E", "+", "-"].includes(e.key)) {
        e.preventDefault();
        return false;
    }
}

function limitNumberInput(event) {
            var inputField = event.target;
            var value = inputField.value;

            // Hanya izinkan input sampai 13 digit
            if (value.length > 13) {
                inputField.value = value.slice(0, 13);
            }
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

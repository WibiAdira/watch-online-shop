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

$sql = "SELECT SUM(TotalHarga) AS total_penjualan FROM penjualan";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$totalPenjualan = $row['total_penjualan'] ?? 0;

$today = date('Y-m-d');
$sql = "SELECT SUM(TotalHarga) AS total_penjualan_hari_ini FROM penjualan WHERE DATE(TanggalPenjualan) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalHariIni = $row['total_penjualan_hari_ini'] ?? 0;

// // Hitung total penjualan bulan ini
// $bulanIni = date('m');
// $tahunIni = date('Y');

// $totalBulanIniResult = $conn->query("
//     SELECT COALESCE(SUM(TotalHarga), 0) AS TotalBulanIni 
//     FROM penjualan 
//     WHERE MONTH(TanggalPenjualan) = $bulanIni AND YEAR(Tanggal) = $tahunIni
// ");

// $totalBulanIniRow = $totalBulanIniResult->fetch_assoc();
// $totalBulanIni = $totalBulanIniRow['TotalBulanIni'];



?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard </title>
  <link rel="icon" type="image/png" href="img/ikan.png">
</head>

<!DOCTYPE html>
<html>
<head>
	<title>Dashboard (Admin)</title>
	<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,600;0,900;1,700&display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
	<link href="css/index.css" rel="stylesheet">
	<link rel="icon" type="image/png" href="img/jam.png">
	<!-- Tambahkan di <head> jika belum ada -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
  .card-body {
    animation: fadeIn 1s ease-in-out;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }
</style>

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
<h3>Dashboard</h3>

<div class="container mt-5">
  <div class="row justify-content-center gx-4">
    <div class="col-md-6 col-lg-5 mb-4">
      <div class="card shadow-lg border-0 h-100">
        <div class="card-body bg-primary text-white rounded-4">
          <h5 class="card-title text-center">💰 Total Penjualan</h5>
          <p class="display-6 text-center fw-bold">
            Rp <?= number_format($totalPenjualan, 2, ',', '.') ?>
          </p>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-5 mb-4">
      <div class="card shadow-lg border-0 h-100">
        <div class="card-body bg-success text-white rounded-4">
          <h5 class="card-title text-center">🗓️ Penjualan Hari Ini</h5>
          <p class="fs-4 text-center fw-bold"><?= date('d-m-Y') ?></p>
          <p class="display-6 text-center fw-bold">
            Rp <?= number_format($totalHariIni, 2, ',', '.') ?>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
<!--  -->

<div class="container mt-5">
  <h4 class="mb-4 text-center">📂 Shortcut Menu</h4>
  <div class="row g-4 justify-content-center">
    
    <div class="col-6 col-md-4 col-lg-3">
      <a href="penjualan.php" class="text-decoration-none">
        <div class="card shadow-sm text-center bg-light h-100 border-0">
          <div class="card-body rounded-4">
            <i class="bi bi-cash-coin fs-1 text-primary mb-2"></i>
            <h6 class="fw-bold text-dark">Transaksi</h6>
          </div>
        </div>
      </a>
    </div>

    <div class="col-6 col-md-4 col-lg-3">
      <a href="pelanggan.php" class="text-decoration-none">
        <div class="card shadow-sm text-center bg-light h-100 border-0">
          <div class="card-body rounded-4">
            <i class="bi bi-people-fill fs-1 text-success mb-2"></i>
            <h6 class="fw-bold text-dark">Pelanggan</h6>
          </div>
        </div>
      </a>
    </div>

    <div class="col-6 col-md-4 col-lg-3">
      <a href="produk.php" class="text-decoration-none">
        <div class="card shadow-sm text-center bg-light h-100 border-0">
          <div class="card-body rounded-4">
            <i class="bi bi-box-seam fs-1 text-warning mb-2"></i>
            <h6 class="fw-bold text-dark">Stok Barang</h6>
          </div>
        </div>
      </a>
    </div>

    <?php if ($_SESSION['Level'] === 'admin'): ?>
    <div class="col-6 col-md-4 col-lg-3">
      <a href="crud_user.php" class="text-decoration-none">
        <div class="card shadow-sm text-center bg-light h-100 border-0">
          <div class="card-body rounded-4">
            <i class="bi bi-person-lines-fill fs-1 text-danger mb-2"></i>
            <h6 class="fw-bold text-dark">Kelola User</h6>
          </div>
        </div>
      </a>
    </div>

    <div class="col-6 col-md-4 col-lg-3">
      <a href="laporan.php" class="text-decoration-none">
        <div class="card shadow-sm text-center bg-light h-100 border-0">
          <div class="card-body rounded-4">
            <i class="bi bi-file-earmark-bar-graph fs-1 text-info mb-2"></i>
            <h6 class="fw-bold text-dark">Laporan</h6>
          </div>
        </div>
      </a>
    </div>
    <?php endif; ?>

  </div>
</div>

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

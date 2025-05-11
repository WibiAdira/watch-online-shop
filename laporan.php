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

// Ambil Data Penjualan & Produk untuk Dropdown
$penjualanResult = $conn->query("SELECT PenjualanID FROM penjualan");
$produkResult = $conn->query("SELECT ProdukID, NamaProduk, Harga FROM produk");

// Tambah atau Edit Detail Penjualan
if (isset($_POST['save'])) {
    $detailID = $_POST['DetailID'] ?? '';
    $penjualanID = $_POST['PenjualanID'] ?? '';
    $produkID = $_POST['ProdukID'] ?? '';
    $jumlah = $_POST['Jumlahproduk'] ?? 1;

    // Ambil Harga Produk
    $hargaResult = $conn->query("SELECT Harga FROM produk WHERE ProdukID = '$produkID'");
    $hargaRow = $hargaResult->fetch_assoc();
    $harga = $hargaRow['Harga'] ?? 0;
    $subtotal = $harga * $jumlah;

    if ($detailID) {
        $stmt = $conn->prepare("UPDATE detailpenjualan SET ProdukID=?, Jumlahproduk=?, Subtotal=? WHERE DetailID=?");
        $stmt->bind_param("iiii", $produkID, $jumlah, $subtotal, $detailID);
    } else {
        $stmt = $conn->prepare("INSERT INTO detailpenjualan (PenjualanID, ProdukID, Jumlahproduk, Subtotal) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiii", $penjualanID, $produkID, $jumlah, $subtotal);
    }
    $stmt->execute();
    $stmt->close();

    // Update Total Harga di Penjualan
    $conn->query("UPDATE penjualan 
                  SET TotalHarga = (SELECT COALESCE(SUM(Subtotal), 0) FROM detailpenjualan WHERE PenjualanID = '$penjualanID') 
                  WHERE PenjualanID = '$penjualanID'");

    header("Location: laporan.php");
    exit();
}

// Hapus Detail Penjualan
if (isset($_GET['delete'])) {
    $detailID = $_GET['delete'];

    // Dapatkan PenjualanID sebelum menghapus
    $penjualanQuery = $conn->query("SELECT PenjualanID FROM detailpenjualan WHERE DetailID = '$detailID'");
    $penjualanRow = $penjualanQuery->fetch_assoc();
    $penjualanID = $penjualanRow['PenjualanID'];

    // Hapus Detail Penjualan
    $stmt = $conn->prepare("DELETE FROM detailpenjualan WHERE DetailID=?");
    $stmt->bind_param("i", $detailID);
    $stmt->execute();
    $stmt->close();

    // Update Total Harga di Penjualan
    $conn->query("UPDATE penjualan 
                  SET TotalHarga = (SELECT COALESCE(SUM(Subtotal), 0) FROM detailpenjualan WHERE PenjualanID = '$penjualanID') 
                  WHERE PenjualanID = '$penjualanID'");

    header("Location: laporan.php");
    exit();
}

// Ambil Data Detail Penjualan
$detailResult = $conn->query("SELECT d.DetailID, d.PenjualanID, d.ProdukID, p.NamaProduk, d.Jumlahproduk, d.Subtotal 
                              FROM detailpenjualan d
                              JOIN produk p ON d.ProdukID = p.ProdukID");
$detailpenjualan = $detailResult->fetch_all(MYSQLI_ASSOC);

$conn->close();

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Admin</title>
</head>

<!DOCTYPE html>
<html>
<head>
	<title>Laporan Detail Penjualan</title>
	<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,600;0,900;1,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
	<link href="css/laporan.css" rel="stylesheet">
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
		
    <h3>Detail Penjualan</h3>
    <div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr class="table-primary text-center">
                <th>ID Detail</th>
                <th>Penjualan ID</th>
                <th>Produk</th>
                <th>Jumlah</th>
                <th>Subtotal</th>
                
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detailpenjualan as $d) : ?>
                <tr>
    <td><?= htmlspecialchars($d['DetailID']) ?></td>
    <td><?= htmlspecialchars($d['PenjualanID']) ?></td>
    <td><?= htmlspecialchars($d['NamaProduk']) ?></td>
    <td><?= htmlspecialchars($d['Jumlahproduk']) ?></td>
    <td>Rp <?= number_format($d['Subtotal'], 0, ',', '.') ?></td>
   
</tr>

            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="cetak_pdf.php?d1=<?php echo $d1; ?>&d2=<?php echo $d2; ?>" target="_blank">
    <button class="btn btn-danger btn-sm"><i class="fa fa-file-pdf-o"></i> Cetak PDF</button>
</a>

			
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

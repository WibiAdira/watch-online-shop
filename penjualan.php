<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'kasir';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
// Ambil data pelanggan untuk combo box
$pelangganResult = $conn->query("SELECT PelangganID, NamaPelanggan FROM pelanggan");
$pelanggan = $pelangganResult->fetch_all(MYSQLI_ASSOC);


session_start();
if (!isset($_SESSION['UserID']) ) {
    header("Location: login.php");
    exit();
}
$totalHarga = 0;
if (isset($_POST['sell'])) {
    $produkId = $_POST['ProdukID'] ?? '';
    $jumlah = $_POST['Jumlah'] ?? 0;
    $pelangganId = $_POST['PelangganID'] ?? ''; // Ambil PelangganID dari form

    if (!empty($produkId) && $jumlah > 0 && !empty($pelangganId)) {
        // Cek stok dan harga sebelum penjualan
        $check = $conn->prepare("SELECT Stok, Harga FROM produk WHERE ProdukID = ?");
        $check->bind_param("i", $produkId);
        $check->execute();
        $result = $check->get_result();
        $row = $result->fetch_assoc();
        $check->close();

        if ($row && $row['Stok'] >= $jumlah) {
            $totalHarga = $jumlah * $row['Harga'];

            // Simpan transaksi ke tabel penjualan
            $insertPenjualan = $conn->prepare("INSERT INTO penjualan (TanggalPenjualan, TotalHarga, PelangganID) VALUES (NOW(), ?, ?)");
            $insertPenjualan->bind_param("di", $totalHarga, $pelangganId);
            $insertPenjualan->execute();
            // Ambil ID terakhir yang baru saja dimasukkan
            $penjualanID = $conn->insert_id;
            $insertPenjualan->close();

            // Kurangi stok produk
            $sql = "UPDATE produk SET Stok = Stok - ? WHERE ProdukID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $jumlah, $produkId);
            $stmt->execute();
            $stmt->close();


            // Hitung subtotal
            $subtotal = $jumlah * $row['Harga']; 

            // Simpan detail transaksi ke tabel detailpenjualan
            $stmt = $conn->prepare("INSERT INTO detailpenjualan (PenjualanID, ProdukID, Jumlahproduk, Subtotal) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiii", $penjualanID, $produkId, $jumlah, $subtotal);
            $stmt->execute();
            $stmt->close();



            echo "<script>alert('Transaksi berhasil!'); window.location.href='cetak_struk.php?id=$penjualanID';</script>";
exit();

        } else {
            echo "<script>alert('Stok tidak mencukupi atau produk tidak ditemukan!'); window.location.href='penjualan.php';</script>";
        }
    } else {
        echo "<script>alert('Silakan isi semua data dengan benar!');</script>";
    }
}

// Ambil data penjualan
$sql = "SELECT p.PenjualanID , pl.NamaPelanggan, p.TanggalPenjualan, p.TotalHarga 
        FROM penjualan p 
        JOIN pelanggan pl ON p.PelangganID = pl.PelangganID 
        ORDER BY p.TanggalPenjualan DESC";
$penjualanResult = $conn->query($sql);

// Ambil data produk
$result = $conn->query("SELECT * FROM produk");
$produk = $result->fetch_all(MYSQLI_ASSOC);

// Ambil produk untuk combo box
$produkCombo = $conn->query("SELECT ProdukID, NamaProduk FROM produk");
$comboList = $produkCombo->fetch_all(MYSQLI_ASSOC);

$conn->close();


// Handle permintaan harga langsung dalam file yang sama
if (isset($_GET['get_harga']) && isset($_GET['ProdukID'])) {
    $produkId = $_GET['ProdukID'];
    $conn = new mysqli($host, $user, $password, $dbname);
    $stmt = $conn->prepare("SELECT Harga FROM produk WHERE ProdukID = ?");
    $stmt->bind_param("i", $produkId);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    echo json_encode(["harga" => $data['Harga'] ?? 0]);
    $stmt->close();
    $conn->close();
    exit;
}

function updateTotalHarga($conn, $penjualanID) {
    // Hitung ulang total harga berdasarkan jumlah subtotal di detailpenjualan
    $stmt = $conn->prepare("SELECT SUM(Subtotal) FROM detailpenjualan WHERE PenjualanID = ?");
    $stmt->bind_param("i", $penjualanID);
    $stmt->execute();
    $stmt->bind_result($totalHarga);
    $stmt->fetch();
    $stmt->close();

    // Update total harga di tabel penjualan
    $stmt = $conn->prepare("UPDATE penjualan SET TotalHarga = ? WHERE PenjualanID = ?");
    $stmt->bind_param("di", $totalHarga, $penjualanID);
    $stmt->execute();
    $stmt->close();
}





?>


<!DOCTYPE html>
<html lang="en">
<head>
	<title>Form Penjualan</title>
	<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,600;0,900;1,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
	<link href="css/penjualan.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="img/jam.png">
    <style>
        body { font-family: Arial, sans-serif;  }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        form { margin-top: 20px; }
        .total { margin-top: 20px; font-weight: bold; }
    </style>
    <script>
        function fetchHarga() {
            const produkId = document.getElementById('ProdukID').value;
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `?get_harga=1&ProdukID=${produkId}`, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    document.getElementById('harga').value = response.harga ?? 0;
                }
            };
            xhr.send();
        }

        function hitungTotal() {
            const harga = parseFloat(document.getElementById('harga').value) || 0;
            const jumlah = parseInt(document.getElementById('jumlah').value) || 0;
            const total = harga * jumlah;
            document.getElementById('totalHarga').value = total;
        }
    </script>
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
       


<h3>Form Penjualan Produk</h3>


<!-- Tabel Data Produk -->
 <h4>list produk</h4>
 <div class="container">
    <div class="row">
        <?php foreach ($produk as $item) : ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm">
                    <!-- Foto Produk -->
                    <img src="uploads/<?= htmlspecialchars($item['img']) ?>"
                         class="card-img-top"
                         alt="Produk"
                         style="height: 200px; object-fit: cover;">

                    <!-- Isi Card -->
                    <div class="card-body text-center">
                        <p class="mb-1 text-muted">ID: <?= htmlspecialchars($item['ProdukID']) ?></p>
                        <h5 class="card-title mb-1"><?= htmlspecialchars($item['NamaProduk']) ?></h5>
                        <p class="card-text text-success fw-bold">
                            Rp <?= number_format($item['Harga'], 2, ',', '.') ?>
                        </p>
                    </div>

                    <!-- Footer Card: Stok -->
                    <div class="card-footer text-start">
                        <small class="text-muted">Stok: <?= htmlspecialchars($item['Stok']) ?></small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

    <!-- Form Penjualan Produk -->
    <form action="penjualan.php" method="post">
        <input type="hidden" name="sell" value="1">
        <select id="ProdukID" name="ProdukID" onchange="fetchHarga()" required>
    <option value="">-- Pilih Produk --</option>
    <?php foreach ($comboList as $row): ?>
    <option value="<?= $row['ProdukID'] ?>"><?= $row['NamaProduk'] ?></option>
<?php endforeach; ?>

</select>


        <input type="number" id="harga" placeholder="Harga Produk" disabled>
        <input type="number" id="jumlah" name="Jumlah" placeholder="Jumlah yang Dijual" oninput="hitungTotal()" required>
        <input type="number" id="totalHarga" name="Subtotal" placeholder="Total Harga" readonly>


         <!-- Combo Box untuk Pelanggan -->
         <select id="PelangganID" name="PelangganID" required>
            <option value="">Pilih Pelanggan</option>
            <?php foreach ($pelanggan as $p) : ?>
                <option value="<?= htmlspecialchars($p['PelangganID']) ?>">
                    <?= htmlspecialchars($p['NamaPelanggan']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-success btn-lg w-100" name="sell"> <i class="bi bi-cart-plus"></i> Beli</button>
    </form>
    

<div class="container mt-5">
  <h4 class="mb-4 text-center">🧾 Riwayat Penjualan</h4>
  <div class="table-responsive">
    <table class="table table-striped table-bordered text-center">
      <thead class="table-dark">
        <tr>
          <th>No</th>
          <th>ID Penjualan</th>
          <th>Nama Pelanggan</th>
          <th>Tanggal</th>
          <th>Total Harga</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $no = 1;
        if ($penjualanResult->num_rows > 0):
            while($row = $penjualanResult->fetch_assoc()):           
        ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= $row['PenjualanID'] ?></td>
          <td><?= $row['NamaPelanggan'] ?></td>
          <td><?= date('d-m-Y', strtotime($row['TanggalPenjualan'])) ?></td>
          <td>Rp <?= number_format($row['TotalHarga'], 2, ',', '.') ?></td>
        </tr>
        <?php endwhile; else: ?>
        <tr>
          <td colspan="5">Belum ada data penjualan.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

		</div>
	</div>
    

	<script>
     function hitungTotal() {
    const harga = parseFloat(document.getElementById('harga').value) || 0;
    const jumlah = parseInt(document.getElementById('jumlah').value) || 0;
    const total = harga * jumlah;
    document.getElementById('totalHarga').value = total; // Bisa terlihat
}

     
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

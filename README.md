
<!DOCTYPE html>
<html>
<head>
	<title>Toggle Sidebar Navigation html css javascript</title>
	<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,600;0,900;1,700&display=swap" rel="stylesheet">
	<link href="style.css" rel="stylesheet">
</head>
<body>
	<div class="sideMenu" id="side-menu">
		<a class="closebtn" href="javascript:void(0)" onclick="closeNav()">×</a>
		<div class="main-menu">
			<h2>SideMenu</h2><a href="#"><i class="fa fa-home"></i>Home</a> <a href="#"><i class="fa fa-users"></i>About</a> <a href="#"><i class="fa fa-book"></i>Portfolio</a> <a href="#"><i class="fa fa-file-o"></i>Services</a> <a href="#"><i class="fa fa-phone"></i>Contact</a>
		</div>
	</div>
	<div id="content-area">
		<span onclick="openNav()" style="font-size:30px;cursor:pointer">☰ TOGGLEMENU</span>
		<div class="content-text">
			<h2>Toggle Sidebar Navigation</h2>
			<h3>html css javascript</h3>
		</div>
	</div>
	<script>
	function openNav() {
	 document.getElementById("side-menu").style.width = "300px";
	 document.getElementById("content-area").style.marginLeft = "300px"; 
	}

	function closeNav() {
	 document.getElementById("side-menu").style.width = "0";
	 document.getElementById("content-area").style.marginLeft= "0";  
	}
	</script>
</body>
</html>
            




			<h2>CRUD User</h2>

    <!-- Form Tambah Data -->
    <form action="" method="post">
        <input type="hidden" name="create" value="1">
        <input type="text" name="Username" placeholder="Username" required>
        <input type="text" name="Password" placeholder="Password" required>
        <input type="text" name="NamaUser" placeholder="Nama User" required>
        <select name="Level" required>
            <option value="admin">Admin</option>
            <option value="user">User</option>
        </select>
        <button type="submit">Tambah User</button>
    </form>

    <!-- Tabel Data User -->
    <table>
        <thead>
            <tr>
                <th>UserID</th>
                <th>Username</th>
                <th>Password</th>
                <th>Nama User</th>
                <th>Level</th>
                <th>Timestamp</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user) : ?>
                <tr>
                    <td><?= $user['UserID'] ?></td>
                    <td><?= $user['Username'] ?></td>
                    <td><?= $user['Password'] ?></td>
                    <td><?= $user['NamaUser'] ?></td>
                    <td><?= $user['Level'] ?></td>
                    <td><?= $user['TimeStamp'] ?></td>
                    <td>
                        <!-- Form Edit -->
                        <form action="" method="post" style="display:inline-block;">
                            <input type="hidden" name="update" value="1">
                            <input type="hidden" name="UserID" value="<?= $user['UserID'] ?>">
                            <input type="text" name="Username" value="<?= $user['Username'] ?>" required>
                            <input type="text" name="Password" value="<?= $user['Password'] ?>" required>
                            <input type="text" name="NamaUser" value="<?= $user['NamaUser'] ?>" required>
                            <select name="Level" required>
                                <option value="admin" <?= $user['Level'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="user" <?= $user['Level'] == 'user' ? 'selected' : '' ?>>User</option>
                            </select>
                            <button type="submit">Edit</button>
                        </form>
                        <!-- Tombol Hapus -->
                        <a href="?delete=<?= $user['UserID'] ?>" onclick="return confirm('Hapus user ini?')">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>


    //              $subtotal = $_POST['Subtotal'] ?? 0;

    // Tambah produk ke detail penjualan
if (isset($_POST['add_detail'])) {
    $penjualanID = $_POST['PenjualanID'];
    $produkID = $_POST['ProdukID'];
    $jumlah = $_POST['Jumlahproduk'];

    // Ambil harga produk dari database
    $stmt = $conn->prepare("SELECT Harga FROM produk WHERE ProdukID = ?");
    $stmt->bind_param("i", $produkID);
    $stmt->execute();
    $stmt->bind_result($harga);
    $stmt->fetch();
    $stmt->close();

    // Debugging: Cek apakah harga valid
    if ($harga === null || $harga <= 0) {
        die("❌ Error: Produk dengan ID $produkID tidak ditemukan atau harga tidak valid.");
    }

    // Hitung subtotal
    $subtotal = $harga * $jumlah;

    // Debugging: Cek apakah subtotal berhasil dihitung
    if ($subtotal === null || $subtotal <= 0) {
        die("❌ Error: Subtotal tidak bisa dihitung. Harga: $harga, Jumlah: $jumlah");
    }

    // Masukkan ke detail penjualan
    $stmt = $conn->prepare("INSERT INTO detailpenjualan (PenjualanID, ProdukID, Jumlahproduk, Subtotal) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiii", $penjualanID, $ProdukID, $jumlah, $subtotal);
    $stmt->execute();
    $stmt->close();

    // Update TotalHarga di tabel penjualan
    updateTotalHarga($conn, $penjualanID);
}

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6 mb-4">
      <div class="card shadow-lg border-0">
        <div class="card-body bg-primary text-white rounded-4">
          <h5 class="card-title text-center">💰 Total Penjualan</h5>
          <p class="display-6 text-center fw-bold">
            Rp <?= number_format($totalPenjualan, 2, ',', '.') ?>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
  <div class="row justify-content-center">
    <div class="col-md-3 mb-4">
      <div class="card shadow-lg border-0">
        <div class="card-body bg-primary text-white rounded-4">
		<h5>Penjualan Hari Ini (<?= date('d-m-Y') ?>):</h5>
		<p><strong>Rp <?= number_format($totalHariIni, 2, ',', '.') ?></strong></p>
        </div>
      </div>
    </div>
  </div>
</div>#   w a t c h - o n l i n e - s h o p  
 
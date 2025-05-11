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
    $username = $_POST['Username'];
    $password = $_POST['Password'];
    $namaUser = $_POST['NamaUser'];
    $level = $_POST['Level'];

    $sql = "INSERT INTO user (Username, Password, NamaUser, Level) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $password, $namaUser, $level);
    $stmt->execute();
    $stmt->close();
    header("Location: crud_user.php");
}


// Handle UPDATE
if (isset($_POST['update'])) {
    $userId = $_POST['UserID'];
    $username = $_POST['Username'];
    $password = $_POST['Password'];
    $namaUser = $_POST['NamaUser'];
    $level = $_POST['Level'];

    $sql = "UPDATE user SET Username=?, Password=?, NamaUser=?, Level=? WHERE UserID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $username, $password, $namaUser, $level, $userId);
    $stmt->execute();
    $stmt->close();
    header("Location: crud_user.php");
}

// Handle DELETE
if (isset($_GET['delete'])) {
    $userId = $_GET['delete'];
    $sql = "DELETE FROM user WHERE UserID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
    header("Location: crud_user.php");
}

// Menampilkan data
$result = $conn->query("SELECT * FROM user");
$users = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Manage User</title>
	<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,600;0,900;1,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
	<link href="css/crud_user.css" rel="stylesheet">
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
<form action="" method="post">
    <input type="hidden" name="create" value="1">
    <input type="text"  name="Username" placeholder="Username" required>
    <input type="Password" name="Password" placeholder="Password" required>
    <input type="text" name="NamaUser" placeholder="Nama User" required>
    <!-- <input type="numbers" name="UserID" placeholder="ID User" required> -->
    <select name="Level" required>
        <option value="admin">Admin</option>
        <option value="user">User</option>
    </select>
    <button type="submit" style="margin-top:10px;" class="btn btn-success"><i class="bi bi-person-plus-fill"></i> Tambah User</button>
</form>

<!-- Tabel Data User -->
<table class="table table-bordered">
    <thead>
        <tr class="table-primary text-center">
            <th>UserID</th>
            <th>Username</th>
            <th>Password</th>
            <th>Nama User</th>
            <th>Level</th>
            <th>Timestamp</th>
            <th>Edit/Hapus data</th>
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
                        <input type="Password" name="Password" value="<?= $user['Password'] ?>" required>
                        <input type="text" name="NamaUser" value="<?= $user['NamaUser'] ?>" required>
                        <select name="Level" required>
                            <option value="admin" <?= $user['Level'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="user" <?= $user['Level'] == 'user' ? 'selected' : '' ?>>User</option>
                        </select>
                        <button type="submit" class="btn btn-warning btn-sm">✏️ Edit</button>
                    </form>
                    <!-- Tombol Hapus -->
                    <a href="?delete=<?= $user['UserID'] ?>" onclick="return confirm('Hapus user ini?')" class="btn btn-danger btn-sm">🗑 Hapus</a>
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

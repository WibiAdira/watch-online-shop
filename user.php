<?php



session_start();
if (!isset($_SESSION['UserID']) || $_SESSION['Level'] != 'user') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Manager</title>
</head>

<!DOCTYPE html>
<html>
<head>
	<title>Toggle Sidebar Navigation html css javascript</title>
	<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,600;0,900;1,700&display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
	<link href="css/user.css" rel="stylesheet">
	<link rel="icon" type="image/png" href="img/jam.png">
</head>
<body>
	<div class="sideMenu" id="side-menu">
		<a class="closebtn" href="javascript:void(0)" onclick="closeNav()">×</a>
		<div class="main-menu">
		<h2><i class="bi bi-clock"></i><div id="clock" style="font-size: 24px; font-weight: bold;"></div></h2>
			<a href="index.php"><i class="bi bi-house-door-fill"></i> Beranda</a>
			<a href="penjualan.php"><i class="fa fa-home"></i>transaksi</a> 
			<a href="pelanggan.php"><i class="fa fa-users"></i>Pelanggan</a> 
			<a href="logout.php"><i class="bi bi-door-open"></i>Log out</a> 
		</div>
	</div>
	<div id="content-area">
		<span onclick="openNav()" style="font-size:30px;cursor:pointer">☰ Cosette Time Atelier</span>
		<div class="content-text">
			<h2>Selamat datang, <?php echo $_SESSION['NamaUser']; ?> (Kasir)!</h2>
			
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
            
  
</html>

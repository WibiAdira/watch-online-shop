
<!DOCTYPE html>
<html>
<head>
	<title>Toggle Sidebar Navigation html css javascript</title>
	<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,600;0,900;1,700&display=swap" rel="stylesheet">
	<link href="sidebar.css" rel="stylesheet">
</head>
<body>
	<div class="sideMenu" id="side-menu">
		<a class="closebtn" href="javascript:void(0)" onclick="closeNav()">×</a>
		<div class="main-menu">
			<h2>SideMenu</h2><a href="#"><i class="fa fa-home"></i>Home</a> <a href="#"><i class="fa fa-users"></i>About</a> <a href="#"><i class="fa fa-book"></i>Portfolio</a> <a href="#"><i class="fa fa-file-o"></i>Services</a> <a href="#"><i class="fa fa-phone"></i>Contact</a>
		</div>
	</div>
	<div id="content-area">
		<span onclick="openNav()" style="font-size:30px;cursor:pointer">☰ WAROENG WIBIAZ</span>
		<!-- <div class="content-text">
			<h2>Toggle Sidebar Navigation</h2>
			<h3>html css javascript</h3>
		</div> -->
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
            
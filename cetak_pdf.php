<?php
require(__DIR__ . '/fpdf/fpdf.php');

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

// Ambil tanggal jika ada, kalau tidak pakai hari ini
$d1 = isset($_GET['d1']) ? $_GET['d1'] : date("Y-m-d");
$d2 = isset($_GET['d2']) ? $_GET['d2'] : date("Y-m-d");

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(190, 10, 'LAPORAN DETAIL PENJUALAN', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
// $pdf->Cell(190, 10, 'Periode: ' . $d1 . ' - ' . $d2, 0, 1, 'C');
$pdf->Ln(10);

// Header tabel
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(10, 7, 'No', 1);
$pdf->Cell(40, 7, 'Tanggal', 1);
$pdf->Cell(60, 7, 'Nama Produk', 1);
$pdf->Cell(20, 7, 'Jumlah', 1);
$pdf->Cell(30, 7, 'Harga Satuan', 1);
$pdf->Cell(30, 7, 'Subtotal', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 10);
$total = 0;
$no = 1;

// Ambil data penjualan dengan join ke produk
$qry = $conn->query("
    SELECT dp.*, p.TanggalPenjualan, pr.NamaProduk 
    FROM detailpenjualan dp
    LEFT JOIN penjualan p ON dp.PenjualanID = p.PenjualanID
    LEFT JOIN produk pr ON dp.ProdukID = pr.ProdukID
    
");

while ($row = $qry->fetch_assoc()) {
    $subtotal = $row['Jumlahproduk'] * $row['Subtotal'];
    $total += $subtotal;

    $pdf->Cell(10, 7, $no++, 1);
    $pdf->Cell(40, 7, date("M d, Y", strtotime($row['TanggalPenjualan'])), 1);
    $pdf->Cell(60, 7, $row['NamaProduk'], 1);
    $pdf->Cell(20, 7, $row['Jumlahproduk'], 1, 0, 'C');
    $pdf->Cell(30, 7, 'Rp ' . number_format($row['Subtotal'], 2, ',', '.'), 1, 0, 'R');
    $pdf->Cell(30, 7, 'Rp ' . number_format($subtotal, 2, ',', '.'), 1, 0, 'R');
    $pdf->Ln();
}

// Total akhir
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(160, 7, 'Total', 1, 0, 'R');
$pdf->Cell(30, 7, 'Rp ' . number_format($total, 2, ',', '.'), 1, 0, 'R');

$pdf->Output();
?>

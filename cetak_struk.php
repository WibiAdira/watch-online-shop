<?php
ob_start(); // Start output buffering

require('fpdf/fpdf.php');

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'kasir';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if (!isset($_GET['id'])) {
    die("ID Transaksi tidak ditemukan!");
}

$penjualanID = $_GET['id'];

$query = $conn->query("
    SELECT p.TanggalPenjualan, p.TotalHarga, pl.NamaPelanggan, dp.Jumlahproduk, pr.NamaProduk, pr.Harga 
    FROM detailpenjualan dp
    JOIN penjualan p ON dp.PenjualanID = p.PenjualanID
    JOIN pelanggan pl ON p.PelangganID = pl.PelangganID
    JOIN produk pr ON dp.ProdukID = pr.ProdukID
    WHERE dp.PenjualanID = '$penjualanID'
");

if ($query->num_rows == 0) {
    die("Data transaksi tidak ditemukan.");
}

// Ukuran kertas struk mini
$pdf = new FPDF('P', 'mm', array(80, 150)); 
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(60, 7, 'Struk Pembelian', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(60, 5, 'Tanggal: ' . date("d-m-Y"), 0, 1, 'C');
$pdf->Ln(3);

// Data pertama untuk info pelanggan
$row = $query->fetch_assoc();
$pdf->Cell(60, 5, 'Pelanggan: ' . $row['NamaPelanggan'], 0, 1, 'C');
$pdf->Ln(5);

// Header tabel
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(25, 7, 'Nama', 1, 0, 'C');
$pdf->Cell(8, 7, 'Qty', 1, 0, 'C');
$pdf->Cell(15, 7, 'Harga', 1, 0, 'C');
$pdf->Cell(20, 7, 'Subtotal', 1, 1, 'C');

$total = 0;
$query->data_seek(0);
$pdf->SetFont('Arial', '', 6);

while ($row = $query->fetch_assoc()) {
    $jumlah = isset($row['Jumlahproduk']) ? $row['Jumlahproduk'] : 0;
    $harga = $row['Harga'];
    $subtotal = $jumlah * $harga;
    $total += $subtotal;

    $pdf->Cell(25, 7, $row['NamaProduk'], 1);
    $pdf->Cell(8, 7, $jumlah, 1, 0, 'C');
    $pdf->Cell(15, 7, 'Rp ' . number_format($harga, 0, ',', '.'), 1, 0, 'R');
    $pdf->Cell(20, 7, 'Rp ' . number_format($subtotal, 0, ',', '.'), 1, 1, 'R');
}

// Total
$pdf->SetFont('Arial', 'B', 7);
$pdf->Cell(48, 7, 'Total', 1, 0, 'C');
$pdf->Cell(20, 7, 'Rp ' . number_format($total, 0, ',', '.'), 1, 1, 'R');

$pdf->Ln(8);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(60, 5, 'Terima Kasih telah berbelanja!', 0, 1, 'C');

ob_end_clean(); // Bersihkan semua output sebelum kirim PDF
$pdf->Output();
?>
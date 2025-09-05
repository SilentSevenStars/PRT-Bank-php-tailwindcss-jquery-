<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'config/class/Database.php';
require_once("config/class/fpdf.php"); 

$mydb = new Database;
$userId = $_SESSION['user_id'];

$type = $_GET['type'] ?? 'csv';

$stmt = $mydb->conn->prepare("
    SELECT id, type, amount, status, created_at 
    FROM transactions 
    WHERE user_id=? 
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

if ($type === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=transactions.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','Type','Amount','Status','Date']);
    foreach ($data as $row) {
        fputcsv($out, [$row['id'],$row['type'],$row['amount'],$row['status'],$row['created_at']]);
    }
    fclose($out);
    exit;
}

if ($type === 'xlsx') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename=transactions.xls');
    echo "ID\tType\tAmount\tStatus\tDate\n";
    foreach ($data as $row) {
        echo "{$row['id']}\t{$row['type']}\t{$row['amount']}\t{$row['status']}\t{$row['created_at']}\n";
    }
    exit;
}

if ($type === 'pdf') {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(190,10,'Transaction Report',0,1,'C');
    $pdf->Ln(5);

    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(20,10,'ID',1);
    $pdf->Cell(40,10,'Type',1);
    $pdf->Cell(40,10,'Amount',1);
    $pdf->Cell(30,10,'Status',1);
    $pdf->Cell(60,10,'Date',1);
    $pdf->Ln();

    $pdf->SetFont('Arial','',10);
    foreach ($data as $row) {
        $amount = "PHP " . number_format($row['amount'], 2); // replace ₱ with PHP

        $pdf->Cell(20,10,$row['id'],1);
        $pdf->Cell(40,10,ucfirst($row['type']),1);
        $pdf->Cell(40,10,$amount,1);
        $pdf->Cell(30,10,$row['status'],1);
        $pdf->Cell(60,10,$row['created_at'],1);
        $pdf->Ln();
    }

    $pdf->Output("D","transactions.pdf");
    exit;
}

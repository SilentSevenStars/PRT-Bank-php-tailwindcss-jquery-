<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include 'config/class/Database.php';

$mydb = new Database;

$userId = $_SESSION['user_id'];
$stmt = $mydb->conn->prepare("SELECT type, amount, status, date FROM transactions WHERE user_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=transactions.csv');

$output = fopen('php://output', 'w');

fputcsv($output, ['Type', 'Amount', 'Status', 'Date']);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        ucfirst($row['type']),
        $row['amount'],
        ucfirst($row['status']),
        $row['date']
    ]);
}

fclose($output);
exit;

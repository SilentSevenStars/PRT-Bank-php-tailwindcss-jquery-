<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include 'config/class/Database.php';
$userId = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    die("No transaction ID.");
}

$mydb = new Database;

$txnId = $_GET['id'];

$stmt = $mydb->conn->prepare("SELECT id, type, amount, status, created_at FROM transactions WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $txnId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Transaction not found.");
}

$txn = $result->fetch_assoc();

if (isset($_GET['view'])) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Receipt #<?= $txn['id'] ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-[400px]">
        <h2 class="text-xl font-bold mb-4 text-center">Transaction Receipt</h2>
        <p><strong>ID:</strong> <?= $txn['id'] ?></p>
        <p><strong>Type:</strong> <?= ucfirst($txn['type']) ?></p>
        <p><strong>Amount:</strong> ₱<?= number_format($txn['amount'], 2) ?></p>
        <p><strong>Status:</strong> <?= ucfirst($txn['status']) ?></p>
        <p><strong>Date:</strong> <?= $txn['created_at'] ?></p>
        <div class="mt-6 text-center">
            <a href="transaction.php" class="px-4 py-2 bg-blue-600 text-white rounded">Back</a>
        </div>
    </div>
</body>
</html>
<?php
exit;
}

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=receipt-{$txn['id']}.pdf");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Receipt #<?= $txn['id'] ?></title>
</head>
<body>
    <h2>Transaction Receipt</h2>
    <p><strong>ID:</strong> <?= $txn['id'] ?></p>
    <p><strong>Type:</strong> <?= ucfirst($txn['type']) ?></p>
    <p><strong>Amount:</strong> ₱<?= number_format($txn['amount'], 2) ?></p>
    <p><strong>Status:</strong> <?= ucfirst($txn['status']) ?></p>
    <p><strong>Date:</strong> <?= $txn['created_at'] ?></p>
</body>
</html>

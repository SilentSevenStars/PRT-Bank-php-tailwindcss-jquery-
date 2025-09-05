<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'config/class/Database.php';
$mydb = new Database;
$userId = $_SESSION['user_id'];

$summary = ["deposit" => 0, "withdraw" => 0, "balance" => 0];
$stmt = $mydb->conn->prepare("
    SELECT type, SUM(amount) as total 
    FROM transactions 
    WHERE user_id=? AND status='success' 
    GROUP BY type
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $summary[$row['type']] = $row['total'];
}
$summary['balance'] = $summary['deposit'] - $summary['withdraw'];

$chartData = [];
$stmt = $mydb->conn->prepare("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
           SUM(CASE WHEN type='deposit' THEN amount ELSE 0 END) as deposits,
           SUM(CASE WHEN type='withdraw' THEN amount ELSE 0 END) as withdrawals
    FROM transactions
    WHERE user_id=? AND status='success'
    GROUP BY month
    ORDER BY month ASC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $chartData[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reports & Analysis - PRT Bank</title>
  <script type="text/javascript" src="assets/js/tailwind.js"></script>
  <script type="text/javascript" src="assets/js/chart.js"></script>
</head>
<body class="bg-gray-100 font-sans">
<div class="flex h-screen">
    <?php include 'layout/sidebar.php'; ?>

    <main class="flex-1 p-6 overflow-y-auto">
        <h2 class="text-3xl font-bold mb-8 text-gray-800"> Reports & Analysis</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition text-center">
                <h3 class="text-gray-500 text-sm">Total Deposits</h3>
                <p class="text-3xl font-bold text-green-600 mt-2">₱<?= number_format($summary['deposit'], 2) ?></p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition text-center">
                <h3 class="text-gray-500 text-sm">Total Withdrawals</h3>
                <p class="text-3xl font-bold text-red-600 mt-2">₱<?= number_format($summary['withdraw'], 2) ?></p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition text-center">
                <h3 class="text-gray-500 text-sm">Net Balance</h3>
                <p class="text-3xl font-bold text-blue-600 mt-2">₱<?= number_format($summary['balance'], 2) ?></p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow mb-8">
            <h3 class="text-lg font-semibold mb-4 text-gray-700">Monthly Deposits vs Withdrawals</h3>
            <canvas id="reportChart" style="height:450px;"></canvas>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow flex gap-4">
            <a href="export_reports.php?type=csv" class="px-5 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition">Export CSV</a>
            <a href="export_reports.php?type=xlsx" class="px-5 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">Export Excel</a>
            <a href="export_reports.php?type=pdf" class="px-5 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 transition">Export PDF</a>
        </div>
    </main>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('reportChart').getContext('2d');
    const chartData = <?= json_encode($chartData) ?>;

    if (chartData.length > 0) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.map(r => r.month),
                datasets: [
                    {
                        label: 'Deposits',
                        data: chartData.map(r => r.deposits ? Number(r.deposits) : 0),
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderRadius: 6
                    },
                    {
                        label: 'Withdrawals',
                        data: chartData.map(r => r.withdrawals ? Number(r.withdrawals) : 0),
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderRadius: 6
                    },
                    {
                        label: 'Net Balance',
                        type: 'line',
                        data: chartData.map(r => (Number(r.deposits) - Number(r.withdrawals))),
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgba(59, 130, 246, 1)'
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true }
                }
            }
        });
    } else {
        document.getElementById('reportChart').replaceWith("⚠️ No data available for chart.");
    }
});
</script>
</body>
</html>

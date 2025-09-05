<?php
require_once "config/class/User.php";
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['fullname'])) {
    header("Location: login.php");
    exit;
}

$user = new User;

$id = ["id" => $_SESSION['user_id']];
$user->select("*", $id);
$data = $user->res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PRT Bank</title>
    <script type="text/javascript" src="assets/js/tailwind.js"></script>
    <script type="text/javascript" src="assets/js/jquery.min.js"></script>
    <script type="text/javascript" src="assets/js/chart.js"></script>
</head>

<body class="bg-gray-100 font-sans">
    <div class="flex h-screen">

        <?php include 'layout/sidebar.php'; ?>

        <main class="flex-1 p-6 overflow-y-auto">
            <h2 class="text-2xl font-bold text-gray-800 mb-6" id="greeting"></h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-lg p-4 text-center border">
                    <h3 class="text-gray-500">Balance</h3>
                    <p id="balanceText" class="text-2xl font-bold text-gray-800"></p>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-4 text-center border">
                    <h3 class="text-gray-500">Active Loans</h3>
                    <p id="activeLoans" class="text-2xl font-bold text-green-500">0</p>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-4 text-center border">
                    <h3 class="text-gray-500">Loan Balance</h3>
                    <p id="loanBalance" class="text-2xl font-bold text-red-500">₱0.00</p>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-4 text-center border">
                    <h3 class="text-gray-500">Savings</h3>
                    <p id="savingsText" class="text-2xl font-bold text-blue-500">₱0.00</p>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl shadow-lg p-6 col-span-2 border h-64">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Finances</h3>
                    <canvas id="financeChart"></canvas>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 border">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Quick Transaction</h3>
                    <form id="quickTransactionForm" class="space-y-4">
                        <input type="hidden" name="balance" id="balance">
                        <input type="number" id="amountInput" name="amount" placeholder="Enter Amount"
                            class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500" required>
                        <div class="flex space-x-2">
                            <button type="button" onclick="submitTransaction('deposit', <?= $_SESSION['user_id'] ?>)"
                                class="flex-1 bg-green-600 text-white py-2 rounded-lg hover:bg-green-500">Deposit</button>
                            <button type="button" onclick="submitTransaction('withdraw', <?= $_SESSION['user_id'] ?>)"
                                class="flex-1 bg-red-600 text-white py-2 rounded-lg hover:bg-red-500">Withdraw</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-6 mt-6 border">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Transaction History</h3>
                <div class="max-h-64 overflow-y-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b">
                                <th class="pb-2 text-gray-500">Type</th>
                                <th class="pb-2 text-gray-500">Amount</th>
                                <th class="pb-2 text-gray-500">Status</th>
                                <th class="pb-2 text-gray-500">Date</th>
                            </tr>
                        </thead>
                        <tbody id="tBodyTransaction"></tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
   <div id="successModalDeposit" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-96 text-center">
            <h2 class="text-xl font-bold text-green-600 mb-4">✅ Deposit Successfully</h2>
            <p class="text-gray-700 mb-6" id="deposit"></p>
            <button id="closeModalDeposit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-500">OK</button>
        </div>
    </div>

    <div id="successModalWithdraw" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-96 text-center">
            <h2 class="text-xl font-bold text-green-600 mb-4">✅ Withdraw Successfully</h2>
            <p class="text-gray-700 mb-6" id="withdraw"></p>
            <button id="closeModalWithdraw" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-500">OK</button>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            loadTransaction(<?= $_SESSION['user_id'] ?>);
            loadInfo(<?= $_SESSION['user_id'] ?>);
            loadDashboardStats(<?= $_SESSION['user_id'] ?>);
            loadChart(<?= $_SESSION['user_id'] ?>);
        });

        function loadInfo(user_id) {
            $.ajax({
                url: "config/request.php",
                method: "POST",
                data: { 'get_info': true, 'id': user_id },
                success: function(result) {
                    let datas = JSON.parse(result);
                    if (datas.length > 0) {
                        datas.forEach(function(data) {
                            $('#quickTransactionForm #balance').val(data['balance']);
                            $('#greeting').html(`Welcome, ${data['fullname']}`);
                        });
                    }
                }
            });
        }

        function loadDashboardStats(user_id) {
            $.ajax({
                url: "config/request.php",
                method: "POST",
                data: { get_dashboard_stats: true, user_id: user_id },
                success: function(result) {
                    let stats = JSON.parse(result);
                    $("#balanceText").html(`₱ ${parseFloat(stats.balance).toFixed(2)}`);
                    $("#activeLoans").html(stats.activeLoans);
                    $("#loanBalance").html(`₱ ${parseFloat(stats.loanBalance).toFixed(2)}`);
                    $("#savingsText").html(`₱ ${parseFloat(stats.savings).toFixed(2)}`);
                }
            });
        }

        function submitTransaction(type, user_id) {
            let amount = $('#amountInput').val();
            let balance = $('#quickTransactionForm #balance').val();
            amount = parseFloat(amount);
            balance = parseFloat(balance);

            if (type === 'deposit') {
                if (amount > 0) {
                    balance = balance + amount;
                    $.ajax({
                        url: "config/request.php",
                        method: "POST",
                        data: {
                            'deposit': true,
                            'user_id': user_id,
                            'type': type,
                            'amount': amount,
                            'status': 'success',
                            'balance': balance,
                        },
                        success: function() {
                            $("#successModalDeposit").removeClass("hidden");
                            $('#deposit').html(`Your balance has been deposited ₱${amount} successfully!`);
                            $('#amountInput').val('');
                            loadInfo(user_id);
                            loadTransaction(user_id);
                            loadDashboardStats(user_id);
                            loadChart(user_id);
                        }
                    });
                } else {
                    alert("Invalid amount, must be greater than zero");
                }
            }
            if (type === 'withdraw') {
                if (balance > amount && amount > 0) {
                    balance -= amount;
                    $.ajax({
                        url: "config/request.php",
                        method: "POST",
                        data: {
                            'withdraw': true,
                            'user_id': user_id,
                            'type': type,
                            'amount': amount,
                            'status': 'success',
                            'balance': balance,
                        },
                        success: function() {
                            $("#successModalWithdraw").removeClass("hidden");
                            $('#withdraw').html(`Your balance has been withdrawn ₱${amount} successfully!`);
                            $('#amountInput').val('');
                            loadInfo(user_id);
                            loadTransaction(user_id);
                            loadDashboardStats(user_id);
                            loadChart(user_id);
                        }
                    });
                } else {
                    alert("Invalid amount, must be greater than zero");
                }
            }
        }

        function loadTransaction(user_id) {
            $.ajax({
                url: "config/request.php",
                method: "POST",
                data: { "get_transaction": true, "userid": user_id },
                success: function(response) {
                    let datas = JSON.parse(response);
                    let tBody = "";

                    datas.forEach(function(data) {
                        let typeDisplay = data.type.split(" ")
                            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                            .join(" ");
                        let amount = parseFloat(data.amount);

                        let isDeduction = (data.type === "withdraw" || data.type === "loan repayment");
                        let sign = isDeduction ? '-' : '+';
                        let amountDisplay = amount.toFixed(2);
                        let amountColor = isDeduction ? 'text-red-500' : 'text-green-500';
                        let statusColor = data.status.toLowerCase() === 'success' ? 'text-green-500' : 'text-red-500';

                        tBody += `
                            <tr class="border-b hover:bg-gray-100">
                                <td class="py-3 px-6">${typeDisplay}</td>
                                <td class="py-3 px-6 ${amountColor} font-semibold">${sign}₱${amountDisplay}</td>
                                <td class="py-3 px-6 ${statusColor} font-semibold">${data.status.charAt(0).toUpperCase() + data.status.slice(1)}</td>
                                <td class="py-3 px-6">${data.date}</td>
                            </tr>
                        `;
                    });

                    $("#tBodyTransaction").html(tBody);
                },
                error: function() {
                    alert("Something went wrong");
                }
            });

            $("#closeModalDeposit").on("click", function() {
                $("#successModalDeposit").addClass("hidden");
            });

            $("#closeModalWithdraw").on("click", function() {
                $("#successModalWithdraw").addClass("hidden");
            });
        }

        function loadChart(user_id) {
            $.ajax({
                url: "config/request.php",
                method: "POST",
                data: { get_chart_data: true, user_id: user_id },
                success: function(result) {
                    let datas = JSON.parse(result);
                    let labels = datas.map(d => d.txn_date);
                    let deposits = datas.map(d => parseFloat(d.deposits));
                    let withdrawals = datas.map(d => parseFloat(d.withdrawals));
                    let repayments = datas.map(d => parseFloat(d.loan_repayments));

                    const ctx = document.getElementById('financeChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Deposits',
                                    data: deposits,
                                    borderColor: 'green',
                                    fill: false,
                                    tension: 0.3
                                },
                                {
                                    label: 'Withdrawals',
                                    data: withdrawals,
                                    borderColor: 'red',
                                    fill: false,
                                    tension: 0.3
                                },
                                {
                                    label: 'Loan Repayments',
                                    data: repayments,
                                    borderColor: 'blue',
                                    fill: false,
                                    tension: 0.3
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>

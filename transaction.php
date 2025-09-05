<?php 
session_start();
if (!isset($_SESSION['user_id']))
    header("Location: login.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - PRT Bank</title>
    <script type="text/javascript" src="assets/js/tailwind.js"></script>
    <script type="text/javascript" src="assets/js/jquery.min.js"></script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex h-screen">
        <?php include 'layout/sidebar.php'; ?>

        <main class="flex-1 p-6 overflow-y-auto">
            <h2 class="text-2xl font-bold mb-6">Transaction History</h2>

            <form id="filterForm" class="mb-6 bg-white p-4 rounded-lg shadow flex flex-wrap gap-4">
                <div>
                    <label class="block text-gray-700">Type</label>
                    <select name="type" class="border rounded p-2" id="type">
                        <option value="">All</option>
                        <option value="deposit">Deposit</option>
                        <option value="withdraw">Withdraw</option>
                        <option value="loan repayment">Loan Repayment</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700">Status</label>
                    <select name="status" class="border rounded p-2" id="status">
                        <option value="">All</option>
                        <option value="success">Success</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700">From</label>
                    <input type="date" name="from" value="<?= $_GET['from'] ?? '' ?>" class="border rounded p-2" id="from">
                </div>
                <div>
                    <label class="block text-gray-700">To</label>
                    <input type="date" name="to" value="<?= $_GET['to'] ?? '' ?>" class="border rounded p-2" id="to">
                </div>
                <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
                <div class="flex items-end gap-2">
                    <input type="submit" value="Filter" name="filter" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-500">
                    <a href="transaction.php" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-400" onclick="loadTransaction(<?= $_SESSION['user_id'] ?>)">Reset</a>
                </div>
                <div class="flex items-end ml-auto">
                    <button type="button" onclick="exportCSV(<?= $_SESSION['user_id'] ?>)" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-500">Export CSV</button>
                </div>
            </form>

            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="table-auto w-full border-collapse">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-left w-16">ID</th>
                            <th class="px-4 py-2 text-left w-32">Type</th>
                            <th class="px-4 py-2 text-left w-40">Amount</th>
                            <th class="px-4 py-2 text-left w-32">Status</th>
                            <th class="px-4 py-2 text-left w-48">Date</th>
                            <th class="px-4 py-2 text-left w-56">Receipt</th>
                        </tr>
                    </thead>
                    <tbody id="tBodyTransaction"></tbody>
                </table>
            </div>
        </main>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            loadTransaction(<?= $_SESSION['user_id'] ?>);

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                var datas = $(this).serializeArray();
                var data_array = {};
                $.map(datas, function(data) {
                    data_array[data['name']] = data['value'];
                });
                loadTransaction(<?= $_SESSION['user_id'] ?>, data_array);
            });
        });

        function loadTransaction(user_id, datas = null) {
            let ajaxData = {};
            let mt = "POST";
            if (datas) {
                mt = "GET";
                ajaxData = {
                    'get_transaction': true,
                    ...datas
                };
            } else {
                ajaxData = {
                    'get_transaction': true,
                    'userid': user_id
                };
            }

            $.ajax({
                url: "config/request.php",
                method: mt,
                data: ajaxData,
                success: function(result) {
                    let tBody = '';
                    try {
                        console.log(result)
                        let datas = JSON.parse(result);
                        if (datas.length > 0) {
                            datas.forEach(function(data) {
                                let status = data.status ? data.status.toLowerCase() : "N/A";
                                let statusClass = "bg-gray-100 text-gray-700";
                                if (status === "success") statusClass = "bg-green-100 text-green-700";
                                if (status === "failed") statusClass = "bg-red-100 text-red-700";
                                if (status === "pending") statusClass = "bg-yellow-100 text-yellow-700";

                                tBody += `
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-4 py-2">${data.id}</td>
                                        <td class="px-4 py-2 capitalize">${data.type}</td>
                                        <td class="px-4 py-2">₱${parseFloat(data.amount).toFixed(2)}</td>
                                        <td class="px-4 py-2"><span class="px-2 py-1 rounded ${statusClass}">${status}</span></td>
                                        <td class="px-4 py-2">${data.created_at}</td>
                                        <td class="px-4 py-2">
                                            <div class="flex gap-2">
                                                <a href="transaction_receipt.php?id=${data.id}&view=true"
                                                class="px-3 py-1 bg-gray-600 text-white text-sm rounded hover:bg-gray-500">View</a>
                                                <a href="transaction_receipt.php?id=${data.id}&download=true"
                                                class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-500">Download</a>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                            });
                        } else {
                            tBody = `
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-center text-gray-500">No transactions found.</td>
                                </tr>
                            `;
                        }
                    } catch (e) {
                        tBody = `
                            <tr>
                                <td colspan="6" class="px-4 py-3 text-center text-red-500">Failed to load transactions.</td>
                            </tr>
                        `;
                    }
                    $('#tBodyTransaction').html(tBody);
                },
                error: function() {
                    alert("Something went wrong while loading transactions.");
                }
            });
        }

        function exportCSV(user_id) {
            let type = $("#type").val();
            let status = $("#status").val();
            let from = $("#from").val();
            let to = $("#to").val();

            if (type === "" && status === "" && from === "" && to === "") {
                $.ajax({
                    url: "config/request.php",
                    type: "POST",
                    data: {
                        export_csv_all: true,
                        user_id: user_id,
                        order: "ORDER BY created_at DESC",
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(blob) {
                        let link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "all_transactions.csv";
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    }
                });
            } else {
                $.ajax({
                    url: "config/request.php",
                    type: "POST",
                    data: {
                        export_csv_filtered: 1,
                        user_id: user_id,
                        type: type,
                        status: status,
                        from: from,
                        to: to,
                        order: "ORDER BY created_at DESC",
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(blob) {
                        let link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "filtered_transactions.csv";
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    }
                });
            }
        }
    </script>
</body>
</html>

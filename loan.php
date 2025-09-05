<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Loan - PRT Bank</title>
    <script type="text/javascript" src="assets/js/tailwind.js"></script>
    <script type="text/javascript" src="assets/js/jquery.min.js"></script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex min-h-screen">
        <?php include 'layout/sidebar.php'; ?>

        <main class="flex-1 p-6">
            <div class="max-w-6xl mx-auto space-y-8">

                <div class="bg-white shadow-md rounded-xl p-6 flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-gray-800">Loan Services</h2>
                    <img src="assets/image/logo.png" alt="Bank Logo" class="h-16 w-auto object-contain">
                </div>


                <div class="grid md:grid-cols-2 gap-6">
                    <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Available Loan Balance</h3>
                        <p class="text-3xl font-bold text-blue-600" id="availableBalanceDisplay">₱0.00</p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Current Account Balance</h3>
                        <p class="text-3xl font-bold text-green-600" id="balanceDisplay">₱0.00</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Apply for a Loan</h3>
                    <form id="loanForm" class="space-y-4">
                        <input type="hidden" name="balance" id="balance">
                        <input type="hidden" name="availableBalance" id="availableBalance">

                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700">Loan Amount</label>
                            <input type="number" id="amount" name="amount" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="term" class="block text-sm font-medium text-gray-700">Term (Months)</label>
                            <select id="term" name="term" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="3">3 Months</option>
                                <option value="6">6 Months</option>
                                <option value="12">12 Months</option>
                                <option value="24">24 Months</option>
                            </select>
                        </div>
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-500 hover:shadow-md transition">
                            Submit Application
                        </button>
                    </form>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Loan History</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border rounded-lg shadow-sm overflow-hidden">
                            <thead class="bg-gray-100 text-gray-700">
                                <tr>
                                    <th class="px-4 py-2 border">Loan Amount</th>
                                    <th class="px-4 py-2 border">Monthly Payment</th>
                                    <th class="px-4 py-2 border">Term</th>
                                    <th class="px-4 py-2 border">Remaining Balance</th>
                                    <th class="px-4 py-2 border">Next Due Date</th>
                                    <th class="px-4 py-2 border">Status</th>
                                    <th class="px-4 py-2 border">Action</th>
                                </tr>
                            </thead>
                            <tbody id="tBodyLoan"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="successModalLoan" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl shadow-xl p-8 w-96 text-center">
            <h2 class="text-xl font-bold text-green-600 mb-4">Loan Application Successful</h2>
            <p class="text-gray-700 mb-6">Your loan has been applied successfully!</p>
            <button id="closeModalLoan" class="px-6 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-500 transition">OK</button>
        </div>
    </div>

    <script type="text/javascript">
        const USER_ID = <?= (int)$_SESSION['user_id'] ?>;

        $(document).ready(function() {
            loadBalance();
            loadLoanHistory();
        });

        function loadBalance() {
            $.post("config/request.php", { get_balance: true, user_id: USER_ID }, function(result) {
                try {
                    let datas = JSON.parse(result);

                    $('#availableBalance').val(datas.availableBalance);
                    $('#balance').val(datas.balance);

                    $('#availableBalanceDisplay').html(`₱ ${parseFloat(datas.availableBalance).toFixed(2)}`);
                    $('#balanceDisplay').html(`₱ ${parseFloat(datas.balance).toFixed(2)}`);
                } catch (e) {
                    console.error("Balance JSON parse error:", e, result);
                }
            });
        }

        function loadLoanHistory() {
            $.post("config/request.php", { get_loan: true, user_id: USER_ID }, function(result) {
                let tBody = '';
                try {
                    let datas = JSON.parse(result);
                    if (datas.length > 0) {
                        datas.forEach(function(data) {
                            let term = Number(data.term);
                            let monthsPaid = Number(data.months_paid || 0);
                            let remaining = Number(data.remaining_balance);
                            let statusText = "";
                            let statusClass = "";

                            if (remaining <= 0 || monthsPaid >= term || data.status === "paid") {
                                statusText = "Fully Paid";
                                statusClass = "bg-green-100 text-green-600";
                            } else {
                                statusText = `${monthsPaid}/${term} months paid`;
                                statusClass = "bg-yellow-100 text-yellow-600";
                            }

                            let actionHtml = '';
                            if (statusText === "Fully Paid") {
                                actionHtml = `<a href="loan_view.php?id=${data.id}" 
                                                class="px-3 py-1 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-500 hover:shadow-md transition">
                                                View
                                              </a>`;
                            } else {
                                actionHtml = `<a href="loan_view.php?id=${data.id}" 
                                                class="px-3 py-1 bg-red-600 text-white rounded-lg shadow hover:bg-red-500 hover:shadow-md transition">
                                                Pay
                                              </a>`;
                            }

                            tBody += `
                                <tr class="border-t hover:bg-gray-50 transition">
                                    <td class="px-4 py-2">₱${Number(data.amount).toFixed(2)}</td>
                                    <td class="px-4 py-2">₱${Number(data.monthly_payment).toFixed(2)}</td>
                                    <td class="px-4 py-2">${term} months</td>
                                    <td class="px-4 py-2">₱${remaining.toFixed(2)}</td>
                                    <td class="px-4 py-2">${data.next_due_date ? data.next_due_date : '---'}</td>
                                    <td class="px-4 py-2"><span class="px-2 py-1 rounded-full ${statusClass}">${statusText}</span></td>
                                    <td class="px-4 py-2">${actionHtml}</td>
                                </tr>`;
                        });
                    } else {
                        tBody = `<tr><td colspan="7" class="text-center text-gray-500 py-4">No loans found</td></tr>`;
                    }
                } catch (e) {
                    console.error("Loan JSON parse error:", e, result);
                    tBody = `<tr><td colspan="7" class="text-center text-gray-500 py-4">Error loading loans</td></tr>`;
                }
                $('#tBodyLoan').html(tBody);
            });
        }

        $("#closeModalLoan").on("click", () => $("#successModalLoan").addClass("hidden"));

        $("#loanForm").on("submit", function(e) {
            e.preventDefault();
            let amount = parseFloat($('#amount').val());
            let term = parseInt($('#term').val());
            let balance = parseFloat($('#balance').val());

            $.post("config/request.php", {
                loan_process: true,
                amount: amount,
                term: term,
                balance: balance + amount,
                user_id: USER_ID
            }, function() {
                $("#successModalLoan").removeClass("hidden");
                loadBalance();
                loadLoanHistory();
            });
        });
    </script>
</body>
</html>

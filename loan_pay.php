<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "class/Loan.php";
require_once "class/Transaction.php";

$loanObj = new Loan();
$txnObj  = new Transaction();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['loan_id'])) {
    $loanId         = intval($_POST['loan_id']);
    $userId         = $_SESSION['user_id'];
    $monthsSelected = isset($_POST['months']) ? $_POST['months'] : [];
    $customAmount   = isset($_POST['custom_amount']) ? floatval($_POST['custom_amount']) : 0;

    $loan = $loanObj->selectOne($loanId);

    if ($loan && $loan['user_id'] == $userId && $loan['status'] == 'approved') {
        $monthlyPayment = $loan['monthly_payment'];
        $remaining      = $loan['remaining_balance'];
        $monthsPaid     = intval($loan['months_paid']);
        $term           = intval($loan['term']);

        $monthsToPay = count($monthsSelected);
        if ($monthsToPay > 0) {
            $amountToPay = $monthlyPayment * $monthsToPay;
        } elseif ($customAmount > 0) {
            $amountToPay = $customAmount;
        } else {
            $amountToPay = $monthlyPayment; 
            $monthsToPay = 1;
        }

        $stmt = $loanObj->conn->prepare("SELECT balance FROM users WHERE id=?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && $user['balance'] >= $amountToPay) {
            $stmt = $loanObj->conn->prepare("UPDATE users SET balance = balance - ? WHERE id=?");
            $stmt->bind_param("di", $amountToPay, $userId);
            $stmt->execute();
            $stmt->close();

            $newRemaining = $remaining - $amountToPay;
            if ($newRemaining < 0.01) {
                $newRemaining  = 0;
                $status        = 'paid';
                $nextDue       = null;
                $newMonthsPaid = $term;
            } else {
                $status        = 'approved';
                $nextDue       = date("Y-m-d", strtotime("+{$monthsToPay} month"));
                $newMonthsPaid = $monthsPaid + $monthsToPay;
                if ($newMonthsPaid > $term) $newMonthsPaid = $term;
            }

            $stmt = $loanObj->conn->prepare("UPDATE loans 
                SET remaining_balance=?, status=?, next_due_date=?, months_paid=? 
                WHERE id=?");
            $stmt->bind_param("dssii", $newRemaining, $status, $nextDue, $newMonthsPaid, $loanId);
            $stmt->execute();
            $stmt->close();

            if ($monthsToPay > 0) {
                foreach ($monthsSelected as $m) {
                    $stmt = $loanObj->conn->prepare("INSERT INTO loan_repayments (loan_id, amount, paid_at) VALUES (?, ?, NOW())");
                    $stmt->bind_param("id", $loanId, $monthlyPayment);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            if ($customAmount > 0 && $monthsToPay == 0) {
                $stmt = $loanObj->conn->prepare("INSERT INTO loan_repayments (loan_id, amount, paid_at) VALUES (?, ?, NOW())");
                $stmt->bind_param("id", $loanId, $customAmount);
                $stmt->execute();
                $stmt->close();
            }

            $txnObj->insert([
                "user_id"    => $userId,
                "type"       => "loan repayment",
                "amount"     => $amountToPay,
                "status"     => "success",
                "created_at" => date("Y-m-d H:i:s")
            ]);

            error_log("LoanPay: LoanID=$loanId | Status=$status | Remaining=$newRemaining | MonthsPaid=$newMonthsPaid");

            $_SESSION['message'] = "Loan payment successful!";
        } else {
            $_SESSION['error'] = "Insufficient balance for this payment.";
        }
    } else {
        $_SESSION['error'] = "Loan not found or not approved.";
    }
}

header("Location: loan.php");
exit;

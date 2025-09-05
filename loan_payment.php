<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'backend/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loanId = intval($_POST['loan_id']);
    $userId = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT * FROM loans WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $loanId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $loan = $result->fetch_assoc();
    $stmt->close();

    if (!$loan || $loan['status'] !== 'approved') {
        header("Location: loan.php?error=InvalidLoan");
        exit;
    }

    $monthlyPayment = $loan['monthly_payment'];
    $remainingBalance = $loan['remaining_balance'];

    $paymentAmount = 0;

    if (!empty($_POST['months'])) {
        $monthsToPay = count($_POST['months']);
        $paymentAmount = $monthsToPay * $monthlyPayment;
    }

    if (!empty($_POST['custom_amount']) && floatval($_POST['custom_amount']) > 0) {
        $paymentAmount = floatval($_POST['custom_amount']);
    }

    if ($paymentAmount <= 0 || $paymentAmount > $remainingBalance) {
        header("Location: loan_payment.php?id=" . $loanId . "&error=InvalidAmount");
        exit;
    }

    $stmt = $conn->prepare("SELECT balance FROM users WHERE id=?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($userBalance);
    $stmt->fetch();
    $stmt->close();

    if ($paymentAmount > $userBalance) {
        header("Location: loan_payment.php?id=" . $loanId . "&error=InsufficientFunds");
        exit;
    }

    $conn->begin_transaction();

    try {
        $newRemaining = $remainingBalance - $paymentAmount;
        $newStatus = $newRemaining <= 0 ? 'paid' : 'approved';

        $stmt = $conn->prepare("UPDATE loans SET remaining_balance=?, status=? WHERE id=?");
        $stmt->bind_param("dsi", $newRemaining, $newStatus, $loanId);
        $stmt->execute();
        $stmt->close();

        $newUserBalance = $userBalance - $paymentAmount;
        $stmt = $conn->prepare("UPDATE users SET balance=? WHERE id=?");
        $stmt->bind_param("di", $newUserBalance, $userId);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO loan_repayments (loan_id, amount, paid_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("id", $loanId, $paymentAmount);
        $stmt->execute();
        $stmt->close();

        $desc = "Loan payment for Loan ID #$loanId";
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount, status, created_at) VALUES (?, 'loan_payment', ?, 'success', NOW())");
        $stmt->bind_param("id", $userId, $paymentAmount);
        $stmt->execute();
        $stmt->close();

        $conn->commit();

        header("Location: loan.php?success=PaymentDone");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        header("Location: loan_payment.php?id=" . $loanId . "&error=PaymentFailed");
        exit;
    }
}

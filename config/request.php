<?php
require_once "class/user.php";
require_once "class/Auth.php";
require_once "class/Transaction.php";
require_once "class/Loan.php";

$auth = new Auth;
$transaction = new Transaction;
$loan = new Loan;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

function add_txn($transaction, $user_id, $type, $amount, $status = 'success') {
    $amount = floatval($amount);
    $now = date("Y-m-d H:i:s");
    $transaction->insert([
        'user_id'    => intval($user_id),
        'type'       => $type,
        'amount'     => $amount,
        'status'     => $status,
        'date'       => $now,
        'created_at' => $now,
    ]);
}

if (isset($_POST['register'])) {
    unset($_POST['register']);
    if (isset($_POST['password'], $_POST['confirm_password']) && $_POST['password'] === $_POST['confirm_password']) {
        unset($_POST['confirm_password']);
        $auth->register($_POST);
    }
}

if (isset($_POST['login'])) {
    unset($_POST['login']);
    $auth->login($_POST);
}

if(isset($_POST['forget-password'])){
    unset($_POST['forget-password']);

    $auth->select("id, fullname", [...$_POST]);

    if($auth->res->num_rows > 0){
        $data = $auth->res->fetch_assoc();
        $userId = $data['id'];

        $token = bin2hex(random_bytes(16));
        $token_hashed = hash("sha256", $token);

        $expires_at= date("Y-m-d H:i:s", time() + 60 * 30);

        $auth->update([
            "reset_token" => $token_hashed,
            "reset_expires" => $expires_at,
            "id" => $userId,
        ]);

        $resetLink = "http://localhost/banking-management-system-main/reset-password.php?token=" . $token;

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = "josephmatthewringor@gmail.com";
            $mail->Password = "mupl vngj rstb nbxd";
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('josephmatthewringor@gmail.com', 'PTR Bank');
            $mail->addAddress($_POST['email'], $data['fullname']);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "
                <h2>Password Reset</h2>
                <p>Hello {$data['fullname']},</p>
                <p>Click the link below to reset your password (valid for 1 hour):</p>
                <a href='$resetLink'>Click Here</a>
            ";

            $mail->send();
            header("Location: ../email_send.php?email=" . $_POST['email']);
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

    } else {
        die("Email not found");
    }
}

if(isset($_POST['reset-password'])){
    unset($_POST['reset-password']);

    if($_POST['password'] !== $_POST['confirm_password']){
        die("Password and Confirm Password do not match");
    }

    unset($_POST['confirm_password']);

    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $auth->update([
        "password" => $hashedPassword,
        "reset_token" => null,
        "id" => $_POST['id'],
    ]);

    header("Location: ../login.php");
}

if (isset($_POST['get_info'])) {
    unset($_POST['get_info']);
    $auth->select("*", [...$_POST]);
    $datas = [];
    while ($row = $auth->res->fetch_assoc()) $datas[] = $row;
    echo json_encode($datas);
}

if (isset($_POST['deposit'])) {
    unset($_POST['deposit']);
    $auth->update([
        'balance' => $_POST['balance'],
        'id'      => $_POST['user_id']
    ]);
    $user_id = intval($_POST['user_id']);
    $amount  = floatval($_POST['amount'] ?? 0);
    add_txn($transaction, $user_id, 'deposit', $amount, 'success');
}

if (isset($_POST['withdraw'])) {
    unset($_POST['withdraw']);
    $auth->update([
        'balance' => $_POST['balance'],
        'id'      => $_POST['user_id']
    ]);
    $user_id = intval($_POST['user_id']);
    $amount  = floatval($_POST['amount'] ?? 0);
    add_txn($transaction, $user_id, 'withdraw', $amount, 'success');
}

if (isset($_POST['get_transaction'])) {
    unset($_POST['get_transaction']);
    if (isset($_POST['userid'])) {
        $transaction->select("*", ['user_id' => $_POST['userid']], "ORDER BY date DESC LIMIT 10");
    }
    $datas = [];
    while ($row = $transaction->res->fetch_assoc()) $datas[] = $row;
    echo json_encode($datas);
}

if (isset($_POST['get_profile'])) {
    if (isset($_POST['userId'])) {
        $auth->select("*", ['id' => $_POST['userId']]);
        $datas = [];
        while ($row = $auth->res->fetch_assoc()) $datas[] = $row;
        echo json_encode($datas);
    }
}

if (isset($_POST['update_profile'])) {
    unset($_POST['update_profile']);
    if (isset($_POST['password']) && $_POST['password'] === '') {
        unset($_POST['password']);
    } elseif (!empty($_POST['password'])) {
        $_POST['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }
    $auth->update([...$_POST]);
}

if (isset($_GET['get_transaction'])) {
    unset($_GET['get_transaction']);
    $transaction->filter("*", [...$_GET]);
    $datas = [];
    while ($row = $transaction->res->fetch_assoc()) $datas[] = $row;
    echo json_encode($datas);
}
if (isset($_POST['export_csv_all'])) {
    unset($_POST['export_csv_all']);
    $transaction->select("id, type, amount, status, created_at AS date", ["user_id" => $_POST['user_id']], $_POST['order']);
    $datas = [];
    while ($row = $transaction->res->fetch_assoc()) $datas[] = $row;

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=all_transactions.csv');

    $output = fopen('php://output', 'w');
    if (!empty($datas)) {
        fputcsv($output, array_keys($datas[0]));
        foreach ($datas as $row) {
            fputcsv($output, $row);
        }
    }
    fclose($output);
    exit;
}

if (isset($_POST['export_csv_filtered'])) {
    unset($_POST['export_csv_filtered']);
    $order = $_POST['order']; 
    unset($_POST['order']);
    $transaction->filter("id, type, amount, status, created_at AS date", $_POST, $order);
    $datas = [];
    while ($row = $transaction->res->fetch_assoc()) $datas[] = $row;

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=filtered_transactions.csv');

    $output = fopen('php://output', 'w');
    if (!empty($datas)) {
        fputcsv($output, array_keys($datas[0]));
        foreach ($datas as $row) {
            fputcsv($output, $row);
        }
    }
    fclose($output);
    exit;
}

if (isset($_POST['get_balance'])) {
    unset($_POST['get_balance']);

    $stmt = $loan->conn->prepare("
        SELECT COALESCE(SUM(principal_amount),0) AS total_active
        FROM loans
        WHERE user_id=? 
          AND status='approved'
          AND remaining_balance > 0
    ");
    $stmt->bind_param("i", $_POST['user_id']);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $totalActiveLoans = floatval($row['total_active'] ?? 0);

    $availableBalance = 100000 - $totalActiveLoans;

    $auth->select("balance", ["id" => $_POST['user_id']]);
    $data = $auth->res->fetch_assoc();

    echo json_encode([
        'availableBalance' => $availableBalance,
        'balance'          => $data['balance'] ?? 0,
    ]);
    exit;
}




if (isset($_POST['summary'])) {
    $report = $transaction->getSummary($user_id);
    echo json_encode($report);
    exit;
}

if (isset($_POST['chart'])) {
    $chart = $transaction->getChartData($user_id);
    echo json_encode($chart);
    exit;
}

if (isset($_POST['get_loan'])) {
    unset($_POST['get_loan']);
    $loan->select("*", [...$_POST]);
    $datas = [];
    while ($row = $loan->res->fetch_assoc()) {
        $loanId = intval($row['id']);
        $stmt = $loan->conn->prepare("SELECT COUNT(*) as cnt FROM loan_repayments WHERE loan_id=?");
        $stmt->bind_param("i", $loanId);
        $stmt->execute();
        $rep = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $row['months_paid'] = intval($rep['cnt'] ?? 0);
        $row['months_left'] = max(0, intval($row['term']) - $row['months_paid']);
        $datas[] = $row;
    }
    echo json_encode($datas);
    exit;
}

if (isset($_POST['loan_process'])) {
    unset($_POST['loan_process']);

    $auth->update([
        'balance' => $_POST['balance'],
        'id'      => $_POST['user_id'],
    ]);
    unset($_POST['balance']);

        $amount = floatval($_POST['amount']); 
        $term   = intval($_POST['term']);
        $interestRate = floatval($_POST['interest_rate'] ?? 0.01);

        $total   = $amount + ($amount * $interestRate * $term); 
        $monthly = $total / max($term,1);

        $_POST['monthly_payment']   = round($monthly,2);
        $_POST['remaining_balance'] = round($total,2); 
        $_POST['principal_amount']  = $amount;         
        $_POST['next_due_date']     = date("Y-m-d", strtotime("+1 month"));
        $_POST['status']            = "approved"; 
        $_POST['months_paid']       = 0;
        $_POST['months_left']       = $term;

        $loan->insert([...$_POST]);

        add_txn($transaction, intval($_POST['user_id']), 'loan', $amount, 'success');


}


if (isset($_POST['get_loan_by_id'])) {
    unset($_POST['get_loan_by_id']);
    if (!empty($_POST['loan_id']) && !empty($_POST['user_id'])) {
        $loan->select("*", ["id" => $_POST['loan_id'], "user_id" => $_POST['user_id']]);
        $data = $loan->res->fetch_assoc();
        if ($data) {
            $loanId = intval($data['id']);
            $term   = intval($data['term']);
            $monthsPaid = 0;
            if (property_exists($loan, 'conn') && $loan->conn) {
                $stmt = $loan->conn->prepare("SELECT COUNT(*) AS cnt FROM loan_repayments WHERE loan_id=?");
                $stmt->bind_param("i", $loanId);
                $stmt->execute();
                $res = $stmt->get_result()->fetch_assoc();
                $monthsPaid = intval($res['cnt'] ?? 0);
                $stmt->close();
            }
            $monthsLeft = max(0, $term - $monthsPaid);
            $data['months_paid'] = $monthsPaid;
            $data['months_left'] = $monthsLeft;
            echo json_encode($data);
        } else {
            echo json_encode([]);
        }
    } else {
        echo json_encode([]);
    }
    exit;
}
if (isset($_POST['loan_pay'])) {
    unset($_POST['loan_pay']);
    $loanId      = intval($_POST['id']);
    $userId      = intval($_POST['user_id']);
    $monthsToPay = max(1, intval($_POST['months_to_pay'] ?? 1));

    $loanData = $loan->selectOne($loanId);
    if (!$loanData || intval($loanData['user_id']) !== $userId) {
        echo json_encode(["success"=>false,"message"=>"Loan not found"]); exit;
    }
    if ($loanData['status'] !== 'approved') {
        echo json_encode(["success"=>false,"message"=>"Loan is not active."]); exit;
    }

    $term           = intval($loanData['term']);
    $monthlyPayment = floatval($loanData['monthly_payment']);
    $remaining      = floatval($loanData['remaining_balance']);
    $monthsPaid     = intval($loanData['months_paid'] ?? 0);
    $months_left    = $term - $monthsPaid;

    if ($months_left <= 0) {
        echo json_encode(["success"=>false,"message"=>"Loan fully paid."]); exit;
    }

    if ($monthsToPay > $months_left) $monthsToPay = $months_left;
    $totalPayment = min($remaining, round($monthlyPayment * $monthsToPay, 2));

    $auth->select("balance", ["id"=>$userId]);
    $userRow = $auth->res->fetch_assoc();
    $currentBalance = floatval($userRow['balance'] ?? 0);
    if ($currentBalance < $totalPayment) {
        echo json_encode(["success"=>false,"message"=>"Insufficient balance."]); exit;
    }

    $newUserBalance = $currentBalance - $totalPayment;
    $auth->update(['balance'=>$newUserBalance,'id'=>$userId]);

    $newRemaining  = max(0, $remaining - $totalPayment);
    $newMonthsPaid = $monthsPaid + $monthsToPay;
    if ($newMonthsPaid > $term) $newMonthsPaid = $term;
    $newMonthsLeft = max(0, $term - $newMonthsPaid);

    if (property_exists($loan,'conn') && $loan->conn) {
        for ($i=1; $i<=$monthsToPay; $i++) {
            $installment_no = $monthsPaid + $i;
            $stmt = $loan->conn->prepare("
                INSERT INTO loan_repayments (loan_id, amount, paid_at, installment_no)
                VALUES (?, ?, NOW(), ?)
            ");
            $stmt->bind_param("idi", $loanId, $monthlyPayment, $installment_no);
            $stmt->execute();
            $stmt->close();
        }
    }

    add_txn($transaction, $userId, 'loan repayment', $totalPayment, 'success');

    if ($newRemaining <= 0.01 || $newMonthsPaid >= $term) {
        $newRemaining  = 0.00;
        $newMonthsPaid = $term;
        $newMonthsLeft = 0;

        $stmt = $loan->conn->prepare("
            UPDATE loans
               SET remaining_balance = 0,
                   status            = 'Fully Paid',
                   next_due_date     = NULL,
                   months_paid       = ?,
                   months_left       = 0
             WHERE id = ?
        ");
        $stmt->bind_param("ii", $newMonthsPaid, $loanId);
        $stmt->execute();
        $stmt->close();
    } else {
        $currentDue = $loanData['next_due_date'];
        if (!empty($currentDue)) {
            $nextDue = date("Y-m-d", strtotime($currentDue . " +{$monthsToPay} month"));
        } else {
            $nextDue = date("Y-m-d", strtotime("+{$monthsToPay} month"));
        }

        $stmt = $loan->conn->prepare("
            UPDATE loans
               SET remaining_balance = ?,
                   status            = 'approved',
                   next_due_date     = ?,
                   months_paid       = ?,
                   months_left       = ? 
             WHERE id = ?
        ");
        $stmt->bind_param("dsiii", $newRemaining, $nextDue, $newMonthsPaid, $newMonthsLeft, $loanId);
        $stmt->execute();
        $stmt->close();
    }

    echo json_encode([
        "success"  => true,
        "message"  => "Payment successful!",
        "redirect" => "loan_view.php?loan_id=" . $loanId
    ]);
    exit;
}


if (isset($_POST['get_loan_history'])) {
    unset($_POST['get_loan_history']);
    $loanId = intval($_POST['loan_id'] ?? 0);
    if ($loanId > 0 && property_exists($loan,'conn') && $loan->conn) {
        $stmt = $loan->conn->prepare("SELECT * FROM loan_repayments WHERE loan_id=? ORDER BY paid_at DESC");
        $stmt->bind_param("i",$loanId);
        $stmt->execute();
        $result = $stmt->get_result();
        $datas = [];
        while ($row = $result->fetch_assoc()) $datas[] = $row;
        echo json_encode($datas);
        $stmt->close();
    } else {
        echo json_encode([]);
    }
    exit;
}
if (isset($_POST['get_dashboard_stats'])) {
    $userId = intval($_POST['user_id']);

    $auth->select("balance", ["id" => $userId]);
    $userRow = $auth->res->fetch_assoc();
    $balance = floatval($userRow['balance'] ?? 0);

    $loan->select("*", ["user_id" => $userId]);
    $activeLoans = 0;
    $loanBalance = 0;
    while ($row = $loan->res->fetch_assoc()) {
        if ($row['status'] === 'approved') {
            $activeLoans++;
            $loanBalance += floatval($row['remaining_balance'] ?? 0);
        }
    }
    $savings = max(0, $balance - $loanBalance);

    echo json_encode([
        "balance"     => $balance,
        "activeLoans" => $activeLoans,
        "loanBalance" => $loanBalance,
        "savings"     => $savings
    ]);
    exit;
}
if (isset($_POST['get_chart_data'])) {
    $userId = intval($_POST['user_id']);
    $datas = [];

    if (property_exists($transaction,'conn') && $transaction->conn) {
        $stmt = $transaction->conn->prepare("
            SELECT DATE(date) as txn_date,
                   SUM(CASE WHEN type='deposit' THEN amount ELSE 0 END) as deposits,
                   SUM(CASE WHEN type='withdraw' THEN amount ELSE 0 END) as withdrawals,
                   SUM(CASE WHEN type='loan repayment' THEN amount ELSE 0 END) as loan_repayments
            FROM transactions
            WHERE user_id = ?
            GROUP BY DATE(date)
            ORDER BY DATE(date) ASC
            LIMIT 7
        ");
        $stmt->bind_param("i",$userId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $datas[] = $row;
        }
        $stmt->close();
    }

    echo json_encode($datas);
    exit;
}

?>

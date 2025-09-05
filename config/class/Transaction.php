<?php
require_once "Database.php";

class Transaction extends Database
{
    private $table = "transactions";

    private function getTypeChar($value)
    {
        switch (gettype($value)) {
            case 'integer':
                return 'i';
            case 'double':
                return 'd';
            case 'boolean':
                return 'i';
            default:
                return 's';
        }
    }

    public function insert($data)
    {
        try {
            $table_column = implode(',', array_keys($data));
            $prep = $types = "";
            foreach ($data as $value) {
                $prep .= "?,";
                $types .= $this->getTypeChar($value);
            }
            $prep = rtrim($prep, ',');
            $stmt = $this->conn->prepare("INSERT INTO $this->table ($table_column) VALUES ($prep)");
            $stmt->bind_param($types, ...array_values($data));
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("Transaction Insert Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function select($row = "*", $where = NULL, $extra = "")
    {
        try {
            if (!is_null($where) && count($where) > 0) {
                $cond = $types = "";
                foreach ($where as $key => $value) {
                    $cond .= "$key = ? AND ";
                    $types .= $this->getTypeChar($value);
                }
                $cond = substr($cond, 0, -4);
                $stmt = $this->conn->prepare("SELECT $row FROM $this->table WHERE $cond $extra");
                $stmt->bind_param($types, ...array_values($where));
            } else {
                $stmt = $this->conn->prepare("SELECT $row FROM $this->table $extra");
            }
            $stmt->execute();
            $this->res = $stmt->get_result();
        } catch (Exception $e) {
            error_log("Transaction Select Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function filter($row = "*", $where = NULL)
    {
        try {
            if (!is_null($where)) {
                $cond = $types = "";
                $values = [];

                foreach ($where as $key => $value) {
                    if (!empty($value)) {
                        if ($key === "from") {
                            $cond .= "DATE(created_at) >= ? AND ";
                            $types .= "s";
                            $values[] = $value;
                        } elseif ($key === "to") {
                            $cond .= "DATE(created_at) <= ? AND ";
                            $types .= "s";
                            $values[] = $value;
                        } else {
                            $cond .= "$key = ? AND ";
                            $types .= $this->getTypeChar($value);
                            $values[] = $value;
                        }
                    }
                }

                $cond = substr($cond, 0, -4); 
                $sql = "SELECT $row FROM $this->table WHERE $cond";

                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param($types, ...$values);
            } else {
                $stmt = $this->conn->prepare("SELECT $row FROM $this->table");
            }

            $stmt->execute();
            $this->res = $stmt->get_result();
        } catch (Exception $e) {
            error_log("Transaction Filter Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function exportCSV($datas)
    {
        if (empty($datas)) return;

        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=transactions.csv");
        $out = fopen("php://output", "w");

        fputcsv($out, array_keys($datas[0]));
        foreach ($datas as $row) {
            fputcsv($out, $row);
        }
        fclose($out);
    }

    public function getSummary($userId)
    {
        $stmt = $this->conn->prepare("
            SELECT 
                SUM(CASE WHEN type='deposit' THEN amount ELSE 0 END) AS total_deposit,
                SUM(CASE WHEN type='withdraw' THEN amount ELSE 0 END) AS total_withdraw,
                SUM(CASE WHEN type='loan repayment' THEN amount ELSE 0 END) AS total_repayment
            FROM $this->table
            WHERE user_id=?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    public function getChartData($userId)
    {
        $datas = [];
        $stmt = $this->conn->prepare("
            SELECT DATE(date) as txn_date,
                   SUM(CASE WHEN type='deposit' THEN amount ELSE 0 END) as deposits,
                   SUM(CASE WHEN type='withdraw' THEN amount ELSE 0 END) as withdrawals,
                   SUM(CASE WHEN type='loan repayment' THEN amount ELSE 0 END) as loan_repayments
            FROM $this->table
            WHERE user_id = ?
            GROUP BY DATE(date)
            ORDER BY DATE(date) ASC
            LIMIT 7
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $datas[] = $row;
        }
        $stmt->close();
        return $datas;
    }
}

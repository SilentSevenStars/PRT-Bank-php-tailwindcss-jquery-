<?php
require_once "Database.php";

class Loan extends Database
{
    private $table = "loans";

    private function getTypeChar($value)
    {
        switch (gettype($value)) {
            case 'integer': return 'i';
            case 'double':  return 'd';
            case 'boolean': return 'i';
            default:        return 's';
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
            error_log("Loan Insert Error: " . $e->getMessage());
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
            error_log("Loan Select Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function selectOne($loanId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM $this->table WHERE id=? LIMIT 1");
        $stmt->bind_param("i", $loanId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res;
    }

    public function update($data)
    {
        try {
            if (!isset($data['id'])) {
                throw new Exception("Update requires 'id' key.");
            }
            $set = $types = '';
            $values = [];
            foreach ($data as $key => $value) {
                if ($key === 'id') continue;
                $set .= "$key = ?,";
                $types .= $this->getTypeChar($value);
                $values[] = $value;
            }
            $set = rtrim($set, ',');
            $types .= "i";
            $values[] = $data['id'];

            $stmt = $this->conn->prepare("UPDATE $this->table SET $set WHERE id=?");
            $stmt->bind_param($types, ...$values);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("Loan Update Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function destroy($where)
    {
        try {
            $cond = $types = '';
            foreach ($where as $key => $value) {
                $cond .= "$key = ? AND ";
                $types .= $this->getTypeChar($value);
            }
            $cond = substr($cond, 0, -4);
            $stmt = $this->conn->prepare("DELETE FROM $this->table WHERE $cond");
            $stmt->bind_param($types, ...array_values($where));
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("Loan Delete Error: " . $e->getMessage());
            throw $e;
        }
    }
}

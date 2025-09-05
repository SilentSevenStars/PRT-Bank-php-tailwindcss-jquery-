<?php
require_once "Database.php";

class User extends Database
{
    private $table = "users";

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
                $prep .= '?,';
                $types .= $this->getTypeChar($value);
            }
            $prep = rtrim($prep, ',');
            $stmt = $this->conn->prepare("INSERT INTO $this->table($table_column) VALUES ($prep)");
            $stmt->bind_param($types, ...array_values($data));
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("Insert Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function select($row = "*", $where = NULL)
    {
        try {
            if (!is_null($where) && count($where) > 0) {
                $cond = $types = "";
                foreach ($where as $key => $value) {
                    $cond .= "$key = ? AND ";
                    $types .= $this->getTypeChar($value);
                }
                $cond = substr($cond, 0, -4);
                $stmt = $this->conn->prepare("SELECT $row FROM $this->table WHERE $cond");
                $stmt->bind_param($types, ...array_values($where));
            } else {
                $stmt = $this->conn->prepare("SELECT $row FROM $this->table");
            }
            $stmt->execute();
            $this->res = $stmt->get_result();
        } catch (Exception $e) {
            error_log("Select Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function update($data)
    {
        try {
            if (!isset($data['id'])) {
                throw new Exception("Update requires 'id' key in data array.");
            }

            $set = $cond = $types = '';
            $values = [];
            foreach ($data as $key => $value) {
                if ($key === 'id') {
                    $cond = "$key = ?";
                } else {
                    $set .= "$key = ?,";
                }
                $types .= $this->getTypeChar($value);
                $values[] = $value;
            }
            $set = rtrim($set, ',');
            $stmt = $this->conn->prepare("UPDATE $this->table SET $set WHERE $cond");
            $stmt->bind_param($types, ...$values);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("Update Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function destroy($where)
    {
        try {
            $cond = $types = "";
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
            error_log("Delete Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function search($row, $where)
    {
        try {
            $cond = $types = "";
            foreach ($where as $key => $value) {
                $cond .= "$key LIKE ? OR ";
                $types .= $this->getTypeChar($value);
            }
            $cond = substr($cond, 0, -3);
            $stmt = $this->conn->prepare("SELECT $row FROM $this->table WHERE $cond");
            $stmt->bind_param($types, ...array_values($where));
            $stmt->execute();
            $this->res = $stmt->get_result();
        } catch (Exception $e) {
            error_log("Search Error: " . $e->getMessage());
            throw $e;
        }
    }
}

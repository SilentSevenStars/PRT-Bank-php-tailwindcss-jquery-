<?php
    require_once "Database.php";

    class Announcement extends Database{
        private $table = "announcements";

        public function insert($data)
        {
            try {
                $table_column = implode(',',array_keys($data));
                $prep=$types="";
                foreach ($data as $key => $value) {
                    $prep .='?,';
                    $types .= substr(gettype($value),0,1);
                }
                $prep = substr($prep, 0, -1);
                $stmt = $this->conn->prepare("INSERT INTO $this->table($table_column) VALUES ($prep)");
                $stmt->bind_param($types, ...array_values($data));
                $stmt->execute();
                $stmt->close();
            } catch (Exception $e) {
                die("Error while insertind data.<br>".$e);
            }
        }

        public function select($row="*", $where=NULL)
        {
            try {
                if(!is_null($where)){
                    $cond=$types="";
                    foreach ($where as $key => $value) {
                        $cond .= $key . " = ? AND ";
                        $types .= substr(gettype($value),0,1);
                    }
                    $cond = substr($cond,0,-4);
                    $stmt = $this->conn->prepare("SELECT $row FROM $this->table WHERE $cond");
                    $stmt->bind_param($types, ...array_values($where));
                } else {
                    $stmt = $this->conn->prepare("SELECT $row FROM $this->table");
                }
                $stmt->execute();
                $this->res = $stmt->get_result();
            } catch (Exception $e) {
                die("Error requesting data!. <br>". $e);
            }
        }

        public function update($data)
        {
            try {
                $set = $cond = $types = '';
                foreach ($data as $key => $value) {
                    if($key == 'id'){
                        $cond .= $key . " = ? AND ";
                    } else {
                        $set .= "$key = ?,";
                    }
                    $types .= substr(gettype($value),0,1);
                }
                $set = substr($set, 0, -1);
                $cond = substr($cond,0,-4);
                $stmt = $this->conn->prepare("UPDATE $this->table SET $set WHERE $cond");
                $stmt->bind_param($types, ...array_values($data));
                $stmt->execute();
                $stmt->close();
            } catch (Exception $e) {
                die("Error updating data! <br>".$e);
            }
        }

        public function destroy($where)
        {
            try {
                $cond = $types = '';
                    foreach($where as $key => $value){
                        $cond .= $key . " = ? AND ";
                        $types .= substr(gettype($value),0,1);
                    }
                    $cond = substr($cond,0,-4);
                    $stmt = $this->conn->prepare("DELETE FROM $this->table WHERE $cond");
                    $stmt->bind_param($types, ...array_values($where));
                    $stmt->execute();
                    $stmt->close();
            } catch (Exception $e) {
                die("Error while deleting data! . <br>".$e);
            }
        }

        public function search($row, $where)
        {
            try {
                $cond=$types="";
                    foreach ($where as $key => $value) {
                        $cond .= $key . " LIKE ? OR ";
                        $types .= substr(gettype($value),0,1);
                    }
                    $cond = substr($cond,0,-3);
                    $stmt = $this->conn->prepare("SELECT $row FROM $this->table WHERE $cond");
                    $stmt->bind_param($types, ...array_values($where));
                $stmt->execute();
                $this->res = $stmt->get_result();
            } catch (Exception $e) {
                die("Error requesting data!. <br>". $e);
            }
        }
    }
?>
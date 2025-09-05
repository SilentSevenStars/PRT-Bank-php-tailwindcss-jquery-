<?php
    class Database{
        private $servername = "localhost";
        private $username = "root";
        private $password = "";
        private $db = "bank_db";
        public $conn;
        public $res;

        public function __construct()
        {
            try {
                $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->db);
            } catch (Exception $e) {
                die("Connection error: <br>".$e);
            }
        }
        public function __destruct()
        {
            $this->conn->close();
        }
    }
?>
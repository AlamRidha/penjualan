<?php
class Database
{
    private $host = "localhost";
    private $db_name = "db_penjualan";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
            $this->conn->set_charset("utf8");
        } catch (mysqli_sql_exception $e) {
            echo "Koneksi database gagal: " . $e->getMessage();
        }
        return $this->conn;
    }
}

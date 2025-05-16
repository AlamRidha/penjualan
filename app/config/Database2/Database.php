<?php
class Database
{

    public static function getConnection()
    {
        $host = "localhost";
        $db_name = "db_penjualan";
        $username = "root";
        $password = "";

        try {
            return new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
}

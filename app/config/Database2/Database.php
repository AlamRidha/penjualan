<?php
class Database
{

    public static function getConnection()
    {
        $host = "localhost";
        $db_name = "tokoserbafinal";
        $username = "root";
        $password = "";

        try {
            return new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
}

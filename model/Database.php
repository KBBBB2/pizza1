<?php
class Database {
    private static $host = "localhost";
    private static $dbname = "pizzabazis";
    private static $username = "root";  // Cseréld ki, ha kell
    private static $password = "";      // Cseréld ki, ha kell
    private static $conn = null;

    public static function connect() {
        if (self::$conn === null) {
            self::$conn = new mysqli(self::$host, self::$username, self::$password, self::$dbname);
            if (self::$conn->connect_error) {
                die("Adatbázis hiba: " . self::$conn->connect_error);
            }
        }
        return self::$conn;
    }
}
?>

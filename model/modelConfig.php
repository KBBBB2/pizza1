<?php

class modelConfig {

    private $conn;

    public function __construct() {
        $this->conn = mysqli_connect('localhost', 'root', '', 'pizzabazis');
    }
    
    public function getConn() {
        return $this->conn;
    }

    public function conClose() {
        mysqli_close($this->conn);
    }

    // Hibaellenőrzés
    //if ($conn->connect_error) {
      //  die("Kapcsolati hiba: " . $conn->connect_error);
    //}
}

?>
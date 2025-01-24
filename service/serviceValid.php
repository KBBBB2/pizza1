<?php
require_once '../model/modelConfig.php';

class ServiceCheck extends modelConfig {
    private $id;
    public $username;
    private $password;
    private $password2;
    private $email;

    public function __construct($id = null, $username = null, $password = null, $password2 = null, $email = null, $sql = null) {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->password2 = $password2;
        $this->email = $email;
    }/*
    public function usernameCheck() {
        $conn = $this->getConn(); // Kapcsold a DB kapcsolatot
        $sql = 'SELECT * FROM account WHERE username = ?';
        if (!$conn) {
            die("Database connection is null. Check your config file.");
        }
        $stmt = mysqli_prepare($conn, $sql); // Prepared statement létrehozása
        mysqli_stmt_bind_param($stmt, 's', $this->username); // Paraméter megkötése
        mysqli_stmt_execute($stmt); // Lekérdezés végrehajtása
        $result = mysqli_stmt_get_result($stmt); // Eredmény begyűjtése
        
        if (mysqli_num_rows($result) == 1) {
            return true;
        } else {
            return false;
        }
        
    }
    */

    public function usernameCheck() {
        $sql = 'SELECT * FROM account WHERE username='.$this->username. '"username"';
        $query = mysqli_query($this->getConn(), $sql);
        if (mysqli_num_rows($query) == 1) {
            return true;
        } else {
            return false;
        }
    }
    /*
    public function passwordCheck() {
        $sql = 'SELECT * FROM account WHERE password='.$this->password. '"password"';
        $query = mysqli_query($this->getConn(), $sql);
        if (mysqli_num_rows($query) == 1) {
            return true;
        } else {
            return false;
        }
    }*/
    public function emailCheck() {
        $sql = 'SELECT * FROM account WHERE email='.$this->email. '"email"';
        $query = mysqli_query($this->getConn(), $sql);
        if (mysqli_num_rows($query) == 1) {
            return true;
        } else {
            return false;
        }
    }
    

}

?>
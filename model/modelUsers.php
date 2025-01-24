<?php
require_once 'modelConfig.php';

class modelUsers extends modelConfig {

    public $id;
    public $firstname;
    public $lastname;
    public $username;
    public $password;
    public $locked;
    public $enabled;
    public $email;
    public $phonenumber;

    public function __construct($id=null, $firstname=null, $lastname=null, $username=null, $password=null, $locked=null, $enabled=null, $email=null, $phonenumber=null) {
        parent::__construct();
        $this->id = $id;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->username = $username;
        $this->password = $password;
        $this->locked = $locked;
        $this->enabled = $enabled;
        $this->email = $email;
        $this->phonenumber = $phonenumber;
    }

    public function register($data){ //kiszedtem a register($data)
        $sql = 'INSERT INTO account (firstname, lastname, username, phonenumber, email, password) VALUES ("'.$data['firstname'].'", "'.$data['lastname'].'", "'.$data['username'].'", "'.$data['phonenumber'].'", "'.$data['email'].'", "'.$data['password'].'")';
        $query = mysqli_query($data['conn'], $sql);
        return $query ? true : false;
    }

    public function login() {
        $sql = 'SELECT * FROM customers WHERE name = "'.$this->username.'" AND password = "'.sha1($this->password).'"';
        $query = mysqli_query($this->getConn(), $sql);
        if (mysqli_num_rows($query) == 1) {
            $record = mysqli_fetch_assoc($query);
            return new modelUsers($record['id'], $record['']);
        }
        else {
            return false;
        }
    }
}



?>
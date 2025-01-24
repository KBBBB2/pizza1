<?php
require_once 'serviceCheck.php';

class ServiceReg extends ServiceCheck {
    private $id;
    private $username;
    private $password;
    private $password2;
    private $email;

    public function __construct($id = null, $username = null, $password = null, $password2 = null, $email = null, $sql = null) {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->password2 = $password2;
        $this->email = $email;
    }

    public function saveProfil($data) {
        $sql = 'INSERT INTO account (firstname, lastname, username, phonenumber, email, password) VALUES ("'.$data['firstname'].'", "'.$data['lastname'].'", "'.$data['username'].'", "'.$data['phonenumber'].'", "'.$data['email'].'", "'.$data['password'].'")';
        $query = mysqli_query($this->getConn(), $sql);
        return $query ? true : false;
    }

    //A service csak validálást tartalmazzon
}
?>
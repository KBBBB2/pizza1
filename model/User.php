<?php
class User {
    public $first_name;
    public $last_name;
    public $username;
    public $password;
    public $email;
    public $phonenumber;

    public function __construct($first_name, $last_name, $username, $password, $email, $phonenumber) {
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->username = $username;
        $this->password = password_hash($password, PASSWORD_BCRYPT);
        $this->email = $email;
        $this->phonenumber = $phonenumber;
    }
}
?>

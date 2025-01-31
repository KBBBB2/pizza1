<?php
require_once __DIR__ . '/../model/Database.php';
require_once __DIR__ . '/../model/User.php';

class UserService {
    public static function registerUser($user) {
        $conn = Database::connect();
        $stmt = $conn->prepare("INSERT INTO account (firstname, lastname, username, password, email, phonenumber) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $user->first_name, $user->last_name, $user->username, $user->password, $user->email, $user->phonenumber);
        return $stmt->execute();
    }

    public static function authenticateUser($username, $password) {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM account WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
}
?>

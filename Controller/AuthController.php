<?php
require_once __DIR__ . '/../service/UserService.php';

class AuthController {
    public static function register() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $user = new User($_POST['first_name'], $_POST['last_name'], $_POST['username'], $_POST['password'], $_POST['email'], $_POST['phonenumber']);
            if (UserService::registerUser($user)) {
                header("Location: ../view/login.php");
                exit();
            } else {
                echo "Hiba a regisztráció során!";
            }
        }
    }

    public static function login() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $username = $_POST['username'];
            $password = $_POST['password'];

            if ($user = UserService::authenticateUser($username, $password)) {
                session_start();
                $_SESSION['username'] = $user['username'];
                header("Location: ../View/MainPage.php");
                exit();
            } else {
                echo "Hibás bejelentkezési adatok!";
            }
        }
    }
}
?>

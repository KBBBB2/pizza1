<?php

include 'serviceValid.php'; // Osztály betöltése

// Például:
$test1 = new ServiceCheck();
$test1->username = 'fel'; // Létező felhasználónév


if ($test1->usernameCheck()) {
    echo 'Felhasználónév létezik.';
} else {
    echo 'Felhasználónév nem létezik.';
}
?>
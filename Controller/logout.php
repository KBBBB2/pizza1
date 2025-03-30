<?php
session_start();
session_unset();
session_destroy();
header("Location: /merged/view/customer/login.html");
exit();
?>

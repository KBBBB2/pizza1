<?php
// controllers/UnbanController.php

require_once '../models/AdminAccount.php';

header("Content-Type: text/plain");

try {
    $adminAccount = new AdminAccount();
    $rows = $adminAccount->unbanExpired();
    echo "Unban executed at " . date('Y-m-d H:i:s') . ". Rows affected: $rows\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

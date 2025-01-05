<?php
session_start();

// Kezdeti kosár beállítása
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

try {
    // Kosár tartalmának visszaküldése GET kérés esetén
    if ($isAjax && $_SERVER['REQUEST_METHOD'] === 'GET') {
        header('Content-Type: application/json');
        echo json_encode($_SESSION['cart']);
        exit;
    }

    // Új elem hozzáadása a kosárhoz
    if ($isAjax && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pizza_name']) && isset($_POST['pizza_price'])) {
        $pizza = [
            'name' => $_POST['pizza_name'],
            'price' => $_POST['pizza_price'],
        ];
        $_SESSION['cart'][] = $pizza;

        header('Content-Type: application/json');
        echo json_encode($_SESSION['cart']);
        exit;
    }

    // Kosár törlése
    if ($isAjax && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'clear') {
        $_SESSION['cart'] = [];
        header('Content-Type: application/json');
        echo json_encode([]);
        exit;
    }
} catch (Exception $e) {
    if ($isAjax) {
        header('Content-Type: application/json', true, 500);
        echo json_encode(['error' => $e->getMessage()]);
    } else {
        echo "Hiba történt: " . $e->getMessage();
    }
    exit;
}

?>
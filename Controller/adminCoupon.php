<?php
// controllers/AdminCouponController.php

require_once '../model/adminCoupon.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if (isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $rawData = file_get_contents("php://input");
    $jsonData = json_decode($rawData, true);
    if (is_array($jsonData)) {
        $_REQUEST = array_merge($_REQUEST, $jsonData);
    }
}

$action = $_REQUEST['action'] ?? 'read';

$couponModel = new Coupon();

try {
    if ($action == 'read') {
        $q = $_REQUEST['q'] ?? '';
        $coupons = $couponModel->readCoupons($q);
        echo json_encode($coupons);
    } elseif ($action == 'create') {
        $data = [
            'name'             => $_REQUEST['name'] ?? '',
            'description'      => $_REQUEST['description'] ?? '',
            'code'             => $_REQUEST['code'] ?? '',
            'discount_type'    => $_REQUEST['discount_type'] ?? '',
            'discount_value'   => $_REQUEST['discount_value'] ?? 0,
            'expiration_date'  => $_REQUEST['expiration_date'] ?? null,
            'is_active'        => $_REQUEST['is_active'] ?? 0,
        ];
        $id = $couponModel->createCoupon($data);
        if ($id) {
            echo json_encode(["success" => true, "id" => $id]);
        } else {
            echo json_encode(["error" => "Insertion failed"]);
        }
    } elseif ($action == 'update') {
        $data = [
            'id'               => $_REQUEST['id'] ?? 0,
            'name'             => $_REQUEST['name'] ?? '',
            'description'      => $_REQUEST['description'] ?? '',
            'code'             => $_REQUEST['code'] ?? '',
            'discount_type'    => $_REQUEST['discount_type'] ?? '',
            'discount_value'   => $_REQUEST['discount_value'] ?? 0,
            'expiration_date'  => $_REQUEST['expiration_date'] ?? null,
            'is_active'        => $_REQUEST['is_active'] ?? 0,
        ];
        if ($couponModel->updateCoupon($data)) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["error" => "Update failed"]);
        }
    } elseif ($action == 'delete') {
        $id = $_REQUEST['id'] ?? 0;
        if ($couponModel->deleteCoupon($id)) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["error" => "Deletion failed"]);
        }
    } else {
        echo json_encode(["error" => "Invalid action"]);
    }
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>

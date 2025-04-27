<?php
// controllers/CouponController.php

class CouponController {
    protected $couponModel;

    // Konstruktorban injektáljuk a Coupon modellt, így teszteléskor mockolhatjuk
    public function __construct($couponModel) {
        $this->couponModel = $couponModel;
    }

    public function handleRequest() {
        // Ha a kérés Content-Type-ja JSON, dekódoljuk a tartalmat
        if (isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
            $rawData = file_get_contents("php://input");
            $jsonData = json_decode($rawData, true);
            if (is_array($jsonData)) {
                $_REQUEST = array_merge($_REQUEST, $jsonData);
            }
        }

        // Ellenőrizzük, hogy érkezett-e kupon kód
        if (!isset($_REQUEST['code']) || empty(trim($_REQUEST['code']))) {
            return json_encode(["error" => "Nincs kupon kód megadva."]);
        }

        $code = trim($_REQUEST['code']);
        $coupon = $this->couponModel->getCouponByCode($code);

        if ($coupon) {
            return json_encode(["success" => true, "coupon" => $coupon]);
        } else {
            return json_encode(["error" => "Érvénytelen vagy lejárt kupon kód."]);
        }
    }
}

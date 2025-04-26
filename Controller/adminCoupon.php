<?php
require_once __DIR__ . '/../model/adminCoupon.php';

function handleRequest($method, $request, $input = null) {
    $couponModel = new AdminCoupon();
    $action = $request['action'] ?? 'read';

    try {
        switch ($action) {
            case 'read':
                $q = $request['q'] ?? '';
                $coupons = $couponModel->readCoupons($q);
                return ['status' => 200, 'data' => $coupons];

            case 'create':
                $data = [ 
                    'name'             => $request['name'] ?? '',
                    'description'      => $request['description'] ?? '',
                    'code'             => $request['code'] ?? '',
                    'discount_type'    => $request['discount_type'] ?? '',
                    'discount_value'   => $request['discount_value'] ?? 0,
                    'expiration_date'  => $request['expiration_date'] ?? null,
                    'is_active'        => $request['is_active'] ?? 0,
                ];
                $id = $couponModel->createCoupon($data);
                return $id ? ['status' => 200, 'data' => ['success' => true, 'id' => $id]] 
                           : ['status' => 500, 'data' => ['error' => 'Insertion failed']];

            case 'update':
                $data = [
                    'id'               => $request['id'] ?? 0,
                    'name'             => $request['name'] ?? '',
                    'description'      => $request['description'] ?? '',
                    'code'             => $request['code'] ?? '',
                    'discount_type'    => $request['discount_type'] ?? '',
                    'discount_value'   => $request['discount_value'] ?? 0,
                    'expiration_date'  => $request['expiration_date'] ?? null,
                    'is_active'        => $request['is_active'] ?? 0,
                ];
                $ok = $couponModel->updateCoupon($data);
                return $ok ? ['status' => 200, 'data' => ['success' => true]]
                           : ['status' => 500, 'data' => ['error' => 'Update failed']];

            case 'delete':
                $id = $request['id'] ?? 0;
                $ok = $couponModel->deleteCoupon($id);
                return $ok ? ['status' => 200, 'data' => ['success' => true]]
                           : ['status' => 500, 'data' => ['error' => 'Deletion failed']];

            default:
                return ['status' => 400, 'data' => ['error' => 'Invalid action']];
        }
    } catch (Exception $e) {
        return ['status' => 500, 'data' => ['error' => $e->getMessage()]];
    }
}

<?php
// controllers/IndexedPizzaController.php

require_once '../model/Pizza.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Ha a bejövő tartalom JSON, akkor dekódoljuk és egyesítjük a $_REQUEST-t
if (isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $rawData = file_get_contents("php://input");
    $jsonData = json_decode($rawData, true);
    if (is_array($jsonData)) {
        $_REQUEST = array_merge($_REQUEST, $jsonData);
    }
}

$method = $_SERVER['REQUEST_METHOD'];
$pizzaModel = new Pizza();

try {
    if ($method === 'GET') {
        // Csak azok a pizzák, amelyek nincsenek a featured_pizzas táblában (CALL pizza_product())
        $pizzas = $pizzaModel->getIndexedPizzas();
        echo json_encode(["success" => true, "data" => $pizzas]);
        exit;
        
    } elseif ($method === 'POST') {
        // Képfeltöltés esetén: action = uploadPizza
        if (isset($_POST['action']) && $_POST['action'] === 'uploadPizza') {
            // Ellenőrizzük, hogy minden kötelező adat és a fájl is megvan-e
            if (
                !isset($_POST['name']) || 
                !isset($_POST['crust']) || 
                !isset($_POST['cutstyle']) || 
                !isset($_POST['pizzasize']) || 
                !isset($_POST['ingredient']) || 
                !isset($_POST['price']) || 
                !isset($_FILES['image'])
            ) {
                echo json_encode(["error" => "Hiba: hiányzó adatok!"]);
                exit;
            }
            
            // Fájl ellenőrzése: csak JPG engedélyezett
            $file = $_FILES['image'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(["error" => "Hiba a fájl feltöltése során!"]);
                exit;
            }
            $fileName = $file['name'];
            $fileTmp  = $file['tmp_name'];
            $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if ($fileExt !== 'jpg') {
                echo json_encode(["error" => "Csak JPG fájlok engedélyezettek!"]);
                exit;
            }
            
            // POST adatok
            $name      = $_POST['name'];
            $price     = $_POST['price'];
            $crust     = $_POST['crust'];
            $cutstyle  = $_POST['cutstyle'];
            $pizzasize = $_POST['pizzasize'];
            $ingredient= $_POST['ingredient'];
            
            // Pizza beszúrása az adatbázisba a modell segítségével
            $pizzaId = $pizzaModel->insertPizza($name, $crust, $cutstyle, $pizzasize, $ingredient, $price);
            if (!$pizzaId) {
                echo json_encode(["error" => "Hiba történt az adatbázisba írás során."]);
                exit;
            }
            
            // Képfájl mentése:
            $targetDir = "../assets/images/" . $pizzaId;
            if (!is_dir($targetDir)) {
                if (!mkdir($targetDir, 0777, true)) {
                    echo json_encode(["error" => "Hiba a könyvtár létrehozása során!"]);
                    exit;
                }
            }
            
            $targetFile = $targetDir . "/pizza_" . $pizzaId . "." . $fileExt;
            if (!move_uploaded_file($fileTmp, $targetFile)) {
                echo json_encode(["error" => "Hiba a fájl elmentése során!"]);
                exit;
            }
            
            echo json_encode([
                "success"  => true,
                "message"  => "Pizza sikeresen feltöltve és kép elmentve!",
                "id"       => $pizzaId,
                "fileExt"  => $fileExt
            ]);
            exit;
            
        } else {
            // Egyéb POST kérések (pl. create vagy update), JSON alapú bemenettel
            $input = $_REQUEST;
            if (isset($input['action']) && $input['action'] === 'create') {
                // Új pizza létrehozása
                $fields = ['name', 'crust', 'cutstyle', 'pizzasize', 'ingredient', 'price'];
                $data = [];
                foreach ($fields as $field) {
                    $data[$field] = isset($input[$field]) ? $input[$field] : null;
                }
                $pizzaId = $pizzaModel->insertPizza($data['name'], $data['crust'], $data['cutstyle'], $data['pizzasize'], $data['ingredient'], $data['price']);
                if ($pizzaId) {
                    echo json_encode(["success" => true, "id" => $pizzaId]);
                } else {
                    echo json_encode(["success" => false, "error" => "Insertion failed"]);
                }
                exit;
            } elseif (isset($input['id'])) {
                // Létező pizza módosítása
                $id = $input['id'];
                $fields = ['name', 'crust', 'cutstyle', 'pizzasize', 'ingredient', 'price'];
                $data = [];
                foreach ($fields as $field) {
                    if (isset($input[$field])) {
                        $data[$field] = $input[$field];
                    }
                }
                if (empty($data)) {
                    echo json_encode(["success" => false, "error" => "No data to update"]);
                    exit;
                }
                if ($pizzaModel->updatePizza($id, $data)) {
                    echo json_encode(["success" => true]);
                } else {
                    echo json_encode(["success" => false, "error" => "Update failed"]);
                }
                exit;
            } else {
                echo json_encode(["success" => false, "error" => "No valid action provided"]);
                exit;
            }
        }
        
    } elseif ($method === 'DELETE') {
        // Pizza törlése – rekord törlése az adatbázisból és a hozzátartozó kép(ek) eltávolítása
        $input = $_REQUEST;
        if (!isset($input['id'])) {
            echo json_encode(["success" => false, "error" => "No id provided"]);
            exit;
        }
        $id = $input['id'];
        
        if ($pizzaModel->deletePizza($id)) {
            // Kép(ek) törlése a ../assets/images/{id} mappából
            $targetDir = "../assets/images/" . $id;
            $files = glob($targetDir . "/pizza_" . $id . ".*");
            if (!empty($files)) {
                foreach ($files as $file) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }
                // Ha a mappa üres, töröljük
                if (is_dir($targetDir) && count(scandir($targetDir)) <= 2) {
                    rmdir($targetDir);
                }
            }
            echo json_encode(["success" => true, "message" => "Pizza és a hozzá tartozó kép törölve"]);
        } else {
            echo json_encode(["success" => false, "error" => "Deletion failed"]);
        }
        exit;
        
    } else {
        echo json_encode(["success" => false, "error" => "Unsupported method"]);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
    exit;
}
?>

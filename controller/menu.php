<?php
// controller/menu.php
require_once __DIR__ . '/../model/Pizza.php';

class MenuController
{
    private $pizzaModel;

    public function __construct($pizzaModel)
    {
        $this->pizzaModel = $pizzaModel;
    }

    public function handleRequest()
    {
        // CORS headers are set in public/menu.php before this call
        $method = $_SERVER['REQUEST_METHOD'];

        // handle image serving separately if needed
        if ($method === 'GET' && isset($_GET['image'], $_GET['id'])) {
            $this->serveImage((int) $_GET['id']);
            return;
        }

        // decode JSON body
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);
            if (is_array($data)) {
                $_REQUEST = array_merge($_REQUEST, $data);
            }
        }

        switch ($method) {
            case 'GET':
                $this->index();
                break;

            case 'POST':
                $this->store();
                break;

            case 'DELETE':
                $this->destroy();
                break;

            default:
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Unsupported method']);
        }
    }

    private function serveImage(int $id)
    {
        $id = preg_replace('/D/', '', (string) $id);
        $pattern = __DIR__ . "/../assets/images/{$id}/pizza_{$id}.*";
        $matches = glob($pattern);
        $file = empty($matches)
            ? __DIR__ . "/../assets/images/default.jpg"
            : $matches[0];
        header('Content-Type: image/jpeg');
        header('Content-Length: ' . filesize($file));
        readfile($file);
    }

    private function index()
    {
        $pizzas = $this->pizzaModel->getIndexedPizzas();
        foreach ($pizzas as &$pizza) {
            $pizza['image'] = "http://" . $_SERVER['HTTP_HOST'] . "/backend/Controller/image.php?id={$pizza['id']}";
        }
        echo json_encode(['success' => true, 'data' => $pizzas]);
    }

    private function store()
    {
        if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['success'=>false,'error'=>'No image uploaded']);
            return;
        }
    
        $name      = $_POST['name']      ?? '';
        $crust     = $_POST['crust']     ?? '';
        $cutstyle  = $_POST['cutstyle']  ?? '';
        $size      = $_POST['pizzasize'] ?? '';
        $ing       = $_POST['ingredient']?? '';
        $price     = $_POST['price']     ?? 0;
    
        $newId = $this->pizzaModel->insertPizza($name, $crust, $cutstyle, $size, $ing, $price);
        if (!$newId) {
            http_response_code(500);
            echo json_encode(['success'=>false, 'error'=>'Record insert failed']);
            return;
        }

        $ext = null;
        if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $targetDir = __DIR__ . '/../images/' . $newId;
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
            $dest = "$targetDir/pizza_{$newId}.$ext";
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                http_response_code(500);
                echo json_encode(['success'=>false, 'error'=>'File move failed']);
                return;
            }
        } else {
            http_response_code(400);
            echo json_encode(['success'=>false,'error'=>'No image uploaded']);
            return;
        }

        echo json_encode([ 'success' => true, 'id' => $newId, 'fileExt' => $ext ]);
    }

    private function destroy()
    {
        $input = $_REQUEST;
        if (!isset($input['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No id provided']);
            return;
        }
        $id = $input['id'];

        if ($this->pizzaModel->deletePizza($id)) {
            http_response_code(200);

            $dir = __DIR__ . "/../assets/images/{$id}";
            if (is_dir($dir)) {
                foreach (glob($dir . "/pizza_{$id}.*") as $file) {
                    unlink($file);
                }
                if (count(scandir($dir)) <= 2) rmdir($dir);
            }
            echo json_encode(['success' => true, 'message' => 'Pizza and images deleted']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Deletion failed']);
        }
    }
}

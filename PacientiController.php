<?php
require_once '../models/PacientiModel.php';
require_once '../views/PacientiView.php';

$model = new PacientiModel();
$view = new PacientiView();

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $data = $model->search($_GET['search']);
} else {
    $data = $model->getAllPacienti();
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    header('Content-Type: application/json');
    parse_str(file_get_contents("php://input"), $deleteVars);
    $idPac = $deleteVars['ID_PAC'] ?? null;

    if ($idPac) {
        $collection = $model->getCollection(); 
        $result = $collection->deleteOne(['ID_PAC' => $idPac]);

        echo json_encode(['deletedCount' => $result->getDeletedCount()]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'ID_PAC este necesar.']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['ID_PAC'], $input['field'], $input['newValue'])) {
        require_once __DIR__ . '/../models/PacientiModel.php';
        $model = new PacientiModel();
        $modifiedCount = $model->updateField($input['ID_PAC'], $input['field'], $input['newValue']);
        echo json_encode(["modifiedCount" => $modifiedCount]);
    } else {
        echo json_encode(["modifiedCount" => 0]);
    }
    exit;
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    try {
        $model->insert($data);
        echo "Document adăugat.";
    } catch (Exception $e) {
        http_response_code(400);
        echo $e->getMessage();
    }
    exit;
}

$view->renderTable($data);


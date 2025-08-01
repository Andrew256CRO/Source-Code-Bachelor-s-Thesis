<?php
require_once '../models/BoliModel.php';
require_once '../views/BoliView.php';

$model = new BoliModel();
$view = new BoliView();


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

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    header('Content-Type: application/json');
    parse_str(file_get_contents("php://input"), $deleteVars);
    $idBl = $deleteVars['ID_BL'] ?? null;

    if ($idBl) {
        $collection = $model->getCollection(); 
        $result = $collection->deleteOne(['ID_BL' => $idBl]);

        echo json_encode(['deletedCount' => $result->getDeletedCount()]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'ID_BL este necesar.']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['ID_BL'], $input['field'], $input['newValue'])) {
        require_once __DIR__ . '/../models/BoliModel.php';
        $model = new BoliModel();
        $modifiedCount = $model->updateField($input['ID_BL'], $input['field'], $input['newValue']);
        echo json_encode(['modifiedCount' => $modifiedCount]);
    } else {
        echo json_encode(['modifiedCount' => 0]);
    }
    exit;
}



if (isset($_GET['search']) && !empty($_GET['search'])) {
    $data = $model->search($_GET['search']);
} else {
    $data = $model->findAll();
}


$view->renderTable($data);

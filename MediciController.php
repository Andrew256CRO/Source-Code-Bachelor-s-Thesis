<?php
require_once '../models/MediciModel.php';
require_once '../views/MediciView.php';

$model = new MediciModel();
$view = new MediciView();

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $data = $model->search($_GET['search']);
} else {
    $data = $model->getAllMedici();
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    header('Content-Type: application/json');
    parse_str(file_get_contents("php://input"), $deleteVars);
    $idMed = $deleteVars['ID_MED'] ?? null;

    if ($idMed) {
        $collection = $model->getCollection(); 
        $result = $collection->deleteOne(['ID_MED' => $idMed]);

        echo json_encode(['deletedCount' => $result->getDeletedCount()]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'ID_MED este necesar.']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['ID_MED'], $input['field'], $input['newValue'])) {
        require_once __DIR__ . '/../models/MediciModel.php';
        $model = new MediciModel();
        $modifiedCount = $model->updateField($input['ID_MED'], $input['field'], $input['newValue']);
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

<?php
require_once '../models/MedicamenteModel.php';
require_once '../views/MedicamenteView.php';

$model = new MedicamenteModel();
$view = new MedicamenteView();

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $data = $model->search($_GET['search']);
} else {
    $data = $model->getAllMedicamente();
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    header('Content-Type: application/json');
    parse_str(file_get_contents("php://input"), $deleteVars);
    $idMedicam = $deleteVars['ID_MEDICAM'] ?? null;

    if ($idMedicam) {
        $collection = $model->getCollection();
        $result = $collection->deleteOne(['ID_MEDICAM' => $idMedicam]);

        echo json_encode(['deletedCount' => $result->getDeletedCount()]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'ID_MEDICAM este necesar.']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['ID_MEDICAM'], $input['field'], $input['newValue'])) {
        require_once __DIR__ . '/../models/MedicamenteModel.php';
        $model = new MedicamenteModel();
        $modifiedCount = $model->updateField($input['ID_MEDICAM'], $input['field'], $input['newValue']);
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

<?php
require_once __DIR__ . '/../models/DiagnosticeModel.php';
require_once __DIR__ . '/../views/DiagnosticeView.php';

$model = new DiagnosticeModel();
$view = new DiagnosticeView();

$collection = $database->diagnostice;

if (isset($_GET['pacientiGripaMedici'])) {
    require_once '../models/DiagnosticeModel.php';
    $diagnosticeModel = new DiagnosticeModel($collection);

    $start = microtime(true);
    $rezultate = $diagnosticeModel->getPacientiGripaMedici();
    $end = microtime(true);

    if (count($rezultate) > 0) {
        echo "<table border='1'><thead><tr><th>Nume pacient</th><th>Nume medic</th></tr></thead><tbody>";
        foreach ($rezultate as $item) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($item['pacient']) . "</td>";
            echo "<td>" . htmlspecialchars($item['medic']) . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";

        $execTime = number_format($end - $start, 4);
        echo "<p style='margin-top: 10px; font-style: italic;'>Durata executiei: {$execTime} secunde</p>";
    } else {
        echo "Niciun pacient cu gripa gasit.";
    }

    exit;
}




if (isset($_GET['search']) && !empty($_GET['search'])) {
    $data = $model->search($_GET['search']);
} else {
    $data = $model->getAllDiagnostice();
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    header('Content-Type: application/json');
    parse_str(file_get_contents("php://input"), $deleteVars);
    $idCon = $deleteVars['ID_CON'] ?? null;
    $idBl = $deleteVars['ID_BL'] ?? null;

    if ($idCon && $idBl) {
        $collection = $model->getCollection(); 
        $result = $collection->deleteOne([
            'ID_CON' => $idCon,
            'ID_BL' => $idBl
        ]);

        echo json_encode(['deletedCount' => $result->getDeletedCount()]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'ID_CON și ID_BL sunt necesare.']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['ID_CON'], $input['ID_BL'], $input['field'], $input['newValue'])) {
        require_once __DIR__ . '/../models/DiagnosticeModel.php';
        $model = new DiagnosticeModel();
        $modifiedCount = $model->updateField($input['ID_CON'], $input['ID_BL'], $input['field'], $input['newValue']);
        echo json_encode(['modifiedCount' => $modifiedCount]);
    } else {
        echo json_encode(['modifiedCount' => 0]);
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

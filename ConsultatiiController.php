<?php
require_once '../models/ConsultatiiModel.php';
require_once '../views/ConsultatiiView.php';

$model = new ConsultatiiModel();
$view = new ConsultatiiView();

$collection = $database->consultatii; 

if (isset($_GET['consultatiiMed'])) {
    require_once '../models/ConsultatiiModel.php';
    $consultatiiModel = new ConsultatiiModel($collection);

    $idMed = $_GET['consultatiiMed'];

    $start = microtime(true); // pornim cronometrul
    $consultatii = $consultatiiModel->getConsultatiiByMed($idMed);
    $end = microtime(true); // oprim cronometrul
    $execTime = number_format($end - $start, 4);

    if (count($consultatii) > 0) {
        echo "<table border='1'><thead><tr><th>ID Consultație</th><th>Data</th><th>Pacient</th><th>Medic</th></tr></thead><tbody>";
        foreach ($consultatii as $item) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($item['id_con']) . "</td>";
        echo "<td>" . (
        $item['data_con'] instanceof MongoDB\BSON\UTCDateTime
            ? $item['data_con']->toDateTime()->format('Y-m-d H:i:s')
            : htmlspecialchars($item['data_con'])
        ) . "</td>";
        echo "<td>" . htmlspecialchars($item['pacient']) . "</td>";
        echo "<td>" . htmlspecialchars($item['medic']) . "</td>";
        echo "</tr>";
        }
        echo "</tbody></table>";

        // Afișăm timpul de execuție
        echo "<p style='margin-top: 10px; font-style: italic;'>Durata execuției: {$execTime} secunde</p>";
    } else {
        echo "Medicul nu are consultații.";
    }
    exit;
}



if (isset($_GET['search']) && !empty($_GET['search'])) {
    $data = $model->search($_GET['search']);
} else {
    $data = $model->getAllConsultatii();
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    header('Content-Type: application/json');
    parse_str(file_get_contents("php://input"), $deleteVars);
    $idCon = $deleteVars['ID_CON'] ?? null;

    if ($idCon) {
        $collection = $model->getCollection(); 
        $result = $collection->deleteOne(['ID_CON' => $idCon]);

        echo json_encode(['deletedCount' => $result->getDeletedCount()]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'ID_CON este necesar.']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['ID_CON'], $input['field'], $input['newValue'])) {
        require_once __DIR__ . '/../models/ConsultatiiModel.php';
        $model = new ConsultatiiModel();
        $modifiedCount = $model->updateField($input['ID_CON'], $input['field'], $input['newValue']);
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
<?php

// Includere model si view-ul pentru Alocare_pacienti
require_once '../models/Alocare_pacientiModel.php';
require_once '../views/Alocare_pacientiView.php';

// Instantiere model si view
$model = new Alocare_pacientiModel();
$view = new Alocare_pacientiView();

// Gestionare cererea POST (adaugare document nou)
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

// Gestionare cererea DELETE (stergere document)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    header('Content-Type: application/json'); 

    parse_str(file_get_contents("php://input"), $deleteVars);
    $idPac = $deleteVars['ID_PAC'] ?? null;
    $idMed = $deleteVars['ID_MED'] ?? null;

    if ($idPac && $idMed) {
        $collection = $model->getCollection(); 
        $result = $collection->deleteOne([
            'ID_PAC' => $idPac,
            'ID_MED' => $idMed
        ]);

        echo json_encode(['deletedCount' => $result->getDeletedCount()]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'ID_PAC și ID_MED sunt necesare.']);
    }
    exit; 
}

// Gestionare cererea PUT (actualizare document)
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['ID_PAC'], $data['ID_MED'], $data['field'], $data['newValue'])) {
        http_response_code(400);
        echo json_encode(["error" => "Date lipsă"]);
        exit;
    }

    require_once("../models/Alocare_pacientiModel.php");
    $model = new Alocare_pacientiModel();

    $modifiedCount = $model->updateField($data['ID_PAC'], $data['ID_MED'], $data['field'], $data['newValue']);

    header('Content-Type: application/json');
    echo json_encode(["modifiedCount" => $modifiedCount]);
    exit;
}

// Gestionare cautarea in colectie
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $term = $_GET['search'];
    $data = $model->search($term);
} else {
    $data = $model->findAll();
}

// Gestionare interogarea speciala pentru pacientii unui medic
if (isset($_GET['pacientiMed']) && !empty($_GET['pacientiMed'])) {
    require_once __DIR__ . '/../views/PacientiMedicView.php';
    require_once __DIR__ . '/../models/Alocare_pacientiModel.php';

    $model = new Alocare_pacientiModel();
    $view = new PacientiMedicView();

    $start = microtime(true);
    $pacienti = $model->getPacientiByMed($_GET['pacientiMed']);
    $end = microtime(true);

    $view->renderTable($pacienti);

    $execTime = number_format($end - $start, 4);
    echo "<p style='margin-top: 10px; font-style: italic;'>Durata executiei: {$execTime} secunde</p>";

    exit;
}

// Daca nu este cerere speciala, afisare toate documentele gasite
$view->renderTable($data); 



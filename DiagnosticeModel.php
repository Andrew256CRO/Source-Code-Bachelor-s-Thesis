<?php
require_once __DIR__ . '/../db_connection.php';

class DiagnosticeModel {
    private $collection;

    public function __construct() {
        global $client;
        $this->collection = $client->gestionare_medicala_bd->diagnostice;
    }

    public function getAllDiagnostice() {
        return $this->collection->find()->toArray();
    }

    public function search($term) {
        $normalizedTerm = $this->normalize($term);
    
        $rezultate = [];
    
        foreach ($this->collection->find() as $doc) {
            $text = $this->normalize(($doc['ID_CON'] ?? '') . ' ' . ($doc['ID_BL'] ?? '') . '' . ($doc['SIMPTOME']?? ''));
    
            if (stripos($text, $normalizedTerm) !== false) {
                $rezultate[] = $doc;
            }
        }
    
        return $rezultate;
    }
    
    private function normalize($text) {
        $map = [
            'ă' => 'a', 'â' => 'a', 'î' => 'i', 'ș' => 's', 'ş' => 's', 'ț' => 't', 'ţ' => 't',
            'Ă' => 'a', 'Â' => 'a', 'Î' => 'i', 'Ș' => 's', 'Ş' => 's', 'Ț' => 't', 'Ţ' => 't'
        ];
        return strtr(mb_strtolower($text, 'UTF-8'), $map);
    }

    public function insert($data) {
        $requiredFields = ['ID_CON', 'ID_BL', 'SIMPTOME'];
    
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                throw new Exception("Câmpul '$field' este obligatoriu și nu poate fi gol.");
            }
        }
    
        $this->collection->insertOne([
            'ID_CON' => $data['ID_CON'],
            'ID_BL' => $data['ID_BL'],
            'SIMPTOME' => $data['SIMPTOME']
        ]);
    }

    public function getCollection() {
        return $this->collection;
    }

    public function updateField($idCon, $idBl, $field, $newValue) {
        $filter = ['ID_CON' => $idCon, 'ID_BL' => $idBl];
        $update = ['$set' => [$field => $newValue]];
    
        $result = $this->collection->updateOne($filter, $update);
        return $result->getModifiedCount();
    }

    public function getPacientiGripaMedici() {
        $mongoClient = new MongoDB\Client;
        $db = $mongoClient->gestionare_medicala_bd;
    
        $idGripa = "BL003"; // ID pentru gripa
    
        // Pasul 1: gasire toate diagnosticele cu această boală
        $diagnostice = $db->diagnostice->find(['ID_BL' => $idGripa]);
    
        $idConsultatii = [];
        foreach ($diagnostice as $diag) {
            $idConsultatii[] = $diag['ID_CON'];
        }
    
        if (empty($idConsultatii)) {
            return [];
        }
    
        // Pasul 2: se iau consultațiile aferente
        $consultatii = $db->consultatii->find(['ID_CON' => ['$in' => $idConsultatii]]);
    
        $rezultate = [];
    
        foreach ($consultatii as $con) {
            $pacient = $db->pacienti->findOne(['ID_PAC' => $con['ID_PAC']]);
            $medic = $db->medici->findOne(['ID_MED' => $con['ID_MED']]);
    
            if ($pacient && $medic) {
                $rezultate[] = [
                    'pacient' => $pacient['NUME'] . " " . $pacient['PRENUME'],
                    'medic' => $medic['NUME'] . " " . $medic['PRENUME']
                ];
            }
        }
    
        return $rezultate;
    }
    
    

}

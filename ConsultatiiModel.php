<?php
require_once __DIR__ . '/../db_connection.php';

class ConsultatiiModel {
    private $collection;

    public function __construct() {
        global $client;
        $this->collection = $client->gestionare_medicala_bd->consultatii;
    }

    public function getAllConsultatii() {
        return $this->collection->find()->toArray();
    }

    public function search($term) {
        $term = $this->normalize($term);
    
        $results = [];
        foreach ($this->collection->find() as $doc) {
            $text = $this->normalize(
                ($doc['ID_CON'] ?? '') . ' ' .
                ($doc['ID_PAC'] ?? '') . ' ' .
                ($doc['ID_MED'] ?? '') . ' ' .
                ($doc['DATA_CON'] ?? '')
            );
    
            if (stripos($text, $term) !== false) {
                $results[] = $doc;
            }
        }
    
        return $results;
    }
    
    private function normalize($text) {
        $map = [
            'ă' => 'a', 'â' => 'a', 'î' => 'i', 'ș' => 's', 'ş' => 's', 'ț' => 't', 'ţ' => 't',
            'Ă' => 'a', 'Â' => 'a', 'Î' => 'i', 'Ș' => 's', 'Ş' => 's', 'Ț' => 't', 'Ţ' => 't'
        ];
        return strtr(mb_strtolower((string) $text, 'UTF-8'), $map);
    }

    public function insert($data) {
        $requiredFields = ['ID_CON', 'ID_PAC', 'ID_MED', 'DATA_CON'];
    
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                throw new Exception("Câmpul '$field' este obligatoriu și nu poate fi gol.");
            }
        }
    
        // Convertire string intr-un obiect UTCDateTime
        try {
            $date = new DateTime($data['DATA_CON']);
            $utcDate = new MongoDB\BSON\UTCDateTime($date->getTimestamp() * 1000);
        } catch (Exception $e) {
            throw new Exception("Data introdusă nu este validă: " . $e->getMessage());
        }
    
        $this->collection->insertOne([
            'ID_CON' => $data['ID_CON'],
            'ID_PAC' => $data['ID_PAC'],
            'ID_MED' => $data['ID_MED'],
            'DATA_CON' => $utcDate
        ]);
    }

    public function getCollection() {
        return $this->collection;
    }

    public function updateField($idCon, $field, $newValue) {
        // daca e DATA_CON, convertire la BSON date
        if ($field === "DATA_CON") {
            try {
                $newValue = new MongoDB\BSON\UTCDateTime(strtotime($newValue) * 1000);
            } catch (Exception $e) {
                return 0; // invalid date format
            }
        }
    
        $filter = ['ID_CON' => $idCon];
        $update = ['$set' => [$field => $newValue]];
    
        $result = $this->collection->updateOne($filter, $update);
        return $result->getModifiedCount();
    }

    public function getConsultatiiByMed($idMed) {
        require '../db_connection.php';
        $consultatii = $database->consultatii;
        $pacienti = $database->pacienti;
        $medici = $database->medici;
    
        // Cautare consultatii pentru un anumit medic
        $consultatiiList = $consultatii->find(['ID_MED' => $idMed]);
        $rezultat = [];
    
        foreach ($consultatiiList as $consultatie) {
            $pacient = $pacienti->findOne(['ID_PAC' => $consultatie['ID_PAC']]);
            $medic = $medici->findOne(['ID_MED' => $consultatie['ID_MED']]);
    
            $rezultat[] = [
                'id_con' => $consultatie['ID_CON'],
                'data_con' => $consultatie['DATA_CON'],
                'pacient' => $pacient ? ($pacient['NUME'] . " " . $pacient['PRENUME']) : 'Necunoscut',
                'medic' => $medic ? ($medic['NUME'] . " " . $medic['PRENUME']) : 'Necunoscut'
            ];
        }
    
        return $rezultat;
    }
    

}

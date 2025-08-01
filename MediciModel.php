<?php
require_once __DIR__ . '/../db_connection.php';

class MediciModel {
    private $collection;

    public function __construct() {
        global $client;
        $this->collection = $client->gestionare_medicala_bd->medici;
    }

    public function getAllMedici() {
        return $this->collection->find()->toArray();
    }

    public function search($term) {
        $normalizedTerm = $this->normalize($term);
        $rezultate = [];

        foreach ($this->collection->find() as $doc) {
            $text = $this->normalize($this->flatten($doc));
            if (stripos($text, $normalizedTerm) !== false) {
                $rezultate[] = $doc;
            }
        }

        return $rezultate;
    }

    private function flatten($array) {
        $result = '';

        foreach ($array as $value) {
            if (is_array($value) || is_object($value)) {
                $result .= ' ' . $this->flatten((array)$value);
            } else {
                $result .= ' ' . $value;
            }
        }

        return $result;
    }

    private function normalize($text) {
        $map = [
            'ă' => 'a', 'â' => 'a', 'î' => 'i', 'ș' => 's', 'ş' => 's', 'ț' => 't', 'ţ' => 't',
            'Ă' => 'a', 'Â' => 'a', 'Î' => 'i', 'Ș' => 's', 'Ş' => 's', 'Ț' => 't', 'Ţ' => 't',
        ];
        return strtr(mb_strtolower($text, 'UTF-8'), $map);
    }

    public function insert($data) {
        $requiredFields = ['ID_MED', 'NUME', 'PRENUME', 'SPECIALIZARE', 'TEL', 'EMAIL', 'STRADĂ', 'NUMĂR', 'ORAȘ', 'ADR_CAB'];
    
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                throw new Exception("Câmpul '$field' este obligatoriu și nu poate fi gol.");
            }
        }
    
        $this->collection->insertOne([
            "ID_MED" => $data["ID_MED"],
            "NUME" => $data["NUME"],
            "PRENUME" => $data["PRENUME"],
            "SPECIALIZARE" => $data["SPECIALIZARE"],
            "TEL" => $data["TEL"],
            "EMAIL" => $data["EMAIL"],
            "ADRESA" => [[
            "strada" => $data["STRADĂ"],
            "numar" => intval($data["NUMĂR"]),
            "oras" => $data["ORAȘ"],
            "adr_cab" => intval($data["ADR_CAB"])
        ]]
        ]);
    }

    public function getCollection() {
        return $this->collection;
    }

    public function updateField($idMed, $field, $newValue) {
        $filter = ['ID_MED' => $idMed];
    
        if (in_array($field, ['STRADĂ', 'NUMĂR', 'ORAȘ', 'ADR_CAB'])) {
            $mapField = [
                'STRADĂ' => 'ADRESA.0.strada',
                'NUMĂR' => 'ADRESA.0.numar',
                'ORAȘ' => 'ADRESA.0.oras',
                'ADR_CAB' => 'ADRESA.0.adr_cab'
            ];
            $mongoField = $mapField[$field];
        } else {
            $mongoField = $field;
        }
    
        $update = ['$set' => [$mongoField => $newValue]];
    
        $result = $this->collection->updateOne($filter, $update);
        return $result->getModifiedCount();
    }
    
}

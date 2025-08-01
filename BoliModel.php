<?php
require_once __DIR__ . '/../db_connection.php';

class BoliModel {
    private $collection;

    public function __construct() {
        global $database;
        $this->collection = $database->boli;
    }

    public function findAll() {
        return $this->collection->find()->toArray();
    }
    
    public function search($term) {
        $normalizedTerm = $this->normalize($term);
    
        $rezultate = [];
    
        foreach ($this->collection->find() as $doc) {
            $text = $this->normalize(($doc['ID_BOALA'] ?? '') . ' ' . ($doc['NUME_BL'] ?? '') . '' . ($doc['DESCRIERE']?? ""));
    
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
        $requiredFields = ['ID_BL', 'NUME_BL', 'DESCRIERE'];
    
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                throw new Exception("Câmpul '$field' este obligatoriu și nu poate fi gol.");
            }
        }
    
        $this->collection->insertOne([
            'ID_BL' => $data['ID_BL'],
            'NUME_BL' => $data['NUME_BL'],
            'DESCRIERE' => $data['DESCRIERE']
        ]);
    }

    public function getCollection() {
        return $this->collection;
    }
    
    public function updateField($idBl, $field, $newValue) {
        $filter = ['ID_BL' => $idBl];
        $update = ['$set' => [$field => $newValue]];
        $result = $this->collection->updateOne($filter, $update);
        return $result->getModifiedCount();
    }
    
}
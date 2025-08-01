<?php
require_once __DIR__ . '/../db_connection.php';

class MedicamenteModel {
    private $collection;

    public function __construct() {
        global $client;
        $this->collection = $client->gestionare_medicala_bd->medicamente;
    }

    public function getAllMedicamente() {
        return $this->collection->find()->toArray();
    }

    public function search($term) {
        $normalizedTerm = $this->normalize($term);
    
        $rezultate = [];
    
        foreach ($this->collection->find() as $doc) {
            $text = $this->normalize(($doc['ID_MEDICAM'] ?? '') . ' ' . ($doc['NUME'] ?? '') . '' . ($doc['CANT']?? '') . '' . ($doc['UI']?? '') . '' . ($doc['TIP_ADMIN']?? ''));
    
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
        $requiredFields = ['ID_MEDICAM', 'NUME', 'CANT', 'UI', 'TIP_ADMIN'];
    
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                throw new Exception("Câmpul '$field' este obligatoriu și nu poate fi gol.");
            }
        }
    
        $this->collection->insertOne([
            'ID_MEDICAM' => $data['ID_MEDICAM'],
            'NUME' => $data['NUME'],
            'CANT' => $data['CANT'],
            'UI' => $data['UI'],
            'TIP_ADMIN' => $data['TIP_ADMIN']
        ]);
    }

    public function getCollection() {
        return $this->collection;
    }

    public function updateField($idMedicam, $field, $newValue) {
        $filter = ['ID_MEDICAM' => $idMedicam];
        $update = ['$set' => [$field => $newValue]];
    
        $result = $this->collection->updateOne($filter, $update);
        return $result->getModifiedCount();
    }
    

}

<?php
require_once __DIR__ . '/../db_connection.php';

class Schema_tratamentModel {
    private $collection;

    public function __construct() {
        global $client;
        $this->collection = $client->gestionare_medicala_bd->schema_tratament;
    }

    public function getAllSchema() {
        return $this->collection->find();
    }

    public function search($term) {
        $normalizedTerm = $this->normalize($term);
    
        $rezultate = [];
    
        foreach ($this->collection->find() as $doc) {
            $text = $this->normalize(($doc['ID_MEDICAM'] ?? '') . ' ' . ($doc['FRECV_ZI'] ?? '') . '' . ($doc['NR_ZILE']?? ""));
    
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
        $requiredFields = ['ID_MEDICAM', 'FRECV_ZI', 'NR_ZILE'];
    
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                throw new Exception("Câmpul '$field' este obligatoriu și nu poate fi gol.");
            }
        }
    
        $this->collection->insertOne([
            'ID_MEDICAM' => $data['ID_MEDICAM'],
            'FRECV_ZI' => $data['FRECV_ZI'],
            'NR_ZILE' => $data['NR_ZILE']
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
?>

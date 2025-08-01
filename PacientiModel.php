<?php
require_once __DIR__ . '/../db_connection.php';

class PacientiModel {
    private $collection;

    public function __construct() {
        global $client;
        $this->collection = $client->gestionare_medicala_bd->pacienti;
    }

    public function getAllPacienti() {
        return $this->collection->find();
    }

    public function search($term) {
        $normalizedTerm = $this->normalize($term);
    
        $rezultate = [];
    
        foreach ($this->collection->find() as $doc) {
            $text = $this->normalize(($doc['ID_PAC'] ?? '') . ' ' . ($doc['NUME'] ?? '') . '' . ($doc['PRENUME']?? "") . '' . ($doc['CNP']?? "") . '' . ($doc['DATA_NAST']?? "") . '' . ($doc['GEN']?? "") . '' . ($doc['TEL']?? "") . '' . ($doc['EMAIL']?? "") . '' . ($doc['ADR']?? ""));
    
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
        $requiredFields = ['ID_PAC', 'NUME', 'PRENUME', 'CNP', 'DATA_NAST', 'GEN', 'TEL', 'EMAIL', 'ADR'];
    
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                throw new Exception("Câmpul '$field' este obligatoriu și nu poate fi gol.");
            }
        }
    
        // Convertire string intr-un obiect UTCDateTime
        try {
            $date = new DateTime($data['DATA_NAST']);
            $utcDate = new MongoDB\BSON\UTCDateTime($date->getTimestamp() * 1000);
        } catch (Exception $e) {
            throw new Exception("Data introdusă nu este validă: " . $e->getMessage());
        }
    
        $this->collection->insertOne([
            'ID_PAC' => $data['ID_PAC'],
            'NUME' => $data['NUME'],
            'PRENUME' => $data['PRENUME'],
            'CNP' => $data['CNP'],
            'DATA_NAST' => $utcDate,
            'GEN' => $data['GEN'],
            'TEL' => $data['TEL'],
            'EMAIL' => $data['EMAIL'],
            'ADR' => $data['ADR']
        ]);
    }

    public function getCollection() {
        return $this->collection;
    }

    public function updateField($idPac, $field, $newValue) {
        $filter = ['ID_PAC' => $idPac];
    
        // Dacă se modifică DATA_NAST, se converteste la MongoDB\BSON\UTCDateTime
        if ($field === "DATA_NAST") {
            try {
                $newValue = new MongoDB\BSON\UTCDateTime(strtotime($newValue) * 1000);
            } catch (Exception $e) {
                return 0; // invalid date
            }
        }
    
        $update = ['$set' => [$field => $newValue]];
    
        $result = $this->collection->updateOne($filter, $update);
        return $result->getModifiedCount();
    }
    
    
}
?>

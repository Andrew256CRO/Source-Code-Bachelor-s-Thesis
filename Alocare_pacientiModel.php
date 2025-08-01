<?php
require_once __DIR__ . '/../db_connection.php';

class Alocare_pacientiModel {
    private $collection;

    // Returneaza colectia "alocare_pacienti" din baza de date
    public function __construct() {
        global $database;
        $this->collection = $database->alocare_pacienti;
    }

    // Returneaza toate documentele din colectie
    public function findAll() {
        return $this->collection->find()->toArray();
    }

    // Cauta documente in functie de un termen (in orice camp)
    public function search($term) {
        return $this->collection->find([
            '$or' => [
                ['ID_PAC' => ['$regex' => $term, '$options' => 'i']],
                ['ID_MED' => ['$regex' => $term, '$options' => 'i']]
            ]
        ])->toArray();
    }

     // Adauga un document nou in colectie
    public function insert($data) {
        $requiredFields = ['ID_PAC', 'ID_MED'];
    
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                throw new Exception("Câmpul '$field' este obligatoriu și nu poate fi gol.");
            }
        }
    
        $this->collection->insertOne([
            'ID_PAC' => $data['ID_PAC'],
            'ID_MED' => $data['ID_MED']
        ]);
    }

    public function getCollection() {
        return $this->collection; 
    }

    // Actualizeaza un singur camp (field) pentru un document identificat prin ID_PAC si ID_MED
    public function updateField($idPac, $idMed, $field, $newValue) {
        $filter = [
            'ID_PAC' => $idPac,
            'ID_MED' => $idMed
        ];
        $update = [
            '$set' => [$field => $newValue]
        ];
    
        $result = $this->collection->updateOne($filter, $update);
        return $result->getModifiedCount();
    }

    // Returneaza toti pacientii alocati unui anumit medic
    public function getPacientiByMed($idMed) {
        $pipeline = [
            ['$match' => ['ID_MED' => $idMed]],
            ['$lookup' => [
                'from' => 'pacienti',
                'localField' => 'ID_PAC',
                'foreignField' => 'ID_PAC',
                'as' => 'pacient_info'
            ]],
            ['$lookup' => [
                'from' => 'medici',
                'localField' => 'ID_MED',
                'foreignField' => 'ID_MED',
                'as' => 'medic_info'
            ]],
            ['$unwind' => '$pacient_info'],
            ['$unwind' => '$medic_info']
        ];
    
        $cursor = $this->collection->aggregate($pipeline);
        return $cursor->toArray();
    }
    
    
    

}
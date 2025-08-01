<?php
// Includere biblioteca MongoDB pentru a putea crea conexiunea
require 'vendor/autoload.php'; 

// Creare client pentru conectarea la serverul MongoDB
$client = new MongoDB\Client("mongodb://localhost:27017");

// Selectare baza de date numita 'gestionare_medicala_bd'
$database = $client->gestionare_medicala_bd;
?>

<?php

class Alocare_pacientiView {
    // Functie pentru afisarea datelor intr-un tabel HTML
    public function renderTable($data) {
        echo "<table>";
        echo "<tr>
                <th>ID_PAC</th>
                <th>ID_MED</th>
                <th>Acțiuni</th>
              </tr>";

        // Randuri cu date
        foreach ($data as $doc) {
            $idPac = htmlspecialchars($doc['ID_PAC']);
            $idMed = htmlspecialchars($doc['ID_MED']);

            echo "<tr data-id-pac='$idPac' data-id-med='$idMed'>";
            echo "<td data-field='ID_PAC'>$idPac</td>";
            echo "<td data-field='ID_MED'>$idMed</td>";
            // Buton pentru stergere
            echo "<td><button class='delete-button'>🗑️ Șterge</button></td>";
            echo "</tr>";
        }

        echo "</table>";
    }
}

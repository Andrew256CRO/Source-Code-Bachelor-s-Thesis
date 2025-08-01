<?php
class Schema_tratamentView {
    public function renderTable($data) {
        echo "<table>";
        echo "<tr>
                <th>ID_MEDICAM</th>
                <th>FRECV_ZI</th>
                <th>NR_ZILE</th>
                <th>Acțiuni</th>
              </tr>";

        foreach ($data as $doc) {
            $idMedicam = htmlspecialchars($doc['ID_MEDICAM']);
            $frecvZi = htmlspecialchars($doc['FRECV_ZI']);
            $nrZile = htmlspecialchars($doc['NR_ZILE']);

            echo "<tr data-id-medicam='$idMedicam'>";
            echo "<td data-field='ID_MEDICAM'>$idMedicam</td>";
            echo "<td data-field='FRECV_ZI'>$frecvZi</td>";
            echo "<td data-field='NR_ZILE'>$nrZile</td>";
            echo "<td><button class='delete-button'>🗑️ Șterge</button></td>";
            echo "</tr>";
        }

        echo "</table>";
    }
}

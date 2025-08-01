<?php
class MedicamenteView {
    public function renderTable($data) {
        echo "<table>";
        echo "<tr>
                <th>ID_MEDICAM</th>
                <th>NUME</th>
                <th>CANT</th>
                <th>UI</th>
                <th>TIP_ADMIN</th>
                <th>Acțiuni</th>
              </tr>";

        foreach ($data as $doc) {
            $idMedicam = htmlspecialchars($doc['ID_MEDICAM']);
            $nume = htmlspecialchars($doc['NUME']);
            $cant = htmlspecialchars($doc['CANT']);
            $ui = htmlspecialchars($doc['UI']);
            $tipAdmin = htmlspecialchars($doc['TIP_ADMIN']);

            echo "<tr data-id-medicam='$idMedicam'>";
            echo "<td data-field='ID_MEDICAM'>$idMedicam</td>";
            echo "<td data-field='NUME'>$nume</td>";
            echo "<td data-field='CANT'>$cant</td>";
            echo "<td data-field='UI'>$ui</td>";
            echo "<td data-field='TIP_ADMIN'>$tipAdmin</td>";
            echo "<td><button class='delete-button'>🗑️ Șterge</button></td>";
            echo "</tr>";
        }

        echo "</table>";
    }
}

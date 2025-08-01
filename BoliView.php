<?php
class BoliView {
    public function renderTable($data) {
        echo "<table>";
        echo "<tr>
                <th>ID_BL</th>
                <th>NUME_BL</th>
                <th>DESCRIERE</th>
                <th>Acțiuni</th>
              </tr>";

        foreach ($data as $doc) {
            $idBl = htmlspecialchars($doc['ID_BL']);
            $nume = htmlspecialchars($doc['NUME_BL']);
            $desc = htmlspecialchars($doc['DESCRIERE']);

            echo "<tr data-id-bl='$idBl'>";
            echo "<td data-field='ID_BL'>$idBl</td>";
            echo "<td data-field='NUME_BL'>$nume</td>";
            echo "<td data-field='DESCRIERE'>$desc</td>";
            echo "<td><button class='delete-button'>🗑️ Șterge</button></td>";
            echo "</tr>";
        }

        echo "</table>";
    }
}

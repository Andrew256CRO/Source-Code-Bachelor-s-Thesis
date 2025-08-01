<?php

class DiagnosticeView {
    public function renderTable($data) {
        echo "<table>";
        echo "<tr>
                <th>ID_CON</th>
                <th>ID_BL</th>
                <th>SIMPTOME</th>
                <th>Acțiuni</th>
              </tr>";

              foreach ($data as $doc) {
                $idCon = htmlspecialchars($doc['ID_CON']);
                $idBl = htmlspecialchars($doc['ID_BL']);
                $simptome = htmlspecialchars($doc['SIMPTOME']);
            
                echo "<tr data-id-con='$idCon' data-id-bl='$idBl'>";
                echo "<td data-field='ID_CON'>$idCon</td>";
                echo "<td data-field='ID_BL'>$idBl</td>";
                echo "<td data-field='SIMPTOME'>$simptome</td>";
                echo "<td><button class='delete-button'>🗑️ Șterge</button></td>";
                echo "</tr>";
            }

        echo "</table>";
    }
}


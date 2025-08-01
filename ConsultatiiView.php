<?php
class ConsultatiiView {
    public function renderTable($data) {
        echo "<table>";
        echo "<tr>
                <th>ID_CON</th>
                <th>ID_PAC</th>
                <th>ID_MED</th>
                <th>DATA_CON</th>
                <th>Acțiuni</th>
              </tr>";

        foreach ($data as $doc) {
            $idCon = htmlspecialchars($doc['ID_CON']);
            $idPac = htmlspecialchars($doc['ID_PAC']);
            $idMed = htmlspecialchars($doc['ID_MED']);

            if (isset($doc['DATA_CON']) && $doc['DATA_CON'] instanceof MongoDB\BSON\UTCDateTime) {
                $timestamp = $doc['DATA_CON']->toDateTime()->getTimestamp();
                $dataCon = date("Y-m-d H:i:s", $timestamp);
            } else {
                $dataCon = htmlspecialchars($doc['DATA_CON']);
            }

            echo "<tr data-id-con='$idCon'>";
            echo "<td data-field='ID_CON'>$idCon</td>";
            echo "<td data-field='ID_PAC'>$idPac</td>";
            echo "<td data-field='ID_MED'>$idMed</td>";
            echo "<td data-field='DATA_CON'>$dataCon</td>";
            echo "<td><button class='delete-button'>🗑️ Șterge</button></td>";
            echo "</tr>";
        }

        echo "</table>";
    }
}

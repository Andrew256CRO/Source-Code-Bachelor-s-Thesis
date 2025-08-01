<?php
class PacientiView {
    public function renderTable($data) {
        echo "<table>";
        echo "<tr>
                <th>ID_PAC</th>
                <th>NUME</th>
                <th>PRENUME</th>
                <th>CNP</th>
                <th>DATA_NAST</th>
                <th>GEN</th>
                <th>TEL</th>
                <th>EMAIL</th>
                <th>ADR</th>
                <th>Acțiuni</th>
              </tr>";

        foreach ($data as $doc) {
            $idPac = htmlspecialchars($doc['ID_PAC']);
            $nume = htmlspecialchars($doc['NUME']);
            $prenume = htmlspecialchars($doc['PRENUME']);
            $cnp = htmlspecialchars($doc['CNP']);
            $gen = htmlspecialchars($doc['GEN']);
            $tel = htmlspecialchars($doc['TEL']);
            $email = htmlspecialchars($doc['EMAIL']);
            $adr = htmlspecialchars($doc['ADR']);

            if (isset($doc['DATA_NAST']) && $doc['DATA_NAST'] instanceof MongoDB\BSON\UTCDateTime) {
                $timestamp = $doc['DATA_NAST']->toDateTime()->getTimestamp();
                $dataNast = date("Y-m-d", $timestamp);
            } else {
                $dataNast = htmlspecialchars($doc['DATA_NAST']);
            }

            echo "<tr data-id-pac='$idPac'>";
            echo "<td data-field='ID_PAC'>$idPac</td>";
            echo "<td data-field='NUME'>$nume</td>";
            echo "<td data-field='PRENUME'>$prenume</td>";
            echo "<td data-field='CNP'>$cnp</td>";
            echo "<td data-field='DATA_NAST'>$dataNast</td>";
            echo "<td data-field='GEN'>$gen</td>";
            echo "<td data-field='TEL'>$tel</td>";
            echo "<td data-field='EMAIL'>$email</td>";
            echo "<td data-field='ADR'>$adr</td>";
            echo "<td><button class='delete-button'>🗑️ Șterge</button></td>";
            echo "</tr>";
        }

        echo "</table>";
    }
}

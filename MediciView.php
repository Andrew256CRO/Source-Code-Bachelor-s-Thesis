<?php
class MediciView {
    public function renderTable($data) {
        echo "<table>";
        echo "<tr>
                <th>ID_MED</th>
                <th>NUME</th>
                <th>PRENUME</th>
                <th>SPECIALIZARE</th>
                <th>TEL</th>
                <th>EMAIL</th>
                <th>STRADĂ</th>
                <th>NUMĂR</th>
                <th>ORAȘ</th>
                <th>ADR_CAB</th>
                <th>Acțiuni</th>
              </tr>";

        foreach ($data as $doc) {
            $idMed = htmlspecialchars($doc['ID_MED']);
            $nume = htmlspecialchars($doc['NUME']);
            $prenume = htmlspecialchars($doc['PRENUME']);
            $specializare = htmlspecialchars($doc['SPECIALIZARE']);
            $tel = htmlspecialchars($doc['TEL']);
            $email = htmlspecialchars($doc['EMAIL']);

            $strada = isset($doc['ADRESA'][0]['strada']) ? htmlspecialchars($doc['ADRESA'][0]['strada']) : '';
            $numar = isset($doc['ADRESA'][0]['numar']) ? htmlspecialchars($doc['ADRESA'][0]['numar']) : '';
            $oras = isset($doc['ADRESA'][0]['oras']) ? htmlspecialchars($doc['ADRESA'][0]['oras']) : '';
            $adrCab = isset($doc['ADRESA'][0]['adr_cab']) ? htmlspecialchars($doc['ADRESA'][0]['adr_cab']) : '';

            echo "<tr data-id-med='$idMed'>";
            echo "<td data-field='ID_MED'>$idMed</td>";
            echo "<td data-field='NUME'>$nume</td>";
            echo "<td data-field='PRENUME'>$prenume</td>";
            echo "<td data-field='SPECIALIZARE'>$specializare</td>";
            echo "<td data-field='TEL'>$tel</td>";
            echo "<td data-field='EMAIL'>$email</td>";
            echo "<td data-field='STRADĂ'>$strada</td>";
            echo "<td data-field='NUMĂR'>$numar</td>";
            echo "<td data-field='ORAȘ'>$oras</td>";
            echo "<td data-field='ADR_CAB'>$adrCab</td>";
            echo "<td><button class='delete-button'>🗑️ Șterge</button></td>";
            echo "</tr>";
        }

        echo "</table>";
    }
}

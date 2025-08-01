<?php
class PacientiMedicView {
    public function renderTable($data) {
        if (empty($data)) {
            echo "<h2>Nu există pacienți alocați pentru acest medic.</h2>";
            return;
        }

        // Extragere numele medicului din primul document
        $medic = $data[0]->medic_info ?? null;
        $numeMedic = $medic ? htmlspecialchars($medic->NUME) . " " . htmlspecialchars($medic->PRENUME) : "necunoscut";

        echo "<h2>Pacienți alocați medicului: $numeMedic</h2>";

        echo "<table border='1'>";
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
              </tr>";

        foreach ($data as $doc) {
            $pacient = $doc->pacient_info;

            echo "<tr>";
            echo "<td>" . htmlspecialchars($pacient->ID_PAC ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($pacient->NUME ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($pacient->PRENUME ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($pacient->CNP ?? '') . "</td>";

            if (isset($pacient->DATA_NAST) && $pacient->DATA_NAST instanceof MongoDB\BSON\UTCDateTime) {
                $timestamp = $pacient->DATA_NAST->toDateTime()->format('Y-m-d');
                echo "<td>$timestamp</td>";
            } else {
                echo "<td>" . htmlspecialchars($pacient->DATA_NAST ?? '') . "</td>";
            }

            echo "<td>" . htmlspecialchars($pacient->GEN ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($pacient->TEL ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($pacient->EMAIL ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($pacient->ADR ?? '') . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    }
}

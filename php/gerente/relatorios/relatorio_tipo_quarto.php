<?php
require '../../../vendor/autoload.php';
use Dompdf\Dompdf;

$pdo = new PDO("mysql:host=localhost;dbname=sistema_hotel", "root", "");

$sql = "
SELECT q.tipo, COUNT(r.id_reserva) AS total
FROM reservas r
JOIN quartos q ON r.id_quarto = q.id_quarto
GROUP BY q.tipo
";
$dados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$html = '<h1>Reservas por Tipo de Quarto</h1><table border="1" cellpadding="5"><tr><th>Tipo</th><th>Total de Reservas</th></tr>';
foreach ($dados as $d) {
    $html .= "<tr><td>{$d['tipo']}</td><td>{$d['total']}</td></tr>";
}
$html .= '</table>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->render();
$dompdf->stream("reservas_por_tipo.pdf", ["Attachment" => false]);

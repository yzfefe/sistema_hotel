<?php
require '../../../vendor/autoload.php';
use Dompdf\Dompdf;

$pdo = new PDO("mysql:host=localhost;dbname=sistema_hotel", "root", "");

$dados = $pdo->query("
    SELECT MONTHNAME(data_reserva) AS mes, COUNT(*) AS total
    FROM reservas_quarto
    GROUP BY MONTH(data_reserva), MONTHNAME(data_reserva)
    ORDER BY MONTH(data_reserva)
")->fetchAll(PDO::FETCH_ASSOC);

$html = '<h1>Total de Reservas por Mês</h1><table border="1" cellpadding="5"><tr><th>Mês</th><th>Total de Reservas</th></tr>';
foreach ($dados as $d) {
    $html .= "<tr><td>{$d['mes']}</td><td>{$d['total']}</td></tr>";
}
$html .= '</table>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("reservas_por_mes.pdf", ["Attachment" => false]);

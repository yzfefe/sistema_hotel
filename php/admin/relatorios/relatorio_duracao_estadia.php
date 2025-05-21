<?php
require '../../../vendor/autoload.php';
use Dompdf\Dompdf;

$pdo = new PDO("mysql:host=localhost;dbname=sistema_hotel", "root", "");

$sql = "
SELECT 
    AVG(DATEDIFF(data_encerrada, data_reserva)) AS media,
    MIN(DATEDIFF(data_encerrada, data_reserva)) AS minima,
    MAX(DATEDIFF(data_encerrada, data_reserva)) AS maxima
FROM reservas
WHERE data_encerrada IS NOT NULL
";
$d = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

$html = '<h1>Duração Média de Estadia</h1>';
$html .= "<p><strong>Média Geral:</strong> " . round($d['media'], 1) . " dias</p>";
$html .= "<p><strong>Mínima:</strong> {$d['minima']} dia(s)</p>";
$html .= "<p><strong>Máxima:</strong> {$d['maxima']} dia(s)</p>";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->render();
$dompdf->stream("duracao_estadia.pdf", ["Attachment" => false]);

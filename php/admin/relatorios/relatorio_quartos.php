<?php
require '../../../vendor/autoload.php';
use Dompdf\Dompdf;

$pdo = new PDO("mysql:host=localhost;dbname=sistema_hotel", "root", "");

$sql = "
SELECT q.num_quarto, q.tipo, COUNT(r.id_reserva) AS total_ocupacoes
FROM reservas r
JOIN quartos q ON r.id_quarto = q.id_quarto
GROUP BY q.id_quarto
ORDER BY total_ocupacoes DESC
LIMIT 5
";
$dados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$html = '<h1>Quartos Mais Solicitados</h1><table border="1" cellpadding="5"><tr><th>Nº do Quarto</th><th>Tipo</th><th>Total de Ocupações</th></tr>';
foreach ($dados as $d) {
    $html .= "<tr><td>{$d['num_quarto']}</td><td>{$d['tipo']}</td><td>{$d['total_ocupacoes']}</td></tr>";
}
$html .= '</table>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->render();
$dompdf->stream("quartos_mais_solicitados.pdf", ["Attachment" => false]);

<?php
require '../../../vendor/autoload.php';
use Dompdf\Dompdf;

$pdo = new PDO("mysql:host=localhost;dbname=sistema_hotel", "root", "");

$sql = "
SELECT h.nome, MIN(r.data_reserva) AS primeira_estadia, COUNT(r.id_reserva) AS total_estadas
FROM reservas r
JOIN hospede h ON r.id_hos = h.id_hos
GROUP BY h.id_hos
ORDER BY primeira_estadia ASC
LIMIT 5
";
$dados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$html = '<h1>Clientes Mais Antigos</h1><table border="1" cellpadding="5"><tr><th>Nome</th><th>Primeira Estadia</th><th>Total de Estadas</th></tr>';
foreach ($dados as $d) {
    $html .= "<tr><td>{$d['nome']}</td><td>{$d['primeira_estadia']}</td><td>{$d['total_estadas']}</td></tr>";
}
$html .= '</table>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->render();
$dompdf->stream("clientes_antigos.pdf", ["Attachment" => false]);

<?php
require '../../../vendor/autoload.php';
use Dompdf\Dompdf;

$pdo = new PDO("mysql:host=localhost;dbname=sistema_hotel", "root", "");

$sql = "
SELECT s.nome, COUNT(c.id_servico) AS total
FROM consumo c
JOIN servicos s ON c.id_servico = s.id_serv
GROUP BY s.id_serv
ORDER BY total DESC
";
$dados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$mais = $dados[0] ?? ['nome' => '-', 'total' => 0];
$menos = end($dados);

$html = '<h1>Serviços Mais e Menos Solicitados</h1><table border="1" cellpadding="5">';
$html .= "<tr><th>Serviço</th><th>Solicitações</th></tr>";
$html .= "<tr><td><strong>Mais Solicitado: </strong>{$mais['nome']}</td><td>{$mais['total']}</td></tr>";
$html .= "<tr><td><strong>Menos Solicitado: </strong>{$menos['nome']}</td><td>{$menos['total']}</td></tr>";
$html .= "</table>";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->render();
$dompdf->stream("servicos.pdf", ["Attachment" => false]);

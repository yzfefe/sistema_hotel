<?php
require '../../../vendor/autoload.php';
use Dompdf\Dompdf;

// Conexão com o banco de dados
$pdo = new PDO("mysql:host=localhost;dbname=sistema_hotel", "root", "");

// Consulta para obter os quartos mais solicitados
$sql = "
SELECT q.num_quarto, q.tipo, COUNT(r.id_reserva) AS total_ocupacoes
FROM reservas_quarto r
JOIN quartos q ON r.id_quarto = q.id_quarto
GROUP BY q.id_quarto
ORDER BY total_ocupacoes DESC
LIMIT 5
";

$dados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Criação do HTML para o PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório de Quartos Mais Solicitados</title>
    <style>
        body { 
            font-family: Arial, sans-serif;
            background: linear-gradient(180deg, rgba(107, 57, 42, 0.946) 6%, rgba(133,78,57,1) 18%, rgb(203, 164, 122) 60%, rgba(217, 192, 164, 0.339) 90%, rgba(255,255,255,1) 110%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h1 { 
            color: #c49a6c;
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #c49a6c;
        }
        h2 {
            color: #6b392a;
            margin-top: 30px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px;
            margin-bottom: 30px;
        }
        th { 
            background-color: #c49a6c; 
            color: white; 
            padding: 12px;
            text-align: left;
        }
        td { 
            padding: 10px; 
            border-bottom: 1px solid #e0d3c5;
        }
        .destaque { 
            font-weight: bold; 
            color: #6b392a;
        }
        .top-quarto {
            background-color: rgba(196, 154, 108, 0.1);
        }
        .footer {
            text-align: right;
            margin-top: 30px;
            font-size: 0.8em;
            color: #6b392a;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            max-width: 150px;
        }
        .info-box {
            background-color: rgba(217, 192, 164, 0.2);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="../../../img/logo_hotel.png" alt="Hotel Luxo">
            <h2 style="color: #6b392a; margin-bottom: 5px;">Relatório de Ocupação</h2>
        </div>
        
        <div class="info-box">
            <p>Este relatório apresenta os 5 quartos com maior número de ocupações no período analisado.</p>
        </div>
        
        <h1>Quartos Mais Solicitados</h1>
        
        <table>
            <tr>
                <th>Nº do Quarto</th>
                <th>Tipo</th>
                <th>Total de Ocupações</th>
            </tr>';

// Adiciona os quartos à tabela
foreach ($dados as $index => $quarto) {
    $rowClass = $index === 0 ? 'top-quarto' : ''; // Destaca o quarto mais solicitado
    $html .= '
            <tr class="'.$rowClass.'">
                <td>'.$quarto['num_quarto'].'</td>
                <td>'.$quarto['tipo'].'</td>
                <td class="destaque">'.$quarto['total_ocupacoes'].'</td>
            </tr>';
}

$html .= '
        </table>
        
        <div class="footer">
            Relatório gerado em: ' . date('d/m/Y H:i:s') . ' | Sistema de Gestão Hoteleira
        </div>
    </div>
</body>
</html>';

// Geração do PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Saída do PDF para o navegador
$dompdf->stream("relatorio_quartos_mais_solicitados.pdf", ["Attachment" => false]);
?>

<?php
require '../../../vendor/autoload.php';
use Dompdf\Dompdf;

// Conexão com o banco de dados
$pdo = new PDO("mysql:host=localhost;dbname=sistema_hotel", "root", "");

// Consulta para obter os clientes mais antigos
$sql = "
SELECT h.nome, MIN(r.data_reserva) AS primeira_estadia, COUNT(r.id_reserva) AS total_estadas
FROM reservas_quarto r
JOIN hospede h ON r.id_hos = h.id_hos
GROUP BY h.id_hos
ORDER BY primeira_estadia ASC
LIMIT 5
";

$dados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Criação do HTML para o PDF com estilo profissional
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório de Clientes Antigos</title>
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
    </style>
</head>
<body>
    <div class="logo">
        <img src="../../../img/img/logo_hoteel.png" alt="Caminho das Pedras - Rustic Hotel">
    </div>
    <div class="container">
        <h1>Relatório de Clientes Mais Antigos</h1>
        
        <table>
            <tr>
                <th>Nome do Hóspede</th>
                <th>Primeira Estadia</th>
                <th>Total de Estadas</th>
            </tr>';

// Adiciona os clientes à tabela
foreach ($dados as $cliente) {
    $html .= '
            <tr>
                <td>' . htmlspecialchars($cliente['nome']) . '</td>
                <td>' . date('d/m/Y', strtotime($cliente['primeira_estadia'])) . '</td>
                <td>' . $cliente['total_estadas'] . '</td>
            </tr>';
}

$html .= '
        </table>
        
        <div class="footer">
            Relatório gerado em: ' . date('d/m/Y H:i:s') . '
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
$dompdf->stream("relatorio_clientes_antigos.pdf", ["Attachment" => false]);
?>
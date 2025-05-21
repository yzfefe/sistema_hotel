<?php
require '../../../vendor/autoload.php';
use Dompdf\Dompdf;

// Conexão com o banco de dados
$pdo = new PDO("mysql:host=localhost;dbname=sistema_hotel", "root", "");

// Consulta para obter os serviços mais e menos solicitados
$sql = "
SELECT s.nome, COUNT(ss.id_serv) AS total
FROM solicitacoes_servico ss
JOIN servicos s ON ss.id_serv = s.id_serv
GROUP BY s.id_serv
ORDER BY total DESC
";

$dados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Verifica se há dados
if (count($dados) > 0) {
    $mais = $dados[0];
    $menos = end($dados);
    
    if (count($dados) == 1) {
        $menos = $mais;
    }
} else {
    $mais = ['nome' => 'Nenhum serviço registrado', 'total' => 0];
    $menos = $mais;
}

// Criação do HTML para o PDF com o novo esquema de cores
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório de Serviços</title>
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
        .mais-solicitado {
            background-color: rgba(196, 154, 108, 0.1);
        }
        .menos-solicitado {
            background-color: rgba(107, 57, 42, 0.05);
        }
        .footer {
            text-align: right;
            margin-top: 30px;
            font-size: 0.8em;
            color: #6b392a;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Relatório de Serviços Mais Utilizados</h1>
        
        <table>
            <tr>
                <th colspan="2">Resumo de Solicitações</th>
            </tr>
            <tr class="mais-solicitado">
                <td class="destaque">Serviço Mais Solicitado</td>
                <td>' . htmlspecialchars($mais['nome']) . '</td>
            </tr>
            <tr class="mais-solicitado">
                <td class="destaque">Total de Solicitações</td>
                <td>' . $mais['total'] . '</td>
            </tr>
            <tr class="menos-solicitado">
                <td class="destaque">Serviço Menos Solicitado</td>
                <td>' . htmlspecialchars($menos['nome']) . '</td>
            </tr>
            <tr class="menos-solicitado">
                <td class="destaque">Total de Solicitações</td>
                <td>' . $menos['total'] . '</td>
            </tr>
        </table>
        
        <h2>Detalhamento de Todos os Serviços</h2>
        <table>
            <tr>
                <th>Serviço</th>
                <th>Total de Solicitações</th>
            </tr>';

// Adiciona todos os serviços à tabela
foreach ($dados as $servico) {
    $html .= '
            <tr>
                <td>' . htmlspecialchars($servico['nome']) . '</td>
                <td>' . $servico['total'] . '</td>
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
$dompdf->stream("relatorio_servicos_hotel.pdf", ["Attachment" => false]);
?>
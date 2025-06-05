<?php
require '../../../vendor/autoload.php';
use Dompdf\Dompdf;

// Configurar o fuso horário para São Paulo
date_default_timezone_set('America/Sao_Paulo');

// Conexão com o banco de dados
$pdo = new PDO("mysql:host=localhost;dbname=sistema_hotel", "root", "");

// Consulta para obter as reservas por tipo de quarto
$sql = "
SELECT q.tipo, COUNT(r.id_reserva) AS total
FROM reservas_quarto r
JOIN quartos q ON r.id_quarto = q.id_quarto
GROUP BY q.tipo
ORDER BY total DESC
";
$dados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Verifica se há dados
if (count($dados) == 0) {
    $dados = [['tipo' => 'Nenhuma reserva registrada', 'total' => 0]];
}

// Encontra o tipo mais e menos reservado
$max_reservas = 0;
$min_reservas = PHP_INT_MAX;
$tipo_mais = '';
$tipo_menos = '';

foreach ($dados as $tipo) {
    if ($tipo['total'] > $max_reservas) {
        $max_reservas = $tipo['total'];
        $tipo_mais = $tipo['tipo'];
    }
    if ($tipo['total'] < $min_reservas) {
        $min_reservas = $tipo['total'];
        $tipo_menos = $tipo['tipo'];
    }
}

// Criação do HTML para o PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório de Reservas por Tipo de Quarto</title>
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
        .tipo-mais {
            background-color: rgba(196, 154, 108, 0.2);
        }
        .tipo-menos {
            background-color: rgba(107, 57, 42, 0.1);
        }
        .footer {
            text-align: right;
            margin-top: 30px;
            font-size: 0.8em;
            color: #6b392a;
        }
        .grafico {
            height: 20px;
            background-color: #e0d3c5;
            margin: 5px 0;
            border-radius: 3px;
            overflow: hidden;
        }
        .barra {
            height: 100%;
            background-color: #8a5a44;
            border-radius: 3px;
        }
        .resumo {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f4ee;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="../../../img/logo_hoteel.png" alt="Caminho das Pedras - Rustic Hotel" style="max-height: 80px; margin-bottom: 15px;">
        <h1>Relatório de Reservas por Tipo de Quarto</h1>
        
        <div class="resumo">
            <h2>Resumo Estatístico</h2>
            <p><span class="destaque">Tipo mais reservado:</span> ' . htmlspecialchars($tipo_mais) . ' (' . $max_reservas . ' reservas)</p>
            <p><span class="destaque">Tipo menos reservado:</span> ' . htmlspecialchars($tipo_menos) . ' (' . $min_reservas . ' reservas)</p>
            <p><span class="destaque">Total de tipos analisados:</span> ' . count($dados) . '</p>
        </div>
        
        <h2>Detalhes por Tipo de Quarto</h2>
        <table>
            <tr>
                <th>Tipo de Quarto</th>
                <th>Total de Reservas</th>
                <th>Visualização</th>
            </tr>';

// Encontra o valor máximo para normalizar as barras do gráfico
$max_value = $max_reservas > 0 ? $max_reservas : 1;

// Adiciona os dados à tabela
foreach ($dados as $tipo) {
    $classe = '';
    if ($tipo['tipo'] == $tipo_mais) {
        $classe = 'tipo-mais';
    } elseif ($tipo['tipo'] == $tipo_menos) {
        $classe = 'tipo-menos';
    }
    
    $percentual = ($tipo['total'] / $max_value) * 100;
    
    $html .= '
            <tr class="' . $classe . '">
                <td>' . htmlspecialchars($tipo['tipo']) . '</td>
                <td>' . $tipo['total'] . '</td>
                <td>
                    <div class="grafico">
                        <div class="barra" style="width: ' . $percentual . '%;"></div>
                    </div>
                </td>
            </tr>';
}

$html .= '
        </table>
        
        <div class="footer">
            Relatório gerado em: ' . date('d/m/Y H:i:s') . ' (Horário de São Paulo)
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
$dompdf->stream("relatorio_reservas_tipo_quarto.pdf", ["Attachment" => false]);
?>
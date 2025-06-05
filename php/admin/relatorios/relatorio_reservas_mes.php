<?php
require '../../../vendor/autoload.php';
use Dompdf\Dompdf;

// Configurar o fuso horário para São Paulo
date_default_timezone_set('America/Sao_Paulo');

// Conexão com o banco de dados
$pdo = new PDO("mysql:host=localhost;dbname=sistema_hotel", "root", "");

// Mapeamento dos meses em inglês para português
$meses_pt = [
    1 => 'Janeiro',
    2 => 'Fevereiro',
    3 => 'Março',
    4 => 'Abril',
    5 => 'Maio',
    6 => 'Junho',
    7 => 'Julho',
    8 => 'Agosto',
    9 => 'Setembro',
    10 => 'Outubro',
    11 => 'Novembro',
    12 => 'Dezembro'
];

// Consulta para obter as reservas por mês
$dados_brutos = $pdo->query("
    SELECT MONTH(data_reserva) AS mes_numero, COUNT(*) AS total
    FROM reservas_quarto
    GROUP BY MONTH(data_reserva)
    ORDER BY MONTH(data_reserva)
")->fetchAll(PDO::FETCH_ASSOC);

// Criar array com todos os meses (1-12)
$todos_meses = [];
for ($i = 1; $i <= 12; $i++) {
    $todos_meses[$i] = [
        'mes_numero' => $i,
        'mes' => $meses_pt[$i],
        'total' => 0
    ];
}

// Preencher com os dados existentes
foreach ($dados_brutos as $mes) {
    $todos_meses[$mes['mes_numero']]['total'] = $mes['total'];
}

// Converter para array indexado
$dados = array_values($todos_meses);

// Encontra o mês com mais e menos reservas (considerando apenas meses com reservas)
$max_reservas = 0;
$min_reservas = PHP_INT_MAX;
$mes_mais = '';
$mes_menos = '';

$meses_com_reservas = array_filter($dados, function($mes) {
    return $mes['total'] > 0;
});

if (count($meses_com_reservas) > 0) {
    foreach ($meses_com_reservas as $mes) {
        if ($mes['total'] > $max_reservas) {
            $max_reservas = $mes['total'];
            $mes_mais = $mes['mes'];
        }
        if ($mes['total'] < $min_reservas) {
            $min_reservas = $mes['total'];
            $mes_menos = $mes['mes'];
        }
    }
} else {
    $mes_mais = 'Nenhum';
    $mes_menos = 'Nenhum';
}

// Criação do HTML para o PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório de Reservas por Mês</title>
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
        .mes-mais {
            background-color: rgba(196, 154, 108, 0.2);
        }
        .mes-menos {
            background-color: rgba(107, 57, 42, 0.1);
        }
        .sem-reservas {
            color: #999;
            font-style: italic;
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
        <h1>Relatório de Reservas por Mês</h1>
        
        <div class="resumo">
            <h2>Resumo Estatístico</h2>
            <p><span class="destaque">Mês com mais reservas:</span> ' . htmlspecialchars($mes_mais) . ' (' . $max_reservas . ' reservas)</p>
            <p><span class="destaque">Mês com menos reservas:</span> ' . htmlspecialchars($mes_menos) . ' (' . $min_reservas . ' reservas)</p>
            <p><span class="destaque">Total de meses com reservas:</span> ' . count($meses_com_reservas) . ' de 12</p>
        </div>
        
        <h2>Detalhes por Mês</h2>
        <table>
            <tr>
                <th>Mês</th>
                <th>Total de Reservas</th>
                <th>Visualização</th>
            </tr>';

// Encontra o valor máximo para normalizar as barras do gráfico (considerando apenas meses com reservas)
$max_value = $max_reservas > 0 ? $max_reservas : 1;

// Adiciona os dados à tabela
foreach ($dados as $mes) {
    $classe = '';
    if ($mes['total'] > 0) {
        if ($mes['mes'] == $mes_mais) {
            $classe = 'mes-mais';
        } elseif ($mes['mes'] == $mes_menos) {
            $classe = 'mes-menos';
        }
    } else {
        $classe = 'sem-reservas';
    }
    
    $percentual = $mes['total'] > 0 ? ($mes['total'] / $max_value) * 100 : 0;
    
    $html .= '
            <tr class="' . $classe . '">
                <td>' . htmlspecialchars($mes['mes']) . '</td>
                <td>' . ($mes['total'] > 0 ? $mes['total'] : 'Nenhuma') . '</td>
                <td>
                    <div class="grafico">';
    
    if ($mes['total'] > 0) {
        $html .= '<div class="barra" style="width: ' . $percentual . '%;"></div>';
    }
    
    $html .= '
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
$dompdf->stream("relatorio_reservas_mensais.pdf", ["Attachment" => false]);
?>
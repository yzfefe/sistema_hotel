<?php
require '../../../vendor/autoload.php';
use Dompdf\Dompdf;

// Conexão com o banco de dados
$pdo = new PDO("mysql:host=localhost;dbname=sistema_hotel", "root", "");

$sql = "
SELECT 
    AVG(DATEDIFF(data_encerrada, data_reserva)) AS media,
    MIN(DATEDIFF(data_encerrada, data_reserva)) AS minima,
    MAX(DATEDIFF(data_encerrada, data_reserva)) AS maxima
FROM reservas_quarto
WHERE data_encerrada IS NOT NULL
";

$dados = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

// Criação do HTML para o PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório de Duração de Estadia</title>
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
            padding: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h1 { 
            color: #c49a6c;
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #c49a6c;
            margin-bottom: 30px;
        }
        .stat-card {
            background-color: #f9f5f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #c49a6c;
        }
        .stat-title {
            color: #6b392a;
            font-size: 1.2em;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .stat-value {
            font-size: 1.8em;
            color: #333;
            margin-left: 15px;
        }
        .stat-unit {
            font-size: 0.8em;
            color: #666;
        }
        .chart-container {
            width: 100%;
            margin: 40px 0;
        }
        .chart-title {
            text-align: center;
            color: #6b392a;
            font-weight: bold;
            margin-bottom: 20px;
            font-size: 1.2em;
        }
        .horizontal-bars {
            width: 100%;
        }
        .bar-container {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .bar-label {
            width: 120px;
            font-weight: bold;
            color: #6b392a;
        }
        .bar-wrapper {
            flex-grow: 1;
            height: 30px;
            background-color: #f0f0f0;
            border-radius: 15px;
            overflow: hidden;
            position: relative;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
        }
        .bar {
            height: 100%;
            border-radius: 15px;
            position: relative;
            transition: width 1s ease;
        }
        .bar.min {
            background-color: #8fb3a5;
            width: ' . ($dados['minima']/$dados['maxima']*100) . '%;
        }
        .bar.avg {
            background-color: #c49a6c;
            width: ' . ($dados['media']/$dados['maxima']*100) . '%;
        }
        .bar.max {
            background-color: #6b392a;
            width: 100%;
        }
        .bar-value {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            font-weight: bold;
            text-shadow: 0 0 2px rgba(0,0,0,0.5);
        }
        .legend {
            display: flex;
            justify-content: center;
            margin-top: 25px;
            gap: 25px;
        }
        .legend-item {
            display: flex;
            align-items: center;
        }
        .legend-color {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .footer {
            text-align: right;
            margin-top: 40px;
            font-size: 0.8em;
            color: #6b392a;
            border-top: 1px solid #e0d3c5;
            padding-top: 10px;
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
        <h1>Relatório de Duração de Estadia</h1>
        
        <div class="stat-card">
            <div class="stat-title">Duração Média</div>
            <div class="stat-value">' . round($dados['media'], 1) . ' <span class="stat-unit">dias</span></div>
        </div>
        
        <div class="stat-card">
            <div class="stat-title">Estadia Mais Curta</div>
            <div class="stat-value">' . $dados['minima'] . ' <span class="stat-unit">dia(s)</span></div>
        </div>
        
        <div class="stat-card">
            <div class="stat-title">Estadia Mais Longa</div>
            <div class="stat-value">' . $dados['maxima'] . ' <span class="stat-unit">dia(s)</span></div>
        </div>
        
        <div class="chart-container">
            <div class="chart-title">Comparação de Duração de Estadias</div>
            <div class="horizontal-bars">
                <div class="bar-container">
                    <div class="bar-label">Mínima</div>
                    <div class="bar-wrapper">
                        <div class="bar min">
                            <div class="bar-value">' . $dados['minima'] . ' dias</div>
                        </div>
                    </div>
                </div>
                <div class="bar-container">
                    <div class="bar-label">Média</div>
                    <div class="bar-wrapper">
                        <div class="bar avg">
                            <div class="bar-value">' . round($dados['media'], 1) . ' dias</div>
                        </div>
                    </div>
                </div>
                <div class="bar-container">
                    <div class="bar-label">Máxima</div>
                    <div class="bar-wrapper">
                        <div class="bar max">
                            <div class="bar-value">' . $dados['maxima'] . ' dias</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="legend">
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #8fb3a5;"></div>
                    <span>Mínima</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #c49a6c;"></div>
                    <span>Média</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #6b392a;"></div>
                    <span>Máxima</span>
                </div>
            </div>
        </div>
        
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
$dompdf->stream("relatorio_duracao_estadia.pdf", ["Attachment" => false]);
?>
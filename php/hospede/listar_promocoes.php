<?php
require_once '../check_session.php';

// Verificação específica para recepcionista (exemplo)
if ($_SESSION['role'] != 'hospede') {
    header("Location: ../../html/login.html");
    exit();
}
include "../conex.php";

// Verificar se a conexão foi estabelecida corretamente
if (!$conn) {
    die("Erro na conexão com o banco de dados");
}

// Função para formatar horário
function formatarHorario($time) {
    return date("H:i", strtotime($time));
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <title>Promoções Disponíveis</title>
    <style>
        :root {
            --color-primary: #c49a6c;
            --color-secondary: #6b392a;
            --color-light: #f5e7d8;
            --color-dark: #3a2618;
        }
        
        body {
            background: linear-gradient(180deg, rgba(107, 57, 42, 0.946) 6%, rgba(133,78,57,1) 18%, rgb(203, 164, 122) 60%, rgba(217, 192, 164, 0.339) 90%, rgba(255,255,255,1) 110%);
            font-family: 'Playfair Display', serif;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        /* Botão voltar estilizado */
        .btn-primary {
            background-color: var(--color-primary);
            border: none;
            color: white;
            padding: 10px 18px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background-color 0.3s ease;
            margin-bottom: 25px;
            cursor: pointer;
            font-family: 'Playfair Display', serif;
            font-size: 1rem;
        }

        .btn-primary:hover {
            background-color: var(--color-secondary);
            color: white;
        }
        
        .logo {
            width: 20rem;
            display: block;
            margin: 0 auto 30px;
            filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.3));
        }
        
        h1 {
            color: var(--color-secondary);
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--color-primary);
            font-weight: 700;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        
        h2 {
            color: var(--color-secondary);
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        
        h2:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 60px;
            height: 3px;
            background-color: var(--color-primary);
        }
        
        .promo-container {
            margin-bottom: 40px;
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .promo-item {
            background-color: var(--color-light);
            border-left: 4px solid var(--color-primary);
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .promo-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .promo-item h3 {
            color: var(--color-secondary);
            margin-top: 0;
            margin-bottom: 15px;
        }
        
        .disponivel {
            color: #28a745;
            font-weight: 600;
        }
        
        .indisponivel {
            color: #dc3545;
        }
        
        .periodo-promocao {
            color: var(--color-primary);
            font-weight: 600;
            margin: 10px 0;
        }
        
        .badge-desconto {
            background-color: var(--color-primary);
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            margin-left: 10px;
        }
        
        .preco-normal {
            text-decoration: line-through;
            color: #6c757d;
        }
        
        .preco-promocional {
            color: var(--color-secondary);
            font-weight: 600;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Botão Voltar -->
        <a href="../../html/hospede/tela_hospede.php" class="btn-primary">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
        <img src="../../img/logo_hoteel.png" alt="Hotel Logo" class="logo">
        <h1>Promoções Exclusivas</h1>

        <div class="promo-container">
            <h2><i class="bi bi-star-fill"></i> Promoções de Serviços</h2>
            <?php
            // Buscar promoções de serviços
            $query = "SELECT * FROM promocoes_servicos WHERE disponivel = true";
            $result = $conn->query($query);
            
            if ($result && $result->num_rows > 0) {
                while ($promocao = $result->fetch_assoc()) {
                    echo '<div class="promo-item">';
                    echo '<h3>' . htmlspecialchars($promocao['nome']) . '</h3>';
                    echo '<p><strong><i class="bi bi-clock"></i> Horário:</strong> ' . formatarHorario($promocao['horario_comeca']) . ' às ' . formatarHorario($promocao['horario_termina']) . '</p>';
                    echo '<p><strong><i class="bi bi-tag"></i> Preço promocional:</strong> <span class="preco-promocional">R$ ' . number_format($promocao['preco_promocional'], 2, ',', '.') . '</span></p>';
                    echo '<p class="disponivel"><i class="bi bi-check-circle"></i> Disponível</p>';
                    echo '</div>';
                }
                $result->free();
            } else {
                echo '<div class="alert alert-info">Não há promoções de serviços disponíveis no momento.</div>';
            }
            ?>
        </div>

        <div class="promo-container">
            <h2><i class="bi bi-house-door"></i> Promoções de Quartos</h2>
            <?php
            // Buscar promoções de quartos com JOIN na tabela quartos
            $query = "SELECT pq.*, q.nome, q.tipo, q.andar, q.num_quarto, q.preco_diaria 
                      FROM promocoes_quartos pq
                      JOIN quartos q ON pq.id_quarto = q.id_quarto
                      WHERE pq.disponivel = true";
            $result = $conn->query($query);
            
            if ($result && $result->num_rows > 0) {
                while ($promocao = $result->fetch_assoc()) {
                    $desconto = $promocao['preco_diaria'] - $promocao['preco_promocional'];
                    $percentual = round(($desconto / $promocao['preco_diaria']) * 100);
                    
                    echo '<div class="promo-item">';
                    echo '<h3>' . htmlspecialchars($promocao['nome']) . ' - ' . htmlspecialchars($promocao['tipo']) . ' <span class="badge-desconto">-' . $percentual . '%</span></h3>';
                    echo '<p><strong><i class="bi bi-building"></i> Localização:</strong> Andar ' . htmlspecialchars($promocao['andar']) . ' | Quarto ' . htmlspecialchars($promocao['num_quarto']) . '</p>';
                    echo '<p><strong><i class="bi bi-cash"></i> Preço normal:</strong> <span class="preco-normal">R$ ' . number_format($promocao['preco_diaria'], 2, ',', '.') . '</span></p>';
                    echo '<p><strong><i class="bi bi-arrow-down-circle"></i> Preço promocional:</strong> <span class="preco-promocional">R$ ' . number_format($promocao['preco_promocional'], 2, ',', '.') . '</span></p>';
                    echo '<p class="periodo-promocao"><strong><i class="bi bi-calendar-range"></i> Período:</strong> ' . date('d/m/Y', strtotime($promocao['data_inicio'])) . ' a ' . date('d/m/Y', strtotime($promocao['data_fim'])) . '</p>';
                    echo '<p class="disponivel"><i class="bi bi-check-circle"></i> Disponível</p>';
                    echo '</div>';
                }
                $result->free();
            } else {
                echo '<div class="alert alert-info">Não há promoções de quartos disponíveis no momento.</div>';
            }
            
            // Fechar conexão
            $conn->close();
            ?>
        </div>
    </div>
</body>
</html>

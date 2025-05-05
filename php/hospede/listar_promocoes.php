<?php
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
    <title>Promoções Disponíveis</title>
    <img src="../../img/logo_hoteel.png" alt="">
    <style>
        img{
            width: 20rem;
            display: block;
            margin: 0 auto;
        }
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { color: #2c3e50; }
        .promo-container { margin-bottom: 30px; }
        .promo-item { 
            background-color: #f9f9f9; 
            border: 1px solid #ddd; 
            padding: 15px; 
            margin-bottom: 10px; 
            border-radius: 5px;
        }
        .disponivel { color: green; }
        .indisponivel { color: red; }
    </style>
</head>
<body>
    <h1>Promoções Disponíveis</h1>

    <div class="promo-container">
        <h2>Promoções de Serviços</h2>
        <?php
        // Buscar promoções de serviços
        $query = "SELECT * FROM promocoes_servicos WHERE disponivel = true";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            while ($promocao = $result->fetch_assoc()) {
                echo '<div class="promo-item">';
                echo '<h3>' . htmlspecialchars($promocao['nome']) . '</h3>';
                echo '<p><strong>Horário:</strong> ' . formatarHorario($promocao['horario_comeca']) . ' às ' . formatarHorario($promocao['horario_termina']) . '</p>';
                echo '<p><strong>Preço promocional:</strong> R$ ' . number_format($promocao['preco_promocional'], 2, ',', '.') . '</p>';
                echo '<p class="disponivel">Disponível</p>';
                echo '</div>';
            }
            $result->free();
        } else {
            echo '<p>Não há promoções de serviços disponíveis no momento.</p>';
        }
        ?>
    </div>

    <div class="promo-container">
        <h2>Promoções de Quartos</h2>
        <?php
        // Buscar promoções de quartos
        $query = "SELECT * FROM promocoes_quartos WHERE disponivel = true";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            while ($quarto = $result->fetch_assoc()) {
                echo '<div class="promo-item">';
                echo '<h3>' . htmlspecialchars($quarto['nome']) . ' - ' . htmlspecialchars($quarto['tipo']) . '</h3>';
                echo '<p><strong>Andar:</strong> ' . htmlspecialchars($quarto['andar']) . '</p>';
                echo '<p><strong>Preço promocional (diária):</strong> R$ ' . number_format($quarto['preco_diaria_promo'], 2, ',', '.') . '</p>';
                echo '<p class="disponivel">Disponível</p>';
                echo '</div>';
            }
            $result->free();
        } else {
            echo '<p>Não há promoções de quartos disponíveis no momento.</p>';
        }
        
        // Fechar conexão
        $conn->close();
        ?>
    </div>
</body>
</html>

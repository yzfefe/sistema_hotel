<?php
include "../conex.php";

// Verificar se a conexão foi estabelecida corretamente
if (!$conn) {
    die("Erro na conexão com o banco de dados");
}

// Processar solicitação de reserva
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['solicitar'])) {
    $quarto_id = $_POST['quarto_id'];
    $nome_cliente = $_POST['nome_cliente'];
    $email_cliente = $_POST['email_cliente'];
    $data_entrada = $_POST['data_entrada'];
    $data_saida = $_POST['data_saida'];
    
    // Validar datas
    if (strtotime($data_entrada) >= strtotime($data_saida)) {
        $mensagem = "Data de saída deve ser posterior à data de entrada!";
    } else {
        // Verificar disponibilidade do quarto
        $check_query = "SELECT disponivel FROM quartos WHERE id_quarto = ? AND disponivel = true";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $quarto_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Inserir reserva (você precisará criar esta tabela)
            $insert_query = "INSERT INTO reservas 
                           (quarto_id, nome_cliente, email_cliente, data_entrada, data_saida, data_reserva, status) 
                           VALUES (?, ?, ?, ?, ?, NOW(), 'pendente')";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("issss", $quarto_id, $nome_cliente, $email_cliente, $data_entrada, $data_saida);
            
            if ($stmt->execute()) {
                $mensagem = "Reserva solicitada com sucesso! Entraremos em contato para confirmação.";
            } else {
                $mensagem = "Erro ao processar sua solicitação. Por favor, tente novamente.";
            }
        } else {
            $mensagem = "Quarto não disponível para reserva no momento.";
        }
    }
}

// Função para aplicar desconto promocional
function aplicarDesconto($preco, $desconto) {
    return $preco * (1 - ($desconto / 100));
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Promoções de Quartos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px; }
        .promo-container { margin-bottom: 30px; }
        .promo-item { 
            background-color: #f9f9f9; 
            border: 1px solid #ddd; 
            padding: 15px; 
            margin-bottom: 15px; 
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .disponivel { color: green; font-weight: bold; }
        .indisponivel { color: red; }
        .btn-reservar { 
            background-color: #3498db; 
            color: white; 
            border: none; 
            padding: 8px 15px; 
            border-radius: 4px; 
            cursor: pointer;
            margin-top: 10px;
        }
        .btn-reservar:hover { background-color: #2980b9; }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 5px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover { color: black; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-submit {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-submit:hover { background-color: #27ae60; }
        .mensagem {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .sucesso { background-color: #d4edda; color: #155724; }
        .erro { background-color: #f8d7da; color: #721c24; }
        .preco-promo {
            color: #e74c3c;
            font-size: 1.2em;
            font-weight: bold;
        }
        .preco-normal {
            text-decoration: line-through;
            color: #7f8c8d;
        }
        .badge-promo {
            background-color: #e74c3c;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8em;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <h1>Promoções Especiais de Quartos</h1>
    
    <?php if (isset($mensagem)): ?>
        <div class="mensagem <?php echo strpos($mensagem, 'sucesso') !== false ? 'sucesso' : 'erro'; ?>">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <div class="promo-container">
        <h2>Quartos em Promoção <span class="badge-promo">-20%</span></h2>
        <?php
        // Definir desconto promocional (20%)
        $desconto_promocional = 20;
        
        // Buscar quartos disponíveis
        $query = "SELECT * FROM quartos WHERE disponivel = true";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            while ($quarto = $result->fetch_assoc()) {
                $preco_promocional = aplicarDesconto($quarto['preco_diaria'], $desconto_promocional);
                $economia = $quarto['preco_diaria'] - $preco_promocional;
                
                echo '<div class="promo-item">';
                echo '<h3>' . htmlspecialchars($quarto['nome']) . ' - ' . htmlspecialchars($quarto['tipo']) . '</h3>';
                echo '<p><strong>Número do Quarto:</strong> ' . htmlspecialchars($quarto['num_quarto']) . '</p>';
                echo '<p><strong>Andar:</strong> ' . htmlspecialchars($quarto['andar']) . '</p>';
                echo '<p><strong>Preço normal:</strong> <span class="preco-normal">R$ ' . number_format($quarto['preco_diaria'], 2, ',', '.') . '</span></p>';
                echo '<p><strong>Preço promocional:</strong> <span class="preco-promo">R$ ' . number_format($preco_promocional, 2, ',', '.') . '</span> <small>(-' . $desconto_promocional . '%)</small></p>';
                echo '<p><strong>Você economiza:</strong> R$ ' . number_format($economia, 2, ',', '.') . ' por dia!</p>';
                echo '<p class="disponivel">Disponível</p>';
                echo '<button class="btn-reservar" onclick="abrirModal(' . $quarto['id_quarto'] . ')">Solicitar Reserva</button>';
                echo '</div>';
            }
            $result->free();
        } else {
            echo '<p>Não há quartos disponíveis no momento.</p>';
        }
        ?>
    </div>

    <!-- Modal para reserva -->
    <div id="modalReserva" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fecharModal()">&times;</span>
            <h2>Solicitar Reserva</h2>
            <form id="formReserva" method="POST">
                <input type="hidden" id="quarto_id" name="quarto_id">
                
                <div class="form-group">
                    <label for="nome_cliente">Nome Completo:</label>
                    <input type="text" id="nome_cliente" name="nome_cliente" required>
                </div>
                
                <div class="form-group">
                    <label for="email_cliente">E-mail:</label>
                    <input type="email" id="email_cliente" name="email_cliente" required>
                </div>
                
                <div class="form-group">
                    <label for="data_entrada">Data de Entrada:</label>
                    <input type="date" id="data_entrada" name="data_entrada" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="data_saida">Data de Saída:</label>
                    <input type="date" id="data_saida" name="data_saida" required>
                </div>
                
                <div class="form-group">
                    <input type="submit" name="solicitar" class="form-submit" value="Solicitar Reserva">
                </div>
            </form>
        </div>
    </div>

    <script>
        // Funções para abrir e fechar o modal
        function abrirModal(quartoId) {
            document.getElementById('quarto_id').value = quartoId;
            document.getElementById('modalReserva').style.display = 'block';
            
            // Definir data mínima para saída (um dia após a entrada)
            document.getElementById('data_entrada').addEventListener('change', function() {
                const entrada = new Date(this.value);
                entrada.setDate(entrada.getDate() + 1);
                const minSaida = entrada.toISOString().split('T')[0];
                document.getElementById('data_saida').min = minSaida;
                
                // Se a data de saída atual for anterior à nova data mínima, atualizar
                const saida = new Date(document.getElementById('data_saida').value);
                if (saida < entrada) {
                    document.getElementById('data_saida').value = minSaida;
                }
            });
        }
        
        function fecharModal() {
            document.getElementById('modalReserva').style.display = 'none';
        }
        
        // Fechar modal ao clicar fora dele
        window.onclick = function(event) {
            const modal = document.getElementById('modalReserva');
            if (event.target == modal) {
                fecharModal();
            }
        }
    </script>
</body>
</html>
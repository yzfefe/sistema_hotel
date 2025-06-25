<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caminho das Pedras - Rustic Hotel</title>
    <link rel="stylesheet" href="../../css/gerente/cadastrar_servicos_123.css">
    <link rel="shortcut icon" type="imagex/png" href="../../img/aba.png">
</head>
<body>
    <button class="btn-voltar-topo" onclick="red()">
        <i class="bi bi-arrow-left"></i> Voltar
    </button>
    <header class="header">
        <img src="../../img/logo_hoteel.png" alt="Caminho das Pedras - Rustic Hotel">
    </header>
    
    <!-- Área para mensagens -->
    <div id="message-container" style="text-align: center; margin: 20px 0;">
        <?php 
            if(isset($msg) && !empty($msg)) {
                $class = strpos($msg, 'sucesso') !== false ? 'success-message' : 'error-message';
                echo '<div class="'.$class.'">'.$msg.'</div>';
            }
        ?>
    </div>
    
    <form action="../../php/gerente/cadastrar_servicos.php" method="post">
        <div class="form-container">
            <label for="nome">Serviço:</label>
            <input type="nome" id="nome" name="nome" placeholder="Informe o serviço" required>
            
            <label for="preço">Preço:</label>
            <input type="number" id="preco" name="preco" step="0.01" placeholder="Informe o preço" required> 
            
            <label for="horario">Horário inicial:</label>
            <input type="time" id="horario" name="horario" required>

            <label for="horario_termina">Horário final:</label>
            <input type="time" id="horario_termina" name="horario_termina" required>
        </div>

        <button class="btn-enviar" type="submit">Enviar</button>
    </form>
</body>

<script>
    function red(){
        window.location.href = "http://localhost/sistema_hotel-main/html/gerente/tela_gerente.php"
    }
</script>
</html>
<?php
include "../conex.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Se a busca foi acionada
    if (isset($_POST['search'])) {
        $search = $_POST['search'];
        $sql = "SELECT * FROM servicos WHERE nome = ? OR id_serv LIKE ?";
        $stmt = $conn->prepare($sql);

        // Adicionando os parâmetros ao prepared statement
        $search_like = "%$search%";
        $stmt->bind_param("ss", $search, $search_like);
        
        // Executando a consulta
        if ($stmt->execute()) {
            $result = $stmt->get_result();
        } else {
            echo "Erro ao executar a busca: " . $stmt->error;
        }
    }
    
    // Se a atualização foi acionada
    if (isset($_POST['update'])) {
        $nome = $_POST['nome'];
        $horario_comeca = $_POST['horario_comeca'];
        $horario_termina = $_POST['horario_termina'];
        $preco_promocional = (float)$_POST['preco_promocional'];

        $sql1 = $conn->prepare("INSERT INTO promocoes_servicos (nome, horario_comeca, horario_termina, preco_promocional, disponivel) VALUES (?, ?, ?, ?, TRUE)");
        $sql1->bind_param("sssd", $nome, $horario_comeca, $horario_termina, $preco_promocional);

        if ($sql1->execute()) {
            echo "Promoção inserida com sucesso";
        } else {
            echo "Erro ao inserir Promoção:" . $sql1->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caminho das Pedras - Rustic Hotel</title>
    <link rel="stylesheet" href="../../css/gerente/servico_promocao.css">
</head>
<body>
    <header class="header">
        <img src="../../img/logo_hoteel.png" alt="Caminho das Pedras - Rustic Hotel">
    </header>
    
    <div class="form-container">
        <h2>Buscar Serviço</h2>
        <form method="post">
            <input type="text" name="search" placeholder="Digite o ID ou Nome do serviço">
            <button type="submit" class="btn-enviar">Buscar</button>
        </form>

        <?php if (isset($result) && $result->num_rows > 0): ?>
            <!-- Exibindo resultados da busca -->
            <?php while ($row = $result->fetch_assoc()): ?>
                <form method="post" class="result-form">
                    <input type="hidden" name="id_item" value="<?php echo $row['id_serv']; ?>">

                    <label for="nome">Nome: </label>
                    <input type="text" name="nome" value="<?php echo $row['nome']; ?>"> <br>

                    <label for="horario_comeca">Horário Inicial: </label>
                    <input type="time" name="horario_comeca" value="<?php echo $row['horario_comeca']; ?>"> <br>

                    <label for="horario_termina">Horário Termina: </label>
                    <input type="time" name="horario_termina" value="<?php echo $row['horario_termina']; ?>"> <br>

                    <label for="preco">Preço Promocional: </label>
                    <input type="number" step="0.01" name="preco_promocional" value="<?php echo $row['preco']; ?>"> <br>
                    
                    <button type="submit" name="update" class="btn-enviar">Criar Promoção</button>
                </form>
            <?php endwhile; ?>
        <?php elseif (isset($result)): ?>
            <p class="no-results">Nenhum serviço encontrado.</p>
        <?php endif; ?>
    </div>

    <button class="btn-voltar" onclick="red()">Voltar</button>
    
    <script>
        function red(){
            window.location.href = "http://localhost/sistema_hotel-main/html/gerente/tela_gerente.html"
        }
    </script>
</body>
</html>

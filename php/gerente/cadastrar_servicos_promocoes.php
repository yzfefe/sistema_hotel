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
        $preco_promocional = (float)$_POST['preco_promocional']; // Garantir que seja float

        $sql1 = $conn->prepare("INSERT INTO promocoes_servicos (nome, horario_comeca, horario_termina, preco_promocional, disponivel) VALUES (?, ?, ?, ?, TRUE)");
        $sql1->bind_param("ssss", $nome, $horario_comeca, $horario_termina, $preco_promocional);

        if ($sql1->execute()) {
            echo "Registro inserido com sucesso!";
        } else {
            echo "Erro ao inserir registro: " . $sql1->error;
        }
        
        
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gerenciar Promocoes e Servicos</title>
    <link rel="stylesheet" href="../../css/gerente/servico_promocao.css">
</head>
<body>
    <h2>Buscar Serviço</h2>
    <form method="post">
        <input type="text" name="search" placeholder="Digite o ID ou Nome da promoção">
        <button type="submit">Buscar</button>
    </form>

    <?php if (isset($result) && $result->num_rows > 0): ?>
        <!-- Exibindo resultados da busca -->
        <?php while ($row = $result->fetch_assoc()): ?>
            <form method="post">
                
                <input type="text" name="id_item" value="<?php echo $row['id_serv']; ?>"><br>

                <label for="nome">Nome: </label>
                <input type="text" name="nome" value="<?php echo $row['nome']; ?>"> <br>

                <label for="horario_comeca">Horário Inicial: </label>
                <input type="text" name="horario_comeca" value="<?php echo $row['horario_comeca']; ?>"> <br>

                <label for="horario_termina">Horário Termina: </label>
                <input type="text" name="horario_termina" value="<?php echo $row['horario_termina']; ?>"> <br>

                <label for="preco">Preço Promocional: </label>
                <input type="number" name="preco_promocional" value="<?php echo $row['preco']; ?>"> <br>
                <button type="submit" name="update">Atualizar</button>
            </form>
            
        <?php endwhile; ?>
    <?php elseif (isset($result)): ?>
        <p>Nenhuma promoção encontrada.</p>
    <?php endif; ?>

    <button class="btn-voltar" onclick="red()">Voltar</button>
    <script>
        function red(){
            window.location.href = "http://localhost/sistema_hotel-main/html/gerente/tela_gerente.html"
        }
    </script>
</body>
</html>

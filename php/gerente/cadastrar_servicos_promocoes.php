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
        $id_item = $_POST['id_item']; // ID da promoção
        $nome = $_POST['nome'];
        $tipo = $_POST['tipo'];
        $preco_promocional = (float)$_POST['preco_promocional']; // Garantir que seja float

        $sql = "SELECT id_serv FROM servicos WHERE id_serv = ? ";
        
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gerenciar Promocoes e Servicos</title>
</head>
<body>
    <h2>Buscar Promoção</h2>
    <form method="post">
        <input type="text" name="search" placeholder="Digite o ID ou Nome da promoção">
        <button type="submit">Buscar</button>
    </form>

    <?php if (isset($result) && $result->num_rows > 0): ?>
        <!-- Exibindo resultados da busca -->
        <?php while ($row = $result->fetch_assoc()): ?>
            <form method="post">
                <input type="text" name="id_item" value="<?php echo $row['id_item']; ?>" readonly>
                <input type="text" name="nome" value="<?php echo $row['nome']; ?>">
                <input type="text" name="tipo" value="<?php echo $row['tipo']; ?>">
                <input type="text" name="preco_promocional" value="<?php echo $row['preco_promocional']; ?>">
                <button type="submit" name="update">Atualizar</button>
            </form>
        <?php endwhile; ?>
    <?php elseif (isset($result)): ?>
        <p>Nenhuma promoção encontrada.</p>
    <?php endif; ?>
</body>
</html>

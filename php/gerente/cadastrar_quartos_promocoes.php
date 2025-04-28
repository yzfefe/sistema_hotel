<?php
include "../conex.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Se a busca foi acionada
    if (isset($_POST['search'])) {
        $search = $_POST['search'];
        $sql = "SELECT * FROM quartos WHERE nome = ? OR id_quarto LIKE ?";
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
    if (isset($_POST['update'])) { // ID da promoção
        $nome = $_POST['nome'];
        $tipo = $_POST['tipo'];
        $preco_diaria_promo = (float)$_POST['preco_diaria_promo']; // Garantir que seja float

        $sql1 = $conn->prepare("INSERT INTO promocoes_quartos (tipo, nome, preco_diaria_promo) VALUES (?, ?, ?)");
        $sql1->bind_param("ssi", $tipo, $nome, $preco_diaria_promo);

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
    <title>Gerenciar Promoções de quartos</title>
</head>
<body>

    <?php if (isset($result) && $result->num_rows > 0): ?>
        <!-- Exibindo resultados da busca -->
        <?php while ($row = $result->fetch_assoc()): ?>
            <form method="post">
                <input type="text" name="nome" value="<?php echo $row['nome']; ?>"> <br>
                <input type="text" name="tipo" value="<?php echo $row['tipo']; ?>"><br>
                <input type="number" name="preco_diaria_promo" value="<?php echo $row['preco_diaria']; ?>"> <br>
                <button type="submit" name="update">Atualizar</button>
            </form>
        <?php endwhile; ?>
    <?php elseif (isset($result)): ?>
        <p>Nenhuma promoção encontrada.</p>
    <?php endif; ?>
</body>
</html>

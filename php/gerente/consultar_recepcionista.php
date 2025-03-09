<?php
include "../conex.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['search'])) {
        $search = $_POST['search'];
        $sql = "SELECT * FROM recepcionista WHERE cpf = '$search' OR nome LIKE '%$search%'";
        $result = $conn->query($sql);
    }
    
    if (isset($_POST['update'])) {
        $id_recep= $_POST['id_recep'];
        $nome = $_POST['nome'];
        $cpf = $_POST['cpf'];
        $email = $_POST['email'];
        
        $sql = "UPDATE recepcionista SET nome='$nome', cpf='$cpf', email='$email' WHERE id_recep = $id_recep";
        $conn->query($sql);
        echo "Dados atualizados com sucesso!";
    }
    
    if (isset($_POST['delete'])) {
        $id_recep = $_POST['id_recep'];
        $sql = "DELETE FROM recepcionista WHERE id_recep=$id_recep";
        $conn->query($sql);
        echo "Recepcionista excluído com sucesso!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gerenciar Recepcionistas</title>
</head>
<body>
    <h2>Buscar Recepcionista</h2>
    <form method="post">
        <input type="text" name="search" placeholder="Digite o CPF ou Nome">
        <button type="submit">Buscar</button>
    </form>

    <?php if (isset($result) && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <form method="post">
                <input type="hidden" name="id_recep" value="<?php echo $row['id_recep']; ?>">
                <input type="text" name="nome" value="<?php echo $row['nome']; ?>">
                <input type="text" name="cpf" value="<?php echo $row['cpf']; ?>">
                <input type="email" name="email" value="<?php echo $row['email']; ?>">
                <button type="submit" name="update">Atualizar</button>
                <button type="submit" name="delete">Excluir</button>
            </form>
        <?php endwhile; ?>
    <?php elseif (isset($result)): ?>
        <p>Nenhuma recepcionista encontrada.</p>
    <?php endif; ?>
</body>
</html>
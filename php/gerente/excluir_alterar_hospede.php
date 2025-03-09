<?php
include "../conex.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['search'])) {
        $search = $_POST['search'];
        $sql = "SELECT * FROM hospede WHERE cpf = '$search' OR nome LIKE '%$search%'";
        $result = $conn->query($sql);
    }
    
    if (isset($_POST['update'])) {
        $id_hos = $_POST['id_hos'];
        $nome = $_POST['nome'];
        $cpf = $_POST['cpf'];
        $email = $_POST['email'];
        
        $sql = "UPDATE hospede SET nome='$nome', cpf='$cpf', email='$email' WHERE id_hos = $id_hos";
        $conn->query($sql);
        echo "Dados atualizados com sucesso!";
    }
    
    if (isset($_POST['delete'])) {
        $id_hos = $_POST['id_hos'];
        $sql = "DELETE FROM hospede WHERE id_hos=$id_hos";
        $conn->query($sql);
        echo "Hóspede excluído com sucesso!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gerenciar Hóspedes</title>
</head>
<body>
    <h2>Buscar Hóspede</h2>
    <form method="post">
        <input type="text" name="search" placeholder="Digite o CPF ou Nome">
        <button type="submit">Buscar</button>
    </form>

    <?php if (isset($result) && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <form method="post">
                <input type="hidden" name="id_hos" value="<?php echo $row['id_hos']; ?>">
                <input type="text" name="nome" value="<?php echo $row['nome']; ?>">
                <input type="text" name="cpf" value="<?php echo $row['cpf']; ?>">
                <input type="email" name="email" value="<?php echo $row['email']; ?>">
                <button type="submit" name="update">Atualizar</button>
                <button type="submit" name="delete">Excluir</button>
            </form>
        <?php endwhile; ?>
    <?php elseif (isset($result)): ?>
        <p>Nenhum hóspede encontrado.</p>
    <?php endif; ?>
</body>
</html>
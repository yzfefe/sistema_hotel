<?php

include "conex.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitizar entrada de dados
    $RG = $conn->real_escape_string($_POST['RG']);
    $CPF = $conn->real_escape_string($_POST['CPF']);
    $telefone = $conn->real_escape_string($_POST['telefone']);
    $endereco = $conn->real_escape_string($_POST['endereco']); // Trocar para "endereco"
    $email = $conn->real_escape_string($_POST['email']);
    $nome = $conn->real_escape_string($_POST['nome']);
    $login = $conn->real_escape_string($_POST['login']);
    $password = $_POST['password'];

    // Verificar se o usuário já existe (RG, CPF ou login duplicado)
    $sql_check = "SELECT * FROM hospede WHERE login = '$login' OR RG = '$RG' OR CPF = '$CPF'";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        echo "Usuário já existe!";
    } else {
        // Criptografar a senha
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Inserir novo usuário no banco de dados
        $sql = "INSERT INTO hospede (nome, telefone, endereço, email, RG, CPF, login, senha) 
                VALUES ('$nome', '$telefone', '$endereco', '$email', '$RG', '$CPF', '$login', '$hashed_password')";

        if ($conn->query($sql) === TRUE) {
            echo "Registro bem-sucedido!";
        } else {
            echo "Erro: " . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
</head>
<body>
    <h2>Registrar-se</h2>

    <form action="register_hospede.php" method="POST">
        <label for="login">Login:</label>
        <input type="text" id="login" name="login" required><br><br>

        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required><br><br>

        <label for="email">Email:</label>
        <input type="text" id="email" name="email" required><br><br>

        <label for="CPF">CPF:</label>
        <input type="text" id="CPF" name="CPF" required><br><br>

        <label for="RG">RG:</label>
        <input type="text" id="RG" name="RG" required><br><br>

        <label for="telefone">Telefone:</label>
        <input type="text" id="telefone" name="telefone" required><br><br>

        <label for="endereco">Endereço:</label>
        <input type="text" id="endereco" name="endereco" required><br><br>

        <label for="password">Senha:</label>
        <input type="password" id="password" name="password" required><br><br>

        <input type="submit" value="Registrar">
    </form>
</body>
</html>

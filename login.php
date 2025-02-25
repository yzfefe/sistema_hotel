<?php
session_start();

include "conex.php";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Receber dados do formulário
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prevenir SQL Injection
    $username = $conn->real_escape_string($username);

    // Verificar se o usuário existe no banco de dados
    $sql = "SELECT * FROM hospede WHERE login = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verificar se a senha fornecida corresponde à senha no banco de dados
        if (password_verify($password, $user['senha'])) {
            // Iniciar a sessão e armazenar o nome de usuário
            $_SESSION['login'] = $user['login'];
            echo "Login bem-sucedido! Bem-vindo, " . $_SESSION['username'];
        } else {
            echo "Senha incorreta!";
        }
    } else {
        echo "Usuário não encontrado!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>

    <form action="login.php" method="POST">
        <label for="username">Nome de usuário:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="password">Senha:</label>
        <input type="password" id="password" name="password" required><br><br>

        <input type="submit" value="Entrar">
    </form>
</body>
</html>

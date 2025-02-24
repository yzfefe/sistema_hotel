<?php
// Conexão com o banco de dados
$conn = new mysqli('localhost', 'root', '', 'sistema_site');

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Receber dados do formulário
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prevenir SQL Injection
    $username = $conn->real_escape_string($username);

    // Verificar se o nome de usuário já existe no banco de dados
    $sql_check = "SELECT * FROM usuarios WHERE username = '$username'";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        echo "Usuário já existe!";
    } else {
        // Criptografar a senha com password_hash
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Inserir o novo usuário no banco de dados
        $sql = "INSERT INTO usuarios (username, senha) VALUES ('$username', '$hashed_password')";

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

    <form action="register.php" method="POST">
        <label for="username">Nome de usuário:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="password">Senha:</label>
        <input type="password" id="password" name="password" required><br><br>

        <input type="submit" value="Registrar">

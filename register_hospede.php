<?php

include "conex.php";

date_default_timezone_set('America/Sao_Paulo');
$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitizar entrada de dados
    $RG = $conn->real_escape_string($_POST['RG']);
    $CPF = $conn->real_escape_string($_POST['CPF']);
    $telefone = $conn->real_escape_string($_POST['telefone']);
    $endereco = $conn->real_escape_string($_POST['endereço']); // Trocar para "endereco"
    $email = $conn->real_escape_string($_POST['email']);
    $nome = $conn->real_escape_string($_POST['nome']);
    $dataHoraAtual = date('Y-m-d H:i:s');
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
        $sql = "INSERT INTO hospede (horario, nome, telefone, endereço, email, RG, CPF, login, senha) 
                VALUES ('$dataHoraAtual', '$nome', '$telefone', '$endereco', '$email', '$RG', '$CPF', '$login', '$hashed_password')";

        if ($conn->query($sql) === TRUE) {
            $msg = "Registro bem sucedido";
        } else {
            echo "Erro: " . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caminho das Pedras - Rustic Hotel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background: url('hotel_principal.png') no-repeat center center/cover;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .header {
        background: linear-gradient(to right, #e6aa77, #6f4616);
        width: 100%;
        padding: 5px 100px;
        display: flex;
        align-items: flex-start;
        justify-content: center; /* Centraliza horizontalmente */
        position: relative;
        height: 180px;
        }
        .header img {
            height: 200px; /* Ajuste o tamanho conforme necessário */
            display: block;
            margin-top: -15px;
        }
        .nav {
            margin-right: 200px;
            position: absolute;
            bottom: 5px;
            right: 75px;
        }
        .nav a {
            color: white;
            font-weight: bold;
            text-decoration: none;
            font-size: 18px;
            margin: 0 15px;
        }
        .nav a:hover {
            text-decoration: underline;
        }
        .form-container {
            background-color: #2c3330;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            margin-top: 50px;
        }
        .form-container input {
            display: block;
            width: 250px;
            padding: 10px;
            margin: 10px auto;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        label{
            color: white;
            font-weight: bold;
        }
        .btn-enviar{
            display: flex;
            flex-direction: column;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #636851;
            color: white; /* Cor do texto */
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn-enviar:hover {
          background-color: #99a285; /* Cor ao passar o mouse */
        
        }

        .form-container {
            background-color: #2c3330;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            color: white;
        }

        
    </style>
</head>
<body>
    <header class="header">
        <img src="logo.png" alt="Caminho das Pedras - Rustic Hotel">
        <nav class="nav">
            <a href="#">Sobre</a>
            <a href="#">Reservas</a>
            <a href="#">Login</a>
        </nav>
    </header>
    
    <form action="register_hospede.php" method="POST">
        <div class="form-container">
            <p id="true"></p>
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" required>

            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" required>

            <label for="RG">RG:</label>
            <input type="text" id="RG" name="RG" required>
            
            <label for="CPF">CPF:</label>
            <input type="text" id="CPF" name="CPF" required>
            
            <label for="telefone">Telefone:</label>
            <input type="tel" id="telefone" name="telefone" required>

            <label for="endereço">Endereço:</label>
            <input type="text" id="endereço" name="endereço" required>

            <label for="login">Login:</label>
            <input type="text" id="login" name="login" required>
            
            <label for="password">Senha:</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button class="btn-enviar" value="Registrar">Enviar</button>
    </form>
    <div class="mensagem"><?php echo $msg; ?></div>

</body>
</html>

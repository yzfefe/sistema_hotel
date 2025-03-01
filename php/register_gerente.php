<?php
include "conex.php";
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitizar entrada de dados
    $CPF = $conn->real_escape_string($_POST['CPF']);
    $telefone = $conn->real_escape_string($_POST['telefone']);
    $endereco = $conn->real_escape_string($_POST['endereco']);
    $email = $conn->real_escape_string($_POST['email']);
    $nome = $conn->real_escape_string($_POST['nome']);
    $login = $conn->real_escape_string($_POST['login']);
    $password = $_POST['password'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // Verificar se as senhas coincidem
    if ($password !== $confirmar_senha) {
        $msg = "As senhas não coincidem!";
    } else {
        // Verificar se o usuário já existe (CPF ou login duplicado) em qualquer tabela
        $tables = ['gerente', 'recepcionista', 'hospede'];
        $user_exists = false;

        foreach ($tables as $table) {
            $sql_check = "SELECT login FROM $table WHERE login = ? OR CPF = ?";
            $stmt = $conn->prepare($sql_check);
            $stmt->bind_param("ss", $login, $CPF);
            $stmt->execute();
            $result_check = $stmt->get_result();

            if ($result_check->num_rows > 0) {
                $user_exists = true;
                break;
            }
        }

        if ($user_exists) {
            $msg = "Usuário já existe!";
        } else {
            // Criptografar a senha
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Escolha a tabela correta para inserir (Altere conforme necessário)
            $table = "gerente"; // <- MODIFIQUE PARA O TIPO DE USUÁRIO CORRETO

            // Inserir novo usuário no banco de dados
            $sql = "INSERT INTO $table (nome, telefone, endereco, email, CPF, login, senha) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", $nome, $telefone, $endereco, $email, $CPF, $login, $hashed_password);

            if ($stmt->execute()) {
                $msg = "Registro bem sucedido!";
            } else {
                $msg = "Erro ao registrar: " . $conn->error;
            }
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
            background: url('../img/fotooo.png') no-repeat center center/cover;
            height: 1000px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .header {
        border-radius: 10px;
        background: linear-gradient(to right, #465d53, #d3d3d3);
        width: 80%;
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
            margin-right: 0px;
            position: absolute;
            bottom: 5px;
            right: 40px;
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
            padding: 50px;
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

        .toggle-password {
            position: absolute;
            right: -30px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            color: #555;
        }
        .password-container {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            
        }
        .password-container input {
            width: 250px;
            padding-right: 40px; /* Espaço para o botão */
        }
        .mensagem {
            width: 80%;
            max-width: 400px;
            padding: 15px;
            margin: 20px auto;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }


        .mensagem.sucesso {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .mensagem.erro {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        
    </style>
</head>
<body>
    <header class="header">
        <img src="../img/logo_hoteel.png" alt="Caminho das Pedras - Rustic Hotel">
        <nav class="nav">
            <a href="#">SAIR</a>
        </nav>
    </header>
    <div class="form-container">

        <form action="register_gerente.php" method="POST">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" placeholder="Digite seu nome" required> 

            <label for="Endereço">Endereço:</label>
            <input type="text" id="endereco" name="endereco" placeholder="Digite seu Endereço" required>

            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" placeholder="Digite seu E-mail" required>
        
            <label for="CPF">CPF:</label>
            <input type="text" id="CPF" name="CPF" placeholder="Digite seu CPF" required>
        
            <label for="telefone">Telefone:</label>
            <input type="tel" id="telefone" name="telefone" placeholder="Digite seu Telefone" required>

            <label for="login">Login:</label>
            <input type="text" id="login" name="login" placeholder="Digite seu Login" required>
        
            <label for="senha">Senha:</label>
            <div class="password-container">
                <input type="password" id="password" name="password" placeholder="Informe a Senha" required>
                <button type="button" class="toggle-password" onclick="ver_senha('password')">👁️</button>
            </div>

            <label for="senha">Confirme a senha:</label>
            <div class="password-container">
                <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Confirme a Senha" required>
                <button type="button" class="toggle-password" onclick="ver_senha2('password')">👁️</button>
            </div>

            <button class="btn-enviar" type="submit">Enviar</button>

        </form>
        


        
    </div>


    <div class="mensagem <?php echo strpos($msg, 'sucesso') !== false ? 'sucesso' : 'erro'; ?>">
    <?php echo $msg; ?>
    </div>



    <script>
        function ver_senha(){
        var passwordField = document.getElementById("password");
            if (passwordField.type === "password") {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        }

        function ver_senha2(){
        var passwordField = document.getElementById("confirmar_senha");
            if (passwordField.type === "password") {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        }
    </script>
    
</body>
</html>
<?php
include "../conex.php";
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitizar entrada de dados
    $cpf = $conn->real_escape_string($_POST['cpf']);
    $telefone = $conn->real_escape_string($_POST['telefone']);
    $horario_de_trabalho = $conn->real_escape_string($_POST['horario_de_trabalho']);
    $rg = $conn->real_escape_string($_POST['rg']);
    $endereco = $conn->real_escape_string($_POST['endereco']);
    $email = $conn->real_escape_string($_POST['email']);
    $nome = $conn->real_escape_string($_POST['nome']);
    $login = $conn->real_escape_string($_POST['login']);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // Verificar se as senhas coincidem
    if ($senha !== $confirmar_senha) {
        $msg = "As senhas não coincidem!";
    } else {
        // Verificar se o CPF ou RG já está cadastrado
        $tables = ['recepcionista', 'hospede'];
        $user_exists = false;

        foreach ($tables as $table) {
            $sql_check = "SELECT login, cpf, rg FROM $table WHERE cpf = ? OR rg = ?";
            $stmt = $conn->prepare($sql_check);
            $stmt->bind_param("ss", $cpf, $rg);
            $stmt->execute();
            $result_check = $stmt->get_result();

            if ($result_check->num_rows > 0) {
                $user_exists = true;
                $stmt->close();
                break;
            }
            $stmt->close();
        }
        $tables2 = ['administrador'];
        $user_exists2 = false;

        foreach ($tables2 as $table2) {
            $sql_check = "SELECT login FROM $table2 WHERE login = ?";
            $stmt = $conn->prepare($sql_check);
            $stmt->bind_param("s", $login);
            $stmt->execute();
            $result_check = $stmt->get_result();

            if ($result_check->num_rows > 0) {
                $user_exists2 = true;
                $stmt->close();
                break;
            }
            $stmt->close();
        }

        // Caso CPF ou RG já exista, não inserimos no banco
        if ($user_exists || $user_exists2) {
            $msg2 = "Usuário já existe";
        } else {
            // Criptografar a senha
            $hashed_password = password_hash($senha, PASSWORD_DEFAULT);

            // Inserir novo usuário no banco de dados
            $table = "recepcionista"; // <- MODIFIQUE PARA O TIPO DE USUÁRIO CORRETO

            $sql = "INSERT INTO $table (nome, telefone, endereco, email, cpf, rg, horario, login, senha) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssss", $nome, $telefone, $endereco, $email, $cpf, $rg, $horario_de_trabalho, $login, $hashed_password);

            if ($stmt->execute()) {
                $msg = "Registro bem-sucedido!";
            } else {
                $msg = "Erro ao registrar: " . $conn->error;
            }
            $stmt->close();
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
            
            height: 1000px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .header {
        background: linear-gradient(180deg, rgba(107, 57, 42, 0.946) 6%, rgba(133,78,57,1) 18%, rgb(203, 164, 122) 60%, rgba(217, 192, 164, 0.339) 90%, rgba(255,255,255,1) 110%);
        width: 100%;
        padding: 5px 100px;
        display: flex;
        justify-content: center; /* Centraliza horizontalmente */
        position: relative;
        height: 180px;
        }
        .header img {
            height: 200px; /* Ajuste o tamanho conforme necessário */
            display: block;
            margin-top: -15px;
        }
        
        .form-container {
            border: 3.5px solid black;
            background-color: white;
            display: flex;
            gap: 1rem;
            padding: 35px;
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
        .colun{
            display: flex;
            flex-direction: column;
            text-align: start;
    
        }
        .colun label{
            margin-left: .3rem;       
        }
        label{
            color: black;
        }
        .btn-enviar{
            display: flex;
            flex-direction: column;
            margin: 20px auto;
            padding: 10px 50px;
            background-color: #c49a6c;
            color: white; /* Cor do texto */
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.4s ease;
        }

        .btn-enviar:hover {
          background-color: #7d3a26; /* Cor ao passar o mouse */
        
        }
        .toggle-password {
            position: absolute;
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
        }
        .password-container input {
            width: 240px; /* Deixe o input com um tamanho fixo para que o botão não sobreponha */
        }
        .password-container button {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header class="header">
        <img src="../../img/logo_hoteel.png" alt="Caminho das Pedras - Rustic Hotel">
    </header>
    <div class="form-container">
        <div class="colun">
            <form action="register_recep.php" method="post">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" placeholder="Digite seu nome">

                <label for="rg">RG:</label>
                <input type="text" id="rg" name="rg" placeholder="Digite seu RG" maxlength="12" onkeypress="RG()">

                <label for="Endereço">Endereço:</label>
                <input type="text" id="endereco" name="endereco" placeholder="Digite seu Endereço">

                <label for="login">Login:</label>
                <input type="text" id="login" name="login" placeholder="Digite seu Login">

                <label for="rg">Horário de trabalho:</label>
                <input type="time" id="horario_de_trabalho" name="horario_de_trabalho">
            </div>

            <div class="colun">
                <label for="email">E-mail:</label>
                <input type="text" id="email" name="email" placeholder="Digite seu E-mail" onkeypress="email()">

                <label for="cpf">CPF:</label>
                <input type="text" id="cpf" name="cpf" placeholder="Digite seu CPF" onkeypress="cpf()" maxlength="14">

                <label for="telefone">Telefone:</label>
                <input type="text" id="telefone" name="telefone" placeholder="Digite seu Telefone" onkeypress="telefone()" maxlength="14">

                <label for="senha">Senha:</label>
                <div class="password-container">
                    <input type="password" id="senha" name="senha" placeholder="Confirme a Senha">
                    <button type="button" class="toggle-password" onclick="ver_senha('senha')">👁️</button>
                </div>

                <label for="senha">Confirme a senha:</label>
                <div class="password-container">
                    <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Confirme a Senha">
                    <button type="button" class="toggle-password" onclick="ver_senha('confirmar_senha')">👁️</button>
                </div>

                <button class="btn-enviar" type="submit">Enviar</button>
            </form>
        </div>     
    </div>
    <?php if (!empty($msg2)) : ?>
        <p style="color: red;"><?= $msg2; ?></p>
    <?php endif; ?>

    <?php if ($msg): ?>
        <div class="msg <?php echo strpos($msg, 'sucesso') !== false ? 'success' : ''; ?>">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>
</body>
<script>
    document.querySelector("#cpf").addEventListener("input", function() {
            let cpf = this.value.replace(/\D/g, ""); // Remove tudo que não for número
            if (cpf.length > 11) cpf = cpf.slice(0, 11); // Limita a 11 caracteres numéricos
    
            // Formata como XXX.XXX.XXX-XX
            if (cpf.length > 9) {
                cpf = cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
            } else if (cpf.length > 6) {
                cpf = cpf.replace(/(\d{3})(\d{3})(\d{1,3})/, "$1.$2.$3");
            } else if (cpf.length > 3) {
                cpf = cpf.replace(/(\d{3})(\d{1,3})/, "$1.$2");
            }

            this.value = cpf;
        });

        document.querySelector("#telefone").addEventListener("input", function () {
            let tel = this.value.replace(/\D/g, ""); // Remove tudo que não for número

            if (tel.length > 11) tel = tel.slice(0, 11); 
            if (tel.length > 10) {
                tel = tel.replace(/(\d{2})(\d{5})(\d{4})/, "($1) $2-$3");
            } else if (tel.length > 6) {
                tel = tel.replace(/(\d{2})(\d{4})(\d{0,4})/, "($1) $2-$3");
            } else if (tel.length > 2) {
                tel = tel.replace(/(\d{2})(\d{0,5})/, "($1) $2");
            } else if (tel.length > 0) {
                tel = tel.replace(/(\d{0,2})/, "($1");
            }

            this.value = tel;
        });

        document.querySelector("#email").addEventListener("input", function () {
            this.value = this.value.toLowerCase();
        });

        function ver_senha(id) {
            var passwordField = document.getElementById(id);
            if (passwordField.type === "password") {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        }
</script>
</html>

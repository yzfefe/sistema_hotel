<?php 
    include "conex.php";
    $msg = "";
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Sanitizar entrada de dados
        $nome = $conn->real_escape_string($_POST['nome']);
        $email = $conn->real_escape_string($_POST['email']);
        $telefone = $conn->real_escape_string($_POST['telefone']);
        $CPF = $conn->real_escape_string($_POST['CPF']);
        $endereco = $conn->real_escape_string($_POST['endereço']); 
        $login = $conn->real_escape_string($_POST['login']);
        $password = $_POST['password'];
    
        // Verificar se o usuário já existe (RG, CPF ou login duplicado)
        $sql_check = "SELECT * FROM gerente WHERE login = '$login' OR CPF = '$CPF'";
        $result_check = $conn->query($sql_check);
    
        if ($result_check->num_rows > 0) {
            echo "Usuário já existe!";
        } else {
            // Criptografar a senha
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
            // Inserir novo usuário no banco de dados
            $sql = "INSERT INTO hospede (nome, telefone, endereço, email, cpf, login, senha) 
                    VALUES ('$nome', '$telefone', '$endereco', '$email', '$CPF', '$login', '$hashed_password')";
    
            if ($conn->query($sql) === TRUE) {
                $msg = "Registro bem sucedido";
            } else {
                echo "Erro: " . $conn->error;
            }
        }
    }
    
    $conn->close();



?>
<?php
// utilizei para reportar todos os tipos de erros em php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../conex.php";
include "../../html/recepcionista/registrar_hospede.html";

date_default_timezone_set('America/Sao_Paulo');
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitizar entrada de dados
    $rg = $conn->real_escape_string($_POST['rg']);
    $cpf = $conn->real_escape_string($_POST['cpf']);
    $telefone = $conn->real_escape_string($_POST['telefone']);
    $endereco = $conn->real_escape_string($_POST['endereco']);
    $email = $conn->real_escape_string($_POST['email']);
    $nome = $conn->real_escape_string($_POST['nome']);
    $login = $conn->real_escape_string($_POST['login']);
    $password = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    $dataHoraAtual = date('Y-m-d H:i:s');

    // Verificar se as senhas coincidem
    if ($password !== $confirmar_senha) {
        $msg = "As senhas não coincidem!";
    } else {
        // Verificar se o usuário já existe (CPF ou login duplicado) em qualquer tabela
        $tables = ['gerente', 'recepcionista', 'hospede'];
        $user_exists = false;

        foreach ($tables as $table) {
            $sql_check = "SELECT login FROM $table WHERE login = ? OR cpf = ?";
            $stmt = $conn->prepare($sql_check);
            $stmt->bind_param("ss", $login, $cpf);
            $stmt->execute();
            $result_check = $stmt->get_result();

            if ($result_check->num_rows > 0) {
                $user_exists = true;
                break;
            }
        }

        if ($user_exists) {
            $msg = "Usuário já existe!";
        }elseif (strlen($cpf)!== 14){
            $msg = "O CPF deve conter 11 dígitos";
        }elseif (strlen($telefone) !== 15){
            $msg = "O número de telefone deve ter esse formato: (XX) XXXXX-XXXX";
        }else {
            // Criptografar a senha
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Escolha a tabela correta para inserir
            $table = "hospede";

            // Inserir novo usuário no banco de dados
            $sql = "INSERT INTO $table (nome, telefone, endereco, email, cpf, rg, horario, login, senha, status_atual, gastos_totais, gastos_atuais) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'ATIVO', 0.00, 0.00)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssss", $nome, $telefone, $endereco, $email, $cpf, $rg, $dataHoraAtual, $login, $hashed_password);

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
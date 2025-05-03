<?php
// Reportar todos os erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../conex.php";
include "../../html/recepcionista/registrar_hospede.html";

date_default_timezone_set('America/Sao_Paulo');
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verifica se todos os campos obrigatórios foram preenchidos
    if (
        empty($_POST['rg']) || empty($_POST['cpf']) || empty($_POST['telefone']) ||
        empty($_POST['endereco']) || empty($_POST['email']) || empty($_POST['nome']) ||
        empty($_POST['login']) || empty($_POST['senha']) || empty($_POST['confirmar_senha'])
    ) {
        $msg = "Todos os campos são obrigatórios!";
    } else {
        // Sanitizar entrada
        $rg = $conn->real_escape_string(trim($_POST['rg']));
        $cpf = $conn->real_escape_string(trim($_POST['cpf']));
        $telefone = $conn->real_escape_string(trim($_POST['telefone']));
        $endereco = $conn->real_escape_string(trim($_POST['endereco']));
        $email = $conn->real_escape_string(trim($_POST['email']));
        $nome = $conn->real_escape_string(trim($_POST['nome']));
        $login = $conn->real_escape_string(trim($_POST['login']));
        $password = $_POST['senha'];
        $confirmar_senha = $_POST['confirmar_senha'];
        $dataHoraAtual = date('Y-m-d H:i:s');

        // Verificação de formato do CPF (considerando formato 000.000.000-00)
        if (!preg_match("/^\d{3}\.\d{3}\.\d{3}-\d{2}$/", $cpf)) {
            $msg = "O CPF deve estar no formato 000.000.000-00";
        } elseif (!preg_match("/^\(\d{2}\) \d{5}-\d{4}$/", $telefone)) {
            $msg = "O número de telefone deve estar no formato (XX) XXXXX-XXXX";
        } elseif ($password !== $confirmar_senha) {
            $msg = "As senhas não coincidem!";
        } else {
            // Verificar se login ou CPF já existem
            $tables = ['gerente', 'recepcionista', 'hospede'];
            $user_exists = false;

            foreach ($tables as $table_name) {
                $sql_check = "SELECT login FROM $table_name WHERE login = ? OR cpf = ?";
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
            } else {
                // Criptografar senha
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Inserir na tabela 'hospede'
                $sql = "INSERT INTO hospede (nome, telefone, endereco, email, cpf, rg, horario, status_atual, login, senha, gastos_totais, gastos_atuais) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($sql);
                $status = "ATIVO";
                $gastos_totais = 0.00;
                $gastos_atuais = 0.00;

                $stmt->bind_param(
                    "ssssssssssdd",
                    $nome,
                    $telefone,
                    $endereco,
                    $email,
                    $cpf,
                    $rg,
                    $dataHoraAtual,
                    $status,
                    $login,
                    $hashed_password,
                    $gastos_totais,
                    $gastos_atuais
                );

                if ($stmt->execute()) {
                    $msg = "Registro bem-sucedido!";
                } else {
                    $msg = "Erro ao registrar: " . $conn->error;
                }
            }
        }
    }
}

$conn->close();

// Exibir mensagem (certifique-se de que seu HTML tem um local para isso)
echo "$msg";
?>

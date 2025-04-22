<?php
include "../conex.php";
include "../../html/registrar_hospede.html";


date_default_timezone_set('America/Sao_Paulo');
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitizar entrada de dados
    $RG = $conn->real_escape_string($_POST['rg']);
    $CPF = $conn->real_escape_string($_POST['cpf']);
    $telefone = $conn->real_escape_string($_POST['telefone']);
    $endereco = $conn->real_escape_string($_POST['endereco']); // Corrigido
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
            $table = "hospede"; // <- MODIFIQUE PARA O TIPO DE USUÁRIO CORRETO

            // Inserir novo usuário no banco de dados
            $sql = "INSERT INTO $table (nome, telefone, endereço, email, cpf, rg, horario, login, senha) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssss", $nome, $telefone, $endereco, $email, $CPF, $RG, $dataHoraAtual, $login, $hashed_password);

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


<?php if ($msg): ?>
    <div class="msg <?= $tipo === 'sucesso' ? 'sucesso' : 'erro' ?>">
    <?= htmlspecialchars($msg); ?>
    </div>
<?php endif; ?>

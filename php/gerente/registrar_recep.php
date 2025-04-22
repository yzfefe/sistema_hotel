<?php
include "../conex.php";
include "../../html/registrar_recep.html";
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

<?php if (!empty($msg2)) : ?>
        <p style="color: red;"><?= $msg2; ?></p>
    <?php endif; ?>

    <?php if ($msg): ?>
        <div class="msg <?php echo strpos($msg, 'sucesso') !== false ? 'success' : ''; ?>">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

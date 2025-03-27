<?php
include "../conex.php";
include "../../html/registrar_gerente.html";
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitizar entrada de dados
    $cpf = $conn->real_escape_string($_POST['cpf']);
    $telefone = $conn->real_escape_string($_POST['telefone']);
    $endereco = $conn->real_escape_string($_POST['endereco']);
    $email = $conn->real_escape_string($_POST['email']);
    $nome = $conn->real_escape_string($_POST['nome']);
    $login = $conn->real_escape_string($_POST['login']);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // Validar senha
    if ($senha !== $confirmar_senha) { 
        $msg = "As senhas não coincidem!";
    } else {
        // Verificar se o usuário já existe (CPF ou login duplicado)
        $tables = ['gerente', 'recepcionista', 'hospede'];
        $user_exists = false;

        $stmt = $conn->prepare("SELECT login FROM gerente WHERE login = ? OR CPF = ?");
        if (!$stmt) {
            error_log("Erro ao preparar a query de verificação: " . $conn->error);
            die("Erro interno, tente novamente mais tarde.");
        }

        foreach ($tables as $table) {
            $stmt->bind_param("ss", $login, $cpf);
            $stmt->execute();
            $result_check = $stmt->get_result();

            if ($result_check->num_rows > 0) {
                $user_exists = true;
                break;
            }
        }
        $stmt->close();

        if ($user_exists) {
            $msg2 = "Usuário já existe!";
            
            

        } else {
            // Definir a tabela correta (mudar conforme a lógica do sistema)
            $table = "gerente"; // Modifique se necessário

            // Validar se a tabela é permitida
            if (!in_array($table, $tables)) {
                die("Erro: Tipo de usuário inválido!");
            }

            // Criptografar a senha
            $hashed_password = password_hash($senha, PASSWORD_DEFAULT);

            // Inserir novo usuário no banco de dados
            $sql = "INSERT INTO $table (nome, telefone, endereco, email, CPF, login, senha) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                error_log("Erro ao preparar a query de inserção: " . $conn->error);
                die("Erro ao registrar. Por favor, tente novamente mais tarde.");
            }

            $stmt->bind_param("sssssss", $nome, $telefone, $endereco, $email, $cpf, $login, $hashed_password);

            if ($stmt->execute()) {
                $msg = "Registro bem sucedido!";
            } else {
                error_log("Erro ao executar a query de inserção: " . $stmt->error);
                $msg = "Erro ao registrar. Por favor, tente novamente mais tarde.";
            }

            $stmt->close();
        }
    }
}

$conn->close();
?>

<?php if ($msg): ?>
    <div class="msg <?php echo strpos($msg, 'sucesso') !== false ? 'success' : ''; ?>">
<?php echo $msg; ?>
   </div>
<?php endif; ?>

<?php if (!empty($msg2)) : ?>
    <p style="color: red;"><?= $msg2; ?></p>
<?php endif; ?>
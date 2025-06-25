<?php
require_once '../check_session.php';

if ($_SESSION['role'] != 'gerente') {
    header("Location: ../../html/login.html");
    exit();
}
include "../conex.php";
include "../../html/gerente/registrar_recep.html";
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
    } 
    // Verificar formato do CPF
    elseif(strlen($cpf) !== 14) {
        $msg = "O CPF deve conter 11 dígitos";
    }
    // Verificar formato do telefone
    elseif(strlen($telefone) !== 15) {
        $msg = "O número de telefone deve ter esse formato: (XX) XXXXX-XXXX";
    }
    else {
        // Verificar se o usuário já existe (CPF, login ou RG duplicado)
        $tables_to_check = [
            'recepcionista' => "SELECT login, cpf, rg FROM recepcionista WHERE login = ? OR cpf = ? OR rg = ?",
            'hospede' => "SELECT login, cpf, rg FROM hospede WHERE login = ? OR cpf = ? OR rg = ?",
            'gerente' => "SELECT login, cpf FROM gerente WHERE login = ? OR cpf = ?",
            'administrador' => "SELECT login FROM administrador WHERE login = ?"
        ];
        
        $user_exists = false;

        foreach ($tables_to_check as $table => $query) {
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                error_log("Erro ao preparar a query de verificação: " . $conn->error);
                die("Erro interno, tente novamente mais tarde.");
            }
            
            // Bind dos parâmetros conforme a query
            if ($table === 'recepcionista' || $table === 'hospede') {
                $stmt->bind_param("sss", $login, $cpf, $rg);
            } elseif($table === 'gerente') {
                $stmt->bind_param("ss", $login, $cpf);
            } elseif($table === 'administrador') {
                $stmt->bind_param("s", $login);
            }
            
            $stmt->execute();
            $result_check = $stmt->get_result();

            if ($result_check->num_rows > 0) {
                $user_exists = true;
                $stmt->close();
                break;
            }
            $stmt->close();
        }

        if ($user_exists) {
            $msg = "Usuário já existe!";
        } else {
            // Tabela de destino
            $target_table = "recepcionista";

            $hashed_password = password_hash($senha, PASSWORD_DEFAULT);

            // Inserir novo usuário no banco de dados
            $sql = "INSERT INTO $target_table (nome, telefone, horario, rg, endereco, email, cpf, login, senha) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                error_log("Erro ao preparar a query de inserção: " . $conn->error);
                die("Erro ao registrar. Por favor, tente novamente mais tarde.");
            }

            $stmt->bind_param("sssssssss", $nome, $telefone, $horario_de_trabalho, $rg, $endereco, $email, $cpf, $login, $hashed_password);

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
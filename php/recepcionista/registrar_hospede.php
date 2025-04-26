<?php
// utilizei para reportar todos os tipos de erros em php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../conex.php";

date_default_timezone_set('America/Sao_Paulo');
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitizar entrada de dados
    $RG = $conn->real_escape_string($_POST['rg']);
    $CPF = $conn->real_escape_string($_POST['cpf']);
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
        }elseif (strlen($cpf)!== 14){
            $msg = "O CPF deve conter 11 dígitos";
        }elseif (strlen($telefone) !== 15){
            $msg = "O número de telefone deve ter esse formato: (XX) XXXXX-XXXX"
        }else {
            // Criptografar a senha
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Escolha a tabela correta para inserir
            $table = "hospede";

            // Inserir novo usuário no banco de dados
            $sql = "INSERT INTO $table (nome, telefone, endereco, email, cpf, rg, horario, login, senha) 
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

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caminho das Pedras - Rustic Hotel</title>
    <link rel="stylesheet" href="../../css/registrar_hospede.css">
    
</head>
<body>
    <header class="header">
        <img src="../../img/logo_hoteel.png" alt="Caminho das Pedras - Rustic Hotel">
    </header>
    <form method="POST" action="">
    <div class="form-container">
        <div class="grid">
            <div class="colun">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" placeholder="Digite seu nome" required>

                <label for="rg">RG:</label>
                <input type="text" id="rg" name="rg" placeholder="Digite seu RG" onkeypress="RG()" maxlength="12" required>

                <label for="endereco">Endereço:</label>
                <input type="text" id="endereco" name="endereco" placeholder="Digite seu Endereço" required>

                <label for="login">Login:</label>
                <input type="text" id="login" name="login" placeholder="Digite seu Login" required>
            </div>

            <div class="colun">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" placeholder="Digite seu E-mail" required>

                <label for="cpf">CPF:</label>
                <input type="text" id="cpf" name="cpf" placeholder="Digite seu CPF" onkeypress="cpf()" maxlength="14" required>

                <label for="telefone">Telefone:</label>
                <input type="text" id="telefone" name="telefone" placeholder="Digite seu Telefone" onkeypress="telefone()" maxlength="15" required>

                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" placeholder="Informe a Senha" required>

                <label for="confirmar_senha">Confirme a Senha:</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Confirme a Senha" required>
                
                <!-- Botão para alternar a visibilidade das senhas -->
                <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">👁️</button>
            </div>
        </div>
        
        <button type="submit">Enviar</button>
    </div>
</form>

<script>
    function togglePasswordVisibility() {
        const senhaField = document.getElementById('senha');
        const confirmarSenhaField = document.getElementById('confirmar_senha');

        // Alternar o tipo dos campos de senha
        if (senhaField.type === 'password') {
            senhaField.type = 'text';
            confirmarSenhaField.type = 'text';
        } else {
            senhaField.type = 'password';
            confirmarSenhaField.type = 'password';
        }
    }

    function RG() {
        // para validar RG 
    }
    
    function cpf() {
        // para validar CPF 
    }
    
    function telefone() {
        //para validar telefone 
    }
</script>


<?php if ($msg): ?>
    <div class="msg <?= strpos($msg, 'sucesso') !== false ? 'sucesso' : 'erro' ?>">
        <?= htmlspecialchars($msg); ?>
    </div>
<?php endif; ?>

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

            if (tel.length > 11) tel = tel.slice(0, 11); // Limita a 11 caracteres numéricos

            // Formata como (XX) XXXXX-XXXX ou (XX) XXXX-XXXX
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
</body>
</html>

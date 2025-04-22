<?php
session_start();
include('conex.php');

$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['login'] ?? '';
    $password = $_POST['senha'] ?? '';

    $tables = ['administrador', 'gerente', 'recepcionista', 'hospede'];
    $user = null;
    $role = null;

    foreach ($tables as $table) {
        $query = "SELECT * FROM $table WHERE login = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['senha'])) {
                $role = $table;
                break;
            }
        }
    }

    if ($user && $role) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $role;

        // Redireciona para o dashboard correspondente
        $redirects = [
            'administrador' => 'admin/dashboard_admin.php',
            'gerente' => 'gerente/dashboard_gerente.php',
            'recepcionista' => 'recepcionista/dashboard_recepcionista.php',
            'hospede' => 'hospede/dashboard_hospede.php'
        ];

        header("Location: " . $redirects[$role]);
        exit();
    } else {
        $erro = "Usuário ou senha incorretos.";
    }
}
?>

<!-- HTML COMEÇA AQUI -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caminho das Pedras - Rustic Hotel</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <header class="header">
        <img src="img/logo_hoteel.png" alt="Caminho das Pedras - Rustic Hotel">
    </header>

    <div class="form-container">
        <form action="login.php" method="post">

            <label for="login">Login:</label>
            <input type="text" id="login" name="login" placeholder="Digite seu Login" required>

            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" placeholder="Informe a Senha" required>

            <button class="btn-enviar" type="submit">Enviar</button>

            <?php if (!empty($erro)) : ?>
                <p class="mensagem erro"><?= htmlspecialchars($erro); ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>

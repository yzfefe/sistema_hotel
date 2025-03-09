<?php
session_start();
include('conex.php'); // Arquivo de conexão com o banco de dados

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['login'];
    $password = $_POST['senha'];
    
    $tables = ['administrador', 'gerente', 'recepcionista', 'hospede'];
    $user = null;
    $role = null;
    
    foreach ($tables as $table) {
        $query = "SELECT * FROM $table WHERE login = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verifica a senha usando password_verify()
            if (password_verify($password, $user['senha'])) {
                $role = $table;
                break;
            }
        }
    }
    
    if ($user && $role) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $role;
        
        // Redirecionamento de acordo com o papel do usuário
        $redirects = [
            'administrador' => 'dashboard_admin.php',
            'gerente' => 'dashboard_gerente.php',
            'recepcionista' => 'dashboard_recepcionista.php',
            'hospede' => 'dashboard_hospede.php'
        ];

        header("Location: " . $redirects[$role]);
        exit();
    } else {
        $erro = "Usuário ou senha incorretos.";
    }
}
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
            background: url('../img/fotooo.png') no-repeat center center/cover;
            height: 1000px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .header {
            border-radius: 10px;
            background: linear-gradient(to right, #465d53, #d3d3d3);
            width: 80%;
            padding: 5px 100px;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            position: relative;
            height: 180px;
        }
        .header img {
            height: 200px;
            display: block;
            margin-top: -15px;
        }
        .nav {
            position: absolute;
            bottom: 5px;
            right: 40px;
        }
        .nav a {
            color: white;
            font-weight: bold;
            text-decoration: none;
            font-size: 18px;
            margin: 0 15px;
        }
        .nav a:hover {
            text-decoration: underline;
        }
        .form-container {
            background-color: #2c3330;
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            margin-top: 150px;
        }
        .form-container input {
            display: block;
            width: 250px;
            padding: 10px;
            margin: 10px auto;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        label {
            color: white;
            font-weight: bold;
        }
        .btn-enviar {
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #636851;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .btn-enviar:hover {
            background-color: #99a285;
        }
        .mensagem {
            color: red;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <header class="header">
        <img src="../img/logo_hoteel.png" alt="Caminho das Pedras - Rustic Hotel">
        <nav class="nav">
            <a href="#">SAIR</a>
        </nav>
    </header>
    <div class="form-container">
        <form action="login.php" method="POST">
            <label for="login">Login:</label>
            <input type="text" id="login" name="login" placeholder="Digite seu Login" required>
        
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" placeholder="Informe a Senha" required>

            <button class="btn-enviar" type="submit">Entrar</button>
        </form>

        <?php if (isset($erro)) { ?>
            <p class="mensagem"><?php echo $erro; ?></p>
        <?php } ?>
    </div>
</body>
</html>

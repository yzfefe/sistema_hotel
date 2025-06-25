<?php
session_start([
    'cookie_lifetime' => 86400, // 1 dia em segundos
    'cookie_secure' => isset($_SERVER['HTTPS']), // Habilitar apenas em HTTPS
    'cookie_httponly' => true, // Impede acesso via JavaScript
    'cookie_samesite' => 'Strict', // Proteção contra CSRF
    'use_strict_mode' => true // Prevenção contra fixation
]);
include "../html/login.html";
include "conex.php";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['login'] ?? '';
    $password = $_POST['senha'] ?? '';

    $tables = [
        'administrador' => ['id_field' => 'id_adm', 'name_field' => 'nome'],
        'gerente' => ['id_field' => 'id_ger', 'name_field' => 'nome'],
        'recepcionista' => ['id_field' => 'id_recep', 'name_field' => 'nome'],
        'hospede' => ['id_field' => 'id_hos', 'name_field' => 'nome']
    ];

    $user_found = false;
    $senha_correta = false;

    foreach ($tables as $table => $fields) {
        $query = "SELECT * FROM $table WHERE login = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user_found = true;
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['senha'])) {
                $senha_correta = true;
                
                // Regenera o ID da sessão para prevenir fixation
                session_regenerate_id(true);
                
                // Armazena dados na sessão
                $_SESSION['user_id'] = $user[$fields['id_field']];
                $_SESSION['role'] = $table;
                $_SESSION['user_name'] = $user[$fields['name_field']];
                $_SESSION['user_data'] = $user;
                $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();

                $redirects = [
                    'administrador' => '../html/adm/tela_adm.php',
                    'gerente' => '../html/gerente/tela_gerente.php',
                    'recepcionista' => '../html/recepcionista/tela_recep.php',
                    'hospede' => '../html/hospede/tela_hospede.php'
                ];

                header("Location: " . $redirects[$table]);
                exit();
            }
            break; // Sai do loop quando encontra o usuário, mesmo se a senha estiver errada
        }
    }

    if (!$user_found) {
        $erro = "Login não encontrado!";
    } elseif ($user_found && !$senha_correta) {
        $erro = "Senha incorreta!";
    }
}

// Exibe a mensagem de erro se existir
if (!empty($erro)) {
    echo $erro;
}
?>
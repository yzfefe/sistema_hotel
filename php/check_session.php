<?php
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true
]);

// Verifica se o usuário está logado
if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
    header("Location: ../login.html");
    exit();
}

// Verifica se o IP ou User-Agent mudaram (possível roubo de sessão)
if ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR'] || 
    $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    session_destroy();
    header("Location: ../login.html?error=security");
    exit();
}

// Verifica tempo de inatividade
$inactive = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive)) {
    session_destroy();
    header("Location: ../login.html?error=timeout");
    exit();
}

// Atualiza o tempo da última atividade
$_SESSION['last_activity'] = time();
?>
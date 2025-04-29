<?php
session_start();
include "conex.php";
include "../html/login.html";
$erro = "oiii";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['login'] ?? '';
    $password = $_POST['senha'] ?? '';

    // Tabelas e respectivos campos de ID
    $tables = [
        'administrador' => 'id_adm',
        'gerente' => 'id_ger',
        'recepcionista' => 'id_recep',
        'hospede' => 'id_hos'
    ];

    $user = null;
    $role = null;

    foreach ($tables as $table => $id_field) {
        $query = "SELECT * FROM $table WHERE login = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verifica primeiro se a senha está criptografada
            if (password_verify($password, $user['senha'])) {
                $role = $table;
            } 
            // Se não estiver criptografada, faz comparação direta
            elseif ($password === $user['senha']) {
                $role = $table;
            }

            // Se autenticado (por qualquer método)
            if ($role) {
                // Salva o ID correto na sessão
                $_SESSION['user_id'] = $user[$id_field];
                $_SESSION['role'] = $role;

                // Redireciona para o dashboard correto
                $redirects = [
                    'administrador' => '../html/adm/tela_adm.html',
                    'gerente' => '../html/gerente/tela_gerente.php',
                    'recepcionista' => '../html/recepcionista/tela_recep.ht',
                    'hospede' => '../html/hospede/tela_hospede.html'
                ];

                header("Location: " . $redirects[$role]);
                exit();
                $erro = "Deu algum erro.";
            }
        }
    }

    // Se chegou aqui, login falhou
    // $erro = "Usuário ou senha incorretos.";
    // echo $erro;
}
?>

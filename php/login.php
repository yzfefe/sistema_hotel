<?php
session_start();
include "conex.php";
include "../html/login.html";
$erro = "";

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

    $user_found = false;
    $senha_correta = false;

    foreach ($tables as $table => $id_field) {
        $query = "SELECT * FROM $table WHERE login = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user_found = true;
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['senha']) || $password === $user['senha']) {
                $senha_correta = true;
                $role = $table;

                $_SESSION['user_id'] = $user[$id_field];
                $_SESSION['role'] = $role;

                $redirects = [
                    'administrador' => '../html/adm/tela_adm.html',
                    'gerente' => '../html/gerente/tela_gerente.html',
                    'recepcionista' => '../html/recepcionista/tela_recep.html',
                    'hospede' => '../html/hospede/tela_hospede.html'
                ];

                header("Location: " . $redirects[$role]);
                exit();
            }
        }
    }

    // Verificação final e exibição de erro apropriado
    if (!$user_found) {
        $erro = "Login não encontrado!";
    } elseif (!$senha_correta) {
        $erro = "Senha incorreta!";
    }

    echo "<div style='color: red; text-align: center; font-weight: bold;'>$erro</div>";
}
?>

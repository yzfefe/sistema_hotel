<?php
session_start();
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
                
                // Armazena todos os dados importantes na sessão
                $_SESSION['user_id'] = $user[$fields['id_field']];
                $_SESSION['role'] = $table;
                $_SESSION['user_name'] = $user[$fields['name_field']];
                $_SESSION['user_data'] = $user; // Armazena todos os dados do usuário

                $redirects = [
                    'administrador' => '../html/adm/tela_adm.html',
                    'gerente' => '../html/gerente/tela_gerente.php',
                    'recepcionista' => '../html/recepcionista/tela_recep.php',
                    'hospede' => '../html/hospede/tela_hospede.php'
                ];

                header("Location: " . $redirects[$table]);
                exit();
            }
        }
    }

    if (!$user_found) {
        $erro = "Login não encontrado!";
    } elseif (!$senha_correta) {
        $erro = "Senha incorreta!";
    }
}
?>

<!-- HTML só aparece se não houve redirecionamento -->
<?php include "../html/login.html"; ?>

<?php if ($erro): ?>
    <div style='color: red; text-align: center; font-weight: bold;'><?= $erro ?></div>
<?php endif; ?>
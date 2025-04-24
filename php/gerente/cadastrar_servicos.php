<?php
include "../conex.php"; // Inclui a conexão com o banco de dados

$msg = ""; 


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recebe os dados e armazena
    $nome = trim($_POST['nome']);
    $preco = trim($_POST['preco']);
    $horario = trim($_POST['horario']);

    // Validação
    if (empty($nome) || empty($preco) || empty($horario)) {
        $msg = "Todos os campos são obrigatórios.";
    } else {
     
        $stmt = $conn->prepare("INSERT INTO servicos (nome, preco, horario) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $nome, $preco, $horario); // 's' para string, 'd' para double

        // verifica se os dados foram inseridos
        if ($stmt->execute()) {
            $msg = "Cadastro realizado com sucesso!";
        } else {
            $msg = "Erro ao cadastrar: " . $stmt->error;
        }

        $stmt->close();
    }

    // Redireciona de volta para o formulário com a mensagem
    header("Location: ../../html/gerente/cadastrar_servicos.html?msg=" . urlencode($msg));
    exit();
}

$conn->close(); 
?>

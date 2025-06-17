<?php
require_once '../check_session.php';

if ($_SESSION['role'] != 'gerente') {
    header("Location: ../../html/login.html");
    exit();
}

include "../conex.php";

$msg = "";

// Verifica se foi enviado via método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // recebe os dados do formulário e armazenando
    $nome = trim($_POST['nome']); 
    $preco = trim($_POST['preco']); 
    $horario_termina = trim($_POST['horario_termina']); 
    $horario = trim($_POST['horario']); 
    
    // Validação
    if (empty($nome) || empty($preco) || empty($horario) || empty($horario_termina)) {
        $msg = "Todos os campos são obrigatórios.";
    } else {
        // Preparando a query SQL
        $stmt = $conn->prepare("INSERT INTO servicos (nome, preco, horario_comeca, horario_termina, disponivel) VALUES (?, ?, ?, ?, TRUE)");
        $stmt->bind_param("sdss", $nome, $preco, $horario, $horario_termina);

        // Executa a query e verifica se os dados foram inseridos 
        if ($stmt->execute()) {
            $msg = "Cadastro realizado com sucesso!";
        } else {
            $msg = "Erro ao cadastrar: " . $stmt->error; 
        }
        
        $stmt->close();
    }
    $conn->close();
}

// Inclui o HTML após processar tudo
include "../../html/gerente/cadastrar_servicos.php";
?>
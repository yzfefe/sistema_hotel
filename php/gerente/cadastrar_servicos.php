<?php

    include "../conex.php";
    include "../../html/cadastrar_servicos.html";

    $msg = "";

    // Verifica se foi enviado via método POST
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // recebe os dados do formulário e armazenando
        $nome = trim($_POST['nome']); 
        $preco = trim($_POST['preco']); 
        $horario = trim($_POST['horario']); 
        // Validação
        if (empty($nome) || empty($preco) || empty($horario)) {
            echo "Todos os campos são obrigatórios.";
            exit;
        }

        // Preparando a query SQL
        $stmt = $conn->prepare("INSERT INTO servicos (nome, preco, horario) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $nome, $preco, $horario); // 's' para string, 'd' para double

        // Executa a query e verifica se os dados foram inseridos 
        if ($stmt->execute()) {
            echo "Cadastro realizado com sucesso!";
        } else {
            echo "Erro ao cadastrar: " . $stmt->error; 
        }
        
        $stmt->close();
        $conn->close();
    }
?>

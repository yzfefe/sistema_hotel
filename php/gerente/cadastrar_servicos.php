<?php
    include "../conex.php";
    include "../../html/gerente/cadastrar_servicos.html";

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
            echo "Todos os campos são obrigatórios.";
            exit;
        }

        // Preparando a query SQL
        $stmt = $conn->prepare("INSERT INTO servicos (nome, preco, horario_comeca, horario_termina, disponivel) VALUES (?, ?, ?, ?, TRUE)");
        $stmt->bind_param("sdss", $nome, $preco, $horario, $horario_termina); // 's' para string, 'd' para double

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

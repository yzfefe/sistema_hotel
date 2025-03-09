<?php

    include "../conex.php";

    $msg = "";

    // Verifica se foi enviado via método POST
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // recebe os dados do formulário e armazenando
        $nome = trim($_POST['nome']); 
        $preco = trim($_POST['preco']); 
        $tipo = trim($_POST['tipo']); 
        // Validação
        if (empty($nome) || empty($preco) || empty($tipo)) {
            echo "Todos os campos são obrigatórios.";
            exit;
        }

        // Preparando a query SQL
        $stmt = $conn->prepare("INSERT INTO quartos (nome, tipo, preco_diaria) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nome, $tipo, $preco); // 's' para string, 'd' para double

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

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Serviços - Caminho das Pedras</title>
    <style>
       
       * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            
            height: 1000px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .header {
        background: rgb(70,93,83);
        background: linear-gradient(180deg, rgba(70,93,83,1) 0%, rgba(126,191,163,0.7539390756302521) 50%, rgba(0,212,255,0) 100%);
        width: 80%;
        padding: 5px 100px;
        display: flex;
        align-items: flex-start;
        justify-content: center; /* Centraliza horizontalmente */
        position: relative;
        height: 180px;
        }
        .header img {
            height: 200px; /* Ajuste o tamanho conforme necessário */
            display: block;
            margin-top: -15px;
        }
        .nav {
            margin-right: 0px;
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

        label{
            color: white;
            font-weight: bold;
        }
        .btn-enviar{
            display: flex;
            flex-direction: column;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #636851;
            color: white; /* Cor do texto */
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn-enviar:hover {
          background-color: #99a285; /* Cor ao passar o mouse */
        
        }
        
    </style>
</head>
<body>
    <header class="header">
        <img src="../../img/logo_hoteel.png" alt="Caminho das Pedras - Rustic Hotel"> 
        <nav class="nav">
            <a href="#">SAIR</a>
        </nav>
    </header>
    
    <form class="form-container" id="cadastroQuarto" method="POST" action="">
        <label for="nome">Nome do quarto:</label>
        <input type="text" id="nome" name="nome" placeholder="Informe o nome do quarto" required>
        
        <label for="preco">Preço:</label>
        <input type="text" id="preco" name="preco" placeholder="Informe o preço" required>
        
        <label for="tipo">Tipo de quarto:</label>
        <input type="text" id="tipo" name="tipo" required>
        
        <button type="submit" class="btn-enviar">Enviar</button>
    </form>


</body>
</html>
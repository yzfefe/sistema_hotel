<?php
include "conex.php";

// Verificando se houve erro na conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error); // exibir mensagem se falhar
}

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
            background: url('../img/fotooo.png') no-repeat center center/cover;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .header {
            border-radius: 10px;
            background: linear-gradient(to right, #465d53, #d3d3d3);
            width: 80%;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
        }
        .header img {
            height: 100px; /* Altura da imagem do cabeçalho */
        }
        .nav a {
            color: white; /* Cor dos links de navegação */
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
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Sombra do formulário */
            text-align: center;
            width: 300px;
        }
        .form-container label {
            color: white; /* Cor dos rótulos */
            font-weight: bold;
            display: block;
            margin: 10px 0 5px;
        }
        .form-container input {
            width: 100%; /* Largura total dos campos de entrada */
            padding: 10px;
            border: 1px solid #ccc; /* Borda dos campos */
            border-radius: 5px;
            margin-bottom: 15px; 
        }
        .btn-enviar {
            width: 100%; 
            padding: 10px;
            background-color: #636851; /* Cor de fundo do botão */
            color: white; /* Cor do texto do botão */
            font-size: 16px;
            font-weight: bold;
            border: none; /* Remove a borda padrão */
            border-radius: 5px; 
            cursor: pointer; /* Cursor de ponteiro ao passar sobre o botão */
            transition: background 0.3s ease; /* Transição suave para a cor de fundo */
        }
        .btn-enviar:hover {
            background-color: #99a285; /* Cor de fundo do botão ao passar o mouse */
        }
    </style>
</head>
<body>
    <header class="header">
        <img src="../img/logo_hoteel.png" alt="Caminho das Pedras - Rustic Hotel"> 
        <nav class="nav">
            <a href="#">SAIR</a>
        </nav>
    </header>
    
    <form class="form-container" id="cadastroServico" method="POST" action="">
        <label for="nome">Serviço:</label>
        <input type="text" id="nome" name="nome" placeholder="Informe o serviço" required>
        
        <label for="preco">Preço:</label>
        <input type="number" id="preco" name="preco" placeholder="Informe o preço" required>
        
        <label for="horario">Horário:</label>
        <input type="time" id="horario" name="horario" required>
        
        <button type="submit" class="btn-enviar">Enviar</button>
    </form>
</body>
</html>
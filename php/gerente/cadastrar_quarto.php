<?php

include "../conex.php";

$msg = "";

// Verifica se foi enviado via método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recebe os dados e armazena
    $nome = trim($_POST['nome']);
    $preco = trim($_POST['preco']);
    $tipo = trim($_POST['tipo']);
    $andar = trim($_POST['andar']);
    
    // Validação
    if (empty($nome) || empty($preco) || empty($tipo) || empty($andar)) {
        $msg = "Todos os campos são obrigatórios.";
    } else {
        // Verifica se o andar está dentro do limite
        if ($andar < 1 || $andar > 10) {
            $msg = "O andar deve estar entre 1 e 10.";
        } elseif ($nome < 1 || $nome > 16) { // Verifica se o número do quarto está entre 1 e 16
            $msg = "O número do quarto deve estar entre 1 e 16.";
        } else {
            // Verifica se o número total de quartos não excede 16 por andar
            $sql_count = "SELECT COUNT(*) as total FROM quartos WHERE andar = ?";
            $stmt_count = $conn->prepare($sql_count);
            $stmt_count->bind_param("i", $andar); // 'i' para inteiro
            $stmt_count->execute();
            $result_count = $stmt_count->get_result();
            $row_count = $result_count->fetch_assoc();
            $total_quartos = $row_count['total'];

            if ($total_quartos >= 16) {
                $msg = "O número máximo de 16 quartos por andar já foi atingido.";
            } else {
                // Preparando a query SQL
                $stmt = $conn->prepare("INSERT INTO quartos (nome, tipo, preco_diaria, andar) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssdi", $nome, $tipo, $preco, $andar); // 's' para string, 'd' para double, 'i' para inteiro

                //  verifica se os dados foram inseridos 
                if ($stmt->execute()) {
                    $msg = "Cadastro realizado com sucesso!";
                } else {
                    $msg = "Erro ao cadastrar: " . $stmt->error; 
                }
                
                $stmt->close();
            }
            $stmt_count->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Quartos - Caminho das Pedras</title>
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
            background: linear-gradient(180deg, rgba(107, 57, 42, 0.946) 6%, rgba(133,78,57,1) 18%, rgb(203, 164, 122) 60%, rgba(217, 192, 164, 0.339) 90%, rgba(255,255,255,1) 110%);
            width: 100%;
            padding: 5px 100px;
            display: flex;
            justify-content: center; 
            position: relative;
            height: 180px;
        }
        .header img {
            height: 200px; 
            display: block;
            margin-top: -15px;
        }
        .form-container {
            border: 3.5px solid black;
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            margin-top: 75px;
        }
        .form-container input {
            display: block;
            width: 250px;
            padding: 10px;
            margin: 10px auto;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        label {
            color: black;
        }
        .btn-enviar {
            display: flex;
            flex-direction: column;
            margin: 20px auto;
            padding: 10px 50px;
            background-color: #c49a6c;
            color: white; /* Cor do texto */
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.4s ease;
        }
        .btn-enviar:hover {
            background-color: #7d3a26; /* Cor ao passar o mouse */
        }
        .msg {
            margin-top: 20px;
            text-align: center;
            font-weight: bold;
            padding: 10px;
            border-radius: 5px;
        }
        .msg.sucesso {
            color: green;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .msg.erro {
            color: red;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <header class="header">
        <img src="../../img/logo_hoteel.png" alt="Caminho das Pedras - Rustic Hotel"> 
    </header>
    
    <div class="form-container">
        <h2>Cadastrar Quarto</h2>
        
        <?php if ($msg): ?>
            <div class="msg <?= strpos($msg, 'sucesso') !== false ? 'sucesso' : 'erro' ?>">
                <?= htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="nome">Nº do Quarto:</label>
            <input type="number" id="nome" name="nome" placeholder="Informe o nº do quarto" required>
            
            <label for="andar">Andar:</label>
            <input type="number" id="andar" name="andar" placeholder="Informe o andar do quarto" required>
            
            <label for="tipo">Tipo:</label>
            <input type="text" id="tipo" name="tipo" placeholder="Ex:(suíte normal ou suíte plus)" required>

            <label for="preco">Preço:</label>
            <input type="number" id="preco" name="preco" placeholder="Informe o preço" required>

            <button type="submit" class="btn-enviar">Enviar</button>
        </form>
    </div>
</body>
</html>

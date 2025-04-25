<?php

include "../conex.php";
include "../../html/gerente/cadastrar_quarto.html";
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


       
<?php if ($msg): ?>
            <div class="msg <?= strpos($msg, 'sucesso') !== false ? 'sucesso' : 'erro' ?>">
                <?= htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

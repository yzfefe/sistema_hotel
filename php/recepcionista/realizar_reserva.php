<?php
include '../conex.php';

// Inicializa variáveis
$mensagem = '';
$tipo_mensagem = '';
$preco_diaria = 0;

// Se GET, carrega preços dos quartos para exibição
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $precos_quartos = [];
    $sql = "SELECT id_quarto, preco_diaria FROM quartos WHERE disponivel = TRUE";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $precos_quartos[$row['id_quarto']] = $row['preco_diaria'];
        }
    }
}

// Processa o formulário POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cliente_id = $_POST['cliente_id'] ?? null;
    $quarto_id = $_POST['quarto_id'] ?? null;
    $data_entrada = $_POST['data_entrada'] ?? null;

    if (!$cliente_id || !$quarto_id || !$data_entrada) {
        $mensagem = "Todos os campos são obrigatórios!";
        $tipo_mensagem = "danger";
    } else {
        // Inicia transação
        $conn->begin_transaction();

        try {
            // 1. Insere a reserva
            $stmt_reserva = $conn->prepare("INSERT INTO reservas (id_hos, id_quarto, data_reserva, status_atual) 
                                          VALUES (?, ?, ?, 'CONFIRMADA')");
            $stmt_reserva->bind_param("iis", $cliente_id, $quarto_id, $data_entrada);
            $stmt_reserva->execute();
            
            // 2. Atualiza status do quarto
            $stmt_quarto = $conn->prepare("UPDATE quartos SET disponivel = FALSE WHERE id_quarto = ?");
            $stmt_quarto->bind_param("i", $quarto_id);
            $stmt_quarto->execute();
            
            // 3. Obtém preço da diária
            $stmt_preco = $conn->prepare("SELECT preco_diaria FROM quartos WHERE id_quarto = ?");
            $stmt_preco->bind_param("i", $quarto_id);
            $stmt_preco->execute();
            $result = $stmt_preco->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $preco_diaria = $row['preco_diaria'];
                
                // Verifica se o preço é válido
                if (!is_numeric($preco_diaria)) {
                    throw new Exception("Preço da diária inválido");
                }
                
                // 4. Atualiza gastos do hóspede
                $stmt_gastos = $conn->prepare("UPDATE hospede SET gastos_atuais = gastos_atuais + ?, gastos_totais = gastos_totais + ? WHERE id_hos = ?");
                
                // Usar "ddi" se os campos forem DECIMAL, ou "iii" se forem INT
                $stmt_gastos->bind_param("ddi", $preco_diaria, $preco_diaria, $cliente_id);
                $stmt_gastos->execute();
            }
            
            // Confirma transação
            $conn->commit();
            $mensagem = "Reserva realizada com sucesso!";
            $tipo_mensagem = "success";
            
        } catch (Exception $e) {
            // Rollback em caso de erro
            $conn->rollback();
            $mensagem = "Erro ao realizar reserva: " . $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva de Quarto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .form-container {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Realizar Reserva</h1>
        
        <?php if ($mensagem): ?>
            <div class="alert alert-<?= $tipo_mensagem ?> alert-dismissible fade show" role="alert">
                <?= $mensagem ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="post" action="">
                <div class="mb-3">
                    <label for="cliente" class="form-label">Selecione o Cliente:</label>
                    <select class="form-select" id="cliente" name="cliente_id"      required>
                        <?php
                        $sql = "SELECT id_hos, nome, telefone FROM hospede";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='".$row['id_hos']."'>".$row['nome']." - ".$row['telefone']."</option>";
                            }
                        } else {
                            echo "<option disabled>Não há hóspedes cadastrados</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="quarto" class="form-label">Selecione o Quarto:</label>
                    <select class="form-select" id="quarto" name="quarto_id" required>
                        <?php
                        $sql = "SELECT id_quarto, nome, tipo FROM quartos WHERE disponivel = TRUE";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $preco = isset($precos_quartos[$row['id_quarto']]) ? number_format($precos_quartos[$row['id_quarto']], 2, ',', '.') : '0,00';
                                echo "<option value='".$row['id_quarto']."' data-preco='".$preco."'>"
                                    .$row['nome']." - ".$row['tipo']." (R$ ".$preco.")"
                                    ."</option>";
                            }
                        } else {
                            echo "<option disabled>Não há quartos disponíveis no momento</option>";
                        }
                        ?>
                    </select>
                    
                    <div class="mt-2">
                        <label for="preco" class="form-label">Preço da Diária:</label>
                        <input type="text" class="form-control" id="preco" name="preco" value="R$ 0,00" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="data_entrada" class="form-label">Data de Entrada:</label>
                    <input type="date" class="form-control" id="data_entrada" name="data_entrada" required 
                           min="<?= date('Y-m-d') ?>">
                </div>

                <button type="submit" class="btn btn-primary w-100">Realizar Reserva</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Atualiza preço quando seleciona outro quarto
        document.getElementById('quarto').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const preco = selectedOption.getAttribute('data-preco') || '0,00';
            document.getElementById('preco').value = 'R$ ' + preco;
        });

        // Inicializa o preço ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            const quartoSelect = document.getElementById('quarto');
            if (quartoSelect.options.length > 0) {
                quartoSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>
</body>
</html>

<?php
// Fecha a conexão
$conn->close();
?>

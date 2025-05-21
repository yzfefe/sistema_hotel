<?php
session_start();
include '../conex.php';
date_default_timezone_set('America/Sao_Paulo');

// Inicializa variáveis
$mensagem = '';
$tipo_mensagem = '';

// Gera token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Processa o formulário POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Token de segurança inválido!");
    }

    $cliente_id = $_POST['cliente_id'] ?? null;
    $quarto_id = $_POST['quarto_id'] ?? null;
    $data_entrada = $_POST['data_entrada'] ?? null;

    if (!$cliente_id || !$quarto_id || !$data_entrada) {
        $_SESSION['mensagem'] = "Todos os campos são obrigatórios!";
        $_SESSION['tipo_mensagem'] = "danger";
    } else {
        // Validação da data
        $hoje = new DateTime('today');
        $data_selecionada = new DateTime($data_entrada);
        if ($data_selecionada < $hoje) {
            $_SESSION['mensagem'] = "A data de entrada não pode ser no passado!";
            $_SESSION['tipo_mensagem'] = "danger";
        } else {
            // Inicia transação
            $conn->begin_transaction();

            try {
                // Verifica se já existe reserva para este quarto na mesma data
                $stmt_verifica = $conn->prepare("SELECT id_reserva FROM reservas_quarto WHERE id_quarto = ? AND status = ?");
                $stmt_verifica->bind_param("is", $quarto_id, $data_entrada);
                $stmt_verifica->execute();
                $result = $stmt_verifica->get_result();

                if ($result->num_rows > 0) {
                    $_SESSION['mensagem'] = "Este quarto já está reservado para a data selecionada!";
                    $_SESSION['tipo_mensagem'] = "danger";
                } else {
                    // 1. Insere a reserva
                    $stmt_reserva = $conn->prepare("INSERT INTO reservas_quarto (id_hos, id_quarto, data_reserva, status) 
                                                VALUES (?, ?, ?, 'CONFIRMADA')");
                    $stmt_reserva->bind_param("iis", $cliente_id, $quarto_id, $data_entrada);
                    $stmt_reserva->execute();

                    // 2. Atualiza status do quarto
                    $stmt_quarto = $conn->prepare("UPDATE quartos SET disponivel = FALSE WHERE id_quarto = ?");
                    $stmt_quarto->bind_param("i", $quarto_id);
                    $stmt_quarto->execute();

                    $_SESSION['mensagem'] = "Reserva realizada com sucesso!";
                    $_SESSION['tipo_mensagem'] = "success";
                }

                // Confirma transação
                $conn->commit();
            } catch (Exception $e) {
                // Rollback em caso de erro
                $conn->rollback();
                $_SESSION['mensagem'] = "Erro ao realizar reserva: " . $e->getMessage();
                $_SESSION['tipo_mensagem'] = "danger";
            }
        }
    }
    
    // Redireciona para evitar reenvio do formulário
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Recupera mensagem da sessão se existir
if (isset($_SESSION['mensagem'])) {
    $mensagem = $_SESSION['mensagem'];
    $tipo_mensagem = $_SESSION['tipo_mensagem'];
    unset($_SESSION['mensagem']);
    unset($_SESSION['tipo_mensagem']);
}

// Função para carregar preços dos quartos disponíveis
function carregarPrecosQuartos($conn) {
    $precos = [];
    $sql = "SELECT id_quarto, preco_diaria FROM quartos WHERE disponivel = TRUE";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $precos[$row['id_quarto']] = $row['preco_diaria'];
        }
    }
    return $precos;
}

// Carrega preços dos quartos para exibição
$precos_quartos = carregarPrecosQuartos($conn);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva de Quarto</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --color-primary: #c49a6c;
            --color-secondary: #6b392a;
            --color-light: #f5e7d8;
            --color-dark: #3a2618;
        }
        body {
            background: linear-gradient(180deg, rgba(107, 57, 42, 0.946) 6%, rgba(133,78,57,1) 18%, rgb(203, 164, 122) 60%, rgba(217, 192, 164, 0.339) 90%, rgba(255,255,255,1) 110%);
            background-attachment: fixed;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
            margin-top: 2rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(203, 164, 122, 0.3);
        }
        
        h1 {
            color: #5a3022;
            text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.5);
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .form-container {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .form-label {
            font-weight: 500;
            color: #5a3022;
        }
        
        .btn-primary {
            background-color: #6b392a;
            border-color: #5a3022;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: #5a3022;
            border-color: #4a281b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .form-select:focus, .form-control:focus {
            border-color: #c44512;
            box-shadow: 0 0 0 0.25rem rgba(194, 87, 44, 0.25);
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        .price-display {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 10px;
            font-weight: bold;
            color: #8a5a44;
            border-left: 4px solid #6b392a;
        }
        
        .option-details {
            display: flex;
            justify-content: space-between;
        }
        
        .option-type {
            color: #6c757d;
            font-size: 0.9em;
        }
        
        .option-price {
            color: #28a745;
            font-weight: bold;
        }
        .btn-outline-primary {
            border-color: var(--color-secondary);
            color: var(--color-secondary);
        }

        .btn-outline-primary:hover {
            background-color: var(--color-secondary);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-start mb-4">
            <a href="../../html/recepcionista/tela_recep.html" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
        </div>
        <h1><i class="bi bi-calendar-plus"></i> Realizar Reserva</h1>
        
        <?php if ($mensagem): ?>
            <div class="alert alert-<?= $tipo_mensagem ?> alert-dismissible fade show" role="alert">
                <?= $mensagem ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="post" action="">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <div class="mb-4">
                    <label for="cliente" class="form-label"><i class="bi bi-person"></i> Selecione o Cliente:</label>
                    <select class="form-select" id="cliente" name="cliente_id" required>
                        <?php
                        $sql = "SELECT id_hos, nome, telefone FROM hospede WHERE status_atual = 'ATIVO'";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='".$row['id_hos']."'>".$row['nome']." - ".$row['telefone']."</option>";
                            }
                        } else {
                            echo "<option disabled>Não há hóspedes cadastrados ou ativos no momento</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="quarto" class="form-label"><i class="bi bi-door-open"></i> Selecione o Quarto:</label>
                    <select class="form-select" id="quarto" name="quarto_id" required>
                        <?php
                        $sql = "SELECT id_quarto, nome, tipo, preco_diaria FROM quartos WHERE disponivel = TRUE";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $preco = number_format($row['preco_diaria'], 2, ',', '.');
                                echo "<option value='".$row['id_quarto']."' data-preco='".$preco."' data-tipo='".$row['tipo']."'>
                                        <div class='option-details'>
                                            <span>".$row['nome']."</span>
                                            <span class='option-type'>".$row['tipo']."</span>
                                            <span class='option-price'>R$ ".$preco."</span>
                                        </div>
                                    </option>";
                            }
                        } else {
                            echo "<option disabled>Não há quartos disponíveis no momento</option>";
                        }
                        ?>
                    </select>
                    
                    <div class="price-display mt-3">
                        <label class="form-label"><i class="bi bi-cash-coin"></i> Preço da Diária:</label>
                        <div id="preco" class="fs-4">R$ 0,00</div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="data_entrada" class="form-label"><i class="bi bi-calendar"></i> Data de Entrada:</label>
                    <input type="date" class="form-control" id="data_entrada" name="data_entrada" required
                        min="<?= date('Y-m-d') ?>"
                        value="<?= date('Y-m-d') ?>">
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 py-3" onclick="this.disabled=true;this.form.submit()">
                    <i class="bi bi-check-circle"></i> Confirmar Reserva
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Atualiza preço quando seleciona outro quarto
        document.getElementById('quarto').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const preco = selectedOption.getAttribute('data-preco') || '0,00';
            document.getElementById('preco').textContent = 'R$ ' + preco;
        });

        // Inicializa o preço ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            const quartoSelect = document.getElementById('quarto');
            if (quartoSelect.options.length > 0) {
                quartoSelect.dispatchEvent(new Event('change'));
            }
            
            // Configura data mínima
            const dataInput = document.getElementById('data_entrada');
            const hoje = new Date();
            const dia = String(hoje.getDate()).padStart(2, '0');
            const mes = String(hoje.getMonth() + 1).padStart(2, '0');
            const ano = hoje.getFullYear();
            const dataMinima = `${ano}-${mes}-${dia}`;
            
            dataInput.min = dataMinima;
            
            // Valida data selecionada
            dataInput.addEventListener('change', function() {
                const dataSelecionada = new Date(this.value);
                if (dataSelecionada < hoje) {
                    this.value = dataMinima;
                    alert('A data mínima é hoje!');
                }
            });
        });

        // Confirmação antes de enviar o formulário
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!confirm('Tem certeza que deseja confirmar esta reserva?')) {
                e.preventDefault();
                this.querySelector('button[type="submit"]').disabled = false;
            }
        });
    </script>
</body>
</html>

<?php
// Fecha a conexão
$conn->close();
?>
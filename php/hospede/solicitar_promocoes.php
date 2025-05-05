<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include "../conex.php";

$user_id = $_SESSION['user_id'];
$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_promocao'])) {
    $id_promocao = intval($_POST['id_promocao']);
    date_default_timezone_set('America/Sao_Paulo');
    $horaAtual = date("H:i:s");
    $dataAtual = date("Y-m-d H:i:s");

    // Verifica se a promoção está disponível e no horário válido
    $stmt = $conn->prepare("SELECT id_promocao, nome, preco_promocional, horario_comeca, horario_termina 
                          FROM promocoes_servicos 
                          WHERE id_promocao = ? AND disponivel = TRUE");
    $stmt->bind_param("i", $id_promocao);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($promo = $result->fetch_assoc()) {
        $preco = $promo['preco_promocional'];
        $horaInicio = $promo['horario_comeca'];
        $horaFim = $promo['horario_termina'];

        if ($horaAtual >= $horaInicio && $horaAtual <= $horaFim) {
            // Inicia transação para garantir integridade dos dados
            $conn->begin_transaction();
            
            try {
                // 1. Insere solicitação promocional
                $stmt_insert = $conn->prepare("INSERT INTO solicitacoes_servico (id_hos, id_promocao, data_solicitacao) 
                                             VALUES (?, ?, ?)");
                $stmt_insert->bind_param("iis", $user_id, $id_promocao, $dataAtual);
                $stmt_insert->execute();
                
                // 2. Atualiza gastos do hóspede
                $stmt_update = $conn->prepare("UPDATE hospede 
                                             SET gastos_atuais = gastos_atuais + ?, 
                                                 gastos_totais = gastos_totais + ? 
                                             WHERE id_hos = ?");
                $stmt_update->bind_param("ddi", $preco, $preco, $user_id);
                $stmt_update->execute();
                
                // Confirma a transação
                $conn->commit();
                
                $mensagem = "success|Serviço promocional solicitado com sucesso!";
            } catch (Exception $e) {
                // Em caso de erro, reverte a transação
                $conn->rollback();
                $mensagem = "danger|Erro ao processar solicitação: " . $e->getMessage();
            }
        } else {
            $mensagem = "warning|Serviço promocional indisponível neste horário. Funcionamento: " . 
                       substr($horaInicio, 0, 5) . " às " . substr($horaFim, 0, 5);
        }
    } else {
        $mensagem = "danger|Promoção não encontrada ou indisponível.";
    }
}

// Consulta promoções disponíveis
$sql = "SELECT * FROM promocoes_servicos WHERE disponivel = TRUE ORDER BY nome";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promoções de Serviços</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <img src="../../img/logo_hoteel.png" alt="">
    <style>
        img{
            width: 20rem;
            display: block;
            margin: 0 auto;
        }
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .promo-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .promo-header {
            font-size: 1.25rem;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 10px;
        }
        .price-tag {
            font-size: 1.1rem;
            font-weight: bold;
            color: #28a745;
        }
        .time-badge {
            background-color: #ffc107;
            color: #212529;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: 500;
        }
        .btn-promo {
            background-color: #fd7e14;
            color: white;
            font-weight: 500;
        }
        .btn-promo:hover {
            background-color: #e86109;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-percentage me-2"></i>Promoções de Serviços</h1>
            <a href="../dashboard.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Voltar
            </a>
        </div>

        <?php if (!empty($mensagem)): 
            list($tipo, $texto) = explode('|', $mensagem, 2); ?>
            <div class="alert alert-<?= $tipo ?> alert-dismissible fade show">
                <?= htmlspecialchars($texto) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="row">
                <?php while ($promo = $result->fetch_assoc()): ?>
                    <div class="col-md-6">
                        <div class="promo-card">
                            <div class="promo-header">
                                <i class="fas fa-tag me-2"></i><?= htmlspecialchars($promo['nome']) ?>
                            </div>
                            <div class="mb-2">
                                <span class="price-tag">
                                    <i class="fas fa-money-bill-wave me-1"></i>
                                    R$ <?= number_format($promo['preco_promocional'], 2, ',', '.') ?>
                                </span>
                            </div>
                            <div class="mb-3">
                                <span class="time-badge">
                                    <i class="fas fa-clock me-1"></i>
                                    <?= substr($promo['horario_comeca'], 0, 5) ?> às <?= substr($promo['horario_termina'], 0, 5) ?>
                                </span>
                            </div>
                            
                            <form method="post" class="mt-3">
                                <input type="hidden" name="id_promocao" value="<?= $promo['id_promocao'] ?>">
                                <button type="submit" class="btn btn-promo w-100">
                                    <i class="fas fa-hand-holding-usd me-1"></i> Solicitar Promoção
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> Nenhuma promoção disponível no momento.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confirmação antes de solicitar promoção
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('Tem certeza que deseja solicitar esta promoção?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
<?php
// Fechar conexão apenas no final do arquivo
$conn->close();
?>

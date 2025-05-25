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
                $stmt_insert = $conn->prepare("INSERT INTO solicitacoes_servico_promo (id_hos, id_promocao, data_solicitacao) 
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
    <style>
        :root {
            --color-primary: #c49a6c;
            --color-secondary: #6b392a;
            --color-light: #f5e7d8;
            --color-dark: #3a2618;
        }
        
        body {
            background: linear-gradient(180deg, rgba(107, 57, 42, 0.946) 6%, rgba(133,78,57,1) 18%, rgb(203, 164, 122) 60%, rgba(217, 192, 164, 0.339) 90%, rgba(255,255,255,1) 110%);
            font-family: 'Playfair Display', serif;
            padding: 20px;
            min-height: 100vh;
        }
        
        .logo {
            width: 20rem;
            display: block;
            margin: 0 auto 2rem;
            filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.3));
        }
        
        .container-main {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        
        .header-title {
            color: var(--color-secondary);
            font-weight: 700;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
            border-bottom: 2px solid var(--color-primary);
            padding-bottom: 10px;
            margin-bottom: 25px;
        }
        
        .promo-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            background-color: white;
        }
        
        .promo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .promo-header {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--color-secondary);
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--color-light);
        }
        
        .price-tag {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--color-primary);
        }
        
        .time-badge {
            background-color: var(--color-light);
            color: var(--color-secondary);
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 500;
            display: inline-block;
            margin: 5px 0;
        }
        
        .btn-promo {
            background-color: var(--color-primary);
            color: white;
            font-weight: 500;
            border: none;
            transition: all 0.3s;
        }
        
        .btn-promo:hover {
            background-color: var(--color-secondary);
            color: white;
            transform: translateY(-2px);
        }
        
        .alert {
            border-radius: 8px;
        }
        
        .btn-outline-primary {
            border-color: var(--color-primary);
            color: var(--color-primary);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--color-primary);
            color: white;
        }
        
        .icon {
            color: var(--color-primary);
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <img src="../../img/logo_hoteel.png" alt="Hotel Logo" class="logo">

    <div class="container container-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="header-title">
                <i class="fas fa-percentage me-2"></i>Promoções de Serviços
            </h1>
            <a href="../../html/hospede/tela_hospede.html" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Voltar
            </a>
        </div>

        <?php if (!empty($mensagem)): 
            list($tipo, $texto) = explode('|', $mensagem, 2); ?>
            <div class="alert alert-<?= $tipo ?> alert-dismissible fade show">
                <i class="fas <?= $tipo === 'success' ? 'fa-check-circle' : ($tipo === 'warning' ? 'fa-exclamation-triangle' : 'fa-times-circle') ?> me-2"></i>
                <?= htmlspecialchars($texto) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="row">
                <?php while ($promo = $result->fetch_assoc()): ?>
                    <div class="col-md-6">
                        <div class="promo-card">
                            <div class="card-body">
                                <div class="promo-header">
                                    <i class="fas fa-tag icon"></i><?= htmlspecialchars($promo['nome']) ?>
                                </div>
                                <div class="mb-3">
                                    <span class="price-tag">
                                        <i class="fas fa-money-bill-wave icon"></i>
                                        R$ <?= number_format($promo['preco_promocional'], 2, ',', '.') ?>
                                    </span>
                                </div>
                                <div class="mb-3">
                                    <span class="time-badge">
                                        <i class="fas fa-clock icon"></i>
                                        <?= substr($promo['horario_comeca'], 0, 5) ?> às <?= substr($promo['horario_termina'], 0, 5) ?>
                                    </span>
                                </div>
                                
                                <form method="post" class="mt-4">
                                    <input type="hidden" name="id_promocao" value="<?= $promo['id_promocao'] ?>">
                                    <button type="submit" class="btn btn-promo w-100">
                                        <i class="fas fa-hand-holding-usd me-2"></i> Solicitar Promoção
                                    </button>
                                </form>
                            </div>
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
$conn->close();
?>
<?php 
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hospede') {
    header("Location: ../login.php");
    exit();
}

include "../conex.php";

$user_id = $_SESSION['user_id'];
$mensagem = "";

// Processa solicitação de serviço
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_serv'])) {
    $id_servico = intval($_POST['id_serv']);
    date_default_timezone_set('America/Sao_Paulo');
    $horaAtual = date("H:i:s");

    // Pega os dados do serviço
    $stmt = $conn->prepare("SELECT preco, horario_comeca, horario_termina FROM servicos WHERE id_serv = ? AND disponivel = TRUE");
    $stmt->bind_param("i", $id_servico);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($servico = $result->fetch_assoc()) {
        $preco = $servico['preco'];
        $horaInicio = $servico['horario_comeca'];
        $horaFim = $servico['horario_termina'];

        // Verifica se está dentro do horário de atendimento
        if ($horaAtual >= $horaInicio && $horaAtual <= $horaFim) {
            // Inserir solicitação
            $stmt_insert = $conn->prepare("INSERT INTO solicitacoes_servico (id_hos, id_serv) VALUES (?, ?)");
            $stmt_insert->bind_param("ii", $user_id, $id_servico);
            $stmt_insert->execute();

            // Atualiza gastos
            $stmt_update = $conn->prepare("UPDATE hospede SET 
                gastos_atuais = gastos_atuais + ?, 
                gastos_totais = gastos_totais + ?
                WHERE id_hos = ?");
            $stmt_update->bind_param("ddi", $preco, $preco, $user_id);
            $stmt_update->execute();

            $mensagem = "success|Serviço solicitado com sucesso!";
        } else {
            $mensagem = "warning|Serviço indisponível neste horário. Funcionamento: ".substr($horaInicio, 0, 5)." às ".substr($horaFim, 0, 5);
        }
    } else {
        $mensagem = "danger|Serviço não encontrado ou indisponível.";
    }
}

// Consulta os serviços disponíveis
$sql = "SELECT * FROM servicos WHERE disponivel = TRUE ORDER BY nome";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serviços Disponíveis</title>
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
            min-height: 100vh;
            padding: 20px;
        }
        
        .logo {
            width: 20rem;
            display: block;
            margin: 0 auto 2rem;
            filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.3));
        }
        
        .container-main {
            max-width: 800px;
            margin: 0 auto;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        .header-title {
            color: var(--color-secondary);
            font-weight: 700;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
            border-bottom: 2px solid var(--color-primary);
            padding-bottom: 10px;
            margin-bottom: 25px;
        }
        
        .servico-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            background-color: white;
        }
        
        .servico-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .servico-header {
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
        
        .btn-primary {
            background-color: var(--color-primary);
            border-color: var(--color-primary);
            color: white;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: var(--color-secondary);
            border-color: var(--color-secondary);
            transform: translateY(-2px);
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
                <i class="fas fa-concierge-bell"></i> Serviços Disponíveis
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
                <?php while ($servico = $result->fetch_assoc()): ?>
                    <div class="col-12">
                        <div class="servico-card">
                            <div class="card-body">
                                <div class="servico-header">
                                    <i class="fas fa-<?= 
                                        strpos(strtolower($servico['nome']), 'massagem') !== false ? 'spa' : 
                                        (strpos(strtolower($servico['nome']), 'café') !== false ? 'coffee' : 
                                        'concierge-bell') 
                                    ?> icon"></i>
                                    <?= htmlspecialchars($servico['nome']) ?>
                                </div>
                                <div class="mb-3">
                                    <span class="price-tag">
                                        <i class="fas fa-money-bill-wave icon"></i>
                                        R$ <?= number_format($servico['preco'], 2, ',', '.') ?>
                                    </span>
                                </div>
                                <div class="mb-3">
                                    <span class="time-badge">
                                        <i class="fas fa-clock icon"></i>
                                        <?= substr($servico['horario_comeca'], 0, 5) ?> às <?= substr($servico['horario_termina'], 0, 5) ?>
                                    </span>
                                </div>
                                
                                <form method="post" class="mt-3" onsubmit="return confirm('Você deseja solicitar este serviço?');">
                                    <input type="hidden" name="id_serv" value="<?= $servico['id_serv'] ?>">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-hand-holding-usd me-1"></i> Solicitar Serviço
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> Nenhum serviço disponível no momento.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
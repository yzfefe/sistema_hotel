<?php
require_once '../check_session.php';

// Verificação específica para RECEPCIONISTA
if ($_SESSION['role'] != 'recepcionista') {
    header("Location: ../../html/login.html");
    exit();
}

include "../conex.php";
date_default_timezone_set('America/Sao_Paulo');

$mensagem = "";

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

    $hospede_id = $_POST['hospede_id'] ?? null;
    $salao_id = $_POST['salao_id'] ?? null;
    $data_inicio = $_POST['data_inicio'] ?? null;
    $decoracao_id = $_POST['decoracao_id'] ?? null;

    if (!$hospede_id || !$salao_id || !$data_inicio) {
        $mensagem = "danger|Preencha todos os campos obrigatórios!";
    } else {
        // Validação das datas
        $hoje = new DateTime('today');
        $dataInicioObj = new DateTime($data_inicio);
        
        if ($dataInicioObj < $hoje) {
            $mensagem = "danger|A data de início não pode ser no passado!";
        } else {
            // Verifica disponibilidade do salão
            $stmt = $conn->prepare("SELECT * FROM saloes WHERE id_salao = ? AND disponibilidade = 'Disponível'");
            $stmt->bind_param("i", $salao_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($salao = $result->fetch_assoc()) {
                // Inicia transação
                $conn->begin_transaction();
                try {
                    // 1. Insere a reserva (sem valor total)
                    $stmt = $conn->prepare("INSERT INTO reservas_salao (id_hos, id_salao, id_decoracao, data_inicio) 
                                        VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("iiis", $hospede_id, $salao_id, $decoracao_id, $data_inicio);
                    $stmt->execute();

                    // 2. Atualiza disponibilidade do salão
                    $stmt = $conn->prepare("UPDATE saloes SET disponibilidade = 'Indisponível' WHERE id_salao = ?");
                    $stmt->bind_param("i", $salao_id);
                    $stmt->execute();

                    $conn->commit();
                    $mensagem = "success|Reserva do salão realizada com sucesso!";
                } catch (Exception $e) {
                    $conn->rollback();
                    $mensagem = "danger|Erro ao reservar salão: " . $e->getMessage();
                }
            } else {
                $mensagem = "danger|Salão não disponível para reserva.";
            }
        }
    }
}
// Carrega salões disponíveis
$saloes = [];
$result = $conn->query("SELECT * FROM saloes WHERE disponibilidade = 'Disponível' ORDER BY nome");
if ($result) {
    $saloes = $result->fetch_all(MYSQLI_ASSOC);
}

// Carrega decorações
$decoracoes = [];
$result = $conn->query("SELECT * FROM decoracoes ORDER BY nome");
if ($result) {
    $decoracoes = $result->fetch_all(MYSQLI_ASSOC);
}

// Carrega hóspedes ativos
$hospedes = [];
$result = $conn->query("SELECT id_hos, nome, telefone FROM hospede WHERE status_atual = 'ATIVO' ORDER BY nome");
if ($result) {
    $hospedes = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar Salão de Festa</title>
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
        
        .card-salao {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            background-color: white;
        }
        
        .card-salao:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .card-img-top {
            height: 220px;
            object-fit: cover;
            border-bottom: 3px solid var(--color-primary);
        }
        
        .card-title {
            color: var(--color-secondary);
            font-weight: 600;
        }
        
        .btn-primary {
            background-color: var(--color-primary);
            border-color: var(--color-primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--color-secondary);
            border-color: var(--color-secondary);
        }
        
        .btn-outline-primary {
            border-color: var(--color-primary);
            color: var(--color-primary);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--color-primary);
            color: white;
        }
        
        .modal-header {
            background-color: var(--color-secondary);
            color: white;
        }
        
        .alert-info {
            background-color: var(--color-light);
            border-color: var(--color-primary);
            color: var(--color-dark);
        }
        
        .section-title {
            color: var(--color-secondary);
            position: relative;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 60px;
            height: 3px;
            background-color: var(--color-primary);
        }
        
        .badge {
            font-weight: 500;
            padding: 5px 10px;
        }
        
        .text-primary {
            color: var(--color-primary) !important;
        }
    </style>
</head>
<body>
    <div class="container container-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="header-title"><i class="fas fa-glass-cheers me-2"></i>Reservar Salão de Festa</h1>
            <a href="../../html/recepcionista/tela_recep.php" class="btn btn-outline-primary">
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

        <!-- Seção de Salões Disponíveis -->
        <div class="mb-5">
            <h3 class="section-title"><i class="far fa-calendar-check me-2"></i>Salões Disponíveis</h3>
            
            <div class="row">
                <?php if (!empty($saloes)): ?>
                    <?php foreach ($saloes as $salao): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card card-salao h-100">
                                <img src="img/saloes/<?= $salao['id_salao'] ?>.jpg" class="card-img-top" alt="<?= htmlspecialchars($salao['nome']) ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($salao['nome']) ?></h5>
                                    <p class="card-text">
                                        <i class="fas fa-users me-2 text-primary"></i>Capacidade: <?= $salao['capacidade'] ?> pessoas<br>
                                        <i class="fas fa-money-bill-wave me-2 text-primary"></i>R$ <?= number_format($salao['preco'], 2, ',', '.') ?> / dia
                                    </p>
                                </div>
                                <div class="card-footer bg-white border-0">
                                    <div class="d-flex justify-content-between">
                                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" 
                                                data-bs-target="#detalhesModal<?= $salao['id_salao'] ?>">
                                            <i class="fas fa-info-circle me-1"></i> Detalhes
                                        </button>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" 
                                                data-bs-target="#reservaModal<?= $salao['id_salao'] ?>">
                                            <i class="fas fa-calendar-plus me-1"></i> Reservar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal de Detalhes -->
                        <div class="modal fade" id="detalhesModal<?= $salao['id_salao'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><?= htmlspecialchars($salao['nome']) ?></h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <img src="img/saloes/<?= $salao['id_salao'] ?>.jpg" class="img-fluid rounded mb-4 w-100" alt="<?= htmlspecialchars($salao['nome']) ?>">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong><i class="fas fa-users me-2 text-primary"></i>Capacidade:</strong> <?= $salao['capacidade'] ?> pessoas</p>
                                                <p><strong><i class="fas fa-ruler-combined me-2 text-primary"></i>Tamanho:</strong> 150 m²</p>
                                                <p><strong><i class="fas fa-money-bill-wave me-2 text-primary"></i>Preço:</strong> R$ <?= number_format($salao['preco'], 2, ',', '.') ?> / dia</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong><i class="fas fa-star me-2 text-primary"></i>Características:</strong></p>
                                                <ul class="list-unstyled">
                                                    <li><i class="fas fa-check-circle text-primary me-2"></i>Palco integrado</li>
                                                    <li><i class="fas fa-check-circle text-primary me-2"></i>Sistema de som profissional</li>
                                                    <li><i class="fas fa-check-circle text-primary me-2"></i>Iluminação ambiente</li>
                                                    <li><i class="fas fa-check-circle text-primary me-2"></i>Cozinha anexa</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <h5><i class="fas fa-user-tie me-2 text-primary"></i>Responsáveis:</h5>
                                            <p><i class="fas fa-phone-alt me-2 text-primary"></i>Kelly Lyeger - (11) 11111-1111<br>
                                            <i class="fas fa-phone-alt me-2 text-primary"></i>Alex Gabriel - (11) 99999-9999</p>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" 
                                                data-bs-toggle="modal" data-bs-target="#reservaModal<?= $salao['id_salao'] ?>">
                                            <i class="fas fa-calendar-plus me-1"></i> Reservar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                         <!-- Modal de Reserva -->
                        <div class="modal fade" id="reservaModal<?= $salao['id_salao'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Reservar <?= htmlspecialchars($salao['nome']) ?></h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="POST" action="">
                                        <div class="modal-body">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                            <input type="hidden" name="salao_id" value="<?= $salao['id_salao'] ?>">
                                            
                                            <!-- Campos do formulário permanecem iguais -->
                                            <div class="mb-3">
                                                <label for="hospede_id<?= $salao['id_salao'] ?>" class="form-label">
                                                    <i class="fas fa-user me-2"></i>Hóspede
                                                </label>
                                                <select class="form-select" id="hospede_id<?= $salao['id_salao'] ?>" name="hospede_id" required>
                                                    <option value="">Selecione o hóspede</option>
                                                    <?php foreach ($hospedes as $hospede): ?>
                                                        <option value="<?= $hospede['id_hos'] ?>">
                                                            <?= htmlspecialchars($hospede['nome']) ?> - Tel: <?= htmlspecialchars($hospede['telefone']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <!-- Seletor de Decoração -->
                                            <div class="mb-3">
                                                <label for="decoracao_id<?= $salao['id_salao'] ?>" class="form-label">
                                                    <i class="fas fa-palette me-2"></i>Decoração (Opcional)
                                                </label>
                                                <select class="form-select" id="decoracao_id<?= $salao['id_salao'] ?>" name="decoracao_id">
                                                    <option value="">Nenhuma decoração</option>
                                                    <?php foreach ($decoracoes as $decoracao): ?>
                                                        <option value="<?= $decoracao['id_decoracao'] ?>" data-preco="<?= $decoracao['preco'] ?>">
                                                            <?= htmlspecialchars($decoracao['nome']) ?> (R$ <?= number_format($decoracao['preco'], 2, ',', '.') ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <!-- Data da Reserva -->
                                            <div class="mb-3">
                                                <label for="data_inicio<?= $salao['id_salao'] ?>" class="form-label">
                                                    <i class="fas fa-calendar-day me-2"></i>Data da Reserva
                                                </label>
                                                <input type="date" class="form-control" id="data_inicio<?= $salao['id_salao'] ?>" 
                                                    name="data_inicio" min="<?= date('Y-m-d') ?>" required>
                                            </div>
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i> 
                                                Valor base: R$ <?= number_format($salao['preco'], 2, ',', '.') ?> / dia
                                                <?php if($decoracao['preco'] ?? 0 > 0): ?>
                                                    <br>Decoração: R$ <?= number_format($decoracao['preco'] ?? 0, 2, ',', '.') ?>
                                                <?php endif; ?>
                                                <br><small>O valor final será calculado no check-out com base nos dias utilizados</small>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" name="reservar_salao" class="btn btn-primary">Confirmar Reserva</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Nenhum salão disponível no momento.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Seção de Decorações -->
        <div>
            <h3 class="section-title"><i class="fas fa-palette me-2"></i>Temas de Decoração</h3>
            
            <div class="row">
                <?php if (!empty($decoracoes)): ?>
                    <?php foreach ($decoracoes as $decoracao): ?>
                        <div class="col-md-4 col-lg-3 mb-4">
                            <div class="card card-salao h-100">
                                <img src="img/decoracoes/<?= $decoracao['id_decoracao'] ?>.jpg" class="card-img-top" alt="<?= htmlspecialchars($decoracao['nome']) ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($decoracao['nome']) ?></h5>
                                    <p class="card-text"><?= htmlspecialchars($decoracao['descricao']) ?></p>
                                    <p class="text-success fw-bold">R$ <?= number_format($decoracao['preco'], 2, ',', '.') ?></p>
                                </div>
                                <div class="card-footer bg-white border-0">
                                    <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" 
                                            data-bs-target="#detalhesDecoracao<?= $decoracao['id_decoracao'] ?>">
                                        <i class="fas fa-info-circle me-1"></i> Detalhes
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Modal de Detalhes da Decoração -->
                        <div class="modal fade" id="detalhesDecoracao<?= $decoracao['id_decoracao'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><?= htmlspecialchars($decoracao['nome']) ?></h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <img src="img/decoracoes/<?= $decoracao['id_decoracao'] ?>.jpg" class="img-fluid rounded mb-3 w-100" alt="<?= htmlspecialchars($decoracao['nome']) ?>">
                                        <p><?= htmlspecialchars($decoracao['descricao']) ?></p>
                                        <p><strong><i class="fas fa-money-bill-wave me-2 text-primary"></i>Preço:</strong> R$ <?= number_format($decoracao['preco'], 2, ',', '.') ?></p>
                                        <p><strong><i class="fas fa-check-circle me-2 text-primary"></i>Inclui:</strong></p>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-check text-primary me-2"></i>Decoração completa do ambiente</li>
                                            <li><i class="fas fa-check text-primary me-2"></i>Mesa de doces temática</li>
                                            <li><i class="fas fa-check text-primary me-2"></i>Arranjos de mesa personalizados</li>
                                            <li><i class="fas fa-check text-primary me-2"></i>Iluminação especial</li>
                                        </ul>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Nenhum tema de decoração disponível.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Atualiza o valor total quando a decoração é selecionada
        document.querySelectorAll('select[name="decoracao_id"]').forEach(select => {
            select.addEventListener('change', function() {
                const modal = this.closest('.modal');
                const precoSalao = <?= $salao['preco'] ?? 0 ?>;
                const precoDecoracao = this.selectedOptions[0]?.dataset.preco || 0;
                const total = parseFloat(precoSalao) + parseFloat(precoDecoracao);
                
                const alertElement = modal.querySelector('.alert-info');
                if (alertElement) {
                    let html = `<i class="fas fa-info-circle me-2"></i> Valor base: R$ ${precoSalao.toFixed(2).replace('.', ',')} / dia`;
                    if (precoDecoracao > 0) {
                        html += `<br>Decoração: R$ ${parseFloat(precoDecoracao).toFixed(2).replace('.', ',')}`;
                        html += `<br><strong>Total: R$ ${total.toFixed(2).replace('.', ',')} / dia</strong>`;
                    }
                    html += `<br><small>O valor final será calculado no check-out com base nos dias utilizados</small>`;
                    alertElement.innerHTML = html;
                }
            });
        });
    });
</script>
</body>
</html>

<?php
    $conn->close();
?>
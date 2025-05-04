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

            $mensagem = "Serviço solicitado com sucesso!";
        } else {
            $mensagem = "Serviço indisponível neste horário. Funcionamento: $horaInicio às $horaFim";
        }
    } else {
        $mensagem = "Serviço não encontrado ou indisponível.";
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
    <title>Serviços Disponíveis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            max-width: 800px;
            margin: 20px auto;
            font-family: Arial, sans-serif;
        }
        .servico-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
        }
        .servico-header {
            font-size: 1.2rem;
            font-weight: bold;
        }
    </style>
    <script>
        function confirmarSolicitacao(event, form) {
            event.preventDefault();
            if (confirm("Você deseja solicitar este serviço?")) {
                form.submit();
            }
        }
    </script>
</head>
<body>

    <h1 class="mb-4">Serviços Disponíveis</h1>

    <?php if ($mensagem): ?>
        <div class="alert alert-info"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($servico = $result->fetch_assoc()): ?>
            <div class="servico-card">
                <div class="servico-header">
                    <?= htmlspecialchars($servico['nome']) ?>
                </div>
                <p><strong>Preço:</strong> R$ <?= number_format($servico['preco'], 2, ',', '.') ?></p>
                <p><strong>Horário:</strong> <?= substr($servico['horario_comeca'], 0, 5) ?> às <?= substr($servico['horario_termina'], 0, 5) ?></p>
                
                <form method="post" onsubmit="confirmarSolicitacao(event, this);">
                    <input type="hidden" name="id_serv" value="<?= $servico['id_serv'] ?>">
                    <button type="submit" class="btn btn-sm btn-primary">Solicitar</button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">Nenhum serviço disponível no momento.</div>
    <?php endif; ?>

    <?php $conn->close(); ?>
</body>
</html>

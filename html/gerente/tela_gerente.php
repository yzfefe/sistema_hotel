<?php 
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}
 
// Verifica se é realmente um gerente
if ($_SESSION['role'] !== 'gerente') {
    header("Location: ../login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caminho das Pedras - Rustic Hotel</title>
    <link rel="stylesheet" href="../../css/gerente/tela_gerente.css">
</head>
<body>
    <?php if (isset($_SESSION['user_name'])): ?>
        <h2 class="welcome-message">Seja bem-vindo(a), <?= htmlspecialchars($_SESSION['user_name']) ?></h2>
    <?php else: ?>
        <h2 style="color:red;">Erro: Nome do usuário não encontrado!</h2>
    <?php endif; ?>
    
    <img src="../../img/logo_hoteel.png" alt="Caminho das Pedras - Rustic Hotel">

    <!-- Botões (mantidos iguais) -->
    <div><button class="btn-serv" onclick="red1()">CADASTRAR SERVIÇO</button></div>
    <div><button class="btn-cad" onclick="red2()">CADASTRAR RECEPCIONISTA</button></div> 
    <div><button class="btn-prom-quarto" onclick="red3()">PROMOÇÃO QUARTOS</button></div>
    <div><button class="btn-quarto" onclick="red4()">CADASTRAR QUARTO</button></div>
    <div><button class="btn-relat" onclick="red5()">RELATÓRIOS</button></div>
    <div><button class="btn-prom-servico" onclick="red6()">PROMOÇÃO SERVIÇOS</button></div>
    <div><button class="btn-consult" onclick="red7()">CONSULTAR RECEPCIONISTA</button></div>
    <div><button class="btn-exc" onclick="red8()">EXCLUIR OU ALTERAR RESERVA</button></div>
    <div><button class="btn-list" onclick="red9()">LISTAR QUARTOS MAIS USADOS</button></div>
    <div><button class="btn-exc2" onclick="red10()">EXCLUIR OU ALTERAR DADOS DE HÓSPEDES</button></div>
    <div><button class="btn-sair" onclick="red()">SAIR</button></div>

    <script>
       function red1(){ window.location.href = "http://localhost/sistema_hotel-main/html/gerente/cadastrar_servicos.html" }
        function red2(){ window.location.href = "http://localhost/sistema_hotel-main/html/gerente/registrar_recep.html" }
        function red3(){ window.location.href = "http://localhost/sistema_hotel-main/php/gerente/cadastrar_quartos_promocoes.php" }
        function red4(){ window.location.href = "http://localhost/sistema_hotel-main/html/gerente/cadastrar_quartos.html" }
        function red5(){ window.location.href = "http://localhost/sistema_hotel-main/php/gerente/relatorios_tela.php" }
        function red6(){ window.location.href = "http://localhost/sistema_hotel-main/php/gerente/cadastrar_servicos_promocoes.php" }
        function red7(){ window.location.href = "http://localhost/sistema_hotel-main/php/gerente/consultar_recepcionista.php" }
        function red8(){ window.location.href = "http://localhost/sistema_hotel-main/php/gerente/excluir_alterar_reserva.php" }
        function red9(){ window.location.href = "http://localhost/sistema_hotel-main/php/gerente/quartos_mais_usados.php" }
        function red10(){ window.location.href = "http://localhost/sistema_hotel-main/php/gerente/excluir_alterar_hospede.php" }
        function red(){ window.location.href = "http://localhost/sistema_hotel-main/html/login.html" }
    </script>
</body>
</html>

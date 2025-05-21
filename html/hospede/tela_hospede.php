<?php
    session_start();
    include "../../php/conex.php";

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hospede') {
        header("Location: ../login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];  

    $query = "SELECT nome FROM hospede WHERE id_hos = $user_id";
    $result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caminho das Pedras - Rustic Hotel</title>
    <link rel="stylesheet" href="../../css/hospede/tela_hospede.css">
</head>
<body>
    <?php if ($result->num_rows > 0):?>
        <?php while ($quarto = $result->fetch_assoc()): ?>
            <h2>Seja bem vindo(a) <?= htmlspecialchars($quarto['nome'])?></h2>
        <?php endwhile?>
    <?php endif;?>
    
    
    <img src="../../img/logo_hoteel.png" alt="Caminho das Pedras - Rustic Hotel">
    
   
    <button class="btn-serv" onclick="red1()">SERVIÇOS DE QUARTO</button>  
    <button class="btn-reserv" onclick="red3()">RESERVAR SALÃO</button>  

    <div>
        <button class="btn-sol_serv" onclick="red4()">SOLICITAR SERVIÇOS EM PROMOÇÃO</button>
    </div>
    <button class="btn-sair" onclick="red2()">SAIR</button>  

    <script>
        function red1(){
            window.location.href = "http://localhost/sistema_hotel-main/php/hospede/solicitar_servico.php"
        }
        function red2(){
            window.location.href = "http://localhost/sistema_hotel-main/html/login.html"
        }
        function red3(){
            window.location.href = "http://localhost/sistema_hotel-main/php/hospede/alugar_salao.php"
        }

        function red4(){
            window.location.href = "http://localhost/sistema_hotel-main/php/hospede/solicitar_promocoes.php"
        }
    </script>    
</body>
</html>
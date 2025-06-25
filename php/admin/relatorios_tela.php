<?php 
  require_once '../check_session.php';

  if ($_SESSION['role'] != 'administrador') {
      header("Location: ../../html/login.html");
      exit();
  }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Relatórios do Hotel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="shortcut icon" type="imagex/png" href="../../img/aba.png">
  <style>
  body {
    font-family: Arial, sans-serif;
    padding: 30px;
    margin: 0;
    background: linear-gradient(180deg, rgba(107, 57, 42, 0.946) 6%, rgba(133, 78, 57, 1) 18%, rgb(203, 164, 122) 60%, rgba(217, 192, 164, 0.339) 90%, rgba(255,255,255,1) 110%);
    background-attachment: fixed;
    min-height: 100vh;
  }

  h1 {
    color: #4a2f1e;
    text-align: center;
    margin-bottom: 2rem;
  }

  .logo {
    width: 20rem;
    display: block;
    margin: 0 auto 2rem auto;
    filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.3));
  }

  form {
    margin-bottom: 15px;
    text-align: center;
  }

  button {
    padding: 14px 28px;
    font-size: 18px;
    font-weight: 600;
    background: linear-gradient(135deg, #6b392a, #8c4e39);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    box-shadow: 0 4px 8px rgba(107, 57, 42, 0.4);
    transition: background 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease;
  }

  button:hover {
    background: linear-gradient(135deg, #8c4e39, #a86249);
    box-shadow: 0 6px 14px rgba(107, 57, 42, 0.6);
    transform: translateY(-2px);
  }

  .btn-outline-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #6b392a;
    border: 2px solid #6b392a;
    background-color: transparent;
    padding: 10px 22px;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
    box-shadow: none;
    font-size: 16px;
    margin-bottom: 1rem;
  }

  .btn-outline-primary:hover {
    background-color: #6b392a;
    color: white;
    box-shadow: 0 6px 14px rgba(107, 57, 42, 0.6);
    transform: translateY(-2px);
  }

  .container {
    background-color: rgba(255, 255, 255, 0.96);
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    padding: 30px;
    max-width: 600px;
    margin: auto;
    margin-top: 2rem;
    margin-bottom: 2rem;
    border: 1px solid rgba(203, 164, 122, 0.3);
    position: relative;
  }
</style>
</head>

<body>
  <div class="container">
    <a href="../../html/adm/tela_adm.php" class="btn-outline-primary">
      <i class="bi bi-arrow-left"></i> Voltar
    </a>

    <img src="../../img/logo_hoteel.png" alt="Logo do Hotel" class="logo">
    <h1>Relatórios do Sistema</h1>

    <form action="relatorios/relatorio_reservas_mes.php" method="post" target="_blank">
      <button type="submit">📅 Reservas por Mês</button>
    </form>

    <form action="relatorios/relatorio_servicos.php" method="post" target="_blank">
      <button type="submit">🛎️ Serviços Mais/Menos Solicitados</button>
    </form>

    <form action="relatorios/relatorio_clientes_antigos.php" method="post" target="_blank">
      <button type="submit">👴 Clientes Mais Antigos</button>
    </form>

    <form action="relatorios/relatorio_quartos.php" method="post" target="_blank">
      <button type="submit">🛏️ Quartos Mais Solicitados</button>
    </form>

    <form action="relatorios/relatorio_duracao_estadia.php" method="post" target="_blank">
      <button type="submit">⏳ Duração Média da Estadia</button>
    </form>

    <form action="relatorios/relatorio_tipo_quarto.php" method="post" target="_blank">
      <button type="submit">🏨 Reservas por Tipo de Quarto</button>
    </form>
  </div>
</body>
</html>
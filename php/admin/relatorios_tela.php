<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Relatórios do Hotel</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 30px;
      background: #f9f9f9;
    }
    h1 {
      color: #333;
    }
    form {
      margin-bottom: 15px;
    }
    button {
      padding: 12px 24px;
      font-size: 16px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    button:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>
  <h1>Relatórios do Sistema Hoteleiro</h1>

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

  <form action="relatorios/relatorio_faixa_etaria.php" method="post" target="_blank">
    <button type="submit">📊 Hóspedes por Faixa Etária</button>
  </form>

  <form action="gerar_pdf_geral.php" method="post" target="_blank">
    <button type="submit">📄 Relatório Geral (Todos)</button>
  </form>
</body>
</html>

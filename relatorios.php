<?php
session_start();
require_once 'config.php';

// Verifica autenticação
/*
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
*/
// Consultas para relatórios
try {
    // Métricas Principais
    $stmtTotalBens = $pdo->query("SELECT COUNT(*) AS total FROM bem");
    $totalBens = $stmtTotalBens->fetch()['total'];

    $stmtValorTotal = $pdo->query("SELECT SUM(valor_aquisicao) AS total FROM bem");
    $valorTotal = $stmtValorTotal->fetch()['total'];

    $stmtManutencao = $pdo->query("SELECT SUM(custo) AS total FROM manutencao");
    $totalManutencao = $stmtManutencao->fetch()['total'];

    $stmtDepreciacao = $pdo->query("
        SELECT SUM(valor_aquisicao * porcentagem_depreciacao / 100) AS total 
        FROM bem
    ");
    $totalDepreciacao = $stmtDepreciacao->fetch()['total'];

    // Dados para gráficos
    $stmtCategorias = $pdo->query("
        SELECT c.nome, COUNT(b.id_bem) AS total 
        FROM categoria c
        LEFT JOIN bem b ON c.id_categoria = b.id_categoria
        GROUP BY c.nome
    ");
    $categoriasData = $stmtCategorias->fetchAll();

    $stmtLocalizacao = $pdo->query("
        SELECT s.nome AS setor, COUNT(b.id_bem) AS total
        FROM setor s
        LEFT JOIN bem b ON s.id_setor = b.id_setor
        GROUP BY s.nome
        ORDER BY total DESC
        LIMIT 5
    ");
    $localizacaoData = $stmtLocalizacao->fetchAll();

    // Preparar dados para gráficos
    $chartCategoriasLabels = [];
    $chartCategoriasValues = [];
    foreach ($categoriasData as $item) {
        $chartCategoriasLabels[] = $item['nome'];
        $chartCategoriasValues[] = $item['total'];
    }

    $chartLocalizacaoLabels = [];
    $chartLocalizacaoValues = [];
    foreach ($localizacaoData as $item) {
        $chartLocalizacaoLabels[] = $item['setor'];
        $chartLocalizacaoValues[] = $item['total'];
    }

} catch (PDOException $e) {
    die("Erro ao gerar relatórios: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - TagTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            height: 400px;
            margin: 20px 0;
        }
        .metric-card {
            transition: transform 0.2s;
            min-height: 150px;
        }
        .metric-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">TagTrack</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="relatorios.php">Relatórios</a>
                    </li>
                </ul>
                <a href="logout.php" class="btn btn-outline-light">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <h1 class="mb-4">Relatórios Gerenciais</h1>
        
        <!-- Métricas Rápidas -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-4 mb-4">
            <div class="col">
                <div class="card metric-card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Total de Bens</h5>
                        <h2 class="card-text"><?= number_format($totalBens, 0, ',', '.') ?></h2>
                    </div>
                </div>
            </div>
            
            <div class="col">
                <div class="card metric-card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Valor Total</h5>
                        <h2 class="card-text">R$ <?= number_format($valorTotal, 2, ',', '.') ?></h2>
                    </div>
                </div>
            </div>
            
            <div class="col">
                <div class="card metric-card text-white bg-danger">
                    <div class="card-body">
                        <h5 class="card-title">Custo Manutenções</h5>
                        <h2 class="card-text">R$ <?= number_format($totalManutencao, 2, ',', '.') ?></h2>
                    </div>
                </div>
            </div>
            
            <div class="col">
                <div class="card metric-card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title">Depreciação Total</h5>
                        <h2 class="card-text">R$ <?= number_format($totalDepreciacao, 2, ',', '.') ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Distribuição por Categoria</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="categoriasChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Top 5 Setores</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="localizacaoChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Detalhes -->
        <div class="card shadow-sm mt-4">
            <div class="card-header">
                <h5 class="mb-0">Detalhes por Categoria</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Categoria</th>
                                <th>Total de Bens</th>
                                <th>Porcentagem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categoriasData as $categoria): ?>
                            <tr>
                                <td><?= htmlspecialchars($categoria['nome']) ?></td>
                                <td><?= $categoria['total'] ?></td>
                                <td>
                                    <?= number_format(($categoria['total'] / $totalBens) * 100, 2) ?>%
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Gráfico de Pizza - Categorias
        new Chart(document.getElementById('categoriasChart'), {
            type: 'pie',
            data: {
                labels: <?= json_encode($chartCategoriasLabels) ?>,
                datasets: [{
                    data: <?= json_encode($chartCategoriasValues) ?>,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                        '#9966FF', '#FF9F40', '#E7E9ED'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.formattedValue} bens`;
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de Barras - Localização
        new Chart(document.getElementById('localizacaoChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartLocalizacaoLabels) ?>,
                datasets: [{
                    label: 'Bens por Setor',
                    data: <?= json_encode($chartLocalizacaoValues) ?>,
                    backgroundColor: '#36A2EB'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
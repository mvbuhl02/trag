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
// Consultas para as métricas
try {
    // Total de Bens
    $stmtBens = $pdo->query("SELECT COUNT(*) AS total FROM bem");
    $totalBens = $stmtBens->fetch()['total'];

    // Total de Categorias
    $stmtCategorias = $pdo->query("SELECT COUNT(*) AS total FROM categoria");
    $totalCategorias = $stmtCategorias->fetch()['total'];

    // Total de Setores
    $stmtSetores = $pdo->query("SELECT COUNT(*) AS total FROM setor");
    $totalSetores = $stmtSetores->fetch()['total'];

    // Total de Usuários
    $stmtUsuarios = $pdo->query("SELECT COUNT(*) AS total FROM usuario");
    $totalUsuarios = $stmtUsuarios->fetch()['total'];

    // Últimos Bens Adicionados (com ID para links)
    $stmtUltimosBens = $pdo->query("
        SELECT b.id_bem, b.nome, c.nome AS categoria, s.nome AS setor, b.data_adicao 
        FROM bem b
        LEFT JOIN categoria c ON b.id_categoria = c.id_categoria
        LEFT JOIN setor s ON b.id_setor = s.id_setor
        ORDER BY b.data_adicao DESC LIMIT 5
    ");
    $ultimosBens = $stmtUltimosBens->fetchAll();

} catch (PDOException $e) {
    // Trate erros conforme necessário
    $totalBens = $totalCategorias = $totalSetores = $totalUsuarios = 0;
    $ultimosBens = [];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TagTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .clickable-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .clickable-row:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">TagTrack</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bens.php">Bens</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="usuarios.php">Usuários</a>
                    </li>
                </ul>
                <a href="logout.php" class="btn btn-outline-light">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <h1 class="mb-4">Dashboard</h1>
        
        <!-- Métricas -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Total de Bens</h5>
                        <p class="display-4"><?= $totalBens ?></p>
                        <a href="bens.php" class="btn btn-primary btn-sm">Ver Todos</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-success">Categorias</h5>
                        <p class="display-4"><?= $totalCategorias ?></p>
                        <a href="categorias.php" class="btn btn-success btn-sm">Gerenciar</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-warning">Setores</h5>
                        <p class="display-4"><?= $totalSetores ?></p>
                        <a href="setores.php" class="btn btn-warning btn-sm">Visualizar</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-info">Usuários</h5>
                        <p class="display-4"><?= $totalUsuarios ?></p>
                        <a href="usuarios.php" class="btn btn-info btn-sm">Listar</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ações Rápidas -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Ações Rápidas</h5>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="cadastrar-bem.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Cadastrar Bem
                            </a>
                            <a href="realocacoes.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left-right"></i> Realocações
                            </a>
                            <a href="manutencoes.php" class="btn btn-warning">
                                <i class="bi bi-tools"></i> Manutenções
                            </a>
                            <a href="relatorios.php" class="btn btn-success">
                                <i class="bi bi-file-earmark-bar-graph"></i> Relatórios
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Últimos Bens Adicionados -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-4">Últimos Bens Registrados</h5>
                
                <?php if (!empty($ultimosBens)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Categoria</th>
                                    <th>Setor</th>
                                    <th>Data de Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimosBens as $bem): ?>
                                    <tr class="clickable-row" 
                                        onclick="window.location='Detalhes_Bem.php?id=<?= $bem['id_bem'] ?>'">
                                        <td><?= htmlspecialchars($bem['nome']) ?></td>
                                        <td><?= htmlspecialchars($bem['categoria']) ?></td>
                                        <td><?= htmlspecialchars($bem['setor']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($bem['data_adicao'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">Nenhum bem registrado recentemente.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
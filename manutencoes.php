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
// Consulta manutenções
try {
    $sql = "
        SELECT m.*, 
               b.nome AS bem_nome,
               f.razao_social AS fornecedor,
               u.nome AS responsavel
        FROM manutencao m
        LEFT JOIN bem b ON m.id_bem = b.id_bem
        LEFT JOIN fornecedor f ON m.id_fornecedor = f.id_fornecedor
        LEFT JOIN usuario u ON m.id_usuario_responsavel = u.id_usuario
    ";

    $filtros = [];
    $params = [];

    // Filtro por status
    if (isset($_GET['status']) && in_array($_GET['status'], [1, 2, 3])) {
        $filtros[] = "m.status = ?";
        $params[] = $_GET['status'];
    }

    // Filtro por período
    if (isset($_GET['data_inicio']) && isset($_GET['data_fim'])) {
        $filtros[] = "m.data BETWEEN ? AND ?";
        $params[] = $_GET['data_inicio'];
        $params[] = $_GET['data_fim'];
    }

    if (!empty($filtros)) {
        $sql .= " WHERE " . implode(" AND ", $filtros);
    }

    $sql .= " ORDER BY m.data DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $manutencoes = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Erro ao buscar manutenções: " . $e->getMessage());
}

// Status disponíveis
$statusList = [
    1 => 'Agendada',
    2 => 'Em Andamento',
    3 => 'Concluída'
];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manutenções - TagTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .status-badge {
            padding: 0.35em 0.65em;
            border-radius: 1rem;
            font-size: 0.875em;
        }
        .table-hover tbody tr {
            transition: transform 0.2s;
        }
        .table-hover tbody tr:hover {
            transform: translateX(5px);
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">TagTrack</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="manutencoes.php">Manutenções</a>
                    </li>
                </ul>
                <a href="logout.php" class="btn btn-outline-light">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manutenções</h1>
            <a href="cadastrar-manutencao.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nova Manutenção
            </a>
        </div>

        <!-- Filtros -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="get" class="row g-3">
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Todos os Status</option>
                            <?php foreach ($statusList as $key => $value): ?>
                            <option value="<?= $key ?>" <?= isset($_GET['status']) && $_GET['status'] == $key ? 'selected' : '' ?>>
                                <?= $value ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <input type="date" name="data_inicio" class="form-control" 
                               value="<?= $_GET['data_inicio'] ?? '' ?>" 
                               placeholder="Data Início">
                    </div>
                    
                    <div class="col-md-3">
                        <input type="date" name="data_fim" class="form-control"
                               value="<?= $_GET['data_fim'] ?? '' ?>"
                               placeholder="Data Fim">
                    </div>
                    
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-funnel"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabela de Manutenções -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Bem</th>
                                <th>Tipo</th>
                                <th>Data</th>
                                <th>Status</th>
                                <th>Custo</th>
                                <th>Responsável</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($manutencoes as $m): ?>
                            <tr>
                                <td><?= htmlspecialchars($m['bem_nome']) ?></td>
                                <td><?= htmlspecialchars($m['tipo']) ?></td>
                                <td>
                                    <?= date('d/m/Y', strtotime($m['data'])) ?>
                                    <?php if ($m['data_finalizacao']): ?>
                                        <br><small class="text-muted">Final: <?= date('d/m/Y', strtotime($m['data_finalizacao'])) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge 
                                        <?= match($m['status']) {
                                            1 => 'bg-warning text-dark',
                                            2 => 'bg-info text-white',
                                            3 => 'bg-success text-white'
                                        } ?>">
                                        <?= $statusList[$m['status']] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($m['custo']): ?>
                                        R$ <?= number_format($m['custo'], 2, ',', '.') ?>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($m['responsavel']) ?? 'N/A' ?></td>
                                <td>
                                    <a href="editar-manutencao.php?id=<?= $m['id_manutencao'] ?>" 
                                       class="btn btn-sm btn-outline-secondary"
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="detalhes-manutencao.php?id=<?= $m['id_manutencao'] ?>" 
                                       class="btn btn-sm btn-outline-primary"
                                       title="Detalhes">
                                        <i class="bi bi-info-circle"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
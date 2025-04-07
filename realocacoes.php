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
// Consulta realocações com filtros
try {
    $sql = "
        SELECT r.*, 
               b.nome AS bem_nome,
               so.nome AS setor_origem,
               sd.nome AS setor_destino,
               u.nome AS operador
        FROM realocacao r
        JOIN bem b ON r.id_bem = b.id_bem
        JOIN setor so ON r.id_setor_origem = so.id_setor
        JOIN setor sd ON r.id_setor_destino = sd.id_setor
        LEFT JOIN usuario u ON r.id_operador = u.id_usuario
    ";

    $filtros = [];
    $params = [];

    // Filtro por data
    if (isset($_GET['data_inicio']) && isset($_GET['data_fim'])) {
        $filtros[] = "r.data_transferencia BETWEEN ? AND ?";
        $params[] = $_GET['data_inicio'];
        $params[] = $_GET['data_fim'];
    }

    // Filtro por setor de origem
    if (isset($_GET['origem']) && is_numeric($_GET['origem'])) {
        $filtros[] = "r.id_setor_origem = ?";
        $params[] = $_GET['origem'];
    }

    // Filtro por setor de destino
    if (isset($_GET['destino']) && is_numeric($_GET['destino'])) {
        $filtros[] = "r.id_setor_destino = ?";
        $params[] = $_GET['destino'];
    }

    // Filtro por nome do bem
    if (isset($_GET['busca']) && !empty($_GET['busca'])) {
        $filtros[] = "b.nome LIKE ?";
        $params[] = '%' . $_GET['busca'] . '%';
    }

    if (!empty($filtros)) {
        $sql .= " WHERE " . implode(" AND ", $filtros);
    }

    $sql .= " ORDER BY r.data_transferencia DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $realizacoes = $stmt->fetchAll();

    // Busca setores para filtros
    $stmtSetores = $pdo->query("SELECT id_setor, nome FROM setor ORDER BY nome");
    $setores = $stmtSetores->fetchAll();

} catch (PDOException $e) {
    die("Erro ao buscar realocações: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realocações - TagTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.2s;
        }
        .badge-transferencia {
            background-color: #6c757d;
            color: white;
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
                        <a class="nav-link active" href="realizacoes.php">Realocações</a>
                    </li>
                </ul>
                <a href="logout.php" class="btn btn-outline-light">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Realocações</h1>
            <a href="cadastrar-realocacao.php" class="btn btn-primary">
                <i class="bi bi-arrow-repeat"></i> Nova Realocação
            </a>
        </div>

        <!-- Filtros -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="get" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="busca" class="form-control" 
                               placeholder="Buscar por bem..."
                               value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <select name="origem" class="form-select">
                            <option value="">Setor Origem</option>
                            <?php foreach ($setores as $setor): ?>
                            <option value="<?= $setor['id_setor'] ?>" 
                                <?= isset($_GET['origem']) && $_GET['origem'] == $setor['id_setor'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($setor['nome']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <select name="destino" class="form-select">
                            <option value="">Setor Destino</option>
                            <?php foreach ($setores as $setor): ?>
                            <option value="<?= $setor['id_setor'] ?>" 
                                <?= isset($_GET['destino']) && $_GET['destino'] == $setor['id_setor'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($setor['nome']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="input-group">
                            <input type="date" name="data_inicio" class="form-control" 
                                   value="<?= htmlspecialchars($_GET['data_inicio'] ?? '') ?>">
                            <input type="date" name="data_fim" class="form-control" 
                                   value="<?= htmlspecialchars($_GET['data_fim'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-funnel"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabela -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Bem</th>
                                <th>Origem</th>
                                <th>Destino</th>
                                <th>Data</th>
                                <th>Operador</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($realizacoes)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        Nenhuma realocação encontrada
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($realizacoes as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['bem_nome']) ?></td>
                                    <td>
                                        <span class="badge badge-transferencia">
                                            <?= htmlspecialchars($r['setor_origem']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            <?= htmlspecialchars($r['setor_destino']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($r['data_transferencia'])) ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('H:i', strtotime($r['data_transferencia'])) ?>
                                        </small>
                                    </td>
                                    <td><?= htmlspecialchars($r['operador'] ?? 'Sistema') ?></td>
                                    <td>
                                        <a href="detalhes-realocacao.php?id=<?= $r['id_realocacao'] ?>" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="Detalhes">
                                            <i class="bi bi-info-circle"></i>
                                        </a>
                                        <a href="editar-realocacao.php?id=<?= $r['id_realocacao'] ?>" 
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
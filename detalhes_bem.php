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
// Verifica se o ID do bem foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: bens.php");
    exit();
}

$idBem = $_GET['id'];

try {
    // Busca os dados do bem
    $stmtBem = $pdo->prepare("
        SELECT b.*, 
               c.nome AS categoria_nome,
               s.nome AS setor_nome,
               u.nome AS usuario_nome
        FROM bem b
        LEFT JOIN categoria c ON b.id_categoria = c.id_categoria
        LEFT JOIN setor s ON b.id_setor = s.id_setor
        LEFT JOIN usuario u ON b.id_usuario = u.id_usuario
        WHERE b.id_bem = ?
    ");
    $stmtBem->execute([$idBem]);
    $bem = $stmtBem->fetch();

    if (!$bem) {
        throw new Exception("Bem não encontrado");
    }

    // Calcula valor atual com depreciação
    $valorAtual = $bem['valor_aquisicao'];
    if ($bem['porcentagem_depreciacao'] && $bem['valor_aquisicao']) {
        $depreciacao = ($bem['valor_aquisicao'] * $bem['porcentagem_depreciacao']) / 100;
        $valorAtual = $bem['valor_aquisicao'] - $depreciacao;
    }

    // Busca histórico do bem
    $stmtHistorico = $pdo->prepare("
        SELECT h.*, u.nome AS operador
        FROM historico h
        LEFT JOIN usuario u ON h.id_usuario = u.id_usuario
        WHERE h.id_bem = ?
        ORDER BY h.data_evento DESC
    ");
    $stmtHistorico->execute([$idBem]);
    $historico = $stmtHistorico->fetchAll();

} catch (Exception $e) {
    die("Erro: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Bem - TagTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navbar (igual ao dashboard) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">TagTrack</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bens.php">Bens</a>
                    </li>
                </ul>
                <a href="logout.php" class="btn btn-outline-light">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="bens.php">Bens</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($bem['nome']) ?></li>
            </ol>
        </nav>

        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Detalhes do Bem</h4>
                <div class="btn-group">
                    <a href="editar-bem.php?id=<?= $idBem ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Nome:</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($bem['nome']) ?></dd>

                            <dt class="col-sm-4">Descrição:</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($bem['descricao']) ?? 'N/A' ?></dd>

                            <dt class="col-sm-4">Número Série:</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($bem['numero_serie']) ?? 'N/A' ?></dd>

                            <dt class="col-sm-4">Data Aquisição:</dt>
                            <dd class="col-sm-8"><?= date('d/m/Y', strtotime($bem['data_aquisicao'])) ?></dd>
                        </dl>
                    </div>

                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Valor Aquisição:</dt>
                            <dd class="col-sm-8">R$ <?= number_format($bem['valor_aquisicao'], 2, ',', '.') ?></dd>

                            <dt class="col-sm-4">Valor Atual:</dt>
                            <dd class="col-sm-8">R$ <?= number_format($valorAtual, 2, ',', '.') ?></dd>

                            <dt class="col-sm-4">Depreciação:</dt>
                            <dd class="col-sm-8"><?= $bem['porcentagem_depreciacao'] ?? 0 ?>%</dd>

                            <dt class="col-sm-4">Categoria:</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($bem['categoria_nome']) ?? 'N/A' ?></dd>

                            <dt class="col-sm-4">Setor:</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($bem['setor_nome']) ?? 'N/A' ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ações -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">Ações</h5>
            </div>
            <div class="card-body">
                <div class="d-flex gap-2 flex-wrap">
                    <a href="realocar-bem.php?id=<?= $idBem ?>" class="btn btn-warning">
                        <i class="bi bi-arrow-repeat"></i> Realocar
                    </a>
                    <a href="manutencao.php?id=<?= $idBem ?>" class="btn btn-secondary">
                        <i class="bi bi-tools"></i> Registrar Manutenção
                    </a>
                    <a href="baixa.php?id=<?= $idBem ?>" class="btn btn-danger">
                        <i class="bi bi-x-circle"></i> Dar Baixa
                    </a>
                </div>
            </div>
        </div>

        <!-- Histórico -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Histórico</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($historico)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                    <th>Operador</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historico as $evento): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($evento['data_evento'])) ?></td>
                                        <td><?= htmlspecialchars($evento['tipo_evento']) ?></td>
                                        <td><?= htmlspecialchars($evento['descricao']) ?></td>
                                        <td><?= htmlspecialchars($evento['operador']) ?? 'Sistema' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">Nenhum evento registrado para este bem.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
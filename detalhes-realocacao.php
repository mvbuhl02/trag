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
// Verifica se o ID foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: realocacoes.php");
    exit();
}

$idRealocacao = $_GET['id'];

try {
    // Busca dados detalhados da realocação
    $stmt = $pdo->prepare("
        SELECT 
            r.*,
            b.nome AS bem_nome,
            b.numero_serie,
            so.nome AS setor_origem,
            sd.nome AS setor_destino,
            u.nome AS operador_nome,
            u.email AS operador_email
        FROM realocacao r
        JOIN bem b ON r.id_bem = b.id_bem
        JOIN setor so ON r.id_setor_origem = so.id_setor
        JOIN setor sd ON r.id_setor_destino = sd.id_setor
        LEFT JOIN usuario u ON r.id_operador = u.id_usuario
        WHERE r.id_realocacao = ?
    ");
    $stmt->execute([$idRealocacao]);
    $realocacao = $stmt->fetch();

    if (!$realocacao) {
        throw new Exception("Realocação não encontrada");
    }

} catch (PDOException $e) {
    die("Erro ao buscar detalhes: " . $e->getMessage());
} catch (Exception $e) {
    die($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Realocação - TagTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .info-card {
            border-left: 4px solid #0d6efd;
            background-color: #f8f9fa;
        }
        .badge-origem {
            background-color: #6c757d;
            color: white;
        }
        .badge-destino {
            background-color: #198754;
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
                        <a class="nav-link active" href="realocacoes.php">Realocações</a>
                    </li>
                </ul>
                <a href="logout.php" class="btn btn-outline-light">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Detalhes da Realocação</h1>
            <div class="btn-group">
                <a href="realocacoes.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <a href="editar-realocacao.php?id=<?= $idRealocacao ?>" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Editar
                </a>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-4">
                    <!-- Bem -->
                    <div class="col-md-6">
                        <div class="card info-card h-100">
                            <div class="card-body">
                                <h5 class="card-title mb-3">
                                    <i class="bi bi-box-seam"></i> Bem Movimentado
                                </h5>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Nome:</dt>
                                    <dd class="col-sm-8"><?= htmlspecialchars($realocacao['bem_nome']) ?></dd>

                                    <dt class="col-sm-4">Número Série:</dt>
                                    <dd class="col-sm-8"><?= htmlspecialchars($realocacao['numero_serie'] ?? 'N/A') ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <!-- Setores -->
                    <div class="col-md-6">
                        <div class="card info-card h-100">
                            <div class="card-body">
                                <h5 class="card-title mb-3">
                                    <i class="bi bi-arrow-left-right"></i> Trajeto
                                </h5>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <span class="badge badge-origem rounded-pill">
                                        <?= htmlspecialchars($realocacao['setor_origem']) ?>
                                    </span>
                                    <i class="bi bi-arrow-right fs-4"></i>
                                    <span class="badge badge-destino rounded-pill">
                                        <?= htmlspecialchars($realocacao['setor_destino']) ?>
                                    </span>
                                </div>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Data Transferência:</dt>
                                    <dd class="col-sm-8">
                                        <?= date('d/m/Y H:i', strtotime($realocacao['data_transferencia'])) ?>
                                    </dd>
                                    
                                    <dt class="col-sm-4">Registrado em:</dt>
                                    <dd class="col-sm-8">
                                        <?= date('d/m/Y H:i', strtotime($realocacao['data_registro'])) ?>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <!-- Operador e Motivo -->
                    <div class="col-12">
                        <div class="card info-card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5 class="card-title mb-3">
                                            <i class="bi bi-person"></i> Responsável pela Operação
                                        </h5>
                                        <dl class="row mb-0">
                                            <dt class="col-sm-4">Nome:</dt>
                                            <dd class="col-sm-8">
                                                <?= $realocacao['operador_nome'] ? htmlspecialchars($realocacao['operador_nome']) : 'Sistema' ?>
                                            </dd>
                                            
                                            <?php if ($realocacao['operador_email']): ?>
                                            <dt class="col-sm-4">Email:</dt>
                                            <dd class="col-sm-8">
                                                <?= htmlspecialchars($realocacao['operador_email']) ?>
                                            </dd>
                                            <?php endif; ?>
                                        </dl>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <h5 class="card-title mb-3">
                                            <i class="bi bi-chat-text"></i> Motivo da Transferência
                                        </h5>
                                        <p class="mb-0">
                                            <?= nl2br(htmlspecialchars($realocacao['motivo_transferencia'] ?? 'Nenhum motivo registrado')) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Histórico do Bem -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Últimas Movimentações deste Bem</h5>
            </div>
            <div class="card-body">
                <?php
                try {
                    $stmtHistorico = $pdo->prepare("
                        SELECT 
                            r.data_transferencia,
                            so.nome AS origem,
                            sd.nome AS destino
                        FROM realocacao r
                        JOIN setor so ON r.id_setor_origem = so.id_setor
                        JOIN setor sd ON r.id_setor_destino = sd.id_setor
                        WHERE r.id_bem = ?
                        ORDER BY r.data_transferencia DESC
                        LIMIT 5
                    ");
                    $stmtHistorico->execute([$realocacao['id_bem']]);
                    $historico = $stmtHistorico->fetchAll();

                } catch (PDOException $e) {
                    $historico = [];
                }
                ?>

                <?php if (!empty($historico)): ?>
                    <div class="timeline">
                        <?php foreach ($historico as $movimento): ?>
                            <div class="timeline-item mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="text-muted" style="width: 120px;">
                                        <?= date('d/m/Y H:i', strtotime($movimento['data_transferencia'])) ?>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge badge-origem"><?= htmlspecialchars($movimento['origem']) ?></span>
                                        <i class="bi bi-arrow-right"></i>
                                        <span class="badge badge-destino"><?= htmlspecialchars($movimento['destino']) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        Nenhuma movimentação anterior registrada para este bem.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
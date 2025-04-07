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
    // Busca dados da realocação
    $stmtRealocacao = $pdo->prepare("
        SELECT r.*, b.id_setor AS setor_atual_id
        FROM realocacao r
        JOIN bem b ON r.id_bem = b.id_bem
        WHERE r.id_realocacao = ?
    ");
    $stmtRealocacao->execute([$idRealocacao]);
    $realocacao = $stmtRealocacao->fetch();

    if (!$realocacao) {
        throw new Exception("Realocação não encontrada");
    }

    // Busca dados para os selects
    $stmtBens = $pdo->query("
        SELECT id_bem, nome FROM bem 
        WHERE id_bem = " . $realocacao['id_bem'] . "
        ORDER BY nome
    ");
    $bens = $stmtBens->fetchAll();

    $stmtSetores = $pdo->query("SELECT id_setor, nome FROM setor ORDER BY nome");
    $setores = $stmtSetores->fetchAll();

    $stmtUsuarios = $pdo->query("SELECT id_usuario, nome FROM usuario ORDER BY nome");
    $usuarios = $stmtUsuarios->fetchAll();

} catch (PDOException $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
} catch (Exception $e) {
    die($e->getMessage());
}

// Processa formulário
$erros = [];
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'id_setor_destino' => $_POST['id_setor_destino'] ?? null,
        'data_transferencia' => $_POST['data_transferencia'] ?? null,
        'motivo' => trim($_POST['motivo'] ?? ''),
        'id_operador' => $_POST['id_operador'] ?? null
    ];

    // Validações
    if (empty($dados['id_setor_destino'])) {
        $erros[] = "Selecione o setor de destino";
    }

    if ($realocacao['id_setor_origem'] == $dados['id_setor_destino']) {
        $erros[] = "Setor de destino deve ser diferente da origem";
    }

    if (empty($dados['data_transferencia'])) {
        $erros[] = "Informe a data da transferência";
    }

    if (empty($erros)) {
        try {
            $pdo->beginTransaction();

            // Atualiza a realocação
            $stmtUpdate = $pdo->prepare("
                UPDATE realocacao SET
                    id_setor_destino = ?,
                    data_transferencia = ?,
                    motivo_transferencia = ?,
                    id_operador = ?
                WHERE id_realocacao = ?
            ");
            $stmtUpdate->execute([
                $dados['id_setor_destino'],
                $dados['data_transferencia'],
                $dados['motivo'],
                $dados['id_operador'],
                $idRealocacao
            ]);

            // Atualiza o setor atual do bem se o destino for diferente do atual
            if ($realocacao['setor_atual_id'] != $dados['id_setor_destino']) {
                $stmtBem = $pdo->prepare("
                    UPDATE bem SET id_setor = ? 
                    WHERE id_bem = ?
                ");
                $stmtBem->execute([
                    $dados['id_setor_destino'],
                    $realocacao['id_bem']
                ]);
            }

            $pdo->commit();
            $sucesso = true;
            // Atualiza dados locais para exibição
            $realocacao = array_merge($realocacao, $dados);

        } catch (PDOException $e) {
            $pdo->rollBack();
            $erros[] = "Erro ao atualizar: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Realocação - TagTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
            <h1>Editar Realocação</h1>
            <a href="realocacoes.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <?php if (!empty($erros)): ?>
            <div class="alert alert-danger mb-4">
                <ul class="mb-0">
                    <?php foreach ($erros as $erro): ?>
                        <li><?= htmlspecialchars($erro) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="alert alert-success mb-4">
                Realocação atualizada com sucesso!
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="post">
                    <div class="row g-3">
                        <!-- Bem (não editável) -->
                        <div class="col-md-6">
                            <label class="form-label">Bem</label>
                            <select class="form-select" disabled>
                                <?php foreach ($bens as $bem): ?>
                                    <option selected>
                                        <?= htmlspecialchars($bem['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Setor Origem (não editável) -->
                        <div class="col-md-3">
                            <label class="form-label">Setor Origem</label>
                            <select class="form-select" disabled>
                                <?php foreach ($setores as $setor): ?>
                                    <?php if ($setor['id_setor'] == $realocacao['id_setor_origem']): ?>
                                        <option selected>
                                            <?= htmlspecialchars($setor['nome']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Setor Destino -->
                        <div class="col-md-3">
                            <label class="form-label">Setor Destino <span class="text-danger">*</span></label>
                            <select name="id_setor_destino" class="form-select" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($setores as $setor): ?>
                                    <?php if ($setor['id_setor'] != $realocacao['id_setor_origem']): ?>
                                        <option value="<?= $setor['id_setor'] ?>" 
                                            <?= ($realocacao['id_setor_destino'] == $setor['id_setor']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($setor['nome']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Data e Operador -->
                        <div class="col-md-4">
                            <label class="form-label">Data Transferência <span class="text-danger">*</span></label>
                            <input type="date" name="data_transferencia" class="form-control" 
                                   value="<?= htmlspecialchars($realocacao['data_transferencia'] ?? '') ?>" 
                                   required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Operador</label>
                            <select name="id_operador" class="form-select">
                                <option value="">Nenhum operador</option>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <option value="<?= $usuario['id_usuario'] ?>" 
                                        <?= ($realocacao['id_operador'] ?? '') == $usuario['id_usuario'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($usuario['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Motivo -->
                        <div class="col-md-12">
                            <label class="form-label">Motivo</label>
                            <textarea name="motivo" class="form-control" rows="3"><?= htmlspecialchars($realocacao['motivo_transferencia'] ?? '') ?></textarea>
                        </div>

                        <!-- Botão -->
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Salvar Alterações
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
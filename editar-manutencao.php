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
// Verifica se o ID foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manutencoes.php");
    exit();
}

$idManutencao = $_GET['id'];

// Busca dados para os selects e a manutenção
try {
    // Carrega dados da manutenção
    $stmtManutencao = $pdo->prepare("
        SELECT * FROM manutencao 
        WHERE id_manutencao = ?
    ");
    $stmtManutencao->execute([$idManutencao]);
    $manutencao = $stmtManutencao->fetch();

    if (!$manutencao) {
        throw new Exception("Manutenção não encontrada");
    }

    // Lista de bens
    $stmtBens = $pdo->query("SELECT id_bem, nome FROM bem ORDER BY nome");
    $bens = $stmtBens->fetchAll();

    // Lista de fornecedores
    $stmtFornecedores = $pdo->query("SELECT id_fornecedor, razao_social FROM fornecedor ORDER BY razao_social");
    $fornecedores = $stmtFornecedores->fetchAll();

    // Lista de usuários
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
        'id_bem' => $_POST['id_bem'] ?? null,
        'tipo' => trim($_POST['tipo'] ?? ''),
        'descricao' => trim($_POST['descricao'] ?? ''),
        'data_inicio' => $_POST['data_inicio'] ?? null,
        'data_finalizacao' => $_POST['data_finalizacao'] ?? null,
        'custo' => str_replace(['R$', '.', ','], ['', '', '.'], $_POST['custo'] ?? '0'),
        'id_fornecedor' => $_POST['id_fornecedor'] ?? null,
        'status' => $_POST['status'] ?? 1,
        'id_usuario_responsavel' => $_POST['id_usuario_responsavel'] ?? null
    ];

    // Validações
    if (empty($dados['id_bem'])) {
        $erros[] = "Selecione um bem";
    }

    if (empty($dados['tipo'])) {
        $erros[] = "Informe o tipo de manutenção";
    }

    if (empty($dados['data_inicio'])) {
        $erros[] = "Informe a data de início";
    }

    if (!empty($dados['data_finalizacao']) && $dados['data_finalizacao'] < $dados['data_inicio']) {
        $erros[] = "Data final não pode ser anterior à data de início";
    }

    if (empty($erros)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE manutencao SET
                    id_bem = ?,
                    tipo = ?,
                    descricao = ?,
                    data = ?,
                    data_finalizacao = ?,
                    custo = ?,
                    id_fornecedor = ?,
                    status = ?,
                    id_usuario_responsavel = ?
                WHERE id_manutencao = ?
            ");

            $stmt->execute([
                $dados['id_bem'],
                $dados['tipo'],
                $dados['descricao'],
                $dados['data_inicio'],
                $dados['data_finalizacao'],
                $dados['custo'],
                $dados['id_fornecedor'],
                $dados['status'],
                $dados['id_usuario_responsavel'],
                $idManutencao
            ]);

            $sucesso = true;
            // Atualiza dados exibidos
            $manutencao = array_merge($manutencao, $dados);

        } catch (PDOException $e) {
            $erros[] = "Erro ao atualizar: " . $e->getMessage();
        }
    }
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
    <title>Editar Manutenção - TagTrack</title>
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
                        <a class="nav-link active" href="manutencoes.php">Manutenções</a>
                    </li>
                </ul>
                <a href="logout.php" class="btn btn-outline-light">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Editar Manutenção</h1>
            <a href="manutencoes.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <?php if (!empty($erros)): ?>
            <div class="alert alert-danger mb-4">
                <ul class="mb-0">
                    <?php foreach ($erros as $erro): ?>
                        <li><?= $erro ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="alert alert-success mb-4">
                Manutenção atualizada com sucesso!
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="post">
                    <div class="row g-3">
                        <!-- Bem -->
                        <div class="col-md-6">
                            <label class="form-label">Bem <span class="text-danger">*</span></label>
                            <select name="id_bem" class="form-select" required>
                                <option value="">Selecione um bem...</option>
                                <?php foreach ($bens as $bem): ?>
                                    <option value="<?= $bem['id_bem'] ?>" 
                                        <?= ($manutencao['id_bem'] ?? '') == $bem['id_bem'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($bem['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Tipo -->
                        <div class="col-md-6">
                            <label class="form-label">Tipo <span class="text-danger">*</span></label>
                            <input type="text" name="tipo" class="form-control" 
                                   value="<?= htmlspecialchars($manutencao['tipo'] ?? '') ?>" required>
                        </div>

                        <!-- Descrição -->
                        <div class="col-12">
                            <label class="form-label">Descrição</label>
                            <textarea name="descricao" class="form-control" rows="3"><?= htmlspecialchars($manutencao['descricao'] ?? '') ?></textarea>
                        </div>

                        <!-- Datas -->
                        <div class="col-md-4">
                            <label class="form-label">Data Início <span class="text-danger">*</span></label>
                            <input type="date" name="data_inicio" class="form-control" 
                                   value="<?= htmlspecialchars($manutencao['data'] ?? '') ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Data Finalização</label>
                            <input type="date" name="data_finalizacao" class="form-control" 
                                   value="<?= htmlspecialchars($manutencao['data_finalizacao'] ?? '') ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Custo (R$)</label>
                            <input type="text" name="custo" class="form-control" 
                                   value="<?= number_format($manutencao['custo'], 2, ',', '.') ?>"
                                   placeholder="0,00">
                        </div>

                        <!-- Fornecedor e Status -->
                        <div class="col-md-6">
                            <label class="form-label">Fornecedor</label>
                            <select name="id_fornecedor" class="form-select">
                                <option value="">Selecione um fornecedor...</option>
                                <?php foreach ($fornecedores as $fornecedor): ?>
                                    <option value="<?= $fornecedor['id_fornecedor'] ?>" 
                                        <?= ($manutencao['id_fornecedor'] ?? '') == $fornecedor['id_fornecedor'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($fornecedor['razao_social']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <?php foreach ($statusList as $value => $label): ?>
                                    <option value="<?= $value ?>" 
                                        <?= ($manutencao['status'] ?? 1) == $value ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Responsável -->
                        <div class="col-12">
                            <label class="form-label">Responsável</label>
                            <select name="id_usuario_responsavel" class="form-select">
                                <option value="">Selecione um responsável...</option>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <option value="<?= $usuario['id_usuario'] ?>" 
                                        <?= ($manutencao['id_usuario_responsavel'] ?? '') == $usuario['id_usuario'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($usuario['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
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
    <script>
        // Máscara para o campo de custo
        document.querySelector('input[name="custo"]').addEventListener('input', function(e) {
            let valor = e.target.value.replace(/\D/g, '');
            valor = (valor / 100).toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            e.target.value = valor;
        });
    </script>
</body>
</html>
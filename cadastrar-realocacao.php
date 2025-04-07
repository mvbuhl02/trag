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
// Busca dados para os selects
try {
    // Bens disponíveis com setor atual
    $stmtBens = $pdo->query("
        SELECT 
            b.id_bem, 
            b.nome AS bem_nome,
            s.id_setor AS setor_id,
            s.nome AS setor_nome 
        FROM bem b
        JOIN setor s ON b.id_setor = s.id_setor
        ORDER BY b.nome
    ");
    $bens = $stmtBens->fetchAll();

    // Lista de setores
    $stmtSetores = $pdo->query("SELECT id_setor, nome FROM setor ORDER BY nome");
    $setores = $stmtSetores->fetchAll();

    // Usuários operadores
    $stmtUsuarios = $pdo->query("SELECT id_usuario, nome FROM usuario ORDER BY nome");
    $usuarios = $stmtUsuarios->fetchAll();

} catch (PDOException $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}

// Processa formulário
$erros = [];
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'id_bem' => $_POST['id_bem'] ?? null,
        'id_setor_origem' => $_POST['id_setor_origem'] ?? null,
        'id_setor_destino' => $_POST['id_setor_destino'] ?? null,
        'data_transferencia' => $_POST['data_transferencia'] ?? date('Y-m-d'),
        'motivo' => trim($_POST['motivo'] ?? ''),
        'id_operador' => $_POST['id_operador'] ?? null
    ];

    // Validações
    if (empty($dados['id_bem'])) {
        $erros[] = "Selecione um bem";
    }

    if (empty($dados['id_setor_origem'])) {
        $erros[] = "Selecione o setor de origem";
    }

    if (empty($dados['id_setor_destino'])) {
        $erros[] = "Selecione o setor de destino";
    }

    if ($dados['id_setor_origem'] == $dados['id_setor_destino']) {
        $erros[] = "Setor de origem e destino devem ser diferentes";
    }

    if (empty($dados['data_transferencia'])) {
        $erros[] = "Informe a data da transferência";
    }

    if (empty($erros)) {
        try {
            $pdo->beginTransaction();

            // Insere a realocação
            $stmtRealocacao = $pdo->prepare("
                INSERT INTO realocacao (
                    id_bem,
                    id_setor_origem,
                    id_setor_destino,
                    data_transferencia,
                    motivo_transferencia,
                    id_operador
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmtRealocacao->execute([
                $dados['id_bem'],
                $dados['id_setor_origem'],
                $dados['id_setor_destino'],
                $dados['data_transferencia'],
                $dados['motivo'],
                $dados['id_operador']
            ]);

            // Atualiza o setor atual do bem
            $stmtBem = $pdo->prepare("
                UPDATE bem SET id_setor = ? 
                WHERE id_bem = ?
            ");
            $stmtBem->execute([
                $dados['id_setor_destino'],
                $dados['id_bem']
            ]);

            $pdo->commit();
            $sucesso = true;
            $dados = []; // Limpa formulário

        } catch (PDOException $e) {
            $pdo->rollBack();
            $erros[] = "Erro ao cadastrar: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Realocação - TagTrack</title>
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
                        <a class="nav-link active" href="realizacoes.php">Realocações</a>
                    </li>
                </ul>
                <a href="logout.php" class="btn btn-outline-light">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Cadastrar Realocação</h1>
            <a href="realizacoes.php" class="btn btn-outline-secondary">
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
                Realocação cadastrada com sucesso!
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
                                        data-setor-origem="<?= $bem['setor_id'] ?>"
                                        <?= ($dados['id_bem'] ?? '') == $bem['id_bem'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($bem['bem_nome']) ?> 
                                        (<?= htmlspecialchars($bem['setor_nome']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Setor Origem -->
                        <div class="col-md-3">
                            <label class="form-label">Setor Origem <span class="text-danger">*</span></label>
                            <select name="id_setor_origem" class="form-select" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($setores as $setor): ?>
                                    <option value="<?= $setor['id_setor'] ?>" 
                                        <?= ($dados['id_setor_origem'] ?? '') == $setor['id_setor'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($setor['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Setor Destino -->
                        <div class="col-md-3">
                            <label class="form-label">Setor Destino <span class="text-danger">*</span></label>
                            <select name="id_setor_destino" class="form-select" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($setores as $setor): ?>
                                    <option value="<?= $setor['id_setor'] ?>" 
                                        <?= ($dados['id_setor_destino'] ?? '') == $setor['id_setor'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($setor['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Data e Operador -->
                        <div class="col-md-4">
                            <label class="form-label">Data Transferência <span class="text-danger">*</span></label>
                            <input type="date" name="data_transferencia" class="form-control" 
                                   value="<?= htmlspecialchars($dados['data_transferencia'] ?? date('Y-m-d')) ?>" 
                                   required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Operador</label>
                            <select name="id_operador" class="form-select">
                                <option value="">Selecione um operador...</option>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <option value="<?= $usuario['id_usuario'] ?>" 
                                        <?= ($dados['id_operador'] ?? '') == $usuario['id_usuario'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($usuario['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Motivo -->
                        <div class="col-md-12">
                            <label class="form-label">Motivo</label>
                            <textarea name="motivo" class="form-control" rows="3"><?= htmlspecialchars($dados['motivo'] ?? '') ?></textarea>
                        </div>

                        <!-- Botão -->
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Salvar Realocação
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Atualiza automaticamente o setor de origem ao selecionar o bem
        document.querySelector('select[name="id_bem"]').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const setorOrigemId = selectedOption.getAttribute('data-setor-origem');
            
            // Atualiza setor origem
            const setorOrigemSelect = document.querySelector('select[name="id_setor_origem"]');
            setorOrigemSelect.value = setorOrigemId;
            
            // Desabilita opção de alterar setor origem
            Array.from(setorOrigemSelect.options).forEach(option => {
                option.disabled = (option.value !== '' && option.value !== setorOrigemId);
            });

            // Reseta e habilita setor destino
            const setorDestinoSelect = document.querySelector('select[name="id_setor_destino"]');
            setorDestinoSelect.value = '';
            Array.from(setorDestinoSelect.options).forEach(option => {
                option.disabled = (option.value === setorOrigemId);
            });
        });

        // Inicialização
        document.querySelector('select[name="id_bem"]').dispatchEvent(new Event('change'));
    </script>
</body>
</html>
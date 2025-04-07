<?php
session_start();
require 'config.php';

// Verifica permissões
$isAdmin = isset($_SESSION['usuario']) && $_SESSION['usuario']['administrador'];

// Processa exclusão
if (isset($_GET['delete']) && $isAdmin) {
    try {
        $stmt = $pdo->prepare("DELETE FROM Bem WHERE id_bem = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['sucesso'] = "Item excluído com sucesso!";
    } catch (PDOException $e) {
        $_SESSION['erro'] = "Erro ao excluir item: " . $e->getMessage();
    }
    header("Location: bens.php");
    exit;
}

// Busca filtros
$filtros = [
    'setor' => $_GET['setor'] ?? '',
    'categoria' => $_GET['categoria'] ?? '',
    'responsavel' => $_GET['responsavel'] ?? ''
];

// Busca opções para filtros
try {
    $setores = $pdo->query("SELECT id_setor, nome FROM Setor ORDER BY nome")->fetchAll();
    $categorias = $pdo->query("SELECT id_categoria, nome FROM Categoria ORDER BY nome")->fetchAll();
    $responsaveis = $pdo->query("SELECT id_usuario, nome FROM Usuario ORDER BY nome")->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar filtros: " . $e->getMessage());
}

// Consulta principal com filtros
try {
    $sql = "SELECT b.*, 
                   s.nome AS setor_nome,
                   c.nome AS categoria_nome,
                   u.nome AS responsavel_nome
            FROM Bem b
            LEFT JOIN Setor s ON b.id_setor = s.id_setor
            LEFT JOIN Categoria c ON b.id_categoria = c.id_categoria
            LEFT JOIN Usuario u ON b.id_usuario = u.id_usuario
            WHERE 1=1";

    $params = [];
    
    if ($filtros['setor']) {
        $sql .= " AND b.id_setor = :setor";
        $params[':setor'] = $filtros['setor'];
    }
    
    if ($filtros['categoria']) {
        $sql .= " AND b.id_categoria = :categoria";
        $params[':categoria'] = $filtros['categoria'];
    }
    
    if ($filtros['responsavel']) {
        $sql .= " AND b.id_usuario = :responsavel";
        $params[':responsavel'] = $filtros['responsavel'];
    }

    $sql .= " ORDER BY b.data_aquisicao DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $bens = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Erro na consulta: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de bens Patrimoniais</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0"><i class="bi bi-list-task me-2"></i>Listagem de Bens</h3>
            </div>
            
            <div class="card-body">
                <!-- Filtros -->
                <form method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Setor</label>
                            <select class="form-select" name="setor">
                                <option value="">Todos</option>
                                <?php foreach ($setores as $setor): ?>
                                    <option value="<?= $setor['id_setor'] ?>" 
                                        <?= $setor['id_setor'] == $filtros['setor'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($setor['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Categoria</label>
                            <select class="form-select" name="categoria">
                                <option value="">Todas</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= $categoria['id_categoria'] ?>" 
                                        <?= $categoria['id_categoria'] == $filtros['categoria'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($categoria['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Responsável</label>
                            <select class="form-select" name="responsavel">
                                <option value="">Todos</option>
                                <?php foreach ($responsaveis as $responsavel): ?>
                                    <option value="<?= $responsavel['id_usuario'] ?>" 
                                        <?= $responsavel['id_usuario'] == $filtros['responsavel'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($responsavel['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-funnel me-1"></i>Filtrar
                            </button>
                            <a href="bens.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-clockwise me-1"></i>Limpar
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Lista de bens -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Setor</th>
                                <th>Categoria</th>
                                <th>Responsável</th>
                                <th>Valor</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bens as $item): ?>
                            <tr>
                                <td><?= $item['id_bem'] ?></td>
                                <td><?= htmlspecialchars($item['nome']) ?></td>
                                <td><?= htmlspecialchars($item['setor_nome']) ?></td>
                                <td><?= htmlspecialchars($item['categoria_nome']) ?></td>
                                <td><?= htmlspecialchars($item['responsavel_nome']) ?></td>
                                <td>R$ <?= number_format($item['valor_aquisicao'], 2, ',', '.') ?></td>
                                <td>
                                    <a href="novo_bem.php?id=<?= $item['id_bem'] ?>" 
                                       class="btn btn-sm btn-warning me-1"
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    <?php if ($isAdmin): ?>
                                    <a href="bens.php?delete=<?= $item['id_bem'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       title="Excluir"
                                       onclick="return confirm('Deseja realmente excluir este item?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($bens)): ?>
                            <tr>
                                <td colspan="7" class="text-center">
                                    <div class="alert alert-warning mb-0">Nenhum item encontrado</div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
session_start();
require 'config.php';

// Verifica login e permissão
/*
if (!isset($_SESSION['usuario']) || !$_SESSION['usuario']['administrador']) {
    header('Location: index.php');
    exit;
}
*/

// Inicializa variáveis
$bem = [
    'id_bem' => '',
    'nome' => '',
    'descricao' => '',
    'numero_serie' => '',
    'data_aquisicao' => date('Y-m-d'),
    'valor_aquisicao' => '',
    'vida_util' => '5',
    'condicao' => 'Novo',
    'porcentagem_depreciacao' => '10',
    'id_setor' => '',
    'id_categoria' => '',
    'id_usuario' => ''
];

$erros = [];
$sucesso = '';

// Busca dados para dropdowns
try {
    $setores = $pdo->query("SELECT id_setor, nome FROM Setor ORDER BY nome")->fetchAll();
    $categorias = $pdo->query("SELECT id_categoria, nome FROM Categoria ORDER BY nome")->fetchAll();
    $usuarios = $pdo->query("SELECT id_usuario, nome FROM Usuario ORDER BY nome")->fetchAll();
} catch (PDOException $e) {
    $erros[] = "Erro ao carregar dados: " . $e->getMessage();
}

// Operação CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitiza inputs
    $dados = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Atualiza array $bem
    foreach ($dados as $key => $value) {
        if (array_key_exists($key, $bem)) {
            $bem[$key] = $value;
        }
    }

    // Validação
    $camposObrigatorios = [
        'nome' => 'Nome',
        'id_setor' => 'Setor',
        'id_categoria' => 'Categoria',
        'vida_util' => 'Vida Útil',
        'porcentagem_depreciacao' => 'Depreciação'
    ];

    foreach ($camposObrigatorios as $campo => $nome) {
        if (empty($dados[$campo])) {
            $erros[] = "O campo $nome é obrigatório";
        }
    }

    // Processamento
    if (empty($erros)) {
        try {
            if (!empty($dados['id_bem'])) { // Update
                $stmt = $pdo->prepare("
                    UPDATE Bem SET 
                    nome = ?,
                    descricao = ?,
                    numero_serie = ?,
                    data_aquisicao = ?,
                    valor_aquisicao = ?,
                    vida_util = ?,
                    condicao = ?,
                    porcentagem_depreciacao = ?,
                    id_setor = ?,
                    id_categoria = ?,
                    id_usuario = ?
                    WHERE id_bem = ?
                ");
                
                $params = [
                    $dados['nome'],
                    $dados['descricao'],
                    $dados['numero_serie'],
                    $dados['data_aquisicao'],
                    $dados['valor_aquisicao'],
                    $dados['vida_util'],
                    $dados['condicao'],
                    $dados['porcentagem_depreciacao'],
                    $dados['id_setor'],
                    $dados['id_categoria'],
                    $dados['id_usuario'],
                    $dados['id_bem']
                ];
                
                $stmt->execute($params);
                $sucesso = "Bem atualizado com sucesso!";
                
            } else { // Create
                $stmt = $pdo->prepare("
                    INSERT INTO Bem (
                        nome, descricao, numero_serie, data_aquisicao, 
                        valor_aquisicao, vida_util, condicao, 
                        porcentagem_depreciacao, id_setor, id_categoria, id_usuario
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $params = [
                    $dados['nome'],
                    $dados['descricao'],
                    $dados['numero_serie'],
                    $dados['data_aquisicao'],
                    $dados['valor_aquisicao'],
                    $dados['vida_util'],
                    $dados['condicao'],
                    $dados['porcentagem_depreciacao'],
                    $dados['id_setor'],
                    $dados['id_categoria'],
                    $dados['id_usuario']
                ];
                
                $stmt->execute($params);
                $sucesso = "Bem cadastrado com sucesso!";
                $bem['id_bem'] = $pdo->lastInsertId();
            }
            
        } catch (PDOException $e) {
            $erros[] = "Erro no banco de dados: " . $e->getMessage();
        }
    }
    
} elseif (isset($_GET['id'])) { // Read
    try {
        $stmt = $pdo->prepare("SELECT * FROM Bem WHERE id_bem = ?");
        $stmt->execute([$_GET['id']]);
        $dadosBem = $stmt->fetch();
        
        if (!$dadosBem) {
            $erros[] = "Bem não encontrado";
            header('Location: bens.php');
            exit;
        }
        
        // Mescla com valores padrão
        $bem = array_merge($bem, $dadosBem);
        
    } catch (PDOException $e) {
        $erros[] = "Erro ao carregar bem: " . $e->getMessage();
    }
}

// Delete
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM Bem WHERE id_bem = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['sucesso'] = "Bem excluído com sucesso!";
        header('Location: bens.php');
        exit;
        
    } catch (PDOException $e) {
        $erros[] = "Erro ao excluir: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= empty($bem['id_bem']) ? 'Novo Bem' : 'Editar Bem #' . $bem['id_bem'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .form-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .form-section h5 {
            color: #2a5f8d;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">
                            <i class="bi bi-box-seam me-2"></i>
                            <?= empty($bem['id_bem']) ? 'Cadastrar Novo Bem' : 'Editar Bem #' . $bem['id_bem'] ?>
                        </h3>
                    </div>
                    
                    <div class="card-body">
                        <?php if ($sucesso): ?>
                            <div class="alert alert-success"><?= $sucesso ?></div>
                        <?php endif; ?>
                        
                        <?php foreach ($erros as $erro): ?>
                            <div class="alert alert-danger"><?= $erro ?></div>
                        <?php endforeach; ?>

                        <form method="POST">
                            <input type="hidden" name="id_bem" value="<?= $bem['id_bem'] ?>">
                            
                            <div class="form-section">
                                <h5>Informações Básicas</h5>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Nome do Bem <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="nome" 
                                               value="<?= htmlspecialchars($bem['nome']) ?>" required>
                                    </div>
                                    
                                    <div class="col-md-12">
                                        <label class="form-label">Descrição</label>
                                        <textarea class="form-control" name="descricao" rows="3"><?= 
                                            htmlspecialchars($bem['descricao']) 
                                        ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h5>Detalhes Técnicos</h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Número de Série</label>
                                        <input type="text" class="form-control" name="numero_serie"
                                               value="<?= htmlspecialchars($bem['numero_serie']) ?>">
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Data de Aquisição <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="data_aquisicao"
                                               value="<?= $bem['data_aquisicao'] ?>" required>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Valor (R$) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control" name="valor_aquisicao"
                                               value="<?= $bem['valor_aquisicao'] ?>" required>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Vida Útil (anos) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="vida_util" 
                                               min="1" max="50" step="1" value="<?= $bem['vida_util'] ?>" required>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Depreciação Anual (%) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="porcentagem_depreciacao"
                                               min="0" max="100" step="0.1" value="<?= $bem['porcentagem_depreciacao'] ?>" required>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Condição <span class="text-danger">*</span></label>
                                        <select class="form-select" name="condicao" required>
                                            <option value="Novo" <?= $bem['condicao'] == 'Novo' ? 'selected' : '' ?>>Novo</option>
                                            <option value="Usado" <?= $bem['condicao'] == 'Usado' ? 'selected' : '' ?>>Usado</option>
                                            <option value="Danificado" <?= $bem['condicao'] == 'Danificado' ? 'selected' : '' ?>>Danificado</option>
                                            <option value="Em Manutenção" <?= $bem['condicao'] == 'Em Manutenção' ? 'selected' : '' ?>>Em Manutenção</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h5>Localização e Responsável</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Setor <span class="text-danger">*</span></label>
                                        <select class="form-select" name="id_setor" required>
                                            <option value="">Selecione...</option>
                                            <?php foreach ($setores as $setor): ?>
                                                <option value="<?= $setor['id_setor'] ?>"
                                                    <?= $setor['id_setor'] == $bem['id_setor'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($setor['nome']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Categoria <span class="text-danger">*</span></label>
                                        <select class="form-select" name="id_categoria" required>
                                            <option value="">Selecione...</option>
                                            <?php foreach ($categorias as $categoria): ?>
                                                <option value="<?= $categoria['id_categoria'] ?>"
                                                    <?= $categoria['id_categoria'] == $bem['id_categoria'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($categoria['nome']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-12">
                                        <label class="form-label">Responsável</label>
                                        <select class="form-select" name="id_usuario">
                                            <option value="">Nenhum</option>
                                            <?php foreach ($usuarios as $usuario): ?>
                                                <option value="<?= $usuario['id_usuario'] ?>"
                                                    <?= $usuario['id_usuario'] == $bem['id_usuario'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($usuario['nome']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="d-flex justify-content-between">
                                    <a href="bens.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-2"></i>Voltar para Listagem
                                    </a>
                                    
                                    <div class="d-flex gap-2">
                                        <?php if (!empty($bem['id_bem'])): ?>
                                            <a href="?delete=<?= $bem['id_bem'] ?>" 
                                               class="btn btn-danger"
                                               onclick="return confirm('Deseja excluir permanentemente este bem?')">
                                                <i class="bi bi-trash me-2"></i>Excluir
                                            </a>
                                        <?php endif; ?>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save me-2"></i>Salvar Alterações
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
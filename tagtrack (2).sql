-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 07/04/2025 às 21:24
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `tagtrack`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `baixa`
--

CREATE TABLE `baixa` (
  `id_baixa` bigint(20) UNSIGNED NOT NULL,
  `id_bem` bigint(20) UNSIGNED NOT NULL,
  `id_operador` bigint(20) UNSIGNED DEFAULT NULL,
  `motivo` varchar(255) NOT NULL,
  `data` date NOT NULL DEFAULT curdate(),
  `descricao` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `bem`
--

CREATE TABLE `bem` (
  `id_bem` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `numero_serie` varchar(100) DEFAULT NULL,
  `data_aquisicao` date DEFAULT NULL,
  `valor_aquisicao` decimal(12,2) DEFAULT NULL,
  `vida_util` float DEFAULT NULL,
  `data_adicao` date DEFAULT curdate(),
  `condicao` varchar(100) DEFAULT NULL,
  `porcentagem_depreciacao` float DEFAULT NULL,
  `id_categoria` bigint(20) UNSIGNED DEFAULT NULL,
  `id_setor` bigint(20) UNSIGNED DEFAULT NULL,
  `id_usuario` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `bem`
--

INSERT INTO `bem` (`id_bem`, `nome`, `descricao`, `numero_serie`, `data_aquisicao`, `valor_aquisicao`, `vida_util`, `data_adicao`, `condicao`, `porcentagem_depreciacao`, `id_categoria`, `id_setor`, `id_usuario`) VALUES
(1, 'Computador', 'Dell 25 polegads', '1234567', '2025-04-02', 23456.00, NULL, '2025-04-02', NULL, NULL, 4, 14, 1),
(3, 'Computador Dell 2025', 'Dell 25 polegadas', '123452', '2025-04-03', 22302.00, 5, '2025-04-02', 'Novo', 10, 4, 14, 1),
(4, 'Mesaaa', 'Mesa bonita', '93184918349184', '2025-04-04', 1299.00, 2, '2025-04-03', 'Danificado', 10, 4, 14, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `categoria`
--

CREATE TABLE `categoria` (
  `id_categoria` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `categoria`
--

INSERT INTO `categoria` (`id_categoria`, `nome`, `descricao`) VALUES
(1, 'Informática', 'Equipamentos de computação e periféricos'),
(2, 'Mobiliário', 'Móveis e itens de escritório'),
(3, 'Veículos', 'Frota corporativa'),
(4, 'Equipamentos Industriais', 'Maquinário especializado');

-- --------------------------------------------------------

--
-- Estrutura para tabela `departamento`
--

CREATE TABLE `departamento` (
  `id_departamento` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `id_empresa` bigint(20) UNSIGNED NOT NULL,
  `id_endereco` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `departamento`
--

INSERT INTO `departamento` (`id_departamento`, `nome`, `descricao`, `id_empresa`, `id_endereco`) VALUES
(1, 'TI', 'Tecnologia da Informação', 1, 1),
(2, 'RH', 'Recursos Humanos', 1, 1),
(3, 'Produção', 'Área Industrial', 2, 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `empresa`
--

CREATE TABLE `empresa` (
  `id_empresa` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `id_endereco` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `empresa`
--

INSERT INTO `empresa` (`id_empresa`, `nome`, `descricao`, `id_endereco`) VALUES
(1, 'Matriz SP', 'Sede Principal', 1),
(2, 'Filial RJ', 'Filial Rio de Janeiro', 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `endereco`
--

CREATE TABLE `endereco` (
  `id_endereco` bigint(20) UNSIGNED NOT NULL,
  `rua` varchar(255) NOT NULL,
  `numero` varchar(50) DEFAULT NULL,
  `cep` varchar(10) NOT NULL,
  `cidade` varchar(100) NOT NULL,
  `estado` varchar(50) NOT NULL,
  `pais` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `endereco`
--

INSERT INTO `endereco` (`id_endereco`, `rua`, `numero`, `cep`, `cidade`, `estado`, `pais`) VALUES
(1, 'Av. Paulista', '1000', '01310-100', 'São Paulo', 'SP', 'Brasil'),
(2, 'Rua da Praia', 's/n', '20010-020', 'Rio de Janeiro', 'RJ', 'Brasil'),
(3, 'Av. Paulista', '1000', '01310-100', 'São Paulo', 'SP', 'Brasil'),
(4, 'Rua da Praia', 's/n', '20010-020', 'Rio de Janeiro', 'RJ', 'Brasil'),
(5, 'Av. Paulista', '1000', '01310-100', 'São Paulo', 'SP', 'Brasil'),
(6, 'Rua da Praia', 's/n', '20010-020', 'Rio de Janeiro', 'RJ', 'Brasil');

-- --------------------------------------------------------

--
-- Estrutura para tabela `fornecedor`
--

CREATE TABLE `fornecedor` (
  `id_fornecedor` bigint(20) UNSIGNED NOT NULL,
  `cnpj` varchar(18) NOT NULL,
  `nome_fantasia` varchar(255) DEFAULT NULL,
  `razao_social` varchar(255) NOT NULL,
  `contato` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico`
--

CREATE TABLE `historico` (
  `id_historico` bigint(20) UNSIGNED NOT NULL,
  `id_bem` bigint(20) UNSIGNED NOT NULL,
  `id_usuario` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo_evento` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `local_origem` varchar(255) DEFAULT NULL,
  `local_destino` varchar(255) DEFAULT NULL,
  `data_evento` timestamp NOT NULL DEFAULT current_timestamp(),
  `custo` decimal(10,2) DEFAULT NULL,
  `status` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `manutencao`
--

CREATE TABLE `manutencao` (
  `id_manutencao` bigint(20) UNSIGNED NOT NULL,
  `id_bem` bigint(20) UNSIGNED NOT NULL,
  `id_fornecedor` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo` varchar(100) DEFAULT NULL,
  `descricao` text NOT NULL,
  `data` date NOT NULL,
  `data_finalizacao` date DEFAULT NULL,
  `custo` decimal(10,2) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `id_usuario_responsavel` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `realocacao`
--

CREATE TABLE `realocacao` (
  `id_realocacao` bigint(20) UNSIGNED NOT NULL,
  `id_bem` bigint(20) UNSIGNED NOT NULL,
  `id_operador` bigint(20) UNSIGNED DEFAULT NULL,
  `id_setor_origem` bigint(20) UNSIGNED NOT NULL,
  `id_setor_destino` bigint(20) UNSIGNED NOT NULL,
  `data_transferencia` date NOT NULL,
  `motivo_transferencia` text DEFAULT NULL,
  `data_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `setor`
--

CREATE TABLE `setor` (
  `id_setor` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `capacidade` int(11) DEFAULT NULL,
  `id_departamento` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `setor`
--

INSERT INTO `setor` (`id_setor`, `nome`, `descricao`, `capacidade`, `id_departamento`) VALUES
(13, 'Redes', 'Infraestrutura de redes', 20, 1),
(14, 'Desenvolvimento', 'Squad de desenvolvimento', 15, 1),
(15, 'Recrutamento', 'Processos seletivos', 10, 2),
(16, 'Linha de Montagem', 'Produção industrial', 100, 3);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(255) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `data_nascimento` date DEFAULT NULL,
  `id_endereco` bigint(20) UNSIGNED DEFAULT NULL,
  `administrador` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nome`, `cpf`, `celular`, `email`, `data_nascimento`, `id_endereco`, `administrador`) VALUES
(1, 'João Silva', '123.456.789-00', '(11) 9999-8888', 'joao@empresa.com', '1980-05-15', 1, 1),
(2, 'Maria Santos', '987.654.321-00', '(21) 7777-6666', 'maria@empresa.com', '1990-08-25', 2, 0);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `baixa`
--
ALTER TABLE `baixa`
  ADD PRIMARY KEY (`id_baixa`),
  ADD UNIQUE KEY `id_bem` (`id_bem`),
  ADD KEY `id_operador` (`id_operador`);

--
-- Índices de tabela `bem`
--
ALTER TABLE `bem`
  ADD PRIMARY KEY (`id_bem`),
  ADD UNIQUE KEY `numero_serie` (`numero_serie`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `idx_bem_id_setor` (`id_setor`),
  ADD KEY `idx_bem_id_categoria` (`id_categoria`);

--
-- Índices de tabela `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `departamento`
--
ALTER TABLE `departamento`
  ADD PRIMARY KEY (`id_departamento`),
  ADD UNIQUE KEY `nome` (`nome`,`id_empresa`),
  ADD KEY `id_empresa` (`id_empresa`),
  ADD KEY `id_endereco` (`id_endereco`);

--
-- Índices de tabela `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id_empresa`),
  ADD UNIQUE KEY `nome` (`nome`),
  ADD KEY `id_endereco` (`id_endereco`);

--
-- Índices de tabela `endereco`
--
ALTER TABLE `endereco`
  ADD PRIMARY KEY (`id_endereco`);

--
-- Índices de tabela `fornecedor`
--
ALTER TABLE `fornecedor`
  ADD PRIMARY KEY (`id_fornecedor`),
  ADD UNIQUE KEY `cnpj` (`cnpj`);

--
-- Índices de tabela `historico`
--
ALTER TABLE `historico`
  ADD PRIMARY KEY (`id_historico`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `idx_historico_id_bem` (`id_bem`),
  ADD KEY `idx_historico_data_evento` (`data_evento`);

--
-- Índices de tabela `manutencao`
--
ALTER TABLE `manutencao`
  ADD PRIMARY KEY (`id_manutencao`),
  ADD KEY `id_fornecedor` (`id_fornecedor`),
  ADD KEY `id_usuario_responsavel` (`id_usuario_responsavel`),
  ADD KEY `idx_manutencao_id_bem` (`id_bem`);

--
-- Índices de tabela `realocacao`
--
ALTER TABLE `realocacao`
  ADD PRIMARY KEY (`id_realocacao`),
  ADD KEY `id_operador` (`id_operador`),
  ADD KEY `id_setor_origem` (`id_setor_origem`),
  ADD KEY `id_setor_destino` (`id_setor_destino`),
  ADD KEY `idx_realocacao_id_bem` (`id_bem`),
  ADD KEY `idx_realocacao_data_transferencia` (`data_transferencia`);

--
-- Índices de tabela `setor`
--
ALTER TABLE `setor`
  ADD PRIMARY KEY (`id_setor`),
  ADD UNIQUE KEY `nome` (`nome`,`id_departamento`),
  ADD KEY `id_departamento` (`id_departamento`);

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_endereco` (`id_endereco`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `baixa`
--
ALTER TABLE `baixa`
  MODIFY `id_baixa` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `bem`
--
ALTER TABLE `bem`
  MODIFY `id_bem` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id_categoria` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `departamento`
--
ALTER TABLE `departamento`
  MODIFY `id_departamento` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id_empresa` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `endereco`
--
ALTER TABLE `endereco`
  MODIFY `id_endereco` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `fornecedor`
--
ALTER TABLE `fornecedor`
  MODIFY `id_fornecedor` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `historico`
--
ALTER TABLE `historico`
  MODIFY `id_historico` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `manutencao`
--
ALTER TABLE `manutencao`
  MODIFY `id_manutencao` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `realocacao`
--
ALTER TABLE `realocacao`
  MODIFY `id_realocacao` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `setor`
--
ALTER TABLE `setor`
  MODIFY `id_setor` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `baixa`
--
ALTER TABLE `baixa`
  ADD CONSTRAINT `baixa_ibfk_1` FOREIGN KEY (`id_bem`) REFERENCES `bem` (`id_bem`) ON UPDATE CASCADE,
  ADD CONSTRAINT `baixa_ibfk_2` FOREIGN KEY (`id_operador`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `bem`
--
ALTER TABLE `bem`
  ADD CONSTRAINT `bem_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`) ON UPDATE CASCADE,
  ADD CONSTRAINT `bem_ibfk_2` FOREIGN KEY (`id_setor`) REFERENCES `setor` (`id_setor`) ON UPDATE CASCADE,
  ADD CONSTRAINT `bem_ibfk_3` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `departamento`
--
ALTER TABLE `departamento`
  ADD CONSTRAINT `departamento_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `departamento_ibfk_2` FOREIGN KEY (`id_endereco`) REFERENCES `endereco` (`id_endereco`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `empresa`
--
ALTER TABLE `empresa`
  ADD CONSTRAINT `empresa_ibfk_1` FOREIGN KEY (`id_endereco`) REFERENCES `endereco` (`id_endereco`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `historico`
--
ALTER TABLE `historico`
  ADD CONSTRAINT `historico_ibfk_1` FOREIGN KEY (`id_bem`) REFERENCES `bem` (`id_bem`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `historico_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `manutencao`
--
ALTER TABLE `manutencao`
  ADD CONSTRAINT `manutencao_ibfk_1` FOREIGN KEY (`id_bem`) REFERENCES `bem` (`id_bem`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `manutencao_ibfk_2` FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedor` (`id_fornecedor`) ON UPDATE CASCADE,
  ADD CONSTRAINT `manutencao_ibfk_3` FOREIGN KEY (`id_usuario_responsavel`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `realocacao`
--
ALTER TABLE `realocacao`
  ADD CONSTRAINT `realocacao_ibfk_1` FOREIGN KEY (`id_bem`) REFERENCES `bem` (`id_bem`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `realocacao_ibfk_2` FOREIGN KEY (`id_operador`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `realocacao_ibfk_3` FOREIGN KEY (`id_setor_origem`) REFERENCES `setor` (`id_setor`) ON UPDATE CASCADE,
  ADD CONSTRAINT `realocacao_ibfk_4` FOREIGN KEY (`id_setor_destino`) REFERENCES `setor` (`id_setor`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `setor`
--
ALTER TABLE `setor`
  ADD CONSTRAINT `setor_ibfk_1` FOREIGN KEY (`id_departamento`) REFERENCES `departamento` (`id_departamento`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_endereco`) REFERENCES `endereco` (`id_endereco`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:8485
-- Tempo de geração: 21/03/2026 às 23:30
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
-- Banco de dados: `studyplan`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `cursos`
--

CREATE TABLE `cursos` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `duracao_anos` int(11) DEFAULT 3,
  `ativo` tinyint(1) DEFAULT 1,
  `criado_por` int(11) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `cursos`
--

INSERT INTO `cursos` (`id`, `nome`, `descricao`, `duracao_anos`, `ativo`, `criado_por`, `criado_em`) VALUES
(3, 'Engenharia Informática', '', 3, 1, 1, '2026-03-15 19:25:44'),
(4, 'Design Multimédia', '', 3, 1, 1, '2026-03-15 19:28:47'),
(5, 'Gestão de Empresas', '', 3, 1, 1, '2026-03-21 22:12:34');

-- --------------------------------------------------------

--
-- Estrutura para tabela `fichas_aluno`
--

CREATE TABLE `fichas_aluno` (
  `id` int(11) NOT NULL,
  `utilizador_id` int(11) NOT NULL,
  `curso_id` int(11) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `morada` text DEFAULT NULL,
  `naturalidade` varchar(100) DEFAULT NULL,
  `nacionalidade` varchar(100) DEFAULT 'Portuguesa',
  `foto` varchar(255) DEFAULT NULL,
  `estado` enum('rascunho','submetida','aprovada','rejeitada') DEFAULT 'rascunho',
  `observacoes` text DEFAULT NULL,
  `validado_por` int(11) DEFAULT NULL,
  `validado_em` timestamp NULL DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `fichas_aluno`
--

INSERT INTO `fichas_aluno` (`id`, `utilizador_id`, `curso_id`, `data_nascimento`, `telefone`, `morada`, `naturalidade`, `nacionalidade`, `foto`, `estado`, `observacoes`, `validado_por`, `validado_em`, `criado_em`, `atualizado_em`) VALUES
(1, 3, 3, '2007-01-03', '912 345 678', 'Rua das Flores, 10, 4750-000 Barcelos', 'Braga', 'Portuguesa', 'foto_69b7094c52b027.04201451.jpeg', 'aprovada', 'bons estudos', 1, '2026-03-15 19:33:18', '2026-03-15 19:32:28', '2026-03-15 19:33:18'),
(2, 4, 4, '2004-01-07', '900 220 200', 'tal tal tal', 'guima', 'Portuguesa', 'foto_69bf0acc001325.25571589.jpeg', 'aprovada', '', 1, '2026-03-21 22:15:50', '2026-03-21 21:17:00', '2026-03-21 22:15:50');

-- --------------------------------------------------------

--
-- Estrutura para tabela `matriculas`
--

CREATE TABLE `matriculas` (
  `id` int(11) NOT NULL,
  `utilizador_id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `ano_letivo` varchar(9) NOT NULL,
  `estado` enum('pendente','aprovada','rejeitada') DEFAULT 'pendente',
  `observacoes` text DEFAULT NULL,
  `decidido_por` int(11) DEFAULT NULL,
  `decidido_em` timestamp NULL DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `matriculas`
--

INSERT INTO `matriculas` (`id`, `utilizador_id`, `curso_id`, `ano_letivo`, `estado`, `observacoes`, `decidido_por`, `decidido_em`, `criado_em`) VALUES
(1, 3, 3, '2026/2027', 'aprovada', 'Bons estudos', 2, '2026-03-15 19:34:54', '2026-03-15 19:34:01'),
(2, 3, 4, '2026/2027', 'rejeitada', 'vai ser rejeitado filho', 2, '2026-03-17 16:29:33', '2026-03-17 16:28:24'),
(3, 4, 4, '2026/2027', 'aprovada', '', 2, '2026-03-21 22:17:10', '2026-03-21 22:16:12');

-- --------------------------------------------------------

--
-- Estrutura para tabela `notas`
--

CREATE TABLE `notas` (
  `id` int(11) NOT NULL,
  `pauta_id` int(11) NOT NULL,
  `utilizador_id` int(11) NOT NULL,
  `nota` decimal(4,1) DEFAULT NULL,
  `observacoes` varchar(255) DEFAULT NULL,
  `editado_por` int(11) DEFAULT NULL,
  `editado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `notas`
--

INSERT INTO `notas` (`id`, `pauta_id`, `utilizador_id`, `nota`, `observacoes`, `editado_por`, `editado_em`) VALUES
(1, 1, 3, 18.0, 'quase la kauan', 2, '2026-03-15 19:37:59'),
(2, 2, 3, NULL, NULL, NULL, '2026-03-16 12:58:47'),
(3, 3, 4, 13.7, '', 2, '2026-03-21 22:17:52');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pautas`
--

CREATE TABLE `pautas` (
  `id` int(11) NOT NULL,
  `uc_id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `ano_letivo` varchar(9) NOT NULL,
  `epoca` enum('Normal','Recurso','Especial') NOT NULL,
  `criado_por` int(11) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `pautas`
--

INSERT INTO `pautas` (`id`, `uc_id`, `curso_id`, `ano_letivo`, `epoca`, `criado_por`, `criado_em`) VALUES
(1, 2, 3, '2024/2025', 'Normal', 2, '2026-03-15 19:35:34'),
(2, 6, 3, '2026/2027', 'Normal', 2, '2026-03-16 12:58:47'),
(3, 17, 4, '2026/2027', 'Normal', 2, '2026-03-21 22:17:31');

-- --------------------------------------------------------

--
-- Estrutura para tabela `plano_estudos`
--

CREATE TABLE `plano_estudos` (
  `id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `uc_id` int(11) NOT NULL,
  `ano` int(11) NOT NULL CHECK (`ano` between 1 and 5),
  `semestre` int(11) NOT NULL CHECK (`semestre` between 1 and 2)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `plano_estudos`
--

INSERT INTO `plano_estudos` (`id`, `curso_id`, `uc_id`, `ano`, `semestre`) VALUES
(4, 3, 2, 2, 2),
(2, 3, 3, 1, 2),
(3, 3, 4, 2, 1),
(1, 3, 6, 1, 1),
(8, 4, 8, 1, 1),
(9, 4, 11, 1, 1),
(10, 4, 12, 1, 2),
(11, 4, 13, 2, 1),
(7, 4, 14, 1, 1),
(12, 4, 17, 3, 1),
(5, 5, 15, 1, 2),
(6, 5, 16, 2, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `ucs`
--

CREATE TABLE `ucs` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `creditos` int(11) DEFAULT 6,
  `horas_semana` int(11) DEFAULT 4,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `ucs`
--

INSERT INTO `ucs` (`id`, `nome`, `descricao`, `creditos`, `horas_semana`, `criado_em`) VALUES
(2, 'Programação Web', '', 6, 4, '2026-03-15 19:26:13'),
(3, 'Bases de Dados', '', 6, 4, '2026-03-15 19:28:03'),
(4, 'Redes de Computadores', '', 6, 4, '2026-03-15 19:28:13'),
(5, 'Design de Interface', '', 6, 4, '2026-03-15 19:28:21'),
(6, 'Matemática Discreta', '', 6, 4, '2026-03-15 19:28:31'),
(7, 'Fundamentos de Design', '', 6, 4, '2026-03-21 22:09:03'),
(8, 'Fotografia Digital', '', 6, 4, '2026-03-21 22:09:13'),
(9, 'Programação I', '', 6, 4, '2026-03-21 22:10:24'),
(10, 'Segurança Informática', '', 6, 4, '2026-03-21 22:10:49'),
(11, 'Tipografia e Comunicação', '', 6, 4, '2026-03-21 22:11:09'),
(12, 'Ilustração Digital', '', 6, 4, '2026-03-21 22:11:20'),
(13, 'Design de Interface (UI)', '', 6, 4, '2026-03-21 22:11:29'),
(14, 'Animação 2D e 3D', '', 6, 4, '2026-03-21 22:11:37'),
(15, 'Contabilidade', '', 6, 4, '2026-03-21 22:11:46'),
(16, 'Marketing Digital', '', 6, 4, '2026-03-21 22:11:55'),
(17, 'Motion Graphics', '', 6, 4, '2026-03-21 22:15:18');

-- --------------------------------------------------------

--
-- Estrutura para tabela `utilizadores`
--

CREATE TABLE `utilizadores` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `perfil` enum('aluno','funcionario','gestor') NOT NULL DEFAULT 'aluno',
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `utilizadores`
--

INSERT INTO `utilizadores` (`id`, `nome`, `email`, `senha`, `perfil`, `ativo`, `criado_em`) VALUES
(1, 'Gestor Pedagógico', 'gestor@studyplan.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'gestor', 1, '2026-03-15 19:01:48'),
(2, 'Serviços Académicos', 'func@studyplan.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'funcionario', 1, '2026-03-15 19:01:48'),
(3, 'Kauan', 'kauandeabreumatias@gmail.com', '$2y$10$gtmLPD1diR9ObAaKxrgg1.TnfEEzPkQ80zL/Mj3STe0TU1d7RuYKS', 'aluno', 1, '2026-03-15 19:16:18'),
(4, 'matheus matias', 'mateus@gmail.com', '$2y$10$PF9LSnpw96cXeXStH6nIYe67sorxwRO9dCV1BEz6VR5sI9V89ikK6', 'aluno', 1, '2026-03-21 21:15:05');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `criado_por` (`criado_por`);

--
-- Índices de tabela `fichas_aluno`
--
ALTER TABLE `fichas_aluno`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `utilizador_id` (`utilizador_id`),
  ADD KEY `curso_id` (`curso_id`),
  ADD KEY `validado_por` (`validado_por`);

--
-- Índices de tabela `matriculas`
--
ALTER TABLE `matriculas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unico_matricula` (`utilizador_id`,`curso_id`,`ano_letivo`),
  ADD KEY `curso_id` (`curso_id`),
  ADD KEY `decidido_por` (`decidido_por`);

--
-- Índices de tabela `notas`
--
ALTER TABLE `notas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unico_nota` (`pauta_id`,`utilizador_id`),
  ADD KEY `utilizador_id` (`utilizador_id`),
  ADD KEY `editado_por` (`editado_por`);

--
-- Índices de tabela `pautas`
--
ALTER TABLE `pautas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unico_pauta` (`uc_id`,`curso_id`,`ano_letivo`,`epoca`),
  ADD KEY `curso_id` (`curso_id`),
  ADD KEY `criado_por` (`criado_por`);

--
-- Índices de tabela `plano_estudos`
--
ALTER TABLE `plano_estudos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sem_duplicacao` (`curso_id`,`uc_id`,`ano`,`semestre`),
  ADD KEY `uc_id` (`uc_id`);

--
-- Índices de tabela `ucs`
--
ALTER TABLE `ucs`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `utilizadores`
--
ALTER TABLE `utilizadores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `fichas_aluno`
--
ALTER TABLE `fichas_aluno`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `matriculas`
--
ALTER TABLE `matriculas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `notas`
--
ALTER TABLE `notas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `pautas`
--
ALTER TABLE `pautas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `plano_estudos`
--
ALTER TABLE `plano_estudos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `ucs`
--
ALTER TABLE `ucs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `utilizadores`
--
ALTER TABLE `utilizadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `cursos`
--
ALTER TABLE `cursos`
  ADD CONSTRAINT `cursos_ibfk_1` FOREIGN KEY (`criado_por`) REFERENCES `utilizadores` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `fichas_aluno`
--
ALTER TABLE `fichas_aluno`
  ADD CONSTRAINT `fichas_aluno_ibfk_1` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizadores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fichas_aluno_ibfk_2` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fichas_aluno_ibfk_3` FOREIGN KEY (`validado_por`) REFERENCES `utilizadores` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `matriculas`
--
ALTER TABLE `matriculas`
  ADD CONSTRAINT `matriculas_ibfk_1` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizadores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matriculas_ibfk_2` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matriculas_ibfk_3` FOREIGN KEY (`decidido_por`) REFERENCES `utilizadores` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `notas`
--
ALTER TABLE `notas`
  ADD CONSTRAINT `notas_ibfk_1` FOREIGN KEY (`pauta_id`) REFERENCES `pautas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notas_ibfk_2` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizadores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notas_ibfk_3` FOREIGN KEY (`editado_por`) REFERENCES `utilizadores` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `pautas`
--
ALTER TABLE `pautas`
  ADD CONSTRAINT `pautas_ibfk_1` FOREIGN KEY (`uc_id`) REFERENCES `ucs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pautas_ibfk_2` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pautas_ibfk_3` FOREIGN KEY (`criado_por`) REFERENCES `utilizadores` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `plano_estudos`
--
ALTER TABLE `plano_estudos`
  ADD CONSTRAINT `plano_estudos_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `plano_estudos_ibfk_2` FOREIGN KEY (`uc_id`) REFERENCES `ucs` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

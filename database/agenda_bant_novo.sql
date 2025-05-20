-- phpMyAdmin SQL Dump
-- version 5.0.4deb2+deb11u2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 20/05/2025 às 13:43
-- Versão do servidor: 10.5.28-MariaDB-0+deb11u1
-- Versão do PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `agenda_bant`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `agendamentos`
--

CREATE TABLE `agendamentos` (
  `id` int(11) NOT NULL,
  `espaco_id` int(11) NOT NULL,
  `nome_solicitante` varchar(100) NOT NULL,
  `posto_graduacao` varchar(50) NOT NULL,
  `setor` varchar(100) NOT NULL,
  `ramal` varchar(20) NOT NULL,
  `email_solicitante` varchar(255) NOT NULL,
  `nome_evento` varchar(255) NOT NULL,
  `categoria_evento` varchar(100) NOT NULL,
  `quantidade_participantes` int(11) NOT NULL,
  `observacoes` text DEFAULT NULL,
  `data_inicio` datetime NOT NULL,
  `data_fim` datetime NOT NULL,
  `status` enum('pendente','aprovado','cancelado') DEFAULT 'pendente',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `agendamentos`
--

INSERT INTO `agendamentos` (`id`, `espaco_id`, `nome_solicitante`, `posto_graduacao`, `setor`, `ramal`, `email_solicitante`, `nome_evento`, `categoria_evento`, `quantidade_participantes`, `observacoes`, `data_inicio`, `data_fim`, `status`, `created_at`, `updated_at`) VALUES
(35, 2, 'VANONI', '2S', 'ETIC', '7515', 'vanonidvv@fab.mil.br', 'TESTE', 'TESTE', 50, '', '2025-05-21 15:00:00', '2025-05-21 16:00:00', 'aprovado', '2025-05-20 16:26:41', '2025-05-20 16:27:23');

-- --------------------------------------------------------

--
-- Estrutura para tabela `configuracoes`
--

CREATE TABLE `configuracoes` (
  `id` int(11) NOT NULL,
  `antecedencia_horas` int(11) NOT NULL DEFAULT 24,
  `max_horas_consecutivas` int(11) NOT NULL DEFAULT 4,
  `email_comunicacao` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `configuracoes`
--

INSERT INTO `configuracoes` (`id`, `antecedencia_horas`, `max_horas_consecutivas`, `email_comunicacao`, `created_at`, `updated_at`) VALUES
(1, 24, 8, 'vanonidvv@fab.mil.br', '2025-05-19 14:16:45', '2025-05-20 16:30:31');

-- --------------------------------------------------------

--
-- Estrutura para tabela `espacos`
--

CREATE TABLE `espacos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `capacidade` int(11) DEFAULT NULL,
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `espacos`
--

INSERT INTO `espacos` (`id`, `nome`, `descricao`, `capacidade`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Auditório da BANT', 'Auditório geral da Base Aérea de Natal', 500, 'ativo', '2025-05-19 14:24:26', '2025-05-19 14:24:26'),
(2, 'Auditório Cine Navy', '', 700, 'ativo', '2025-05-19 14:24:52', '2025-05-19 14:24:52'),
(3, 'Sala de Videoconferência', 'Sala localizada no ETIC', 10, 'ativo', '2025-05-19 14:25:22', '2025-05-19 14:25:22'),
(4, 'Auditório do EMB', '', 200, 'inativo', '2025-05-20 14:36:28', '2025-05-20 14:38:54');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `tipo` enum('admin','usuario') DEFAULT 'usuario',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `password`, `tipo`, `created_at`) VALUES
(1, 'admin', '$2y$10$BjPF0LUIcm3VKqwQik4w4.HJfo245k0/GalREfnxVAlAKcXf69.q6', 'admin', '2025-05-19 14:16:45');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `espaco_id` (`espaco_id`);

--
-- Índices de tabela `configuracoes`
--
ALTER TABLE `configuracoes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `espacos`
--
ALTER TABLE `espacos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de tabela `configuracoes`
--
ALTER TABLE `configuracoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `espacos`
--
ALTER TABLE `espacos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

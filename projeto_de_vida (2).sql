-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 09/04/2025 às 20:29
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
-- Banco de dados: `projeto de vida`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mensagem` text NOT NULL,
  `Assunto` varchar(255) NOT NULL,
  `data_envio` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Despejando dados para a tabela `feedback`
--

INSERT INTO `feedback` (`id`, `nome`, `email`, `mensagem`, `Assunto`, `data_envio`) VALUES
(1, 'Eric', 'ericsouzapalma123@gmail.com', 'femgklersngjkrndklgtr', '', '2025-04-04 17:08:50'),
(2, 'Eric', 'ericsouzapalma123@gmail.com', 'amfkwejkarfjrg', 'Viagem', '2025-04-04 17:16:24'),
(3, 'Eric', 'ericsouzapalma123@gmail.com', 'amfkwejkarfjrg', 'Viagem', '2025-04-04 17:28:17'),
(4, 'Eric', 'ericsouzapalma123@gmail.com', 'amfkwejkarfjrg', 'Viagem', '2025-04-04 17:50:10'),
(5, 'dcdsvdcvd', 'vvdsvxsvdscsxsd@email.com', 'asdczxcv\\sd', 'dsvdsavdsvc', '2025-04-09 16:10:59');

-- --------------------------------------------------------

--
-- Estrutura para tabela `quem_sou_eu`
--

CREATE TABLE `quem_sou_eu` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `fale_sobre_voce` text DEFAULT NULL,
  `minhas_lembrancas` text DEFAULT NULL,
  `pontos_fortes` text DEFAULT NULL,
  `pontos_fracos` text DEFAULT NULL,
  `meus_valores` text DEFAULT NULL,
  `principais_aptidoes` text DEFAULT NULL,
  `relacoes_familia` text DEFAULT NULL,
  `relacoes_amigos` text DEFAULT NULL,
  `relacoes_escola` text DEFAULT NULL,
  `relacoes_sociedade` text DEFAULT NULL,
  `gosto_fazer` text DEFAULT NULL,
  `nao_gosto_fazer` text DEFAULT NULL,
  `rotina` text DEFAULT NULL,
  `lazer` text DEFAULT NULL,
  `estudos` text DEFAULT NULL,
  `vida_escolar` text DEFAULT NULL,
  `visao_fisica` text DEFAULT NULL,
  `visao_intelectual` text DEFAULT NULL,
  `visao_emocional` text DEFAULT NULL,
  `visao_dos_amigos` text DEFAULT NULL,
  `visao_dos_familiares` text DEFAULT NULL,
  `visao_dos_professores` text DEFAULT NULL,
  `autovalorizacao_total` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `teste_personalidade`
--

CREATE TABLE `teste_personalidade` (
  `user_id` int(11) NOT NULL,
  `extrovertido` tinyint(3) UNSIGNED NOT NULL,
  `intuitivo` tinyint(3) UNSIGNED NOT NULL,
  `racional` tinyint(3) UNSIGNED NOT NULL,
  `julgador` tinyint(3) UNSIGNED NOT NULL,
  `data_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Despejando dados para a tabela `teste_personalidade`
--

INSERT INTO `teste_personalidade` (`user_id`, `extrovertido`, `intuitivo`, `racional`, `julgador`, `data_registro`) VALUES
(8, 77, 78, 83, 78, '2025-04-09 18:28:34');

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `data_de_registro` datetime NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `description`, `password`, `data_de_registro`, `profile_picture`) VALUES
(4, 'jonata', 'jonatas@docente.br', 'bbhgvbhnvbghjnj', '100.36585365854', '2025-03-28 14:54:10', 'img/67ed0fc589c6d_7ugE.gif'),
(5, 'bernini', 'bernini@bernini.com', 'sor bernas senai', '1234', '2025-03-28 18:29:22', 'img/67e6e878e2c5b_OKUa.gif'),
(6, 'Rafa', 'rafael@gmail.com', '', '1234', '2025-03-28 19:33:54', 'img/67e6ebc794cf4_XGrF.gif'),
(7, 'roberto', 'robertohenryck365@gmail.com', 'afawdawdaw', 'Ce380@3042R', '2025-04-02 18:42:02', 'img/67ed691da037b_OKUa.gif'),
(8, 'Eric', 'ericsouzapalma123@gmail.com', 'gfyghyghg', '$2y$10$alK5Xk0uZvt9Ys/zT5pLm.45soOKN6bfxyT0QXjNII2VJ9vu4c/7G', '2025-04-02 19:20:11', 'img/67f65061738fd_7ugE.gif'),
(9, 'marim', 'marim@gmail.com', NULL, '555', '2025-04-04 17:56:25', NULL),
(10, 'Davi', 'Davi@gmail.com', NULL, '$2y$10$YCKu6bPEhnWbc4Na2rh16.lCoKrkyDDpcfbn6OI3a9NXOXonEvO4m', '2025-04-04 18:10:35', NULL),
(11, 'catarina', 'catarina@gmail.com', NULL, '$2y$10$17hkY1Z1cobIV1fpEFP4quB3aMpL.hxNIse//ct5mNu5N4txLpzl2', '2025-04-04 18:12:23', NULL),
(12, 'jonatas', 'jonatas.goncalves@sp.senai.br', 'professor do tecnico de desenvolvimento de sistemas', '$2y$10$ioo5KdmxZPy/L4cTdJlJr.wggppupiqiA.UelnrS953BnEqKbK5DK', '2025-04-09 15:56:21', 'img/67f6874466791_7ugE.gif');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `quem_sou_eu`
--
ALTER TABLE `quem_sou_eu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `teste_personalidade`
--
ALTER TABLE `teste_personalidade`
  ADD PRIMARY KEY (`user_id`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `quem_sou_eu`
--
ALTER TABLE `quem_sou_eu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `quem_sou_eu`
--
ALTER TABLE `quem_sou_eu`
  ADD CONSTRAINT `quem_sou_eu_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `teste_personalidade`
--
ALTER TABLE `teste_personalidade`
  ADD CONSTRAINT `teste_personalidade_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 16/04/2025 às 20:41
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
-- Estrutura para tabela `landing_pages`
--

CREATE TABLE `landing_pages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `titulo_principal` varchar(255) DEFAULT NULL,
  `subtitulo` text DEFAULT NULL,
  `sobre` text DEFAULT NULL,
  `educacao` text DEFAULT NULL,
  `carreira` text DEFAULT NULL,
  `contato` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `publico` tinyint(1) DEFAULT 0,
  `subtitulo_principal` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Despejando dados para a tabela `landing_pages`
--

INSERT INTO `landing_pages` (`id`, `user_id`, `titulo_principal`, `subtitulo`, `sobre`, `educacao`, `carreira`, `contato`, `criado_em`, `publico`, `subtitulo_principal`) VALUES
(3, 7, 'Cozinheira', 'Conzinhar', 'Cozinho', 'doutorado em conzinhar', 'cozinhei para o Eric jackan e ele gostou', 'Robert@gmail.com', '2025-04-16 13:12:07', 1, 'cozi'),
(4, 4, 'O pai', NULL, 'criador de tudo', 'quando necessario', 'top', 'não gosto', '2025-04-16 13:49:50', 0, 'ta on'),
(5, 8, '', NULL, '', '', '', '', '2025-04-16 16:24:52', 0, '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `plano_acao`
--

CREATE TABLE `plano_acao` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `prazo` date DEFAULT NULL,
  `concluida` tinyint(1) DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Despejando dados para a tabela `plano_acao`
--

INSERT INTO `plano_acao` (`id`, `user_id`, `titulo`, `descricao`, `prazo`, `concluida`, `criado_em`) VALUES
(10, 8, 'dsajfjiasfmksdf', 'sadfgweafsadf', '2025-04-28', 1, '2025-04-11 13:44:42'),
(11, 8, 'werqejwiqjrewr', 'rewarfkelrk', '2025-04-30', 1, '2025-04-11 13:45:06'),
(12, 8, 'ewfaewfEFE', 'sfesFsf', '2025-05-08', 1, '2025-04-11 13:45:21'),
(13, 8, 'dsaDSADSADSAD', 'ASDASFEGFVFCKJNEWJ\\EVN JEHSNDVJ NDJVKA NJD\\V JSDBOKVJcmjjvibhwDHJVn ikmdwejkafhbcuwegqdjmwokndcjndksjiqfvewhuqjfcnjewsjaidugbwhnxqinzimzowkefckpriavhjrodjrfahuvbdcnjdcizgharbjeigtufhjiwkoefkgsjibfjbfkogfiekopfijkefghhfgedfrghjk,,jhgtfrfgfh', '2025-11-26', 1, '2025-04-16 11:15:14'),
(15, 8, 'Arrumar um emprego', 'fefe', '2027-06-16', 1, '2025-04-16 11:16:36'),
(16, 4, 'Arrumar um emprego', '', '2025-04-24', 1, '2025-04-16 13:48:17');

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

--
-- Despejando dados para a tabela `quem_sou_eu`
--

INSERT INTO `quem_sou_eu` (`id`, `user_id`, `fale_sobre_voce`, `minhas_lembrancas`, `pontos_fortes`, `pontos_fracos`, `meus_valores`, `principais_aptidoes`, `relacoes_familia`, `relacoes_amigos`, `relacoes_escola`, `relacoes_sociedade`, `gosto_fazer`, `nao_gosto_fazer`, `rotina`, `lazer`, `estudos`, `vida_escolar`, `visao_fisica`, `visao_intelectual`, `visao_emocional`, `visao_dos_amigos`, `visao_dos_familiares`, `visao_dos_professores`, `autovalorizacao_total`, `created_at`) VALUES
(6, 8, 'safcsafcds', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 50, '2025-04-11 10:28:22'),
(7, 4, 'sou lindo', 'poucas', 'legal', 'canseira', '3000000', 'rapido,organizado', 'pouca', 'menos ainda', 'me matando', 'legal', 'ficar no pc', 'acordar cedo', 'enrolada', 'ficar no pc ', 'bastante', 'enrolada', 'gordo', 'cansado', 'de boa', 'sou legal', 'irritante', 'nem sei', 70, '2025-04-16 13:46:58');

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
(4, 88, 67, 73, 80, '2025-04-16 13:48:50'),
(8, 91, 31, 23, 22, '2025-04-11 12:34:05');

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
(4, 'jonata', 'jonatas@docente.br', 'meu Deus, meu senhor me ajuda por favor!', '$2y$10$8Nv0DHAotlQ4VUcZm.VK8evTCkFnZrR/lvjDAgoFJmJE7zveFpxy6', '2025-03-28 14:54:10', 'img/67ed0fc589c6d_7ugE.gif'),
(5, 'bernini', 'bernini@bernini.com', 'sor bernas senai', '1234', '2025-03-28 18:29:22', 'img/67e6e878e2c5b_OKUa.gif'),
(6, 'Rafa', 'rafael@gmail.com', '', '1234', '2025-03-28 19:33:54', 'img/67e6ebc794cf4_XGrF.gif'),
(7, 'roberto', 'robertohenryck365@gmail.com', 'afawdawdaw', '$2y$10$RCA01/hwl9Vi.ZjRl/FjQ.k0Rj/4TaN/cHrxdSDHar/Mw6hkpBPvW', '2025-04-02 18:42:02', 'img/67ed691da037b_OKUa.gif'),
(8, 'Eric', 'ericsouzapalma123@gmail.com', 'gfyghyghg', '$2y$10$alK5Xk0uZvt9Ys/zT5pLm.45soOKN6bfxyT0QXjNII2VJ9vu4c/7G', '2025-04-02 19:20:11', 'img/67f9042907fc5_OKUa.gif'),
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
-- Índices de tabela `landing_pages`
--
ALTER TABLE `landing_pages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `plano_acao`
--
ALTER TABLE `plano_acao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT de tabela `landing_pages`
--
ALTER TABLE `landing_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `plano_acao`
--
ALTER TABLE `plano_acao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `quem_sou_eu`
--
ALTER TABLE `quem_sou_eu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `landing_pages`
--
ALTER TABLE `landing_pages`
  ADD CONSTRAINT `landing_pages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `plano_acao`
--
ALTER TABLE `plano_acao`
  ADD CONSTRAINT `plano_acao_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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

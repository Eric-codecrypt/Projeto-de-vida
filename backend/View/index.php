<?php
session_start();
require_once __DIR__ . '/../../config.php'; // Caminho correto para o config.php

$logged_in = isset($_SESSION["user_id"]); // Verifica se há um usuário logado

$feedback_msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $mensagem = trim($_POST['mensagem']);
    $Assunto = trim($_POST['Assunto']);

    if (!empty($nome) && !empty($email) && !empty($mensagem) && !empty($Assunto)) {
        $stmt = $pdo->prepare("INSERT INTO feedback (nome, email, mensagem, Assunto) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$nome, $email, $mensagem, $Assunto])) {
            $feedback_msg = "<p style='color: green;'>Feedback enviado com sucesso!</p>";
        } else {
            $feedback_msg = "<p style='color: red;'>Erro ao enviar feedback.</p>";
        }
    } else {
        $feedback_msg = "<p style='color: red;'>Todos os campos são obrigatórios.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projeto de vida - Estudante de programação</title>
    <!-- Adicione aqui os links para CSS externos e outros recursos -->
    <style>
        /* Estilos para o header e navegação */
        #navbar {
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-in-out;
            z-index: 1000;
        }

        #navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 5%;
            max-width: 1200px;
            margin: 0 auto;
        }

        #navbar .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            text-decoration: none;
        }

        #navbar .logo span {
            color: #007bff;
        }

        #navbar nav {
            display: flex;
            align-items: center;
        }

        #navbar .desktop-nav {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        #navbar .desktop-nav li {
            margin-left: 25px;
        }

        #navbar .desktop-nav a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            font-size: 1rem;
            transition: color 0.3s ease;
            position: relative;
        }

        #navbar .desktop-nav a:hover {
            color: #007bff;
        }

        #navbar .desktop-nav a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background-color: #007bff;
            transition: width 0.3s ease;
        }

        #navbar .desktop-nav a:hover::after {
            width: 100%;
        }

        /* Mobile menu button */
        #mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #333;
        }

        /* Mobile menu */
        #mobile-menu {
            position: fixed;
            top: 70px;
            left: 0;
            width: 100%;
            background-color: white;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
            z-index: 999;
            transform: translateY(0);
            transition: transform 0.3s ease;
        }

        #mobile-menu.hidden {
            transform: translateY(-100%);
            visibility: hidden;
        }

        #mobile-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        #mobile-menu li {
            border-bottom: 1px solid #eee;
        }

        #mobile-menu a {
            display: block;
            padding: 15px 20px;
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        #mobile-menu a:hover {
            background-color: #f8f9fa;
            color: #007bff;
        }

        /* Estilo para quando o header fica com fundo sólido após scroll */
        .navbar-scrolled {
            background-color: white !important;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.15) !important;
        }

        /* Media queries para responsividade */
        @media (max-width: 992px) {
            #navbar .desktop-nav {
                display: none;
            }

            #mobile-menu-btn {
                display: block;
            }

            #navbar .container {
                padding: 10px 5%;
            }
        }

        /* Ajustes para telas muito pequenas */
        @media (max-width: 480px) {
            #navbar .logo {
                font-size: 1.5rem;
            }
        }

        /* Espaço para evitar que o conteúdo da página fique atrás do header fixo */
        body {
            padding-top: 80px;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Estilos básicos para o restante da página */
        .content-section {
            padding: 60px 5%;
            max-width: 1200px;
            margin: 0 auto;
        }

        section {
            min-height: 100vh;
            padding: 80px 20px;
            box-sizing: border-box;
        }

        h1, h2, h3 {
            color: #333;
        }
    </style>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
<script>
    // Código JavaScript para controlar o menu mobile
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');

        // Função para alternar o menu mobile
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
            // Alterna o ícone do botão entre hambúrguer e X
            this.textContent = mobileMenu.classList.contains('hidden') ? '☰' : '✕';
            this.setAttribute('aria-label',
                mobileMenu.classList.contains('hidden') ? 'Abrir menu' : 'Fechar menu');
        });

        // Fechar o menu quando um link é clicado
        const mobileLinks = mobileMenu.getElementsByTagName('a');
        for (let i = 0; i < mobileLinks.length; i++) {
            mobileLinks[i].addEventListener('click', function() {
                mobileMenu.classList.add('hidden');
                mobileMenuBtn.textContent = '☰';
                mobileMenuBtn.setAttribute('aria-label', 'Abrir menu');
            });
        }

        // Adicionar funcionalidade de scroll para o header fixo
        let prevScrollpos = window.pageYOffset;
        window.onscroll = function() {
            const navbar = document.getElementById("navbar");
            let currentScrollPos = window.pageYOffset;

            // Adiciona classe quando o scroll é maior que 100px
            if (currentScrollPos > 100) {
                navbar.classList.add("navbar-scrolled");
            } else {
                navbar.classList.remove("navbar-scrolled");
            }

            // Oculta/mostra navbar baseado na direção do scroll
            if (prevScrollpos > currentScrollPos) {
                navbar.style.top = "0";
            } else {
                // Não oculta se o menu mobile estiver aberto
                if (mobileMenu.classList.contains('hidden')) {
                    navbar.style.top = "-80px";
                }
            }
            prevScrollpos = currentScrollPos;
        }
    });
</script>

<!-- Navigation -->
<header id="navbar">
    <div class="container">
        <a href="index.php" class="logo">Projeto de <span>Vida</span></a>

        <nav>
            <ul class="desktop-nav">
                <li><a href="#Inicio">Início</a></li>
                <li><a href="#Sobre">Sobre</a></li>
                <li><a href="#Educacao">Educação</a></li>
                <li><a href="#Carreira">Carreira</a></li>
                <li><a href="#Contato">Contato</a></li>
                <?php if ($logged_in): ?>
                    <li><a href="user.php">Perfil</a></li>
                    <li><a href="logout.php">Sair</a></li>
                <?php else: ?>
                    <li><a href="login.php">Entrar</a></li>
                    <li><a href="register.php">Cadastrar</a></li>
                <?php endif; ?>
            </ul>

            <button id="mobile-menu-btn" aria-label="Abrir menu">☰</button>
        </nav>
    </div>

    <!-- Mobile Navigation -->
    <div id="mobile-menu" class="hidden">
        <ul>
            <li><a href="#Inicio">Início</a></li>
            <li><a href="#Sobre">Sobre</a></li>
            <li><a href="#Educacao">Educação</a></li>
            <li><a href="#Carreira">Carreira</a></li>
            <li><a href="#Contato">Contato</a></li>
            <?php if ($logged_in): ?>
                <li><a href="user.php">Perfil</a></li>
                <li><a href="logout.php">Sair</a></li>
            <?php else: ?>
                <li><a href="login.php">Entrar</a></li>
                <li><a href="register.php">Cadastrar</a></li>
            <?php endif; ?>
        </ul>
    </div>
</header>

<!-- Espaço para evitar sobreposição do conteúdo pelo header fixo -->
<div style="height: 60px;"></div>


<!-- Hero Section -->
    <section id="Inicio" class="hero">
        <div class="container">
            <div class="tag">SESI/SENAI programador & Futuro Estudante de Ciência da Computação</div>
            <h1>
                <span class="title-line">Criando um futuro com</span>
                <span class="gradient-text">Programação & Inovação</span>
            </h1>
            <p class="subtitle">
                Explorando o caminho do ensino médio ao desenvolvimento profissional, com foco na construção de uma carreira em ciência da computação e tecnologia.
            </p>
            <div class="buttons">
                <a href="#Sobre" class="btn btn-primary">Sobre mim</a>
                <a href="#Educacao" class="btn btn-secondary">Saiba mais</a>
            </div>
            <div class="scroll-indicator">
                <a href="#Sobre">
                    <span>
                        Role para baixo</span>
                    <div class="arrow-down"></div>
                </a>
            </div>
        </div>
    </section>

    <!-- Sobre Section -->
    <section id="Sobre" class="section">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">SOBRE MIM</span>
                <h2>Paixão por Tecnologia & <span class="gradient-text">Solução de Problemas</span></h2>
                <p class="section-description">
                    Sou um estudante dedicado de ciência da computação com profundo interesse em programação, desenvolvimento de software e nas infinitas possibilidades que a tecnologia oferece para melhorar nosso mundo.
                </p>
            </div>

            <div class="features">
                <div class="feature-card">
                    <div class="icon">
                        <div class="icon-code"></div>
                    </div>
                    <h3>Programação</h3>
                    <p>Aprender diversas linguagens de programação e frameworks para criar soluções inovadoras.</p>
                </div>
                <div class="feature-card">
                    <div class="icon">
                        <div class="icon-monitor"></div>
                    </div>
                    <h3>Desenvolvimento Web</h3>
                    <p>Criação de sites e aplicativos responsivos e fáceis de usar usando tecnologias modernas.</p>
                </div>
                <div class="feature-card">
                    <div class="icon">
                        <div class="icon-cpu"></div>
                    </div>
                    <h3>
                        Engenharia de Software</h3>
                    <p>Aplicar princípios de engenharia para projetar e desenvolver sistemas de software confiáveis.</p>
                </div>
                <div class="feature-card">
                    <div class="icon">
                        <div class="icon-book"></div>
                    </div>
                    <h3>Aprendizagem Contínua</h3>
                    <p>Expandindo constantemente meu conhecimento para me manter atualizado com tecnologias em rápida evolução.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Educacao Section -->
    <section id="Educacao" class="section section-alt">
        <div class="container">
            <div class="section-header">
                <div class="icon-circle">
                    <div class="icon-graduation"></div>
                </div>
                <h2>
                    Minha<span class="gradient-text"> Jornada </span>educacional</h2>
                <p class="section-description">
                    Construindo uma base sólida em ciência da computação por meio de educação formal e aprendizagem autodirigida.
                </p>
            </div>

            <div class="grid-two-col">
                <div>
                    <h3>Cronograma Acadêmico</h3>

                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="year">2024 - Presente</div>
                            <h4>
                                Ensino Médio - Curso de Programação<span class="badge">Atual</span></h4>
                            <p>Atualmente em um curso tecnico de programação, com foco em variados fundamentos do nicho, algoritmos e desenvolvimento de software.</p>
                        </div>
                        <div class="timeline-item">
                            <div class="year">2025 - 2026</div>
                            <h4>Advanced Placement Computer Science</h4>
                            <p>
                                Planejando fazer cursos de Ciência da Computação AP para obter conhecimento de nível universitário sobre conceitos de programação e estruturas de dados.</p>
                        </div>
                        <div class="timeline-item">
                            <div class="year">2026 - 2029</div>
                            <h4>Bacharel em Ciência da Computação</h4>
                            <p>Meta futura: cursar Bacharelado em Ciência da Computação ou área relacionada em uma universidade focada em tecnologia.</p>
                        </div>
                    </div>
                </div>

                <div class="glass-card">
                    <h3>Habilidades e Tecnologias</h3>

                    <div>
                        <h4>Atualmente aprendendo</h4>
                        <div class="skills">
                            <span class="skill-badge">HTML & CSS</span>
                            <span class="skill-badge">JavaScript</span>
                            <span class="skill-badge">Algoritmos basicos</span>
                            <span class="skill-badge">Resolução de Problemas</span>
                        </div>

                        <h4>Foco em habilidades futuras</h4>
                        <div class="skills">
                            <span class="skill-badge">React.js</span>
                            <span class="skill-badge">Node.js</span>
                            <span class="skill-badge">Sistemas de Banco de Dados</span>
                            <span class="skill-badge">Cloud Computing</span>
                            <span class="skill-badge">Desenvolvedor Mobile</span>
                            <span class="skill-badge">Inteligência artificial</span>
                            <span class="skill-badge">Machine Learning</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Carreira Section -->
    <section id="Carreira" class="section">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">ASPIRAÇÕES DE CARREIRA</span>
                <h2>Construindo um Futuro em <span class="gradient-text">tecnologia</span></h2>
                <p class="section-description">
                    Explorando possíveis caminhos de carreira no setor de tecnologia em constante evolução, com foco em inovação e resolução de problemas.
                </p>
            </div>

            <div class="Carreira-paths">
                <div class="Carreira-card">
                    <div class="icon">
                        <div class="icon-laptop"></div>
                    </div>
                    <h3>desenvolvedor de sistemas</h3>
                    <p>Criar e manter aplicativos, sites e sistemas de software que resolvam problemas do mundo real e melhorem as experiências do usuário.</p>
                    <div class="skills">
                        <span class="skill-badge">Linguagens de programação</span>
                        <span class="skill-badge">
                            Arquitetura de Software</span>
                        <span class="skill-badge">Depurando</span>
                        <span class="skill-badge">Testando</span>
                    </div>
                </div>

                <div class="Carreira-card">
                    <div class="icon">
                        <div class="icon-globe"></div>
                    </div>
                    <h3>
                        Desenvolvedor Web</h3>
                    <p>Projetar, implementar e manter infraestrutura de sistemas complexos, garantindo confiabilidade, escalabilidade e segurança.</p>
                    <div class="skills">
                        <span class="skill-badge">Front-end Frameworks</span>
                        <span class="skill-badge">Back-end Development</span>
                        <span class="skill-badge">UI/UX Design</span>
                        <span class="skill-badge">Otimização de Performace</span>
                    </div>
                </div>

                <div class="Carreira-card">
                    <div class="icon">
                        <div class="icon-server"></div>
                    </div>
                    <h3>Engenheiro de Sistemas</h3>
                    <p>Projetar, implementar e manter infraestrutura de sistemas complexos, garantindo confiabilidade, escalabilidade e segurança.</p>
                    <div class="skills">
                        <span class="skill-badge">Cloud Platforms</span>
                        <span class="skill-badge">Networking</span>
                        <span class="skill-badge">Segurança</span>
                        <span class="skill-badge">DevOps</span>
                    </div>
                </div>

                <div class="Carreira-card">
                    <div class="icon">
                        <div class="icon-code"></div>
                    </div>
                    <h3>
                        Engenheiro de IA/ML</h3>
                    <p>Desenvolver sistemas inteligentes que podem aprender com dados e tomar decisões ou fazer previsões, ultrapassando os limites do que é possível.</p>
                    <div class="skills">
                        <span class="skill-badge">Machine Learning</span>
                        <span class="skill-badge">Data Science</span>
                        <span class="skill-badge">Algorithm Design</span>
                        <span class="skill-badge">Neural Networks</span>
                    </div>
                </div>
            </div>

            <div class="Carreira-plan">
                <h3>Meu Plano de Desenvolvimento de Carreira</h3>
                <ol>
                    <li><span>1</span>Construir uma base sólida em fundamentos da ciência da computação durante o ensino médio</li>
                    <li><span>2</span>Desenvolver projetos pessoais para aplicar conhecimentos teóricos e construir um portfólio</li>
                    <li><span>3</span>Prosseguir com o ensino superior em ciência da computação ou área relacionada</li>
                    <li><span>4</span>Ganhar experiência de estágio para entender as práticas do setor</li>
                    <li><span>5</span>Especializar-se em uma área específica com base em interesses e demanda do setor</li>
                </ol>
            </div>
        </div>
    </section>

    <!-- Contato Section -->
    <section id="Contato" class="section section-alt">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">ENTRE EM CONTATO</span>
                <h2><span class="gradient-text">Conecte-se</span> & colabore</h2>
                <p class="section-description">
                    Interessado em discutir sobre programação?, compartilhar recursos ou explorar potenciais colaborações? Eu adoraria ouvir de você.
                </p>
            </div>

            <section id="Feedback" class="section">
                <div class="Contato-container">
                    <div class="Contato-form">
                        <h3>Me mande uma Mensagem</h3>
                        <?php if (isset($feedback_msg)) echo $feedback_msg; ?>
                        <form method="POST" id="ContatoForm">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="nome">Nome:</label>
                                    <input type="text" id="nome" name="nome" placeholder="Seu nome:" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">E-mail:</label>
                                    <input type="email" id="email" name="email" placeholder="Seu email" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="Assunto">Assunto</label>
                                <input type="text" id="Assunto" name="Assunto" placeholder="Assunto">
                            </div>
                            <div class="form-group">
                                <label for="mensagem">Mensagem</label>
                                <textarea id="mensagem" name="mensagem" rows="5" placeholder="Sua mensagem" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Enviar mensagem</button>
                        </form>
                    </div>
                </div>
            </section>
            <div class="Contato-info">
                <h3>Informações de contato</h3>
                <div class="info-item">
                    <div class="icon-mail"></div>
                    <div>
                        <h4>Email</h4>
                        <p>eric.palma@portalsesisp.org.br</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="icon-map-pin"></div>
                    <div>
                        <h4>
                            Localização</h4>
                        <p>SESI 380 Centro Educacional de Paraguaçu Paulista</p>
                    </div>
                </div>
                <div class="social-links">
                    <a href="https://github.com/Eric-codecrypt" class="social-icon github"></a>
                    <a href="#" class="social-icon linkedin"></a>
                    <a href="#" class="social-icon twitter"></a>
                </div>
            </div>
        </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>© <span id="current-year"></span> Projeto de vida. Todos os Direitos Reservados.</p>

            <p>Projetado com paixão por um entusiasta da programação, <span>Powered by https://github.com/Eric-codecrypt</span></p>
        </div>
    </footer>

    <!-- Particles Background -->
    <canvas id="particles"></canvas>

    <script src="script.js"></script>
</body>

</html>
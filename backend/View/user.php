<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controllerPath = __DIR__ . '/../Controller/UserController.php';
if (file_exists($controllerPath)) {
    require_once $controllerPath;
} else {
    die("Erro: Arquivo UserController.php não encontrado em $controllerPath");
}

$configPath = __DIR__ . '/../../config.php';
if (file_exists($configPath)) {
    require_once $configPath;
} else {
    die("Erro: Arquivo config.php não encontrado em $configPath");
}

if (!class_exists('UserController')) {
    die("Erro: Classe UserController não encontrada.");
}

$Controller = new UserController($pdo);

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $Controller->getUserFromID($user_id)["username"];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Usuário não encontrado.");
}

$authType = $_SESSION['auth_type'] ?? 'normal';

// Buscar dados do formulário "Quem Sou Eu" (se existirem)
$stmt = $pdo->prepare("SELECT * FROM quem_sou_eu WHERE user_id = ?");
$stmt->execute([$user_id]);
$perfil = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['description'])) {
        $new_description = $_POST['description'];
        $stmt = $pdo->prepare("UPDATE users SET description = ? WHERE id = ?");
        $stmt->execute([$new_description, $user_id]);
        header("Location: user.php");
        exit();
    }

    if (isset($_POST['edit_usuario'])) {
        $new_name = trim($_POST['name']);
        $new_email = trim($_POST['email']);

        if (!empty($new_name) && !empty($new_email)) {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([$new_name, $new_email, $user_id]);
            $username = $new_name;
            $user['email'] = $new_email;
        }
    }

    if (isset($_POST['salvar_perfil_completo'])) {
        $fale = $_POST['sobre_voce'] ?? '';
        $lembrancas = $_POST['lembrancas'] ?? '';
        $p_fortes = $_POST['pontos_fortes'] ?? '';
        $p_fracos = $_POST['pontos_fracos'] ?? '';
        $valores = $_POST['valores'] ?? '';
        $aptidoes = implode(', ', $_POST['aptidoes'] ?? []);
        $rel_familia = $_POST['familia'] ?? '';
        $rel_amigos = $_POST['amigos'] ?? '';
        $rel_escola = $_POST['escola'] ?? '';
        $rel_sociedade = $_POST['sociedade'] ?? '';
        $gosto = $_POST['gosto_fazer'] ?? '';
        $nao_gosto = $_POST['nao_gosto'] ?? '';
        $rotina = $_POST['rotina'] ?? '';
        $lazer = $_POST['lazer'] ?? '';
        $estudos = $_POST['estudos'] ?? '';
        $vida_escolar = $_POST['vida_escolar'] ?? '';
        $visao_fisica = $_POST['visao_fisica'] ?? '';
        $visao_intelectual = $_POST['visao_intelectual'] ?? '';
        $visao_emocional = $_POST['visao_emocional'] ?? '';
        $visao_amigos = $_POST['visao_amigos'] ?? '';
        $visao_familiares = $_POST['visao_familiares'] ?? '';
        $visao_professores = $_POST['visao_professores'] ?? '';
        $auto_total = (int) ($_POST['autovalorizacao'] ?? 0);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM quem_sou_eu WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $existe = $stmt->fetchColumn();

        if ($existe) {
            $stmt = $pdo->prepare("UPDATE quem_sou_eu SET 
                fale_sobre_voce=?, minhas_lembrancas=?, pontos_fortes=?, pontos_fracos=?, meus_valores=?,
                principais_aptidoes=?, relacoes_familia=?, relacoes_amigos=?, relacoes_escola=?, relacoes_sociedade=?,
                gosto_fazer=?, nao_gosto_fazer=?, rotina=?, lazer=?, estudos=?, vida_escolar=?,
                visao_fisica=?, visao_intelectual=?, visao_emocional=?,
                visao_dos_amigos=?, visao_dos_familiares=?, visao_dos_professores=?, autovalorizacao_total=?
                WHERE user_id = ?");
            $stmt->execute([
                $fale,
                $lembrancas,
                $p_fortes,
                $p_fracos,
                $valores,
                $aptidoes,
                $rel_familia,
                $rel_amigos,
                $rel_escola,
                $rel_sociedade,
                $gosto,
                $nao_gosto,
                $rotina,
                $lazer,
                $estudos,
                $vida_escolar,
                $visao_fisica,
                $visao_intelectual,
                $visao_emocional,
                $visao_amigos,
                $visao_familiares,
                $visao_professores,
                $auto_total,
                $user_id
            ]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO quem_sou_eu (
                user_id, fale_sobre_voce, minhas_lembrancas, pontos_fortes, pontos_fracos, meus_valores,
                principais_aptidoes, relacoes_familia, relacoes_amigos, relacoes_escola, relacoes_sociedade,
                gosto_fazer, nao_gosto_fazer, rotina, lazer, estudos, vida_escolar,
                visao_fisica, visao_intelectual, visao_emocional,
                visao_dos_amigos, visao_dos_familiares, visao_dos_professores, autovalorizacao_total
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $user_id,
                $fale,
                $lembrancas,
                $p_fortes,
                $p_fracos,
                $valores,
                $aptidoes,
                $rel_familia,
                $rel_amigos,
                $rel_escola,
                $rel_sociedade,
                $gosto,
                $nao_gosto,
                $rotina,
                $lazer,
                $estudos,
                $vida_escolar,
                $visao_fisica,
                $visao_intelectual,
                $visao_emocional,
                $visao_amigos,
                $visao_familiares,
                $visao_professores,
                $auto_total
            ]);
        }

        header("Location: user.php");
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["profile_picture"])) {
    $file = $_FILES["profile_picture"];
    $uploadDir = __DIR__ . "/img/";

    $allowedTypes = ["image/jpeg", "image/png", "image/gif"];
    $fileType = mime_content_type($file["tmp_name"]);

    if ($file["error"] === 0 && in_array($fileType, $allowedTypes)) {
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = uniqid() . "_" . basename($file["name"]);
        $filePath = "img/" . $fileName;

        if (move_uploaded_file($file["tmp_name"], $uploadDir . $fileName)) {
            $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $stmt->execute([$filePath, $user_id]);
            header("Location: user.php");
            exit();
        } else {
            echo "Erro ao mover o arquivo.";
        }
    } else {
        echo "Formato inválido. Use JPG, PNG ou GIF.";
    }
}

if (isset($_POST['teste_personalidade'])) {
    $extrovertido = (int) ($_POST['extrovertido'] ?? 0);
    $intuitivo = (int) ($_POST['intuitivo'] ?? 0);
    $racional = (int) ($_POST['racional'] ?? 0);
    $julgador = (int) ($_POST['julgador'] ?? 0);

    // Salva os dados no banco
    $stmt = $pdo->prepare("REPLACE INTO teste_personalidade (user_id, extrovertido, intuitivo, racional, julgador) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $extrovertido, $intuitivo, $racional, $julgador]);

    // Redireciona após o envio
    header("Location: user.php");
    exit;
}

// Busca os dados do banco
$stmt = $pdo->prepare("SELECT * FROM teste_personalidade WHERE user_id = ?");
$stmt->execute([$user_id]);
$personalidade = $stmt->fetch(PDO::FETCH_ASSOC);

// Define traço dominante, se houver dados
$traço_dominante = '';
if ($personalidade) {
    $dados = [
        'Extrovertido' => (int) $personalidade['extrovertido'],
        'Intuitivo' => (int) $personalidade['intuitivo'],
        'Racional' => (int) $personalidade['racional'],
        'Julgador' => (int) $personalidade['julgador']
    ];
    arsort($dados);
    $traço_dominante = array_key_first($dados);
}

$profilePicture = !empty($user['profile_picture']) ? $user['profile_picture'] : "img/default.png";

//progresso
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['adicionar_meta'])) {
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $prazo = $_POST['prazo'];

    if (!empty($titulo) && !empty($prazo)) {
        $stmt = $pdo->prepare("INSERT INTO plano_acao (user_id, titulo, descricao, prazo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $titulo, $descricao, $prazo]);
        header("Location: user.php");
        exit;
    }
}

$proximasMetas = [];

// Buscar metas pendentes com prazo futuro
$stmt = $pdo->prepare("SELECT * FROM plano_acao WHERE user_id = ? AND concluida = 0 AND prazo >= CURDATE() ORDER BY prazo ASC");
$stmt->execute([$user_id]);
$proximasMetas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Busca todas as metas do usuário
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM plano_acao WHERE user_id = ?");
$stmt->execute([$user_id]);
$totalMetas = $stmt->fetchColumn();

// Busca metas concluídas
$stmt = $pdo->prepare("SELECT COUNT(*) as concluidas FROM plano_acao WHERE user_id = ? AND concluida = 1");
$stmt->execute([$user_id]);
$metasFeitas = $stmt->fetchColumn();

$porcentagemConcluida = $totalMetas > 0 ? round(($metasFeitas / $totalMetas) * 100) : 0;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['concluir_meta'])) {
    $metaId = (int) $_POST['concluir_meta'];
    $stmt = $pdo->prepare("UPDATE plano_acao SET concluida = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$metaId, $user_id]);
    header("Location: user.php");
    exit;
}

// Buscar dados do landing page (se existir)
$stmt = $pdo->prepare("SELECT * FROM landing_pages WHERE user_id = ?");
$stmt->execute([$user_id]);
$landing = $stmt->fetch(PDO::FETCH_ASSOC);

// Processar formulário de edição de landing page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_landing'])) {
    $titulo_principal = $_POST['titulo_principal'] ?? '';
    $subtitulo_principal = $_POST['subtitulo_principal'] ?? '';
    $sobre = $_POST['sobre'] ?? '';
    $educacao = $_POST['educacao'] ?? '';
    $carreira = $_POST['carreira'] ?? '';
    $contato = $_POST['contato'] ?? '';
    $publico = isset($_POST['publico']) ? 1 : 0;

    if ($landing) {
        $query = "UPDATE landing_pages SET titulo_principal=?, subtitulo_principal=?, sobre=?, educacao=?, carreira=?, contato=?, publico=? WHERE user_id=?";
        $pdo->prepare($query)->execute([$titulo_principal, $subtitulo_principal, $sobre, $educacao, $carreira, $contato, $publico, $user_id]);
    } else {
        $query = "INSERT INTO landing_pages (user_id, titulo_principal, subtitulo_principal, sobre, educacao, carreira, contato, publico) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $pdo->prepare($query)->execute([$user_id, $titulo_principal, $subtitulo_principal, $sobre, $educacao, $carreira, $contato, $publico]);
    }

    // Recarregar os dados após atualização
    $stmt = $pdo->prepare("SELECT * FROM landing_pages WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $landing = $stmt->fetch(PDO::FETCH_ASSOC);

    // Redirecionar para atualizar a página
    header("Location: user.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Usuário</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="user-styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">



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
        }
    </style>

    <!-- Navigation -->
    <header id="navbar">
        <div class="container">
            <a href="index.php" class="logo">Projeto de <span>Vida</span></a>

            <nav>
                <ul class="desktop-nav">
                    <li><a href="index.php?#Inicio">Início</a></li>
                    <li><a href="index.php?#Sobre">Sobre</a></li>
                    <li><a href="index.php?#Educacao">Educação</a></li>
                    <li><a href="index.php?#Carreira">Carreira</a></li>
                    <li><a href="index.php?#Contato">Contato</a></li>
                    <li><a href="user.php">Perfil</a></li>
                </ul>

                <button id="mobile-menu-btn" aria-label="Abrir menu">☰</button>
            </nav>
        </div>

        <!-- Mobile Navigation -->
        <div id="mobile-menu" class="hidden">
            <ul>
                <li><a href="index.php?#Inicio">Início</a></li>
                <li><a href="index.php?#Sobre">Sobre</a></li>
                <li><a href="index.php?#Educacao">Educação</a></li>
                <li><a href="index.php?#Carreira">Carreira</a></li>
                <li><a href="index.php?#Contato">Contato</a></li>
                <li><a href="user.php">Perfil</a></li>
            </ul>
        </div>
    </header>

    <!-- Adicione um espaço depois do header -->
    <div style="height: 80px;"></div>



    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Funções para as abas
            const tabs = document.querySelectorAll('.perfil-tab');
            const tabContents = document.querySelectorAll('.tab-content');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    // Remove a classe active de todas as abas
                    tabs.forEach(t => t.classList.remove('active'));
                    // Adiciona a classe active na aba clicada
                    tab.classList.add('active');

                    // Esconde todos os conteúdos
                    tabContents.forEach(content => content.classList.add('hidden'));
                    // Mostra o conteúdo da aba selecionada
                    const tabId = tab.getAttribute('data-tab');
                    document.getElementById(tabId).classList.remove('hidden');
                });
            });

            // Funções para modais
            const modalTriggers = document.querySelectorAll('[data-modal]');
            const modals = document.querySelectorAll('.modal');
            const closeButtons = document.querySelectorAll('.fechar-modal');

            modalTriggers.forEach(trigger => {
                trigger.addEventListener('click', () => {
                    const modalId = trigger.getAttribute('data-modal');
                    document.getElementById(modalId).classList.add('show');
                });
            });

            closeButtons.forEach(button => {
                button.addEventListener('click', () => {
                    button.closest('.modal').classList.remove('show');
                });
            });

            // Fechar modal ao clicar fora
            window.addEventListener('click', (e) => {
                modals.forEach(modal => {
                    if (e.target === modal) {
                        modal.classList.remove('show');
                    }
                });
            });
        });
    </script>
</head>
<body>
<main class="perfil-container animate-fadeIn">
    <section class="perfil-header">
        <div class="perfil-foto">
            <img src="<?php echo $profilePicture; ?>" alt="Foto de perfil">
            <label for="file-upload" class="editar-foto">
                <i class="fas fa-camera"></i>
            </label>
            <form method="POST" enctype="multipart/form-data" style="display: none;">
                <input id="file-upload" type="file" name="profile_picture" onchange="this.form.submit()">
            </form>
        </div>

        <div class="perfil-info">
            <h1 class="perfil-nome"><?php echo $username; ?></h1>
            <p class="perfil-email"><?php echo $user['email']; ?></p>

            <?php if (!empty($user['description'])): ?>
                <div class="perfil-bio">
                    <p><?php echo $user['description']; ?></p>
                </div>
            <?php endif; ?>

            <div class="perfil-acoes">
                <button class="btn btn-primario" data-modal="editar-perfil">
                    <i class="fas fa-user-edit"></i> Editar Perfil
                </button>
                <button class="btn btn-secundario" data-modal="editar-bio">
                    <i class="fas fa-edit"></i> Editar Bio
                </button>
            </div>
        </div>
    </section>

    <div class="perfil-tabs">
        <button class="perfil-tab active" data-tab="resumo">Resumo</button>
        <button class="perfil-tab" data-tab="quem-sou-eu">Quem Sou Eu</button>
        <button class="perfil-tab" data-tab="personalidade">Personalidade</button>
        <button class="perfil-tab" data-tab="metas">Minhas Metas</button>
        <button class="perfil-tab" data-tab="landing_pages">Landing page</button>
    </div>

    <div class="tab-content" id="landing_pages">
        <div class="tab-pane">
            <ul class="nav nav-tabs" id="landing-tabs">
                <li class="nav-item">
                    <a class="nav-link active" href="#" onclick="mostrarAba('landing_preview')">Meu Landing Page</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="mostrarAba('landing_edit')">Editar Landing Page</a>
                </li>
            </ul>

            <div class="tab-content mt-3">
                <!-- Conteúdo da visualização do landing page -->
                <div id="landing_preview" class="aba-conteudo">
                    <h2>Meu Landing Page</h2>

                    <?php if ($landing): ?>
                        <div class="landing-preview">
                            <div class="topo">
                                <h1><?= htmlspecialchars($landing['titulo_principal'] ?? 'Meu Currículo') ?></h1>
                                <p class="subtitulo"><?= htmlspecialchars($landing['subtitulo_principal'] ?? '') ?></p>
                            </div>

                            <div class="section"><h3>Sobre Mim</h3><p><?= nl2br(htmlspecialchars($landing['sobre'] ?? '')) ?></p></div>
                            <div class="section"><h3>Educação</h3><p><?= nl2br(htmlspecialchars($landing['educacao'] ?? '')) ?></p></div>
                            <div class="section"><h3>Carreira</h3><p><?= nl2br(htmlspecialchars($landing['carreira'] ?? '')) ?></p></div>
                            <div class="section"><h3>Contato</h3><div class="contato"><?= nl2br(htmlspecialchars($landing['contato'] ?? '')) ?></div></div>

                            <a href="landing.php?user_id=<?= $user_id ?>" target="_blank" class="btn btn-primary">Ver Página Completa</a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p>Você ainda não criou seu landing page. <a href="#" onclick="mostrarAba('landing_edit')">Clique aqui para criar</a>.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Formulário de edição do landing page -->
                <div id="landing_edit" class="aba-conteudo" style="display:none;">
                    <h2>Editar Meu Landing Page</h2>
                    <form method="POST" action="user.php" class="form-landing">

                    <div class="form-group">
                            <label for="titulo_principal">Título Principal</label>
                            <input type="text" id="titulo_principal" name="titulo_principal" class="form-control" value="<?= htmlspecialchars($landing['titulo_principal'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="subtitulo_principal">Subtítulo</label>
                            <textarea id="subtitulo_principal" name="subtitulo_principal" class="form-control" rows="2"><?= htmlspecialchars($landing['subtitulo_principal'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="sobre">Sobre Mim</label>
                            <textarea id="sobre" name="sobre" class="form-control" rows="4"><?= htmlspecialchars($landing['sobre'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="educacao">Educação</label>
                            <textarea id="educacao" name="educacao" class="form-control" rows="4"><?= htmlspecialchars($landing['educacao'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="carreira">Carreira</label>
                            <textarea id="carreira" name="carreira" class="form-control" rows="4"><?= htmlspecialchars($landing['carreira'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="contato">Contato</label>
                            <textarea id="contato" name="contato" class="form-control" rows="4"><?= htmlspecialchars($landing['contato'] ?? '') ?></textarea>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" id="publico" name="publico" class="form-check-input" <?= isset($landing['publico']) && $landing['publico'] ? 'checked' : '' ?>>
                            <label for="publico" class="form-check-label">Tornar público</label>
                        </div>

                        <button type="submit" name="editar_landing" class="btn btn-primary">Salvar Landing Page</button>

                    </form>
                </div>
            </div>
        </div>
    </div>
    <style>
        /* Estilo das abas do landing page */
        .landing-tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }

        .landing-tab {
            background: none;
            border: none;
            padding: 10px 15px;
            font-size: 16px;
            cursor: pointer;
            position: relative;
            color: #555;
            transition: color 0.3s;
        }

        .landing-tab:hover {
            color: #007bff;
        }

        .landing-tab.active {
            color: #007bff;
            font-weight: 600;
        }

        .landing-tab.active:after {
            content: '';
            position: absolute;
            height: 3px;
            background-color: #007bff;
            width: 100%;
            bottom: -1px;
            left: 0;
        }

        /* Conteúdo das abas */
        .landing-pane {
            display: none;
        }

        .landing-pane.active {
            display: block;
        }

        .section-title {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        /* Landing Preview */
        .landing-preview {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 30px;
        }

        .landing-preview .topo {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .landing-preview h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }

        .landing-preview .subtitulo {
            font-size: 18px;
            color: #666;
            font-style: italic;
        }

        .landing-preview .section {
            margin-bottom: 25px;
        }

        .landing-preview .section h3 {
            font-size: 20px;
            color: #007bff;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eaeaea;
        }

        .landing-preview .contato {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }

        .missing-landing {
            background-color: #f8f9fa;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            color: #555;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .missing-landing a {
            color: #007bff;
            text-decoration: none;
            font-weight: 600;
        }

        .missing-landing a:hover {
            text-decoration: underline;
        }

        /* Formulário */
        .form-landing {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .input-field {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .input-field:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        textarea.input-field {
            resize: vertical;
            min-height: 80px;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }

        .checkbox-container input[type="checkbox"] {
            margin-right: 10px;
            width: 18px;
            height: 18px;
        }

        .checkbox-container label {
            cursor: pointer;
            user-select: none;
        }

        .form-buttons {
            display: flex;
            justify-content: flex-start;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0069d9;
        }
    </style>

    <script>
        function mostrarAbaLanding(abaId) {
            // Desativa todas as abas
            document.querySelectorAll('.landing-pane').forEach(pane => {
                pane.classList.remove('active');
            });

            // Remove a classe ativa de todos os botões
            document.querySelectorAll('.landing-tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Ativa a aba solicitada
            document.getElementById(abaId).classList.add('active');

            // Ativa o botão correspondente
            const botaoAtivo = document.querySelector(`.landing-tab[data-tab="${abaId}"]`);
            if (botaoAtivo) {
                botaoAtivo.classList.add('active');
            }
        }

        // Inicialização - verificar se há um landing já salvo
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($landing): ?>
            // Se já tem landing, mostra a visualização
            mostrarAbaLanding('landing_preview');
            <?php else: ?>
            // Se não tem landing, mostra o editor
            mostrarAbaLanding('landing_edit');
            <?php endif; ?>
        });
    </script>


    <div class="tab-content" id="resumo">
        <section class="perfil-conteudo">
            <h2 class="secao-titulo">Visão Geral</h2>

            <div class="card-grid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-titulo">Progresso</h3>
                        <p class="card-subtitulo">Completou <?php echo $metasFeitas; ?> de <?php echo $totalMetas; ?> metas</p>
                    </div>
                    <div class="card-body">
                        <div class="progresso-container">
                            <div class="progresso-barra" style="width: <?php echo $porcentagemConcluida; ?>%"></div>
                        </div>
                        <p class="text-center"><?php echo $porcentagemConcluida; ?>% completo</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-titulo">Próximas Metas</h3>
                    </div>
                    <div class="card-body">
                        <?php if (count($proximasMetas) > 0): ?>
                            <div class="lista-metas">
                                <?php foreach(array_slice($proximasMetas, 0, 3) as $meta): ?>
                                    <div class="meta-item">
                                        <form method="POST">
                                            <input type="hidden" name="concluir_meta" value="<?php echo $meta['id']; ?>">
                                            <input type="checkbox" class="meta-checkbox" onchange="this.form.submit()">
                                        </form>
                                        <div class="meta-conteudo">
                                            <h4 class="meta-titulo"><?php echo $meta['titulo']; ?></h4>
                                            <p class="meta-descricao"><?php echo $meta['descricao']; ?></p>
                                            <p class="meta-prazo">
                                                <i class="fas fa-calendar-alt"></i>
                                                Prazo: <?php echo date('d/m/Y', strtotime($meta['prazo'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-center">Você não tem metas pendentes.</p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primario btn-pequeno" data-modal="adicionar-meta">
                            <i class="fas fa-plus"></i> Nova Meta
                        </button>
                    </div>
                </div>

                <?php if ($personalidade): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-titulo">Personalidade</h3>
                            <p class="card-subtitulo">Meu traço dominante</p>
                        </div>
                        <div class="card-body">
                            <div class="traco-dominante text-center">
                                <?php echo $traço_dominante; ?>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="#personalidade" class="btn btn-secundario btn-pequeno" onclick="event.preventDefault(); document.querySelector('.perfil-tab[data-tab=\'personalidade\']').click();">
                                <i class="fas fa-chart-bar"></i> Ver Detalhes
                            </a>
                        </div>

                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <div class="tab-content hidden" id="quem-sou-eu">
        <h2 class="section-title">Quem Sou Eu</h2>

        <?php if (!$perfil): ?>
            <!-- Mensagem caso não existam dados -->
            <div class="missing-data">
                <p>Você ainda não preencheu seu perfil completo. Preencha o formulário abaixo para começar.</p>
            </div>
        <?php endif; ?>

        <form method="POST" action="user.php" class="form-quem-sou-eu">
            <!-- Seção 1: Autoconhecimento -->
            <div class="section">
                <h3>Autoconhecimento</h3>

                <div class="form-group">
                    <label for="sobre_voce">Fale sobre você</label>
                    <textarea id="sobre_voce" name="sobre_voce" class="input-field" rows="4"><?= htmlspecialchars($perfil['fale_sobre_voce'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="lembrancas">Minhas lembranças</label>
                    <textarea id="lembrancas" name="lembrancas" class="input-field" rows="4"><?= htmlspecialchars($perfil['minhas_lembrancas'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="pontos_fortes">Meus pontos fortes</label>
                    <textarea id="pontos_fortes" name="pontos_fortes" class="input-field" rows="4"><?= htmlspecialchars($perfil['pontos_fortes'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="pontos_fracos">Meus pontos fracos</label>
                    <textarea id="pontos_fracos" name="pontos_fracos" class="input-field" rows="4"><?= htmlspecialchars($perfil['pontos_fracos'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="valores">Meus valores</label>
                    <textarea id="valores" name="valores" class="input-field" rows="4"><?= htmlspecialchars($perfil['meus_valores'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Minhas principais aptidões</label>
                    <?php
                    // Transformar string em array
                    $aptidoesArray = explode(', ', $perfil['principais_aptidoes'] ?? '');
                    ?>
                    <div class="checkbox-grid">
                        <div class="checkbox-item">
                            <input type="checkbox" id="apt_comunicacao" name="aptidoes[]" value="Comunicação" <?= in_array('Comunicação', $aptidoesArray) ? 'checked' : '' ?>>
                            <label for="apt_comunicacao">Comunicação</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="apt_criatividade" name="aptidoes[]" value="Criatividade" <?= in_array('Criatividade', $aptidoesArray) ? 'checked' : '' ?>>
                            <label for="apt_criatividade">Criatividade</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="apt_lideranca" name="aptidoes[]" value="Liderança" <?= in_array('Liderança', $aptidoesArray) ? 'checked' : '' ?>>
                            <label for="apt_lideranca">Liderança</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="apt_organizacao" name="aptidoes[]" value="Organização" <?= in_array('Organização', $aptidoesArray) ? 'checked' : '' ?>>
                            <label for="apt_organizacao">Organização</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="apt_empatia" name="aptidoes[]" value="Empatia" <?= in_array('Empatia', $aptidoesArray) ? 'checked' : '' ?>>
                            <label for="apt_empatia">Empatia</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="apt_analise" name="aptidoes[]" value="Análise" <?= in_array('Análise', $aptidoesArray) ? 'checked' : '' ?>>
                            <label for="apt_analise">Análise</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="apt_resiliencia" name="aptidoes[]" value="Resiliência" <?= in_array('Resiliência', $aptidoesArray) ? 'checked' : '' ?>>
                            <label for="apt_resiliencia">Resiliência</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="apt_adaptacao" name="aptidoes[]" value="Adaptação" <?= in_array('Adaptação', $aptidoesArray) ? 'checked' : '' ?>>
                            <label for="apt_adaptacao">Adaptação</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção 2: Relações -->
            <div class="section">
                <h3>Minhas Relações</h3>

                <div class="form-group">
                    <label for="familia">Com a família</label>
                    <textarea id="familia" name="familia" class="input-field" rows="4"><?= htmlspecialchars($perfil['relacoes_familia'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="amigos">Com os amigos</label>
                    <textarea id="amigos" name="amigos" class="input-field" rows="4"><?= htmlspecialchars($perfil['relacoes_amigos'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="escola">Com a escola</label>
                    <textarea id="escola" name="escola" class="input-field" rows="4"><?= htmlspecialchars($perfil['relacoes_escola'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="sociedade">Com a sociedade</label>
                    <textarea id="sociedade" name="sociedade" class="input-field" rows="4"><?= htmlspecialchars($perfil['relacoes_sociedade'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Seção 3: Cotidiano -->
            <div class="section">
                <h3>Meu Cotidiano</h3>

                <div class="form-group">
                    <label for="gosto_fazer">O que gosto de fazer</label>
                    <textarea id="gosto_fazer" name="gosto_fazer" class="input-field" rows="4"><?= htmlspecialchars($perfil['gosto_fazer'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="nao_gosto">O que não gosto de fazer</label>
                    <textarea id="nao_gosto" name="nao_gosto" class="input-field" rows="4"><?= htmlspecialchars($perfil['nao_gosto_fazer'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="rotina">Minha rotina</label>
                    <textarea id="rotina" name="rotina" class="input-field" rows="4"><?= htmlspecialchars($perfil['rotina'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="lazer">Meu lazer</label>
                    <textarea id="lazer" name="lazer" class="input-field" rows="4"><?= htmlspecialchars($perfil['lazer'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Seção 4: Vida Escolar -->
            <div class="section">
                <h3>Vida Escolar</h3>

                <div class="form-group">
                    <label for="estudos">Meus estudos</label>
                    <textarea id="estudos" name="estudos" class="input-field" rows="4"><?= htmlspecialchars($perfil['estudos'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="vida_escolar">Minha vida escolar</label>
                    <textarea id="vida_escolar" name="vida_escolar" class="input-field" rows="4"><?= htmlspecialchars($perfil['vida_escolar'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Seção 5: Como Me Vejo -->
            <div class="section">
                <h3>Como Me Vejo</h3>

                <div class="form-group">
                    <label for="visao_fisica">Fisicamente</label>
                    <textarea id="visao_fisica" name="visao_fisica" class="input-field" rows="4"><?= htmlspecialchars($perfil['visao_fisica'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="visao_intelectual">Intelectualmente</label>
                    <textarea id="visao_intelectual" name="visao_intelectual" class="input-field" rows="4"><?= htmlspecialchars($perfil['visao_intelectual'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="visao_emocional">Emocionalmente</label>
                    <textarea id="visao_emocional" name="visao_emocional" class="input-field" rows="4"><?= htmlspecialchars($perfil['visao_emocional'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Seção 6: Como Os Outros Me Veem -->
            <div class="section">
                <h3>Como Os Outros Me Veem</h3>

                <div class="form-group">
                    <label for="visao_amigos">Na visão dos amigos</label>
                    <textarea id="visao_amigos" name="visao_amigos" class="input-field" rows="4"><?= htmlspecialchars($perfil['visao_dos_amigos'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="visao_familiares">Na visão dos familiares</label>
                    <textarea id="visao_familiares" name="visao_familiares" class="input-field" rows="4"><?= htmlspecialchars($perfil['visao_dos_familiares'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="visao_professores">Na visão dos professores</label>
                    <textarea id="visao_professores" name="visao_professores" class="input-field" rows="4"><?= htmlspecialchars($perfil['visao_dos_professores'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Seção 7: Autovalorização -->
            <div class="section">
                <h3>Autovalorização</h3>

                <div class="form-group">
                    <label for="autovalorizacao">Em uma escala de 1 a 10, quanto você se valoriza?</label>
                    <input type="range" id="autovalorizacao" name="autovalorizacao" min="0" max="10" class="range-slider" value="<?= (int)($perfil['autovalorizacao_total'] ?? 5) ?>">
                    <div class="range-value" id="autovalorizacao-value"><?= (int)($perfil['autovalorizacao_total'] ?? 5) ?></div>
                </div>
            </div>

            <div class="form-buttons">
                <button type="submit" name="salvar_perfil_completo" class="btn btn-primary">Salvar Perfil</button>
            </div>
        </form>
    </div>

    <style>
        /* Estilo geral da seção "Quem Sou Eu" */
        #quem-sou-eu {
            padding: 20px;
        }

        #quem-sou-eu .section {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 25px;
        }

        #quem-sou-eu .section h3 {
            color: #007bff;
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        /* Estilo para o formulário */
        .form-quem-sou-eu .form-group {
            margin-bottom: 20px;
        }

        .form-quem-sou-eu label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #333;
        }

        .form-quem-sou-eu .input-field {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-quem-sou-eu .input-field:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        /* Estilo para o grid de checkboxes */
        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }

        .checkbox-item input[type="checkbox"] {
            margin-right: 10px;
            width: 18px;
            height: 18px;
        }

        /* Estilo para o slider */
        .range-slider {
            width: 100%;
            margin-top: 10px;
        }

        .range-value {
            text-align: center;
            font-weight: bold;
            margin-top: 10px;
            font-size: 24px;
            color: #007bff;
        }

        /* Mensagem de dados faltantes */
        .missing-data {
            background-color: #f8f9fa;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            color: #555;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        /* Botões do formulário */
        .form-buttons {
            margin-top: 30px;
            text-align: center;
        }
    </style>

    <script>
        // Script para mostrar valor do slider
        document.addEventListener('DOMContentLoaded', function() {
            const slider = document.getElementById('autovalorizacao');
            const output = document.getElementById('autovalorizacao-value');

            if (slider && output) {
                // Atualizar o valor ao carregar a página
                output.textContent = slider.value;

                // Atualizar o valor quando o slider é movido
                slider.oninput = function() {
                    output.textContent = this.value;
                };
            }
        });
    </script>


    <div class="tab-content hidden" id="personalidade">
        <h2 class="section-title">Minha Personalidade</h2>

        <div class="personality-sections">
            <!-- Seção do gráfico de pizza -->
            <div class="personality-section">
                <h3>Distribuição de Traços</h3>

                <div class="chart-container">
                    <canvas id="personalityChart"></canvas>

                    <?php if ($personalidade): ?>
                        <div class="dominant-trait">
                            <span>Traço dominante:</span>
                            <strong><?= htmlspecialchars($traço_dominante) ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Seção do formulário de avaliação -->
            <div class="personality-section">
                <h3>Teste de Personalidade</h3>
                <p class="section-description">Avalie cada dimensão da sua personalidade numa escala de 0 a 10:</p>

                <form method="POST" action="user.php" class="personality-form">
                    <div class="slider-group">
                        <div class="slider-labels">
                            <span>Introvertido</span>
                            <span>Extrovertido</span>
                        </div>
                        <div class="slider-container">
                            <input type="range" id="extrovertido" name="extrovertido" min="0" max="10" value="<?= (int) ($personalidade['extrovertido'] ?? 5) ?>">
                            <div class="slider-value" id="extrovertido-value"><?= (int) ($personalidade['extrovertido'] ?? 5) ?></div>
                        </div>
                    </div>

                    <div class="slider-group">
                        <div class="slider-labels">
                            <span>Sensorial</span>
                            <span>Intuitivo</span>
                        </div>
                        <div class="slider-container">
                            <input type="range" id="intuitivo" name="intuitivo" min="0" max="10" value="<?= (int) ($personalidade['intuitivo'] ?? 5) ?>">
                            <div class="slider-value" id="intuitivo-value"><?= (int) ($personalidade['intuitivo'] ?? 5) ?></div>
                        </div>
                    </div>

                    <div class="slider-group">
                        <div class="slider-labels">
                            <span>Emocional</span>
                            <span>Racional</span>
                        </div>
                        <div class="slider-container">
                            <input type="range" id="racional" name="racional" min="0" max="10" value="<?= (int) ($personalidade['racional'] ?? 5) ?>">
                            <div class="slider-value" id="racional-value"><?= (int) ($personalidade['racional'] ?? 5) ?></div>
                        </div>
                    </div>

                    <div class="slider-group">
                        <div class="slider-labels">
                            <span>Perceptivo</span>
                            <span>Julgador</span>
                        </div>
                        <div class="slider-container">
                            <input type="range" id="julgador" name="julgador" min="0" max="10" value="<?= (int) ($personalidade['julgador'] ?? 5) ?>">
                            <div class="slider-value" id="julgador-value"><?= (int) ($personalidade['julgador'] ?? 5) ?></div>
                        </div>
                    </div>

                    <div class="form-buttons">
                        <button type="submit" name="teste_personalidade" class="btn btn-primary">Salvar Resultados</button>
                    </div>
                </form>
            </div>

            <!-- Seção de interpretação dos resultados -->
            <div class="personality-section results-section">
                <h3>Interpretação dos Resultados</h3>

                <?php if ($personalidade): ?>
                    <div class="personality-traits">
                        <div class="trait">
                            <h4>Introversão vs. Extroversão <span>(<?= (int) ($personalidade['extrovertido']) ?>/10)</span></h4>
                            <div class="trait-bar">
                                <div class="trait-fill" style="width: <?= (int) ($personalidade['extrovertido'] * 10) ?>%"></div>
                            </div>
                            <p class="trait-description">
                                <?php if ($personalidade['extrovertido'] > 5): ?>
                                    Você tende a ser mais <strong>extrovertido</strong>, preferindo interação social e ambientes estimulantes.
                                <?php else: ?>
                                    Você tende a ser mais <strong>introvertido</strong>, preferindo reflexão e ambientes mais calmos.
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="trait">
                            <h4>Sensorial vs. Intuitivo <span>(<?= (int) ($personalidade['intuitivo']) ?>/10)</span></h4>
                            <div class="trait-bar">
                                <div class="trait-fill" style="width: <?= (int) ($personalidade['intuitivo'] * 10) ?>%"></div>
                            </div>
                            <p class="trait-description">
                                <?php if ($personalidade['intuitivo'] > 5): ?>
                                    Você tende a ser mais <strong>intuitivo</strong>, focando em possibilidades futuras e significados abstratos.
                                <?php else: ?>
                                    Você tende a ser mais <strong>sensorial</strong>, focando em experiências concretas e detalhes práticos.
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="trait">
                            <h4>Emocional vs. Racional <span>(<?= (int) ($personalidade['racional']) ?>/10)</span></h4>
                            <div class="trait-bar">
                                <div class="trait-fill" style="width: <?= (int) ($personalidade['racional'] * 10) ?>%"></div>
                            </div>
                            <p class="trait-description">
                                <?php if ($personalidade['racional'] > 5): ?>
                                    Você tende a ser mais <strong>racional</strong>, priorizando a lógica e objetividade nas decisões.
                                <?php else: ?>
                                    Você tende a ser mais <strong>emocional</strong>, priorizando valores pessoais e harmonia nas decisões.
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="trait">
                            <h4>Perceptivo vs. Julgador <span>(<?= (int) ($personalidade['julgador']) ?>/10)</span></h4>
                            <div class="trait-bar">
                                <div class="trait-fill" style="width: <?= (int) ($personalidade['julgador'] * 10) ?>%"></div>
                            </div>
                            <p class="trait-description">
                                <?php if ($personalidade['julgador'] > 5): ?>
                                    Você tende a ser mais <strong>julgador</strong>, preferindo estrutura, planejamento e decisões definidas.
                                <?php else: ?>
                                    Você tende a ser mais <strong>perceptivo</strong>, preferindo flexibilidade, adaptabilidade e manter opções abertas.
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="no-results">
                        <p>Você ainda não realizou o teste de personalidade. Complete o formulário ao lado para ver seus resultados.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
        /* Estilos para a seção de personalidade */
        #personalidade {
            padding: 20px;
        }

        .personality-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        @media (max-width: 992px) {
            .personality-sections {
                grid-template-columns: 1fr;
            }
        }

        .personality-section {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 25px;
        }

        .results-section {
            grid-column: span 2;
        }

        @media (max-width: 992px) {
            .results-section {
                grid-column: span 1;
            }
        }

        .personality-section h3 {
            color: #007bff;
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .section-description {
            color: #666;
            margin-bottom: 20px;
        }

        /* Estilos para o gráfico */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        .dominant-trait {
            text-align: center;
            margin-top: 20px;
            font-size: 18px;
        }

        .dominant-trait strong {
            color: #007bff;
        }

        /* Estilos para o formulário */
        .slider-group {
            margin-bottom: 25px;
        }

        .slider-labels {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }

        .slider-container {
            position: relative;
        }

        .slider-container input[type="range"] {
            width: 100%;
            height: 8px;
            appearance: none;
            background: #e9ecef;
            border-radius: 5px;
            outline: none;
        }

        .slider-container input[type="range"]::-webkit-slider-thumb {
            appearance: none;
            width: 20px;
            height: 20px;
            background: #007bff;
            border-radius: 50%;
            cursor: pointer;
        }

        .slider-value {
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            background: #007bff;
            color: white;
            padding: 2px 10px;
            border-radius: 4px;
            font-weight: bold;
        }

        /* Estilos para a interpretação dos resultados */
        .personality-traits {
            display: grid;
            gap: 25px;
        }

        .trait h4 {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            color: #333;
        }

        .trait-bar {
            height: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .trait-fill {
            height: 100%;
            background-color: #007bff;
            border-radius: 5px;
            transition: width 0.5s ease;
        }

        .trait-description {
            color: #555;
            font-size: 15px;
        }

        .no-results {
            background-color: #f8f9fa;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            color: #555;
            border-radius: 4px;
        }

        /* Botões do formulário */
        .form-buttons {
            margin-top: 30px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0069d9;
        }
    </style>

    <!-- Incluir Chart.js antes do script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configuração dos sliders
            const sliders = ['extrovertido', 'intuitivo', 'racional', 'julgador'];

            sliders.forEach(slider => {
                const input = document.getElementById(slider);
                const output = document.getElementById(`${slider}-value`);

                if (input && output) {
                    // Atualizar o valor ao carregar a página
                    output.textContent = input.value;
                    updateSliderPosition(input, output);

                    // Atualizar o valor quando o slider é movido
                    input.oninput = function() {
                        output.textContent = this.value;
                        updateSliderPosition(this, output);
                    };
                }
            });

            // Função para atualizar a posição do indicador de valor
            function updateSliderPosition(slider, output) {
                const percent = (slider.value - slider.min) / (slider.max - slider.min);
                const position = percent * (slider.offsetWidth - 20) + 10; // Ajustar para o tamanho do thumb
                output.style.left = `${position}px`;
            }

            // Criar o gráfico de pizza se existir dados
            <?php if ($personalidade): ?>
            const ctx = document.getElementById('personalityChart').getContext('2d');

            // Dados dos traços de personalidade
            const data = {
                labels: ['Extrovertido', 'Intuitivo', 'Racional', 'Julgador'],
                datasets: [{
                    data: [
                        <?= (int) ($personalidade['extrovertido'] ?? 0) ?>,
                        <?= (int) ($personalidade['intuitivo'] ?? 0) ?>,
                        <?= (int) ($personalidade['racional'] ?? 0) ?>,
                        <?= (int) ($personalidade['julgador'] ?? 0) ?>
                    ],
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0'
                    ],
                    borderWidth: 1
                }]
            };

            // Opções do gráfico
            const options = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 14
                            },
                            padding: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                return `${label}: ${value}/10`;
                            }
                        }
                    }
                }
            };

            // Criar gráfico
            new Chart(ctx, {
                type: 'pie',
                data: data,
                options: options
            });
            <?php endif; ?>
        });
    </script>


    <div class="tab-content hidden" id="metas">
        <section class="perfil-conteudo">
            <h2 class="secao-titulo">
                Minhas Metas
                <button class="btn btn-primario btn-pequeno" data-modal="adicionar-meta">
                    <i class="fas fa-plus"></i> Nova Meta
                </button>
            </h2>

            <div class="progresso-container">
                <div class="progresso-barra" style="width: <?php echo $porcentagemConcluida; ?>%"></div>
            </div>
            <p class="text-center mb-4"><?php echo $porcentagemConcluida; ?>% completo (<?php echo $metasFeitas; ?> de <?php echo $totalMetas; ?>)</p>

            <?php if (count($proximasMetas) > 0): ?>
                <div class="lista-metas">
                    <?php foreach($proximasMetas as $meta): ?>
                        <div class="meta-item">
                            <form method="POST">
                                <input type="hidden" name="concluir_meta" value="<?php echo $meta['id']; ?>">
                                <input type="checkbox" class="meta-checkbox" onchange="this.form.submit()">
                            </form>
                            <div class="meta-conteudo">
                                <h4 class="meta-titulo"><?php echo $meta['titulo']; ?></h4>
                                <p class="meta-descricao"><?php echo $meta['descricao']; ?></p>
                                <p class="meta-prazo">
                                    <i class="fas fa-calendar-alt"></i>
                                    Prazo: <?php echo date('d/m/Y', strtotime($meta['prazo'])); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center mt-4">
                    <p>Você não tem metas pendentes</p>
                    <button class="btn btn-primario mt-3" data-modal="adicionar-meta">
                        Adicionar Meta
                    </button>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<!-- Modais -->
<div class="modal" id="editar-perfil">
    <div class="modal-conteudo">
        <div class="modal-cabecalho">
            <h3>Editar Perfil</h3>
            <button class="fechar-modal">&times;</button>
        </div>
        <div class="modal-corpo">
            <form method="POST" class="perfil-form">
                <div class="form-grupo">
                    <label class="form-label" for="name">Nome</label>
                    <input type="text" id="name" name="name" class="form-input" value="<?php echo $username; ?>" required>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input" value="<?php echo $user['email']; ?>" required>
                </div>

                <div class="form-acoes">
                    <button type="button" class="btn btn-secundario fechar-modal">Cancelar</button>
                    <button type="submit" name="edit_usuario" class="btn btn-primario">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="editar-bio">
    <div class="modal-conteudo">
        <div class="modal-cabecalho">
            <h3>Editar Bio</h3>
            <button class="fechar-modal">&times;</button>
        </div>
        <div class="modal-corpo">
            <form method="POST" class="perfil-form">
                <div class="form-grupo">
                    <label class="form-label" for="description">Biografia</label>
                    <textarea id="description" name="description" class="form-input textarea" rows="5"><?php echo $user['description']; ?></textarea>
                </div>

                <div class="form-acoes">
                    <button type="button" class="btn btn-secundario fechar-modal">Cancelar</button>
                    <button type="submit" class="btn btn-primario">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="adicionar-meta">
    <div class="modal-conteudo">
        <div class="modal-cabecalho">
            <h3>Adicionar Nova Meta</h3>
            <button class="fechar-modal">&times;</button>
        </div>
        <div class="modal-corpo">
            <form method="POST" class="perfil-form">
                <div class="form-grupo">
                    <label class="form-label" for="titulo">Título da Meta</label>
                    <input type="text" id="titulo" name="titulo" class="form-input" required>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="descricao">Descrição</label>
                    <textarea id="descricao" name="descricao" class="form-input textarea"></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="prazo">Prazo</label>
                    <input type="date" id="prazo" name="prazo" class="form-input" required>
                </div>

                <div class="form-acoes">
                    <button type="button" class="btn btn-secundario fechar-modal">Cancelar</button>
                    <button type="submit" name="adicionar_meta" class="btn btn-primario">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="editar-quem-sou">
    <div class="modal-conteudo">
        <div class="modal-cabecalho">
            <h3>Quem Sou Eu</h3>
            <button class="fechar-modal">&times;</button>
        </div>
        <div class="modal-corpo">
            <form method="POST" class="perfil-form">
                <div class="form-grupo">
                    <label class="form-label" for="sobre_voce">Fale Sobre Você</label>
                    <textarea id="sobre_voce" name="sobre_voce" class="form-input textarea"><?php echo $perfil['fale_sobre_voce'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="lembrancas">Minhas Lembranças</label>
                    <textarea id="lembrancas" name="lembrancas" class="form-input textarea"><?php echo $perfil['minhas_lembrancas'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="pontos_fortes">Pontos Fortes</label>
                    <textarea id="pontos_fortes" name="pontos_fortes" class="form-input textarea"><?php echo $perfil['pontos_fortes'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="pontos_fracos">Pontos Fracos</label>
                    <textarea id="pontos_fracos" name="pontos_fracos" class="form-input textarea"><?php echo $perfil['pontos_fracos'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="valores">Meus Valores</label>
                    <textarea id="valores" name="valores" class="form-input textarea"><?php echo $perfil['meus_valores'] ?? ''; ?></textarea>
                </div>

                <?php
                $aptidoesArray = [];
                if (isset($perfil['principais_aptidoes'])) {
                    $aptidoesArray = explode(', ', $perfil['principais_aptidoes']);
                }

                $aptidoesPossiveis = [
                    'comunicação', 'liderança', 'organização', 'criatividade',
                    'resolução de problemas', 'trabalho em equipe', 'adaptabilidade',
                    'pensamento crítico', 'empatia', 'gestão de tempo'
                ];
                ?>

                <div class="form-grupo">
                    <label class="form-label">Principais Aptidões</label>
                    <div class="checkbox-group">
                        <?php foreach($aptidoesPossiveis as $aptidao): ?>
                            <div class="checkbox-item">
                                <input type="checkbox"
                                       id="apt_<?php echo str_replace(' ', '_', $aptidao); ?>"
                                       name="aptidoes[]"
                                       value="<?php echo $aptidao; ?>"
                                    <?php echo in_array($aptidao, $aptidoesArray) ? 'checked' : ''; ?>>
                                <label for="apt_<?php echo str_replace(' ', '_', $aptidao); ?>"><?php echo ucfirst($aptidao); ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="familia">Relações com a Família</label>
                    <textarea id="familia" name="familia" class="form-input textarea"><?php echo $perfil['relacoes_familia'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="amigos">Relações com Amigos</label>
                    <textarea id="amigos" name="amigos" class="form-input textarea"><?php echo $perfil['relacoes_amigos'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="escola">Relações na Escola</label>
                    <textarea id="escola" name="escola" class="form-input textarea"><?php echo $perfil['relacoes_escola'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="sociedade">Relações com a Sociedade</label>
                    <textarea id="sociedade" name="sociedade" class="form-input textarea"><?php echo $perfil['relacoes_sociedade'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="gosto_fazer">O que gosto de fazer</label>
                    <textarea id="gosto_fazer" name="gosto_fazer" class="form-input textarea"><?php echo $perfil['gosto_fazer'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="nao_gosto">O que não gosto de fazer</label>
                    <textarea id="nao_gosto" name="nao_gosto" class="form-input textarea"><?php echo $perfil['nao_gosto_fazer'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="rotina">Minha Rotina</label>
                    <textarea id="rotina" name="rotina" class="form-input textarea"><?php echo $perfil['rotina'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="lazer">Meu Lazer</label>
                    <textarea id="lazer" name="lazer" class="form-input textarea"><?php echo $perfil['lazer'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="estudos">Meus Estudos</label>
                    <textarea id="estudos" name="estudos" class="form-input textarea"><?php echo $perfil['estudos'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="vida_escolar">Minha Vida Escolar</label>
                    <textarea id="vida_escolar" name="vida_escolar" class="form-input textarea"><?php echo $perfil['vida_escolar'] ?? ''; ?></textarea>
                </div>

                <h3 class="section-subtitle mt-4">Como me vejo</h3>

                <div class="form-grupo">
                    <label class="form-label" for="visao_fisica">Fisicamente</label>
                    <textarea id="visao_fisica" name="visao_fisica" class="form-input textarea"><?php echo $perfil['visao_fisica'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="visao_intelectual">Intelectualmente</label>
                    <textarea id="visao_intelectual" name="visao_intelectual" class="form-input textarea"><?php echo $perfil['visao_intelectual'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="visao_emocional">Emocionalmente</label>
                    <textarea id="visao_emocional" name="visao_emocional" class="form-input textarea"><?php echo $perfil['visao_emocional'] ?? ''; ?></textarea>
                </div>

                <h3 class="section-subtitle mt-4">Como os outros me veem</h3>

                <div class="form-grupo">
                    <label class="form-label" for="visao_amigos">Na visão dos amigos</label>
                    <textarea id="visao_amigos" name="visao_amigos" class="form-input textarea"><?php echo $perfil['visao_dos_amigos'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="visao_familiares">Na visão dos familiares</label>
                    <textarea id="visao_familiares" name="visao_familiares" class="form-input textarea"><?php echo $perfil['visao_dos_familiares'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="visao_professores">Na visão dos professores</label>
                    <textarea id="visao_professores" name="visao_professores" class="form-input textarea"><?php echo $perfil['visao_dos_professores'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="autovalorizacao">Auto-Valorização (1-10)</label>
                    <input type="range" id="autovalorizacao" name="autovalorizacao" min="1" max="10" value="<?php echo $perfil['autovalorizacao_total'] ?? 5; ?>" class="form-range">
                    <div class="range-value" id="range-value"><?php echo $perfil['autovalorizacao_total'] ?? 5; ?></div>
                </div>

                <div class="form-acoes">
                    <button type="button" class="btn btn-secundario fechar-modal">Cancelar</button>
                    <button type="submit" name="salvar_perfil_completo" class="btn btn-primario">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="teste-personalidade">
    <div class="modal-conteudo">
        <div class="modal-cabecalho">
            <h3>Teste de Personalidade</h3>
            <button class="fechar-modal">&times;</button>
        </div>
        <div class="modal-corpo">
            <form method="POST" class="perfil-form">
                <div class="form-grupo">
                    <label class="form-label">Extroversão vs. Introversão</label>
                    <p class="form-info">Defina quanto você se considera extrovertido(a)</p>
                    <input type="range" name="extrovertido" min="0" max="100" value="<?php echo $personalidade['extrovertido'] ?? 50; ?>" class="form-range">
                    <div class="range-labels">
                        <span>Introvertido</span>
                        <span class="range-value"><?php echo $personalidade['extrovertido'] ?? 50; ?>%</span>
                        <span>Extrovertido</span>
                    </div>
                </div>

                <div class="form-grupo">
                    <label class="form-label">Intuição vs. Sensação</label>
                    <p class="form-info">Defina quanto você se baseia na intuição</p>
                    <input type="range" name="intuitivo" min="0" max="100" value="<?php echo $personalidade['intuitivo'] ?? 50; ?>" class="form-range">
                    <div class="range-labels">
                        <span>Sensação</span>
                        <span class="range-value"><?php echo $personalidade['intuitivo'] ?? 50; ?>%</span>
                        <span>Intuição</span>
                    </div>
                </div>

                <div class="form-grupo">
                    <label class="form-label">Racionalidade vs. Emocionalidade</label>
                    <p class="form-info">Defina quanto você toma decisões baseadas na razão</p>
                    <input type="range" name="racional" min="0" max="100" value="<?php echo $personalidade['racional'] ?? 50; ?>" class="form-range">
                    <div class="range-labels">
                        <span>Emocional</span>
                        <span class="range-value"><?php echo $personalidade['racional'] ?? 50; ?>%</span>
                        <span>Racional</span>
                    </div>
                </div>

                <div class="form-grupo">
                    <label class="form-label">Julgamento vs. Percepção</label>
                    <p class="form-info">Defina quanto você prefere situações estruturadas</p>
                    <input type="range" name="julgador" min="0" max="100" value="<?php echo $personalidade['julgador'] ?? 50; ?>" class="form-range">
                    <div class="range-labels">
                        <span>Percepção</span>
                        <span class="range-value"><?php echo $personalidade['julgador'] ?? 50; ?>%</span>
                        <span>Julgamento</span>
                    </div>
                </div>

                <div class="form-acoes">
                    <button type="button" class="btn btn-secundario fechar-modal">Cancelar</button>
                    <button type="submit" name="teste_personalidade" class="btn btn-primario">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Estilos adicionais para modais que não estão no CSS externo */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        overflow-y: auto;
        padding: 2rem 1rem;
    }

    .modal.show {
        display: flex;
        align-items: flex-start;
        justify-content: center;
    }

    .modal-conteudo {
        background-color: var(--white);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        width: 100%;
        max-width: 800px;
        margin: 2rem auto;
        overflow: hidden;
        animation: modalFadeIn 0.3s ease;
    }

    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-cabecalho {
        padding: 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-cabecalho h3 {
        margin: 0;
        font-weight: 600;
        color: var(--gray-800);
    }

    .fechar-modal {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--gray-600);
        transition: color 0.2s ease;
    }

    .fechar-modal:hover {
        color: var(--danger);
    }

    .modal-corpo {
        padding: 1.5rem;
        max-height: 70vh;
        overflow-y: auto;
    }

    /* Estilos para inputs tipo range */
    .form-range {
        width: 100%;
        height: 1rem;
        padding: 0;
        background-color: transparent;
        -webkit-appearance: none;
        appearance: none;
    }

    .form-range:focus {
        outline: none;
    }

    .form-range::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--primary);
        cursor: pointer;
        margin-top: -8px;
    }

    .form-range::-moz-range-thumb {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--primary);
        cursor: pointer;
    }

    .form-range::-webkit-slider-runnable-track {
        width: 100%;
        height: 4px;
        cursor: pointer;
        background: var(--gray-300);
        border-radius: 999px;
    }

    .form-range::-moz-range-track {
        width: 100%;
        height: 4px;
        cursor: pointer;
        background: var(--gray-300);
        border-radius: 999px;
    }

    .range-labels {
        display: flex;
        justify-content: space-between;
        margin-top: 0.5rem;
        font-size: 0.875rem;
        color: var(--gray-600);
    }

    .range-value {
        font-weight: 600;
        color: var(--primary);
    }

    .section-subtitle {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-700);
        margin-top: 2rem;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--gray-200);
    }

    .checkbox-group {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 0.75rem;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-info {
        font-size: 0.875rem;
        color: var(--gray-600);
        margin-bottom: 0.75rem;
    }

    .form-acoes {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;
    }
</style>

<script>
    // Script adicional para controlar os inputs de range
    document.addEventListener('DOMContentLoaded', function() {
        const rangeInputs = document.querySelectorAll('input[type="range"]');

        rangeInputs.forEach(input => {
            const valueDisplay = input.nextElementSibling?.querySelector('.range-value');

            if (valueDisplay) {
                // Atualizar valor ao carregar
                valueDisplay.textContent = input.value + '%';

                // Atualizar valor ao mover o slider
                input.addEventListener('input', function() {
                    valueDisplay.textContent = this.value + '%';
                });
            }

            // Para o range da autoavaliação que tem display separado
            if (input.id === 'autovalorizacao') {
                const rangeValue = document.getElementById('range-value');
                if (rangeValue) {
                    // Atualizar valor ao carregar
                    rangeValue.textContent = input.value;

                    // Atualizar valor ao mover o slider
                    input.addEventListener('input', function() {
                        rangeValue.textContent = this.value;
                    });
                }
            }
        });
    });
</script>
</body>
</html>
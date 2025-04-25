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
        header("Location: user.php"); // atualiza a página
        exit;
    }
}

// Buscar metas pendentes com prazo futuro
$stmt = $pdo->prepare("SELECT * FROM plano_acao WHERE user_id = ? AND concluida = 0 AND prazo >= CURDATE() ORDER BY prazo ASC");
$stmt->execute([$user_id]);
$proximasMetas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Busca todas as metas do usuário
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM plano_acao WHERE user_id = ?");
$stmt->execute([$user_id]);
$totalMetas = $stmt->fetchColumn();

// Busca metas concluídas
$stmt = $pdo->prepare("SELECT COUNT(*) as feitas FROM plano_acao WHERE user_id = ? AND concluida = 1");
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

// Manipular a edição de metas via AJAX
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'editar_meta') {
    $metaId = (int) $_POST['meta_id'];
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $prazo = $_POST['prazo'];

    $response = ['success' => false, 'message' => ''];

    // Verificar se a meta pertence ao usuário
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM plano_acao WHERE id = ? AND user_id = ?");
    $stmt->execute([$metaId, $user_id]);

    if ($stmt->fetchColumn() > 0) {
        if (!empty($titulo) && !empty($prazo)) {
            $stmt = $pdo->prepare("UPDATE plano_acao SET titulo = ?, descricao = ?, prazo = ? WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$titulo, $descricao, $prazo, $metaId, $user_id])) {
                $response['success'] = true;
                $response['message'] = 'Meta atualizada com sucesso!';
            } else {
                $response['message'] = 'Erro ao atualizar meta no banco de dados.';
            }
        } else {
            $response['message'] = 'Título e prazo são obrigatórios.';
        }
    } else {
        $response['message'] = 'Meta não encontrada ou não pertence ao usuário.';
    }

    // Responder com JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Manipular a exclusão de metas via AJAX
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'excluir_meta') {
    $metaId = (int) $_POST['meta_id'];

    $response = ['success' => false, 'message' => ''];

    // Verificar se a meta pertence ao usuário
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM plano_acao WHERE id = ? AND user_id = ?");
    $stmt->execute([$metaId, $user_id]);

    if ($stmt->fetchColumn() > 0) {
        $stmt = $pdo->prepare("DELETE FROM plano_acao WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$metaId, $user_id])) {
            $response['success'] = true;
            $response['message'] = 'Meta excluída com sucesso!';
        } else {
            $response['message'] = 'Erro ao excluir meta do banco de dados.';
        }
    } else {
        $response['message'] = 'Meta não encontrada ou não pertence ao usuário.';
    }

    // Responder com JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Obter dados de uma meta específica para edição
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['get_meta'])) {
    $metaId = (int) $_GET['get_meta'];

    $response = ['success' => false, 'message' => '', 'meta' => null];

    // Verificar se a meta pertence ao usuário
    $stmt = $pdo->prepare("SELECT * FROM plano_acao WHERE id = ? AND user_id = ?");
    $stmt->execute([$metaId, $user_id]);
    $meta = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($meta) {
        $response['success'] = true;
        $response['meta'] = $meta;
    } else {
        $response['message'] = 'Meta não encontrada ou não pertence ao usuário.';
    }

    // Responder com JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo htmlspecialchars($username); ?></title>
    <link rel="stylesheet" href="user-styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<!-- Navegação Principal -->
<nav class="navbar">
    <div class="container">
        <a href="index.php" class="navbar-logo">Projeto de Vida</a>
        <div class="navbar-links">
            <a href="index.php">Início</a>
            <a href="logout.php">Sair</a>
        </div>
    </div>
</nav>

<!-- Conteúdo Principal -->
<div class="perfil-container">

    <!-- Header do Perfil -->
    <div class="perfil-header">
        <div class="perfil-foto">
            <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="Foto de perfil">
            <label for="upload-foto" class="editar-foto">
                <i class="fas fa-camera"></i>
            </label>
            <form method="post" enctype="multipart/form-data" style="display: none;">
                <input type="file" id="upload-foto" name="profile_picture" accept="image/*" onchange="this.form.submit()">
            </form>
        </div>

        <div class="perfil-info">
            <h1 class="perfil-nome"><?php echo htmlspecialchars($username); ?></h1>
            <p class="perfil-email"><?php echo htmlspecialchars($user['email']); ?></p>

            <div class="perfil-bio">
                <?php if (!empty($user['description'])): ?>
                    <p><?php echo nl2br(htmlspecialchars($user['description'])); ?></p>
                <?php else: ?>
                    <p>Adicione uma descrição sobre você...</p>
                <?php endif; ?>
            </div>

            <div class="perfil-acoes">
                <button class="btn btn-primario btn-icone" onclick="toggleModal('modal-editar-perfil')">
                    <i class="fas fa-edit"></i> Editar Perfil
                </button>
                <button class="btn btn-secundario btn-icone">
                    <i class="fas fa-share"></i> Compartilhar
                </button>
            </div>
        </div>
    </div>

    <!-- Abas de Navegação -->
    <div class="perfil-tabs">
        <button class="perfil-tab active" data-tab="dashboard">Dashboard</button>
        <button class="perfil-tab" data-tab="quem-sou">Quem Sou Eu</button>
        <button class="perfil-tab" data-tab="plano-acao">Plano de Ação</button>
        <button class="perfil-tab" data-tab="personalidade">Personalidade</button>
        <button class="perfil-tab" data-tab="landing-page">Landing Page</button>
    </div>

    <!-- Conteúdo das Abas -->

    <!-- Dashboard -->
    <div class="tab-content active" id="dashboard">
        <div class="perfil-conteudo">
            <h2 class="secao-titulo">Visão Geral</h2>

            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h3>Metas em Andamento</h3>
                    <div class="progresso-container">
                        <div class="progresso-barra" style="width: <?php echo $porcentagemConcluida; ?>%;"></div>
                    </div>
                    <p><?php echo $metasFeitas; ?> de <?php echo $totalMetas; ?> metas concluídas</p>
                </div>

                <div class="dashboard-card">
                    <h3>Traço de Personalidade Dominante</h3>
                    <?php if ($traço_dominante): ?>
                        <p class="traco-dominante"><?php echo $traço_dominante; ?></p>
                    <?php else: ?>
                        <p>Faça o teste de personalidade para descobrir</p>
                    <?php endif; ?>
                </div>

                <div class="dashboard-card">
                    <h3>Perfil de Aptidões</h3>
                    <canvas id="aptidoesChart" width="300" height="200"></canvas>
                </div>
            </div>

            <h2 class="secao-titulo mt-4">Próximas Metas</h2>

            <?php if (!empty($proximasMetas)): ?>
                <div class="lista-metas">
                    <?php foreach ($proximasMetas as $meta): ?>
                        <div class="meta-item">
                            <form method="post">
                                <input type="hidden" name="concluir_meta" value="<?php echo $meta['id']; ?>">
                                <input type="checkbox" class="meta-checkbox" title="Marcar como concluída" onchange="this.form.submit()">
                            </form>
                            <div class="meta-conteudo">
                                <h3 class="meta-titulo"><?php echo htmlspecialchars($meta['titulo']); ?></h3>
                                <p class="meta-descricao"><?php echo htmlspecialchars($meta['descricao']); ?></p>
                                <p class="meta-prazo">
                                    <i class="fas fa-calendar"></i>
                                    Prazo: <?php echo date('d/m/Y', strtotime($meta['prazo'])); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Você não tem metas pendentes. Adicione novas metas no Plano de Ação.</p>
            <?php endif; ?>

            <div class="text-center mt-4">
                <button class="btn btn-primario" onclick="toggleTab('plano-acao')">
                    Ver Plano de Ação Completo
                </button>
            </div>
        </div>
    </div>

    <!-- Quem Sou Eu -->
    <div class="tab-content" id="quem-sou">
        <div class="perfil-conteudo">
            <h2 class="secao-titulo">
                Quem Sou Eu
                <button class="btn btn-primario btn-pequeno" onclick="toggleModal('modal-quem-sou')">
                    Editar
                </button>
            </h2>

            <div class="quem-sou-grid">
                <div class="quem-sou-card">
                    <h3>Sobre Mim</h3>
                    <p><?php echo !empty($perfil['fale_sobre_voce']) ? nl2br(htmlspecialchars($perfil['fale_sobre_voce'])) : 'Adicione informações sobre você...'; ?></p>
                </div>

                <div class="quem-sou-card">
                    <h3>Pontos Fortes</h3>
                    <p><?php echo !empty($perfil['pontos_fortes']) ? nl2br(htmlspecialchars($perfil['pontos_fortes'])) : 'Adicione seus pontos fortes...'; ?></p>
                </div>

                <div class="quem-sou-card">
                    <h3>Pontos a Desenvolver</h3>
                    <p><?php echo !empty($perfil['pontos_fracos']) ? nl2br(htmlspecialchars($perfil['pontos_fracos'])) : 'Adicione pontos a desenvolver...'; ?></p>
                </div>

                <div class="quem-sou-card">
                    <h3>Valores</h3>
                    <p><?php echo !empty($perfil['meus_valores']) ? nl2br(htmlspecialchars($perfil['meus_valores'])) : 'Adicione seus valores...'; ?></p>
                </div>

                <div class="quem-sou-card wide">
                    <h3>Minhas Relações</h3>
                    <div class="relacoes-grid">
                        <div>
                            <h4>Família</h4>
                            <p><?php echo !empty($perfil['relacoes_familia']) ? nl2br(htmlspecialchars($perfil['relacoes_familia'])) : 'Descreva suas relações familiares...'; ?></p>
                        </div>
                        <div>
                            <h4>Amigos</h4>
                            <p><?php echo !empty($perfil['relacoes_amigos']) ? nl2br(htmlspecialchars($perfil['relacoes_amigos'])) : 'Descreva suas relações com amigos...'; ?></p>
                        </div>
                        <div>
                            <h4>Escola/Trabalho</h4>
                            <p><?php echo !empty($perfil['relacoes_escola']) ? nl2br(htmlspecialchars($perfil['relacoes_escola'])) : 'Descreva suas relações escolares/profissionais...'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Plano de Ação -->
    <div class="tab-content" id="plano-acao">
        <div class="perfil-conteudo">
            <h2 class="secao-titulo">
                Plano de Ação
                <button class="btn btn-primario btn-pequeno" onclick="toggleModal('modal-adicionar-meta')">
                    Adicionar Meta
                </button>
            </h2>

            <div class="plano-progresso">
                <h3>Progresso Geral</h3>
                <div class="progresso-container">
                    <div class="progresso-barra" style="width: <?php echo $porcentagemConcluida; ?>%;"></div>
                </div>
                <p class="text-center"><?php echo $porcentagemConcluida; ?>% concluído</p>
            </div>

            <?php
            // Buscar todas as metas do usuário
            $stmt = $pdo->prepare("SELECT * FROM plano_acao WHERE user_id = ? ORDER BY concluida ASC, prazo ASC");
            $stmt->execute([$user_id]);
            $todasMetas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <?php if (!empty($todasMetas)): ?>
                <div class="metas-tabs mt-4">
                    <button class="meta-tab active" data-meta-filter="todas">Todas</button>
                    <button class="meta-tab" data-meta-filter="pendentes">Pendentes</button>
                    <button class="meta-tab" data-meta-filter="concluidas">Concluídas</button>
                </div>

                <div class="lista-metas mt-3">
                    <?php foreach ($todasMetas as $meta): ?>
                        <div class="meta-item <?php echo $meta['concluida'] ? 'meta-concluida' : ''; ?>">
                            <form method="post">
                                <input type="hidden" name="concluir_meta" value="<?php echo $meta['id']; ?>">
                                <input type="checkbox" class="meta-checkbox" <?php echo $meta['concluida'] ? 'checked disabled' : ''; ?>
                                       title="<?php echo $meta['concluida'] ? 'Meta concluída' : 'Marcar como concluída'; ?>"
                                       onchange="this.form.submit()">
                            </form>
                            <div class="meta-conteudo">
                                <h3 class="meta-titulo"><?php echo htmlspecialchars($meta['titulo']); ?></h3>
                                <p class="meta-descricao"><?php echo htmlspecialchars($meta['descricao']); ?></p>
                                <p class="meta-prazo">
                                    <i class="fas fa-calendar"></i>
                                    Prazo: <?php echo date('d/m/Y', strtotime($meta['prazo'])); ?>

                                    <?php if ($meta['concluida']): ?>
                                        <span class="meta-status concluido">Concluída</span>
                                    <?php elseif (strtotime($meta['prazo']) < time()): ?>
                                        <span class="meta-status atrasado">Atrasada</span>
                                    <?php else: ?>
                                        <span class="meta-status pendente">Pendente</span>
                                    <?php endif; ?>
                                </p>
                            </div>

                            <?php if (!$meta['concluida']): ?>
                                <div class="meta-acoes">
                                    <button class="btn btn-secundario btn-pequeno" onclick="editarMeta(<?php echo $meta['id']; ?>)">
                                        Editar
                                    </button>
                                    <button class="btn btn-perigo btn-pequeno" onclick="excluirMeta(<?php echo $meta['id']; ?>)">
                                        Excluir
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center mt-4">
                    <p>Você ainda não definiu metas para seu plano de ação.</p>
                    <button class="btn btn-primario mt-2" onclick="toggleModal('modal-adicionar-meta')">
                        Criar Primeira Meta
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Personalidade -->
    <div class="tab-content" id="personalidade">
        <div class="perfil-conteudo">
            <h2 class="secao-titulo">Teste de Personalidade</h2>

            <?php if ($personalidade): ?>
                <div class="teste-personalidade-resultado">
                    <h3>Seus Resultados</h3>

                    <div class="teste-personalidade">
                        <div class="teste-item">
                            <div class="teste-titulo">Extroversão</div>
                            <div class="teste-valor"><?php echo $personalidade['extrovertido']; ?>%</div>
                            <div class="teste-descricao">
                                <?php if ($personalidade['extrovertido'] > 50): ?>
                                    Você tende a ser extrovertido, obtendo energia de interações sociais.
                                <?php else: ?>
                                    Você tende a ser introvertido, preferindo momentos de introspecção.
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="teste-item">
                            <div class="teste-titulo">Intuição</div>
                            <div class="teste-valor"><?php echo $personalidade['intuitivo']; ?>%</div>
                            <div class="teste-descricao">
                                <?php if ($personalidade['intuitivo'] > 50): ?>
                                    Você tende a ser intuitivo, focando em possibilidades e conexões abstratas.
                                <?php else: ?>
                                    Você tende a ser sensorial, focando em fatos concretos e realidade.
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="teste-item">
                            <div class="teste-titulo">Pensamento</div>
                            <div class="teste-valor"><?php echo $personalidade['racional']; ?>%</div>
                            <div class="teste-descricao">
                                <?php if ($personalidade['racional'] > 50): ?>
                                    Você tende a ser racional, priorizando lógica em decisões.
                                <?php else: ?>
                                    Você tende a ser emotivo, priorizando sentimentos em decisões.
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="teste-item">
                            <div class="teste-titulo">Julgamento</div>
                            <div class="teste-valor"><?php echo $personalidade['julgador']; ?>%</div>
                            <div class="teste-descricao">
                                <?php if ($personalidade['julgador'] > 50): ?>
                                    Você tende a ser estruturado, preferindo planejamento e organização.
                                <?php else: ?>
                                    Você tende a ser perceptivo, preferindo flexibilidade e adaptação.
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($traço_dominante): ?>
                        <div class="traco-dominante-container text-center mt-4">
                            <h3>Seu Traço Dominante</h3>
                            <div class="traco-dominante"><?php echo $traço_dominante; ?></div>
                            <p class="mt-2">
                                <?php
                                $descricoes = [
                                    'Extrovertido' => 'Você é uma pessoa que se energiza através de interações sociais, sendo naturalmente comunicativo e aberto ao mundo exterior.',
                                    'Intuitivo' => 'Você tende a ver padrões e conexões, focando mais nas possibilidades futuras do que na realidade imediata.',
                                    'Racional' => 'Você toma decisões baseadas principalmente na lógica e análise objetiva, valorizando a consistência.',
                                    'Julgador' => 'Você prefere ambientes organizados e estruturados, e se sente melhor quando as situações estão planejadas e decididas.'
                                ];
                                echo $descricoes[$traço_dominante] ?? '';
                                ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <div class="text-center mt-4">
                        <button class="btn btn-primario" onclick="toggleModal('modal-teste-personalidade')">
                            Refazer Teste
                        </button>
                    </div>
                </div>

                <!-- Gráfico de Radar da Personalidade -->
                <div class="graficos-personalidade mt-4">
                    <h3 class="text-center mb-3">Visualização dos Traços</h3>
                    <div class="grafico-container">
                        <canvas id="personalidadeChart" width="300" height="300"></canvas>
                    </div>
                </div>

            <?php else: ?>
                <div class="teste-personalidade-intro text-center">
                    <p>Você ainda não realizou o teste de personalidade.</p>
                    <p class="mt-2">Este teste ajudará você a entender melhor seus traços de personalidade e como eles influenciam suas escolhas de vida.</p>
                    <button class="btn btn-primario mt-3" onclick="toggleModal('modal-teste-personalidade')">
                        Iniciar Teste de Personalidade
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Landing Page -->
    <div class="tab-content" id="landing-page">
        <div class="perfil-conteudo">
            <h2 class="secao-titulo">
                Minha Landing Page
                <button class="btn btn-primario btn-pequeno" onclick="toggleModal('modal-editar-landing')">
                    Editar
                </button>
            </h2>

            <div class="landing-preview">
                <h3>Prévia da sua página pessoal</h3>
                <p class="mb-3">Esta é uma prévia de como sua landing page pública aparecerá para outros visitantes.</p>

                <div class="landing-container">
                    <div class="landing-header">
                        <div class="landing-foto">
                            <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="Foto de perfil">
                        </div>
                        <div class="landing-info">
                            <h1 class="landing-nome"><?php echo htmlspecialchars($username); ?></h1>
                            <p class="landing-bio">
                                <?php echo !empty($user['description']) ? nl2br(htmlspecialchars($user['description'])) : 'Adicione uma descrição sobre você...'; ?>
                            </p>
                        </div>
                    </div>

                    <div class="landing-secao">
                        <h2>Sobre mim</h2>
                        <div class="landing-conteudo">
                            <?php echo !empty($perfil['fale_sobre_voce']) ? nl2br(htmlspecialchars($perfil['fale_sobre_voce'])) : 'Adicione informações sobre você...'; ?>
                        </div>
                    </div>

                    <div class="landing-secao">
                        <h2>Meus valores</h2>
                        <div class="landing-conteudo">
                            <?php echo !empty($perfil['meus_valores']) ? nl2br(htmlspecialchars($perfil['meus_valores'])) : 'Adicione seus valores...'; ?>
                        </div>
                    </div>

                    <!-- Aptidões em um gráfico de radar -->
                    <div class="landing-secao">
                        <h2>Minhas aptidões</h2>
                        <div class="landing-graficos">
                            <canvas id="aptidoesLandingChart" width="300" height="250"></canvas>
                        </div>
                    </div>

                    <div class="landing-secao">
                        <h2>Metas e objetivos</h2>
                        <div class="landing-conteudo">
                            <?php if (!empty($proximasMetas)): ?>
                                <ul class="landing-metas">
                                    <?php foreach (array_slice($proximasMetas, 0, 3) as $meta): ?>
                                        <li>
                                            <span class="landing-meta-titulo"><?php echo htmlspecialchars($meta['titulo']); ?></span>
                                            <span class="landing-meta-prazo">até <?php echo date('d/m/Y', strtotime($meta['prazo'])); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p>Ainda não defini metas públicas.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="landing-secao">
                        <h2>Traço de personalidade dominante</h2>
                        <div class="landing-conteudo text-center">
                            <?php if ($traço_dominante): ?>
                                <div class="traco-dominante"><?php echo $traço_dominante; ?></div>
                                <p class="mt-2">
                                    <?php echo $descricoes[$traço_dominante] ?? ''; ?>
                                </p>
                            <?php else: ?>
                                <p>Ainda não realizei o teste de personalidade.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="landing-contato">
                        <h2>Entre em contato</h2>
                        <p class="landing-email"><?php echo htmlspecialchars($user['email']); ?></p>
                        <div class="landing-redes">
                            <!-- Ícones de redes sociais aqui -->
                        </div>
                    </div>
                </div>

                <div class="landing-acoes mt-3 text-center">
                    <a href="landing-page.php?user=<?php echo $user_id; ?>" target="_blank" class="btn btn-primario btn-icone">
                        <i class="fas fa-external-link-alt"></i> Ver Página Completa
                    </a>
                    <button class="btn btn-secundario btn-icone">
                        <i class="fas fa-share"></i> Compartilhar Link
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modais -->

    <!-- Modal Editar Perfil -->
    <div class="modal" id="modal-editar-perfil">
        <div class="modal-conteudo">
            <div class="modal-header">
                <h2>Editar Perfil</h2>
                <button class="modal-fechar" onclick="toggleModal('modal-editar-perfil')">&times;</button>
            </div>
            <div class="modal-body">
                <form method="post" class="perfil-form">
                    <div class="form-grupo">
                        <label class="form-label" for="name">Nome</label>
                        <input type="text" id="name" name="name" class="form-input" value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>

                    <div class="form-grupo">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="form-grupo">
                        <label class="form-label" for="description">Descrição</label>
                        <textarea id="description" name="description" class="form-input textarea" rows="4"><?php echo htmlspecialchars($user['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secundario" onclick="toggleModal('modal-editar-perfil')">Cancelar</button>
                        <button type="submit" name="edit_usuario" class="btn btn-primario">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Quem Sou Eu -->
    <div class="modal" id="modal-quem-sou">
        <div class="modal-conteudo modal-grande">
            <div class="modal-header">
                <h2>Quem Sou Eu</h2>
                <button class="modal-fechar" onclick="toggleModal('modal-quem-sou')">&times;</button>
            </div>
            <div class="modal-body">
                <form method="post" class="perfil-form">
                    <div class="form-tabs">
                        <button type="button" class="form-tab active" data-form-tab="pessoal">Pessoal</button>
                        <button type="button" class="form-tab" data-form-tab="relacionamentos">Relacionamentos</button>
                        <button type="button" class="form-tab" data-form-tab="cotidiano">Cotidiano</button>
                        <button type="button" class="form-tab" data-form-tab="visao">Como me vejo</button>
                    </div>

                    <div class="form-content active" id="pessoal">
                        <div class="form-grupo">
                            <label class="form-label" for="sobre_voce">Fale sobre você</label>
                            <textarea id="sobre_voce" name="sobre_voce" class="form-input textarea"><?php echo htmlspecialchars($perfil['fale_sobre_voce'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-grupo">
                            <label class="form-label" for="lembrancas">Minhas lembranças</label>
                            <textarea id="lembrancas" name="lembrancas" class="form-input textarea"><?php echo htmlspecialchars($perfil['minhas_lembrancas'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-grupo">
                            <label class="form-label" for="pontos_fortes">Pontos fortes</label>
                            <textarea id="pontos_fortes" name="pontos_fortes" class="form-input textarea"><?php echo htmlspecialchars($perfil['pontos_fortes'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-grupo">
                            <label class="form-label" for="pontos_fracos">Pontos a desenvolver</label>
                            <textarea id="pontos_fracos" name="pontos_fracos" class="form-input textarea"><?php echo htmlspecialchars($perfil['pontos_fracos'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-grupo">
                            <label class="form-label" for="valores">Meus valores</label>
                            <textarea id="valores" name="valores" class="form-input textarea"><?php echo htmlspecialchars($perfil['meus_valores'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-grupo">
                            <label class="form-label">Principais aptidões</label>
                            <div class="form-checkbox-group">
                                <?php
                                $aptidoes = [
                                    'Comunicação', 'Liderança', 'Criatividade', 'Organização',
                                    'Planejamento', 'Trabalho em equipe', 'Resolução de problemas',
                                    'Adaptabilidade', 'Pensamento crítico', 'Tecnologia'
                                ];
                                $selecionadas = explode(', ', $perfil['principais_aptidoes'] ?? '');

                                foreach ($aptidoes as $aptidao):
                                    ?>
                                    <label class="form-checkbox">
                                        <input type="checkbox" name="aptidoes[]" value="<?php echo $aptidao; ?>"
                                            <?php echo in_array($aptidao, $selecionadas) ? 'checked' : ''; ?>>
                                        <?php echo $aptidao; ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-content" id="relacionamentos">
                        <div class="form-grupo">
                            <label class="form-label" for="familia">Relações com família</label>
                            <textarea id="familia" name="familia" class="form-input textarea"><?php echo htmlspecialchars($perfil['relacoes_familia'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-grupo">
                            <label class="form-label" for="amigos">Relações com amigos</label>
                            <textarea id="amigos" name="amigos" class="form-input textarea"><?php echo htmlspecialchars($perfil['relacoes_amigos'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-grupo">
                            <label class="form-label" for="escola">Relações na escola/trabalho</label>
                            <textarea id="escola" name="escola" class="form-input textarea"><?php echo htmlspecialchars($perfil['relacoes_escola'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-grupo">
                            <label class="form-label" for="sociedade">Relações com a sociedade</label>
                            <textarea id="sociedade" name="sociedade" class="form-input textarea"><?php echo htmlspecialchars($perfil['relacoes_sociedade'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="form-content" id="cotidiano">
                        <div class="form-grupo">
                            <label class="form-label" for="gosto_fazer">O que gosto de fazer</label>
                            <textarea id="gosto_fazer" name="gosto_fazer" class="form-input textarea"><?php echo htmlspecialchars($perfil['gosto_fazer'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-grupo">
                            <label class="form-label" for="nao_gosto">O que não gosto de fazer</label>
                            <textarea id="nao_gosto" name="nao_gosto" class="form-input textarea"><?php echo htmlspecialchars($perfil['nao_gosto_fazer'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-grupo">
                            <label class="form-label" for="rotina">Minha rotina</label>
                            <textarea id="rotina" name="rotina" class="form-input textarea"><?php echo htmlspecialchars($perfil['rotina'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-grupo">
                            <label class="form-label" for="lazer">Momentos de lazer</label>
                            <textarea id="lazer" name="lazer" class="form-input textarea"><?php echo htmlspecialchars($perfil['lazer'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-grupo">
                            <label class="form-label" for="estudos">Meus estudos</label>
                            <textarea id="estudos" name="estudos" class="form-input textarea"><?php echo htmlspecialchars($perfil['estudos'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-grupo">
                            <label class="form-label" for="vida_escolar">Vida escolar</label>
                            <textarea id="vida_escolar" name="vida_escolar" class="form-input textarea"><?php echo htmlspecialchars($perfil['vida_escolar'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="form-content" id="visao">
                        <div class="form-grupo">
                            <label class="form-label" for="visao_fisica">Como me vejo fisicamente</label>
                            <textarea id="visao_fisica" name="visao_fisica" class="form-input textarea"><?php echo htmlspecialchars($perfil['visao_fisica'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-grupo">
                            <label class="form-label" for="visao_intelectual">Como me vejo intelectualmente</label>
                            <textarea id="visao_intelectual" name="visao_intelectual" class="form-input textarea"><?php echo htmlspecialchars($perfil['visao_intelectual'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-grupo">
                            <label class="form-label" for="visao_emocional">Como me vejo emocionalmente</label>
                            <textarea id="visao_emocional" name="visao_emocional" class="form-input textarea"><?php echo htmlspecialchars($perfil['visao_emocional'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-grupo">
                            <label class="form-label" for="visao_amigos">Como meus amigos me veem</label>
                            <textarea id="visao_amigos" name="visao_amigos" class="form-input textarea"><?php echo htmlspecialchars($perfil['visao_dos_amigos'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-grupo">
                            <label class="form-label" for="visao_familiares">Como meus familiares me veem</label>
                            <textarea id="visao_familiares" name="visao_familiares" class="form-input textarea"><?php echo htmlspecialchars($perfil['visao_dos_familiares'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-grupo">
                            <label class="form-label" for="visao_professores">Como meus professores me veem</label>
                            <textarea id="visao_professores" name="visao_professores" class="form-input textarea"><?php echo htmlspecialchars($perfil['visao_dos_professores'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-grupo">
                            <label class="form-label" for="autovalorizacao">Quanto me valorizo (1-10)</label>
                            <input type="range" id="autovalorizacao" name="autovalorizacao" min="1" max="10" class="range-input"
                                   value="<?php echo $perfil['autovalorizacao_total'] ?? 5; ?>">
                            <div class="range-value">
                                <span id="autovalorizacao-valor"><?php echo $perfil['autovalorizacao_total'] ?? 5; ?></span>/10
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secundario" onclick="toggleModal('modal-quem-sou')">Cancelar</button>
                        <button type="submit" name="salvar_perfil_completo" class="btn btn-primario">Salvar Informações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Meta -->
    <div class="modal" id="modal-adicionar-meta">
        <div class="modal-conteudo">
            <div class="modal-header">
                <h2>Adicionar Meta</h2>
                <button class="modal-fechar" onclick="toggleModal('modal-adicionar-meta')">&times;</button>
            </div>
            <div class="modal-body">
                <form method="post" class="perfil-form">
                    <div class="form-grupo">
                        <label class="form-label" for="titulo">Título da Meta</label>
                        <input type="text" id="titulo" name="titulo" class="form-input" required>
                    </div>

                    <div class="form-grupo">
                        <label class="form-label" for="descricao">Descrição</label>
                        <textarea id="descricao" name="descricao" class="form-input textarea" rows="3"></textarea>
                    </div>

                    <div class="form-grupo">
                        <label class="form-label" for="prazo">Prazo</label>
                        <input type="date" id="prazo" name="prazo" class="form-input" required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secundario" onclick="toggleModal('modal-adicionar-meta')">Cancelar</button>
                        <button type="submit" name="adicionar_meta" class="btn btn-primario">Adicionar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Teste de Personalidade -->
    <div class="modal" id="modal-teste-personalidade">
        <div class="modal-conteudo">
            <div class="modal-header">
                <h2>Teste de Personalidade</h2>
                <button class="modal-fechar" onclick="toggleModal('modal-teste-personalidade')">&times;</button>
            </div>
            <div class="modal-body">
                <form method="post" class="perfil-form">
                    <p class="mb-3">Avalie as características abaixo conforme elas se aplicam a você.</p>

                    <div class="form-grupo">
                        <label class="form-label">Extroversão vs. Introversão</label>
                        <p class="form-descricao">O quanto você se energiza com interações sociais versus tempo sozinho?</p>
                        <div class="range-container">
                            <span>Introversão</span>
                            <input type="range" name="extrovertido" min="0" max="100" value="<?php echo $personalidade['extrovertido'] ?? 50; ?>" class="range-input">
                            <span>Extroversão</span>
                        </div>
                    </div>

                    <div class="form-grupo">
                        <label class="form-label">Intuição vs. Sensação</label>
                        <p class="form-descricao">Você prefere focar em padrões/possibilidades ou em fatos concretos?</p>
                        <div class="range-container">
                            <span>Sensação</span>
                            <input type="range" name="intuitivo" min="0" max="100" value="<?php echo $personalidade['intuitivo'] ?? 50; ?>" class="range-input">
                            <span>Intuição</span>
                        </div>
                    </div>

                    <div class="form-grupo">
                        <label class="form-label">Pensamento vs. Sentimento</label>
                        <p class="form-descricao">Ao tomar decisões, você prioriza lógica ou valores pessoais?</p>
                        <div class="range-container">
                            <span>Sentimento</span>
                            <input type="range" name="racional" min="0" max="100" value="<?php echo $personalidade['racional'] ?? 50; ?>" class="range-input">
                            <span>Pensamento</span>
                        </div>
                    </div>

                    <div class="form-grupo">
                        <label class="form-label">Julgamento vs. Percepção</label>
                        <p class="form-descricao">Você prefere ter as coisas planejadas ou ser mais espontâneo?</p>
                        <div class="range-container">
                            <span>Percepção</span>
                            <input type="range" name="julgador" min="0" max="100" value="<?php echo $personalidade['julgador'] ?? 50; ?>" class="range-input">
                            <span>Julgamento</span>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secundario" onclick="toggleModal('modal-teste-personalidade')">Cancelar</button>
                        <button type="submit" name="teste_personalidade" class="btn btn-primario">Salvar Resultados</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Landing Page -->
    <div class="modal" id="modal-editar-landing">
        <div class="modal-conteudo">
            <div class="modal-header">
                <h2>Configurações da Landing Page</h2>
                <button class="modal-fechar" onclick="toggleModal('modal-editar-landing')">&times;</button>
            </div>
            <div class="modal-body">
                <form method="post" class="perfil-form">
                    <div class="form-grupo">
                        <label class="form-label" for="landing_titulo">Título da Página</label>
                        <input type="text" id="landing_titulo" name="landing_titulo" class="form-input"
                               value="<?php echo htmlspecialchars($user['landing_titulo'] ?? 'Meu Perfil'); ?>">
                    </div>

                    <div class="form-grupo">
                        <label class="form-label">Seções Visíveis</label>
                        <div class="form-checkbox-group">
                            <label class="form-checkbox">
                                <input type="checkbox" name="landing_seccoes[]" value="sobre" checked>
                                Sobre Mim
                            </label>
                            <label class="form-checkbox">
                                <input type="checkbox" name="landing_seccoes[]" value="valores" checked>
                                Meus Valores
                            </label>
                            <label class="form-checkbox">
                                <input type="checkbox" name="landing_seccoes[]" value="aptidoes" checked>
                                Minhas Aptidões
                            </label>
                            <label class="form-checkbox">
                                <input type="checkbox" name="landing_seccoes[]" value="metas" checked>
                                Metas e Objetivos
                            </label>
                            <label class="form-checkbox">
                                <input type="checkbox" name="landing_seccoes[]" value="personalidade" checked>
                                Traço de Personalidade
                            </label>
                        </div>
                    </div>

                    <div class="form-grupo">
                        <label class="form-label">Redes Sociais</label>
                        <div class="form-row">
                            <div class="form-col">
                                <input type="text" placeholder="Instagram" name="social_instagram" class="form-input">
                            </div>
                            <div class="form-col">
                                <input type="text" placeholder="LinkedIn" name="social_linkedin" class="form-input">
                            </div>
                        </div>
                        <div class="form-row mt-2">
                            <div class="form-col">
                                <input type="text" placeholder="Twitter" name="social_twitter" class="form-input">
                            </div>
                            <div class="form-col">
                                <input type="text" placeholder="GitHub" name="social_github" class="form-input">
                            </div>
                        </div>
                    </div>

                    <div class="form-grupo">
                        <label class="form-label">Tema da Página</label>
                        <div class="temas-container">
                            <label class="tema-item">
                                <input type="radio" name="landing_tema" value="padrao" checked>
                                <span class="tema-preview tema-padrao"></span>
                                <span>Padrão</span>
                            </label>
                            <label class="tema-item">
                                <input type="radio" name="landing_tema" value="escuro">
                                <span class="tema-preview tema-escuro"></span>
                                <span>Escuro</span>
                            </label>
                            <label class="tema-item">
                                <input type="radio" name="landing_tema" value="minimalista">
                                <span class="tema-preview tema-minimalista"></span>
                                <span>Minimalista</span>
                            </label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secundario" onclick="toggleModal('modal-editar-landing')">Cancelar</button>
                        <button type="submit" name="salvar_landing" class="btn btn-primario">Salvar Configurações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Função para alternar entre as abas
        function toggleTab(tabId) {
            // Remover classe active de todas as abas
            document.querySelectorAll('.perfil-tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Adicionar classe active na aba clicada
            document.querySelector(`.perfil-tab[data-tab="${tabId}"]`).classList.add('active');

            // Esconder todos os conteúdos
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });

            // Mostrar o conteúdo da aba selecionada
            document.getElementById(tabId).classList.add('active');
        }

        // Função para abrir/fechar modais
        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.toggle('active');
        }

        // Configurar eventos de clique nas abas
        document.querySelectorAll('.perfil-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                toggleTab(tab.getAttribute('data-tab'));
            });
        });

        // Configurar abas do formulário "Quem Sou Eu"
        document.querySelectorAll('.form-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                // Remover classe active de todas as abas
                document.querySelectorAll('.form-tab').forEach(t => {
                    t.classList.remove('active');
                });

                // Adicionar classe active na aba clicada
                tab.classList.add('active');

                // Esconder todos os conteúdos
                document.querySelectorAll('.form-content').forEach(content => {
                    content.classList.remove('active');
                });

                // Mostrar o conteúdo da aba selecionada
                document.getElementById(tab.getAttribute('data-form-tab')).classList.add('active');
            });
        });

        // Atualizar valor do range de autovalorização
        const rangeAutoValor = document.getElementById('autovalorizacao');
        const valorAutoValor = document.getElementById('autovalorizacao-valor');

        if (rangeAutoValor && valorAutoValor) {
            rangeAutoValor.addEventListener('input', () => {
                valorAutoValor.textContent = rangeAutoValor.value;
            });
        }

        // Configuração dos gráficos
        document.addEventListener('DOMContentLoaded', function() {
            // Gráfico de aptidões no dashboard
            const ctxAptidoes = document.getElementById('aptidoesChart');

            if (ctxAptidoes) {
                const aptidoesChart = new Chart(ctxAptidoes, {
                    type: 'radar',
                    data: {
                        labels: ['Comunicação', 'Liderança', 'Criatividade', 'Organização', 'Planejamento', 'Trabalho em equipe'],
                        datasets: [{
                            label: 'Minhas Aptidões',
                            data: [
                                <?php
                                $aptidoesUsuario = explode(', ', $perfil['principais_aptidoes'] ?? '');
                                $aptidoesGrafico = [
                                    'Comunicação' => in_array('Comunicação', $aptidoesUsuario) ? 80 : 40,
                                    'Liderança' => in_array('Liderança', $aptidoesUsuario) ? 85 : 30,
                                    'Criatividade' => in_array('Criatividade', $aptidoesUsuario) ? 90 : 45,
                                    'Organização' => in_array('Organização', $aptidoesUsuario) ? 75 : 35,
                                    'Planejamento' => in_array('Planejamento', $aptidoesUsuario) ? 70 : 40,
                                    'Trabalho em equipe' => in_array('Trabalho em equipe', $aptidoesUsuario) ? 85 : 50
                                ];
                                echo implode(', ', $aptidoesGrafico);
                                ?>
                            ],
                            backgroundColor: 'rgba(0, 100, 250, 0.2)',
                            borderColor: 'rgba(0, 100, 250, 0.8)',
                            pointBackgroundColor: 'rgba(0, 100, 250, 1)',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: 'rgba(0, 100, 250, 1)'
                        }]
                    },
                    options: {
                        elements: {
                            line: {
                                borderWidth: 3
                            }
                        },
                        scales: {
                            r: {
                                angleLines: {
                                    display: true
                                },
                                suggestedMin: 0,
                                suggestedMax: 100
                            }
                        }
                    }
                });
            }

            // Gráfico de radar da personalidade
            const ctxPersonalidade = document.getElementById('personalidadeChart');

            if (ctxPersonalidade && <?php echo $personalidade ? 'true' : 'false'; ?>) {
                const personalidadeChart = new Chart(ctxPersonalidade, {
                    type: 'radar',
                    data: {
                        labels: ['Extroversão', 'Intuição', 'Pensamento', 'Julgamento'],
                        datasets: [{
                            label: 'Minha Personalidade',
                            data: [
                                <?php
                                if ($personalidade) {
                                    echo $personalidade['extrovertido'] . ', ';
                                    echo $personalidade['intuitivo'] . ', ';
                                    echo $personalidade['racional'] . ', ';
                                    echo $personalidade['julgador'];
                                } else {
                                    echo '50, 50, 50, 50';
                                }
                                ?>
                            ],
                            backgroundColor: 'rgba(0, 100, 250, 0.2)',
                            borderColor: 'rgba(0, 100, 250, 0.8)',
                            pointBackgroundColor: 'rgba(0, 100, 250, 1)',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: 'rgba(0, 100, 250, 1)'
                        }]
                    },
                    options: {
                        elements: {
                            line: {
                                borderWidth: 3
                            }
                        },
                        scales: {
                            r: {
                                angleLines: {
                                    display: true
                                },
                                suggestedMin: 0,
                                suggestedMax: 100
                            }
                        }
                    }
                });
            }

            // Gráfico de aptidões na landing page
            const ctxAptidoesLanding = document.getElementById('aptidoesLandingChart');

            if (ctxAptidoesLanding) {
                const aptidoesLandingChart = new Chart(ctxAptidoesLanding, {
                    type: 'radar',
                    data: {
                        labels: ['Comunicação', 'Liderança', 'Criatividade', 'Organização', 'Planejamento', 'Trabalho em equipe'],
                        datasets: [{
                            label: 'Minhas Aptidões',
                            data: [
                                <?php
                                echo implode(', ', $aptidoesGrafico);
                                ?>
                            ],
                            backgroundColor: 'rgba(0, 100, 250, 0.2)',
                            borderColor: 'rgba(0, 100, 250, 0.8)',
                            pointBackgroundColor: 'rgba(0, 100, 250, 1)',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: 'rgba(0, 100, 250, 1)'
                        }]
                    },
                    options: {
                        elements: {
                            line: {
                                borderWidth: 3
                            }
                        },
                        scales: {
                            r: {
                                angleLines: {
                                    display: true
                                },
                                suggestedMin: 0,
                                suggestedMax: 100
                            }
                        }
                    }
                });
            }
        });

        // Função para filtrar metas
        document.querySelectorAll('.meta-tab').forEach(tabBtn => {
            tabBtn.addEventListener('click', function() {
                // Atualizar estado dos botões
                document.querySelectorAll('.meta-tab').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                const filtro = this.getAttribute('data-meta-filter');
                const metaItems = document.querySelectorAll('.meta-item');

                // Aplicar filtro
                metaItems.forEach(meta => {
                    if (filtro === 'todas') {
                        meta.style.display = 'flex';
                    } else if (filtro === 'pendentes' && !meta.classList.contains('meta-concluida')) {
                        meta.style.display = 'flex';
                    } else if (filtro === 'concluidas' && meta.classList.contains('meta-concluida')) {
                        meta.style.display = 'flex';
                    } else {
                        meta.style.display = 'none';
                    }
                });
            });
        });

        // Funções para editar e excluir metas
        function editarMeta(id) {
            // Primeiro, obter os dados atuais da meta
            fetch('user.php?get_meta=' + id, {
                method: 'GET'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Criar um modal dinâmico para edição
                        const modal = document.createElement('div');
                        modal.className = 'modal fade';
                        modal.id = 'editarMetaModal';
                        modal.setAttribute('tabindex', '-1');
                        modal.setAttribute('role', 'dialog');
                        modal.setAttribute('aria-labelledby', 'editarMetaModalLabel');
                        modal.setAttribute('aria-hidden', 'true');

                        modal.innerHTML = `
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editarMetaModalLabel">Editar Meta</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="formEditarMeta">
                                <input type="hidden" name="meta_id" value="${id}">
                                <input type="hidden" name="action" value="editar_meta">

                                <div class="form-group">
                                    <label for="titulo">Título</label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" value="${data.meta.titulo}" required>
                                </div>

                                <div class="form-group">
                                    <label for="descricao">Descrição</label>
                                    <textarea class="form-control" id="descricao" name="descricao" rows="3">${data.meta.descricao || ''}</textarea>
                                </div>

                                <div class="form-group">
                                    <label for="prazo">Prazo</label>
                                    <input type="date" class="form-control" id="prazo" name="prazo" value="${data.meta.prazo}" required>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" onclick="salvarEdicaoMeta()">Salvar</button>
                        </div>
                    </div>
                </div>
            `;

                        document.body.appendChild(modal);
                        $('#editarMetaModal').modal('show');

                        // Remover o modal do DOM quando for fechado
                        $('#editarMetaModal').on('hidden.bs.modal', function (e) {
                            document.body.removeChild(modal);
                        });
                    } else {
                        alert('Erro ao buscar dados da meta: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Erro na requisição: ' + error);
                });
        }

        function salvarEdicaoMeta() {
            const form = document.getElementById('formEditarMeta');
            const formData = new FormData(form);

            fetch('user.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        $('#editarMetaModal').modal('hide');
                        alert('Meta atualizada com sucesso!');
                        window.location.reload(); // Recarregar para ver as alterações
                    } else {
                        alert('Erro ao atualizar: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Erro na requisição: ' + error);
                });
        }

        function excluirMeta(id) {
            if (confirm('Tem certeza que deseja excluir esta meta?')) {
                const formData = new FormData();
                formData.append('action', 'excluir_meta');
                formData.append('meta_id', id);

                fetch('user.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Meta excluída com sucesso!');
                            window.location.reload(); // Recarregar para ver as alterações
                        } else {
                            alert('Erro ao excluir: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('Erro na requisição: ' + error);
                    });
            }
        }

    </script>
</body>
</html>
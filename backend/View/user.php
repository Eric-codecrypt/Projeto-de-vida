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
    </div>

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
        <section class="perfil-conteudo">
            <h2 class="secao-titulo">
                Quem Sou Eu
                <button class="btn btn-primario btn-pequeno" data-modal="editar-quem-sou">
                    <i class="fas fa-edit"></i> Editar
                </button>
            </h2>

            <?php if ($perfil): ?>
                <div class="perfil-form">
                    <div class="form-grupo">
                        <label class="form-label">Sobre Mim</label>
                        <p><?php echo $perfil['fale_sobre_voce']; ?></p>
                    </div>

                    <div class="form-grupo">
                        <label class="form-label">Minhas Lembranças</label>
                        <p><?php echo $perfil['minhas_lembrancas']; ?></p>
                    </div>

                    <div class="form-grupo">
                        <label class="form-label">Pontos Fortes</label>
                        <p><?php echo $perfil['pontos_fortes']; ?></p>
                    </div>

                    <div class="form-grupo">
                        <label class="form-label">Pontos Fracos</label>
                        <p><?php echo $perfil['pontos_fracos']; ?></p>
                    </div>

                    <div class="form-grupo">
                        <label class="form-label">Meus Valores</label>
                        <p><?php echo $perfil['meus_valores']; ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center mt-4">
                    <p>Você ainda não preencheu seu perfil "Quem Sou Eu"</p>
                    <button class="btn btn-primario mt-3" data-modal="editar-quem-sou">
                        Preencher Agora
                    </button>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <div class="tab-content hidden" id="personalidade">
        <section class="perfil-conteudo">
            <h2 class="secao-titulo">
                Teste de Personalidade
                <button class="btn btn-primario btn-pequeno" data-modal="teste-personalidade">
                    <i class="fas fa-edit"></i> Realizar Teste
                </button>
            </h2>

            <?php if ($personalidade): ?>
                <div class="teste-personalidade">
                    <div class="teste-item">
                        <div class="teste-titulo">Extroversão</div>
                        <div class="teste-valor"><?php echo $personalidade['extrovertido']; ?>%</div>
                        <div class="progresso-container">
                            <div class="progresso-barra" style="width: <?php echo $personalidade['extrovertido']; ?>%"></div>
                        </div>
                    </div>

                    <div class="teste-item">
                        <div class="teste-titulo">Intuição</div>
                        <div class="teste-valor"><?php echo $personalidade['intuitivo']; ?>%</div>
                        <div class="progresso-container">
                            <div class="progresso-barra" style="width: <?php echo $personalidade['intuitivo']; ?>%"></div>
                        </div>
                    </div>

                    <div class="teste-item">
                        <div class="teste-titulo">Racionalidade</div>
                        <div class="teste-valor"><?php echo $personalidade['racional']; ?>%</div>
                        <div class="progresso-container">
                            <div class="progresso-barra" style="width: <?php echo $personalidade['racional']; ?>%"></div>
                        </div>
                    </div>

                    <div class="teste-item">
                        <div class="teste-titulo">Julgamento</div>
                        <div class="teste-valor"><?php echo $personalidade['julgador']; ?>%</div>
                        <div class="progresso-container">
                            <div class="progresso-barra" style="width: <?php echo $personalidade['julgador']; ?>%"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-center">
                    <h3>Seu traço dominante é:</h3>
                    <div class="traco-dominante mt-2"><?php echo $traço_dominante; ?></div>
                </div>
            <?php else: ?>
                <div class="text-center mt-4">
                    <p>Você ainda não realizou o teste de personalidade</p>
                    <button class="btn btn-primario mt-3" data-modal="teste-personalidade">
                        Realizar Teste Agora
                    </button>
                </div>
            <?php endif; ?>
        </section>
    </div>

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
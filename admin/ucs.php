<?php
require_once '../includes/config.php';
exigirPerfil('gestor');
$db = getDB();
$erro = $sucesso = '';

if ($_SERVER['REQUEST_METHOD']==='POST' && $_POST['acao']==='criar') {
    $nome=$_POST['nome']??''; $desc=$_POST['descricao']??''; $cred=(int)($_POST['creditos']??6); $horas=(int)($_POST['horas_semana']??4);
    if (!$nome) $erro='Nome obrigatório.';
    else { $db->prepare("INSERT INTO ucs (nome,descricao,creditos,horas_semana) VALUES (?,?,?,?)")->execute([$nome,$desc,$cred,$horas]); $sucesso='UC criada!'; }
}

if (isset($_GET['apagar'])) {
    $db->prepare("DELETE FROM ucs WHERE id=?")->execute([(int)$_GET['apagar']]);
    $sucesso='UC removida.';
}

$ucs = $db->query("SELECT u.*, COUNT(pe.id) as em_cursos FROM ucs u LEFT JOIN plano_estudos pe ON pe.uc_id=u.id GROUP BY u.id ORDER BY u.nome")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head><meta charset="UTF-8"><title>UCs – Gestor</title>
<link rel="stylesheet" href="../assets/css/style.css"></head>
<body>
<div class="layout">
    <?php include 'sidebar.php'; ?>
    <main class="main">
        <div class="topbar">
            <div><h1>Unidades Curriculares</h1><p>Gira todas as UCs disponíveis.</p></div>
            <button class="btn btn-primary" style="width:auto" onclick="document.getElementById('modal-uc').classList.add('open')">+ Nova UC</button>
        </div>

        <?php if ($erro): ?><div class="alert alert-error">⚠ <?= htmlspecialchars($erro) ?></div><?php endif; ?>
        <?php if ($sucesso): ?><div class="alert alert-success">✓ <?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

        <div class="card">
            <div class="card-header"><h3>Todas as UCs (<?= count($ucs) ?>)</h3></div>
            <?php if ($ucs): ?>
            <table>
                <thead><tr><th>Nome</th><th>Créditos</th><th>Horas/semana</th><th>Nos cursos</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($ucs as $u): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($u['nome']) ?></strong><br><small style="color:var(--muted)"><?= htmlspecialchars(substr($u['descricao']??'',0,50)) ?></small></td>
                    <td><?= $u['creditos'] ?> ECTS</td>
                    <td><?= $u['horas_semana'] ?>h</td>
                    <td><span class="badge badge-blue"><?= $u['em_cursos'] ?></span></td>
                    <td><a href="ucs.php?apagar=<?= $u['id'] ?>" class="btn btn-danger btn-xs" onclick="return confirm('Apagar esta UC?')">Apagar</a></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="empty-state"><div class="empty-icon">📖</div><p>Nenhuma UC criada ainda.</p></div>
            <?php endif; ?>
        </div>
    </main>
</div>

<div class="modal-overlay" id="modal-uc">
    <div class="modal">
        <div class="modal-header"><h3>Nova Unidade Curricular</h3><button class="modal-close" onclick="document.getElementById('modal-uc').classList.remove('open')">✕</button></div>
        <form method="POST">
            <input type="hidden" name="acao" value="criar">
            <div class="form-group"><label>Nome da UC</label><input type="text" name="nome" required placeholder="Ex: Programação Web II"></div>
            <div class="form-group"><label>Descrição</label><textarea name="descricao" placeholder="Descrição breve..."></textarea></div>
            <div class="form-row">
                <div class="form-group"><label>Créditos ECTS</label><input type="number" name="creditos" value="6" min="1" max="30"></div>
                <div class="form-group"><label>Horas/semana</label><input type="number" name="horas_semana" value="4" min="1" max="20"></div>
            </div>
            <button type="submit" class="btn btn-primary">Criar UC</button>
        </form>
    </div>
</div>
</body>
</html>
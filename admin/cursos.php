<?php
require_once '../includes/config.php';
exigirPerfil('gestor');
$db = getDB();
$erro = $sucesso = '';

// Criar
if ($_SERVER['REQUEST_METHOD']==='POST' && $_POST['acao']==='criar') {
    $nome = trim($_POST['nome']??'');
    $desc = trim($_POST['descricao']??'');
    $anos = (int)($_POST['duracao_anos']??3);
    if (!$nome) $erro='Nome obrigatório.';
    else {
        $db->prepare("INSERT INTO cursos (nome,descricao,duracao_anos,criado_por) VALUES (?,?,?,?)")
           ->execute([$nome,$desc,$anos,$_SESSION['user_id']]);
        $sucesso='Curso criado com sucesso!';
    }
}

// Editar
if ($_SERVER['REQUEST_METHOD']==='POST' && $_POST['acao']==='editar') {
    $id=$_POST['id']; $nome=trim($_POST['nome']??''); $desc=trim($_POST['descricao']??''); $anos=(int)$_POST['duracao_anos']; $ativo=isset($_POST['ativo'])?1:0;
    if (!$nome) $erro='Nome obrigatório.';
    else { $db->prepare("UPDATE cursos SET nome=?,descricao=?,duracao_anos=?,ativo=? WHERE id=?")->execute([$nome,$desc,$anos,$ativo,$id]); $sucesso='Curso atualizado!'; }
}

// Apagar
if (isset($_GET['apagar'])) {
    $db->prepare("DELETE FROM cursos WHERE id=?")->execute([(int)$_GET['apagar']]);
    $sucesso='Curso removido.';
}

$cursos = $db->query("SELECT c.*, u.nome as criado_por_nome, (SELECT COUNT(*) FROM plano_estudos WHERE curso_id=c.id) as total_ucs FROM cursos c LEFT JOIN utilizadores u ON c.criado_por=u.id ORDER BY c.criado_em DESC")->fetchAll();

$editar = null;
if (isset($_GET['editar'])) {
    $editar = $db->prepare("SELECT * FROM cursos WHERE id=?");
    $editar->execute([(int)$_GET['editar']]);
    $editar = $editar->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head><meta charset="UTF-8"><title>Cursos – Gestor</title>
<link rel="stylesheet" href="../assets/css/style.css"></head>
<body>
<div class="layout">
    <?php include 'sidebar.php'; ?>
    <main class="main">
        <div class="topbar">
            <div><h1>Cursos</h1><p>Crie e gira os cursos da instituição.</p></div>
            <button class="btn btn-primary" style="width:auto" onclick="document.getElementById('modal-criar').classList.add('open')">+ Novo Curso</button>
        </div>

        <?php if ($erro): ?><div class="alert alert-error">⚠ <?= htmlspecialchars($erro) ?></div><?php endif; ?>
        <?php if ($sucesso): ?><div class="alert alert-success">✓ <?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

        <div class="card">
            <div class="card-header"><h3>Todos os Cursos (<?= count($cursos) ?>)</h3></div>
            <?php if ($cursos): ?>
            <table>
                <thead><tr><th>Nome</th><th>Duração</th><th>UCs no plano</th><th>Estado</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($cursos as $c): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($c['nome']) ?></strong><br><small style="color:var(--muted)"><?= htmlspecialchars(substr($c['descricao']??'',0,60)) ?></small></td>
                    <td><?= $c['duracao_anos'] ?> anos</td>
                    <td><span class="badge badge-blue"><?= $c['total_ucs'] ?> UCs</span></td>
                    <td><span class="badge <?= $c['ativo']?'badge-green':'badge-stone' ?>"><?= $c['ativo']?'Ativo':'Inativo' ?></span></td>
                    <td style="display:flex;gap:6px;flex-wrap:wrap;">
                        <a href="cursos.php?editar=<?= $c['id'] ?>" class="btn btn-secondary btn-xs">Editar</a>
                        <a href="plano_estudos.php?curso=<?= $c['id'] ?>" class="btn btn-secondary btn-xs">Plano</a>
                        <a href="cursos.php?apagar=<?= $c['id'] ?>" class="btn btn-danger btn-xs" onclick="return confirm('Apagar este curso?')">Apagar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="empty-state"><div class="empty-icon">🎓</div><p>Nenhum curso criado. Crie o primeiro!</p></div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Modal Criar -->
<div class="modal-overlay" id="modal-criar">
    <div class="modal">
        <div class="modal-header"><h3>Novo Curso</h3><button class="modal-close" onclick="document.getElementById('modal-criar').classList.remove('open')">✕</button></div>
        <form method="POST">
            <input type="hidden" name="acao" value="criar">
            <div class="form-group"><label>Nome do Curso</label><input type="text" name="nome" required placeholder="Ex: Engenharia Informática"></div>
            <div class="form-group"><label>Descrição</label><textarea name="descricao" placeholder="Descrição breve..."></textarea></div>
            <div class="form-group"><label>Duração (anos)</label>
                <select name="duracao_anos">
                    <option value="1">1 ano</option><option value="2">2 anos</option>
                    <option value="3" selected>3 anos</option><option value="4">4 anos</option><option value="5">5 anos</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Criar Curso</button>
        </form>
    </div>
</div>

<!-- Modal Editar -->
<?php if ($editar): ?>
<div class="modal-overlay open">
    <div class="modal">
        <div class="modal-header"><h3>Editar Curso</h3><button class="modal-close" onclick="window.location='cursos.php'">✕</button></div>
        <form method="POST">
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="id" value="<?= $editar['id'] ?>">
            <div class="form-group"><label>Nome</label><input type="text" name="nome" value="<?= htmlspecialchars($editar['nome']) ?>" required></div>
            <div class="form-group"><label>Descrição</label><textarea name="descricao"><?= htmlspecialchars($editar['descricao']??'') ?></textarea></div>
            <div class="form-group"><label>Duração (anos)</label>
                <select name="duracao_anos">
                    <?php for($i=1;$i<=5;$i++): ?><option value="<?= $i ?>" <?= $editar['duracao_anos']==$i?'selected':'' ?>><?= $i ?> ano<?= $i>1?'s':'' ?></option><?php endfor; ?>
                </select>
            </div>
            <div class="form-group" style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" name="ativo" id="ativo" <?= $editar['ativo']?'checked':'' ?> style="width:auto">
                <label for="ativo" style="text-transform:none;font-size:0.9rem;margin:0">Curso ativo</label>
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </form>
    </div>
</div>
<?php endif; ?>
</body>
</html>
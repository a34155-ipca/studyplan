<?php
require_once '../includes/config.php';
exigirPerfil('gestor');
$db = getDB();
$erro = $sucesso = '';

// Validar / Rejeitar
if ($_SERVER['REQUEST_METHOD']==='POST' && in_array($_POST['acao'],['aprovar','rejeitar'])) {
    $id=(int)$_POST['id']; $obs=trim($_POST['observacoes']??''); $estado=$_POST['acao']==='aprovar'?'aprovada':'rejeitada';
    $db->prepare("UPDATE fichas_aluno SET estado=?,observacoes=?,validado_por=?,validado_em=NOW() WHERE id=?")
       ->execute([$estado,$obs,$_SESSION['user_id'],$id]);
    $sucesso='Ficha '.$estado.' com sucesso!';
}

$filtro = $_GET['filtro'] ?? 'submetida';
$fichas = $db->prepare("SELECT f.*,u.nome,u.email,c.nome as curso,v.nome as validador FROM fichas_aluno f JOIN utilizadores u ON f.utilizador_id=u.id LEFT JOIN cursos c ON f.curso_id=c.id LEFT JOIN utilizadores v ON f.validado_por=v.id WHERE f.estado=? ORDER BY f.atualizado_em DESC");
$fichas->execute([$filtro]);
$fichas = $fichas->fetchAll();

$ver = null;
if (isset($_GET['ver'])) {
    $s=$db->prepare("SELECT f.*,u.nome,u.email,c.nome as curso,v.nome as validador FROM fichas_aluno f JOIN utilizadores u ON f.utilizador_id=u.id LEFT JOIN cursos c ON f.curso_id=c.id LEFT JOIN utilizadores v ON f.validado_por=v.id WHERE f.id=?");
    $s->execute([(int)$_GET['ver']]); $ver=$s->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head><meta charset="UTF-8"><title>Fichas – Gestor</title>
<link rel="stylesheet" href="../assets/css/style.css"></head>
<body>
<div class="layout">
    <?php include 'sidebar.php'; ?>
    <main class="main">
        <div class="topbar"><div><h1>Fichas de Aluno</h1><p>Valide ou rejeite fichas submetidas pelos alunos.</p></div></div>

        <?php if ($sucesso): ?><div class="alert alert-success">✓ <?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

        <!-- Filtros -->
        <div style="display:flex;gap:8px;margin-bottom:20px;">
            <?php foreach (['submetida'=>'Submetidas','aprovada'=>'Aprovadas','rejeitada'=>'Rejeitadas','rascunho'=>'Rascunhos'] as $k=>$v): ?>
            <a href="fichas.php?filtro=<?= $k ?>" class="btn <?= $filtro===$k?'btn-primary':'btn-secondary' ?> btn-sm" style="<?= $filtro===$k?'width:auto':'' ?>"><?= $v ?></a>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <div class="card-header"><h3>Fichas: <?= ucfirst($filtro) ?> (<?= count($fichas) ?>)</h3></div>
            <?php if ($fichas): ?>
            <table>
                <thead><tr><th>Aluno</th><th>Curso pretendido</th><th>Data</th><th>Estado</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($fichas as $f): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($f['nome']) ?></strong><br><small style="color:var(--muted)"><?= htmlspecialchars($f['email']) ?></small></td>
                    <td><?= htmlspecialchars($f['curso']??'—') ?></td>
                    <td style="color:var(--muted)"><?= date('d/m/Y',strtotime($f['atualizado_em'])) ?></td>
                    <td><span class="badge estado-<?= $f['estado'] ?>"><?= ucfirst($f['estado']) ?></span></td>
                    <td><a href="fichas.php?ver=<?= $f['id'] ?>&filtro=<?= $filtro ?>" class="btn btn-secondary btn-xs">Ver ficha</a></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="empty-state"><div class="empty-icon">📋</div><p>Nenhuma ficha com este estado.</p></div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Modal Ver Ficha -->
<?php if ($ver): ?>
<div class="modal-overlay open">
    <div class="modal" style="max-width:580px;">
        <div class="modal-header">
            <h3>Ficha de <?= htmlspecialchars($ver['nome']) ?></h3>
            <button class="modal-close" onclick="window.location='fichas.php?filtro=<?= $filtro ?>'">✕</button>
        </div>

        <?php if ($ver['foto']): ?>
            <img src="../assets/uploads/<?= htmlspecialchars($ver['foto']) ?>" class="foto-preview" alt="Foto">
        <?php else: ?>
            <div class="foto-placeholder">👤</div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:16px 0;">
            <div><small style="color:var(--muted)">Data de Nascimento</small><p><?= $ver['data_nascimento']?date('d/m/Y',strtotime($ver['data_nascimento'])):'—' ?></p></div>
            <div><small style="color:var(--muted)">Telefone</small><p><?= htmlspecialchars($ver['telefone']??'—') ?></p></div>
            <div><small style="color:var(--muted)">Naturalidade</small><p><?= htmlspecialchars($ver['naturalidade']??'—') ?></p></div>
            <div><small style="color:var(--muted)">Nacionalidade</small><p><?= htmlspecialchars($ver['nacionalidade']??'—') ?></p></div>
            <div style="grid-column:1/-1"><small style="color:var(--muted)">Morada</small><p><?= htmlspecialchars($ver['morada']??'—') ?></p></div>
            <div style="grid-column:1/-1"><small style="color:var(--muted)">Curso pretendido</small><p><strong><?= htmlspecialchars($ver['curso']??'—') ?></strong></p></div>
        </div>

        <?php if ($ver['observacoes']): ?>
            <div class="alert alert-info">💬 <?= htmlspecialchars($ver['observacoes']) ?></div>
        <?php endif; ?>

        <?php if ($ver['estado']==='submetida'): ?>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $ver['id'] ?>">
            <div class="form-group"><label>Observações / Justificação</label><textarea name="observacoes" placeholder="Registe aqui as suas observações..."></textarea></div>
            <div style="display:flex;gap:10px;">
                <button type="submit" name="acao" value="aprovar" class="btn btn-success" style="flex:1;justify-content:center">✓ Aprovar</button>
                <button type="submit" name="acao" value="rejeitar" class="btn btn-danger" style="flex:1;justify-content:center" onclick="return confirm('Rejeitar esta ficha?')">✕ Rejeitar</button>
            </div>
        </form>
        <?php else: ?>
            <div class="alert <?= $ver['estado']==='aprovada'?'alert-success':'alert-error' ?>">
                <?= $ver['estado']==='aprovada'?'✓ Aprovada':'✕ Rejeitada' ?> por <?= htmlspecialchars($ver['validador']??'—') ?> em <?= $ver['validado_em']?date('d/m/Y H:i',strtotime($ver['validado_em'])):'—' ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
</body>
</html>
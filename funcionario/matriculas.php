<?php
require_once '../includes/config.php';
exigirPerfil('funcionario');
$db = getDB();
$sucesso = '';

if ($_SERVER['REQUEST_METHOD']==='POST' && in_array($_POST['acao'],['aprovar','rejeitar'])) {
    $id=(int)$_POST['id']; $obs=trim($_POST['observacoes']??''); $estado=$_POST['acao']==='aprovar'?'aprovada':'rejeitada';
    $db->prepare("UPDATE matriculas SET estado=?,observacoes=?,decidido_por=?,decidido_em=NOW() WHERE id=?")
       ->execute([$estado,$obs,$_SESSION['user_id'],$id]);
    $sucesso='Pedido '.$estado.' com sucesso!';
}

$filtro = $_GET['filtro'] ?? 'pendente';
$stmt = $db->prepare("SELECT m.*,u.nome as aluno,u.email,c.nome as curso,d.nome as decidido_nome FROM matriculas m JOIN utilizadores u ON m.utilizador_id=u.id JOIN cursos c ON m.curso_id=c.id LEFT JOIN utilizadores d ON m.decidido_por=d.id WHERE m.estado=? ORDER BY m.criado_em DESC");
$stmt->execute([$filtro]);
$matriculas = $stmt->fetchAll();

$ver = null;
if (isset($_GET['ver'])) {
    $s=$db->prepare("SELECT m.*,u.nome as aluno,u.email,c.nome as curso,d.nome as decidido_nome FROM matriculas m JOIN utilizadores u ON m.utilizador_id=u.id JOIN cursos c ON m.curso_id=c.id LEFT JOIN utilizadores d ON m.decidido_por=d.id WHERE m.id=?");
    $s->execute([(int)$_GET['ver']]); $ver=$s->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head><meta charset="UTF-8"><title>Matrículas – Serviços Académicos</title>
<link rel="stylesheet" href="../assets/css/style.css"></head>
<body>
<div class="layout">
    <?php include 'sidebar.php'; ?>
    <main class="main">
        <div class="topbar"><div><h1>Pedidos de Matrícula</h1><p>Aprove ou rejeite os pedidos dos alunos.</p></div></div>

        <?php if ($sucesso): ?><div class="alert alert-success">✓ <?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

        <div style="display:flex;gap:8px;margin-bottom:20px;">
            <?php foreach (['pendente'=>'Pendentes','aprovada'=>'Aprovadas','rejeitada'=>'Rejeitadas'] as $k=>$v): ?>
            <a href="matriculas.php?filtro=<?= $k ?>" class="btn <?= $filtro===$k?'btn-primary':'btn-secondary' ?> btn-sm" style="<?= $filtro===$k?'width:auto':'' ?>"><?= $v ?></a>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <div class="card-header"><h3>Pedidos: <?= ucfirst($filtro) ?> (<?= count($matriculas) ?>)</h3></div>
            <?php if ($matriculas): ?>
            <table>
                <thead><tr><th>Aluno</th><th>Curso</th><th>Ano Letivo</th><th>Submetido</th><th>Estado</th><th>Ação</th></tr></thead>
                <tbody>
                <?php foreach ($matriculas as $m): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($m['aluno']) ?></strong><br><small style="color:var(--muted)"><?= htmlspecialchars($m['email']) ?></small></td>
                    <td><?= htmlspecialchars($m['curso']) ?></td>
                    <td><?= $m['ano_letivo'] ?></td>
                    <td style="color:var(--muted)"><?= date('d/m/Y',strtotime($m['criado_em'])) ?></td>
                    <td><span class="badge estado-<?= $m['estado'] ?>"><?= ucfirst($m['estado']) ?></span></td>
                    <td><a href="matriculas.php?ver=<?= $m['id'] ?>&filtro=<?= $filtro ?>" class="btn btn-secondary btn-xs">Ver</a></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="empty-state"><div class="empty-icon">📋</div><p>Nenhum pedido <?= $filtro ?>.</p></div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php if ($ver): ?>
<div class="modal-overlay open">
    <div class="modal">
        <div class="modal-header">
            <h3>Pedido de Matrícula</h3>
            <button class="modal-close" onclick="window.location='matriculas.php?filtro=<?= $filtro ?>'">✕</button>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px;">
            <div><small style="color:var(--muted)">Aluno</small><p><strong><?= htmlspecialchars($ver['aluno']) ?></strong></p></div>
            <div><small style="color:var(--muted)">E-mail</small><p><?= htmlspecialchars($ver['email']) ?></p></div>
            <div><small style="color:var(--muted)">Curso</small><p><?= htmlspecialchars($ver['curso']) ?></p></div>
            <div><small style="color:var(--muted)">Ano Letivo</small><p><?= $ver['ano_letivo'] ?></p></div>
            <div><small style="color:var(--muted)">Submetido em</small><p><?= date('d/m/Y H:i',strtotime($ver['criado_em'])) ?></p></div>
            <div><small style="color:var(--muted)">Estado</small><p><span class="badge estado-<?= $ver['estado'] ?>"><?= ucfirst($ver['estado']) ?></span></p></div>
        </div>
        <?php if ($ver['observacoes']): ?><div class="alert alert-info">💬 <?= htmlspecialchars($ver['observacoes']) ?></div><?php endif; ?>
        <?php if ($ver['decidido_nome']): ?><p style="font-size:0.82rem;color:var(--muted);margin-bottom:16px;">Decidido por <strong><?= htmlspecialchars($ver['decidido_nome']) ?></strong> em <?= date('d/m/Y H:i',strtotime($ver['decidido_em'])) ?></p><?php endif; ?>

        <?php if ($ver['estado']==='pendente'): ?>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $ver['id'] ?>">
            <div class="form-group"><label>Observações</label><textarea name="observacoes" placeholder="Registe a justificação da decisão..."></textarea></div>
            <div style="display:flex;gap:10px;">
                <button type="submit" name="acao" value="aprovar" class="btn btn-success" style="flex:1;justify-content:center">✓ Aprovar</button>
                <button type="submit" name="acao" value="rejeitar" class="btn btn-danger" style="flex:1;justify-content:center">✕ Rejeitar</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
</body>
</html>
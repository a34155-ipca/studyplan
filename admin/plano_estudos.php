<?php
require_once '../includes/config.php';
exigirPerfil('gestor');
$db = getDB();
$erro = $sucesso = '';

$curso_id = (int)($_GET['curso'] ?? 0);

if ($_SERVER['REQUEST_METHOD']==='POST' && $_POST['acao']==='adicionar') {
    $cid=(int)$_POST['curso_id']; $uid=(int)$_POST['uc_id']; $ano=(int)$_POST['ano']; $sem=(int)$_POST['semestre'];
    // Verificar duplicação
    $dup=$db->prepare("SELECT id FROM plano_estudos WHERE curso_id=? AND uc_id=? AND ano=? AND semestre=?");
    $dup->execute([$cid,$uid,$ano,$sem]);
    if ($dup->fetch()) $erro='Esta UC já existe neste curso/semestre!';
    else { $db->prepare("INSERT INTO plano_estudos (curso_id,uc_id,ano,semestre) VALUES (?,?,?,?)")->execute([$cid,$uid,$ano,$sem]); $sucesso='UC adicionada ao plano!'; $curso_id=$cid; }
}

if (isset($_GET['remover'])) {
    $db->prepare("DELETE FROM plano_estudos WHERE id=?")->execute([(int)$_GET['remover']]);
    $sucesso='UC removida do plano.';
}

$cursos = $db->query("SELECT * FROM cursos WHERE ativo=1 ORDER BY nome")->fetchAll();
$ucs_todas = $db->query("SELECT * FROM ucs ORDER BY nome")->fetchAll();

$plano = [];
if ($curso_id) {
    $stmt = $db->prepare("SELECT pe.*, u.nome as uc_nome, u.creditos, u.horas_semana FROM plano_estudos pe JOIN ucs u ON pe.uc_id=u.id WHERE pe.curso_id=? ORDER BY pe.ano, pe.semestre, u.nome");
    $stmt->execute([$curso_id]);
    $rows = $stmt->fetchAll();
    foreach ($rows as $r) $plano[$r['ano']][$r['semestre']][] = $r;
}

$curso_sel = null;
if ($curso_id) { $s=$db->prepare("SELECT * FROM cursos WHERE id=?"); $s->execute([$curso_id]); $curso_sel=$s->fetch(); }
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head><meta charset="UTF-8"><title>Plano de Estudos – Gestor</title>
<link rel="stylesheet" href="../assets/css/style.css"></head>
<body>
<div class="layout">
    <?php include 'sidebar.php'; ?>
    <main class="main">
        <div class="topbar">
            <div><h1>Plano de Estudos</h1><p>Associe UCs aos cursos por ano e semestre.</p></div>
            <?php if ($curso_id): ?>
            <button class="btn btn-primary" style="width:auto" onclick="document.getElementById('modal-add').classList.add('open')">+ Adicionar UC</button>
            <?php endif; ?>
        </div>

        <?php if ($erro): ?><div class="alert alert-error">⚠ <?= htmlspecialchars($erro) ?></div><?php endif; ?>
        <?php if ($sucesso): ?><div class="alert alert-success">✓ <?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

        <!-- Seletor de curso -->
        <div class="card" style="margin-bottom:20px;">
            <div class="card-body">
                <form method="GET" style="display:flex;gap:12px;align-items:flex-end;">
                    <div class="form-group" style="flex:1;margin:0">
                        <label>Selecionar Curso</label>
                        <select name="curso" onchange="this.form.submit()">
                            <option value="">-- Selecione um curso --</option>
                            <?php foreach ($cursos as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $curso_id==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($curso_id && $curso_sel): ?>
        <div class="alert alert-info">📚 A visualizar o plano de: <strong><?= htmlspecialchars($curso_sel['nome']) ?></strong> (<?= $curso_sel['duracao_anos'] ?> anos)</div>

        <?php if ($plano): ?>
            <?php foreach ($plano as $ano => $semestres): ?>
            <div class="card" style="margin-bottom:16px;">
                <div class="card-header"><h3>📅 <?= $ano ?>º Ano</h3></div>
                <?php foreach ($semestres as $sem => $ucs): ?>
                <div style="padding:0 22px 16px;">
                    <p style="font-size:0.78rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.8px;font-weight:600;margin:16px 0 10px;"><?= $sem ?>º Semestre</p>
                    <table>
                        <thead><tr><th>UC</th><th>Créditos</th><th>Horas/sem</th><th>Ação</th></tr></thead>
                        <tbody>
                        <?php foreach ($ucs as $u): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($u['uc_nome']) ?></strong></td>
                            <td><?= $u['creditos'] ?> ECTS</td>
                            <td><?= $u['horas_semana'] ?>h</td>
                            <td><a href="plano_estudos.php?curso=<?= $curso_id ?>&remover=<?= $u['id'] ?>" class="btn btn-danger btn-xs" onclick="return confirm('Remover do plano?')">Remover</a></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state"><div class="empty-icon">🗂</div><p>Nenhuma UC no plano. Adicione a primeira!</p></div>
        <?php endif; ?>
        <?php elseif (!$curso_id): ?>
            <div class="empty-state"><div class="empty-icon">👆</div><p>Selecione um curso para ver o plano de estudos.</p></div>
        <?php endif; ?>
    </main>
</div>

<div class="modal-overlay" id="modal-add">
    <div class="modal">
        <div class="modal-header"><h3>Adicionar UC ao Plano</h3><button class="modal-close" onclick="document.getElementById('modal-add').classList.remove('open')">✕</button></div>
        <form method="POST">
            <input type="hidden" name="acao" value="adicionar">
            <input type="hidden" name="curso_id" value="<?= $curso_id ?>">
            <div class="form-group">
                <label>Unidade Curricular</label>
                <select name="uc_id" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($ucs_todas as $u): ?><option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nome']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Ano</label>
                    <select name="ano" required>
                        <?php for($i=1;$i<=($curso_sel['duracao_anos']??5);$i++): ?><option value="<?= $i ?>"><?= $i ?>º Ano</option><?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Semestre</label>
                    <select name="semestre" required>
                        <option value="1">1º Semestre</option>
                        <option value="2">2º Semestre</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Adicionar ao Plano</button>
        </form>
    </div>
</div>
</body>
</html>
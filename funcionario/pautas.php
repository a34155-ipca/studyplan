<?php
require_once '../includes/config.php';
exigirPerfil('funcionario');
$db = getDB();
$erro = $sucesso = '';

// Criar pauta
if ($_SERVER['REQUEST_METHOD']==='POST' && $_POST['acao']==='criar') {
    $uc_id=(int)$_POST['uc_id']; $curso_id=(int)$_POST['curso_id']; $ano=$_POST['ano_letivo']; $epoca=$_POST['epoca'];
    $dup=$db->prepare("SELECT id FROM pautas WHERE uc_id=? AND curso_id=? AND ano_letivo=? AND epoca=?");
    $dup->execute([$uc_id,$curso_id,$ano,$epoca]);
    if ($dup->fetch()) { $erro='Esta pauta já existe!'; }
    else {
        $db->prepare("INSERT INTO pautas (uc_id,curso_id,ano_letivo,epoca,criado_por) VALUES (?,?,?,?,?)")
           ->execute([$uc_id,$curso_id,$ano,$epoca,$_SESSION['user_id']]);
        $pid=$db->lastInsertId();
        // Adicionar alunos elegíveis (com matrícula aprovada neste curso)
        $alunos=$db->prepare("SELECT utilizador_id FROM matriculas WHERE curso_id=? AND estado='aprovada'");
        $alunos->execute([$curso_id]);
        $insNota=$db->prepare("INSERT IGNORE INTO notas (pauta_id,utilizador_id) VALUES (?,?)");
        foreach ($alunos->fetchAll() as $a) $insNota->execute([$pid,$a['utilizador_id']]);
        $sucesso='Pauta criada e alunos adicionados automaticamente!';
    }
}

// Guardar notas
if ($_SERVER['REQUEST_METHOD']==='POST' && $_POST['acao']==='notas') {
    $pid=(int)$_POST['pauta_id'];
    foreach ($_POST['nota'] as $uid=>$nota) {
        $nota=trim($nota)===''?null:(float)$nota;
        $obs=trim($_POST['obs'][$uid]??'');
        $db->prepare("UPDATE notas SET nota=?,observacoes=?,editado_por=?,editado_em=NOW() WHERE pauta_id=? AND utilizador_id=?")
           ->execute([$nota,$obs,$_SESSION['user_id'],$pid,(int)$uid]);
    }
    $sucesso='Notas guardadas!';
}

$ucs    = $db->query("SELECT * FROM ucs ORDER BY nome")->fetchAll();
$cursos = $db->query("SELECT * FROM cursos WHERE ativo=1 ORDER BY nome")->fetchAll();
$pautas = $db->query("SELECT p.*,u.nome as uc_nome,c.nome as curso_nome,f.nome as criador FROM pautas p JOIN ucs u ON p.uc_id=u.id JOIN cursos c ON p.curso_id=c.id LEFT JOIN utilizadores f ON p.criado_por=f.id ORDER BY p.criado_em DESC")->fetchAll();

$pauta_sel = null; $notas_lista = [];
if (isset($_GET['pauta'])) {
    $s=$db->prepare("SELECT p.*,u.nome as uc_nome,c.nome as curso_nome FROM pautas p JOIN ucs u ON p.uc_id=u.id JOIN cursos c ON p.curso_id=c.id WHERE p.id=?");
    $s->execute([(int)$_GET['pauta']]); $pauta_sel=$s->fetch();
    if ($pauta_sel) {
        $ns=$db->prepare("SELECT n.*,ut.nome FROM notas n JOIN utilizadores ut ON n.utilizador_id=ut.id WHERE n.pauta_id=? ORDER BY ut.nome");
        $ns->execute([(int)$_GET['pauta']]); $notas_lista=$ns->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head><meta charset="UTF-8"><title>Pautas – Serviços Académicos</title>
<link rel="stylesheet" href="../assets/css/style.css"></head>
<body>
<div class="layout">
    <?php include 'sidebar.php'; ?>
    <main class="main">
        <div class="topbar">
            <div><h1>Pautas de Avaliação</h1><p>Crie pautas e lance notas por UC.</p></div>
            <button class="btn btn-primary" style="width:auto" onclick="document.getElementById('modal-pauta').classList.add('open')">+ Nova Pauta</button>
        </div>

        <?php if ($erro): ?><div class="alert alert-error">⚠ <?= htmlspecialchars($erro) ?></div><?php endif; ?>
        <?php if ($sucesso): ?><div class="alert alert-success">✓ <?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

        <?php if ($pauta_sel): ?>
        <!-- Vista de notas -->
        <div class="alert alert-info" style="margin-bottom:16px;">
            📝 Pauta: <strong><?= htmlspecialchars($pauta_sel['uc_nome']) ?></strong> — <?= htmlspecialchars($pauta_sel['curso_nome']) ?> | <?= $pauta_sel['ano_letivo'] ?> | Época <?= $pauta_sel['epoca'] ?>
            <a href="pautas.php" style="margin-left:12px;color:var(--accent)">← Voltar</a>
        </div>
        <div class="card">
            <div class="card-header"><h3>Lançamento de Notas (<?= count($notas_lista) ?> alunos)</h3></div>
            <?php if ($notas_lista): ?>
            <form method="POST">
                <input type="hidden" name="acao" value="notas">
                <input type="hidden" name="pauta_id" value="<?= $pauta_sel['id'] ?>">
                <table>
                    <thead><tr><th>Aluno</th><th>Nota (0–20)</th><th>Observações</th></tr></thead>
                    <tbody>
                    <?php foreach ($notas_lista as $n): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($n['nome']) ?></strong></td>
                        <td>
                            <input type="number" name="nota[<?= $n['utilizador_id'] ?>]" class="nota-input"
                                   value="<?= $n['nota']??'' ?>" min="0" max="20" step="0.1" placeholder="—">
                        </td>
                        <td><input type="text" name="obs[<?= $n['utilizador_id'] ?>]" style="width:100%;padding:5px 8px;border:1.5px solid var(--border);border-radius:8px;font-size:0.85rem" value="<?= htmlspecialchars($n['observacoes']??'') ?>" placeholder="Opcional"></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="padding:16px 22px;border-top:1px solid var(--border);">
                    <button type="submit" class="btn btn-primary" style="width:auto">💾 Guardar Notas</button>
                </div>
            </form>
            <?php else: ?>
                <div class="empty-state"><div class="empty-icon">👥</div><p>Nenhum aluno matriculado neste curso.</p></div>
            <?php endif; ?>
        </div>

        <?php else: ?>
        <!-- Lista de pautas -->
        <div class="card">
            <div class="card-header"><h3>Todas as Pautas (<?= count($pautas) ?>)</h3></div>
            <?php if ($pautas): ?>
            <table>
                <thead><tr><th>UC</th><th>Curso</th><th>Ano Letivo</th><th>Época</th><th>Criada por</th><th>Ação</th></tr></thead>
                <tbody>
                <?php foreach ($pautas as $p): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($p['uc_nome']) ?></strong></td>
                    <td><?= htmlspecialchars($p['curso_nome']) ?></td>
                    <td><?= $p['ano_letivo'] ?></td>
                    <td><span class="badge estado-<?= $p['epoca'] ?>"><?= $p['epoca'] ?></span></td>
                    <td style="color:var(--muted)"><?= htmlspecialchars($p['criador']??'—') ?></td>
                    <td><a href="pautas.php?pauta=<?= $p['id'] ?>" class="btn btn-secondary btn-xs">Lançar notas</a></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="empty-state"><div class="empty-icon">📝</div><p>Nenhuma pauta criada ainda.</p></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </main>
</div>

<div class="modal-overlay" id="modal-pauta">
    <div class="modal">
        <div class="modal-header"><h3>Nova Pauta</h3><button class="modal-close" onclick="document.getElementById('modal-pauta').classList.remove('open')">✕</button></div>
        <form method="POST">
            <input type="hidden" name="acao" value="criar">
            <div class="form-group"><label>Curso</label>
                <select name="curso_id" required><option value="">Selecione...</option>
                <?php foreach ($cursos as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Unidade Curricular</label>
                <select name="uc_id" required><option value="">Selecione...</option>
                <?php foreach ($ucs as $u): ?><option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nome']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Ano Letivo</label><input type="text" name="ano_letivo" placeholder="Ex: 2024/2025" required value="<?= date('Y').'/'.(date('Y')+1) ?>"></div>
                <div class="form-group"><label>Época</label>
                    <select name="epoca" required>
                        <option value="Normal">Normal</option>
                        <option value="Recurso">Recurso</option>
                        <option value="Especial">Especial</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Criar Pauta</button>
        </form>
    </div>
</div>
</body>
</html>
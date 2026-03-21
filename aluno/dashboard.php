<?php
require_once '../includes/config.php';
exigirPerfil('aluno');
$db = getDB();
$uid = $_SESSION['user_id'];

$ficha = $db->prepare("SELECT f.*,c.nome as curso FROM fichas_aluno f LEFT JOIN cursos c ON f.curso_id=c.id WHERE f.utilizador_id=?");
$ficha->execute([$uid]); $ficha=$ficha->fetch();

$matriculas = $db->prepare("SELECT m.*,c.nome as curso FROM matriculas m JOIN cursos c ON m.curso_id=c.id WHERE m.utilizador_id=? ORDER BY m.criado_em DESC LIMIT 3");
$matriculas->execute([$uid]); $matriculas=$matriculas->fetchAll();

$notas = $db->prepare("SELECT n.*,u.nome as uc,p.epoca,p.ano_letivo FROM notas n JOIN pautas p ON n.pauta_id=p.id JOIN ucs u ON p.uc_id=u.id WHERE n.utilizador_id=? AND n.nota IS NOT NULL ORDER BY p.criado_em DESC LIMIT 5");
$notas->execute([$uid]); $notas=$notas->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head><meta charset="UTF-8"><title>Início – StudyPlan</title>
<link rel="stylesheet" href="../assets/css/style.css"></head>
<body>
<div class="layout">
    <?php include 'sidebar.php'; ?>
    <main class="main">
        <div class="topbar">
            <div>
                <h1>Olá, <?= htmlspecialchars(explode(' ',$_SESSION['user_nome'])[0]) ?>!</h1>
                <p><?= date('l, d \d\e F \d\e Y') ?></p>
            </div>
        </div>

        <!-- Estado da ficha -->
        <?php if (!$ficha): ?>
        <div class="alert alert-warning">⚠ Ainda não preencheu a sua ficha de aluno. <a href="ficha.php" style="color:var(--warning);font-weight:600">Preencher agora →</a></div>
        <?php elseif ($ficha['estado']==='rascunho'): ?>
        <div class="alert alert-warning">📝 A sua ficha está em rascunho. <a href="ficha.php" style="color:var(--warning);font-weight:600">Submeter para validação →</a></div>
        <?php elseif ($ficha['estado']==='submetida'): ?>
        <div class="alert alert-info">⏳ A sua ficha está a aguardar validação pelo Gestor Pedagógico.</div>
        <?php elseif ($ficha['estado']==='aprovada'): ?>
        <div class="alert alert-success">✓ A sua ficha foi aprovada! Pode fazer pedidos de matrícula.</div>
        <?php elseif ($ficha['estado']==='rejeitada'): ?>
        <div class="alert alert-error">✕ A sua ficha foi rejeitada. <a href="ficha.php" style="color:var(--danger);font-weight:600">Ver observações →</a></div>
        <?php endif; ?>

        <!-- Matrículas recentes -->
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header"><h3>Os meus Pedidos de Matrícula</h3><a href="matriculas.php" class="btn btn-secondary btn-sm">Ver todos</a></div>
            <?php if ($matriculas): ?>
            <table>
                <thead><tr><th>Curso</th><th>Ano letivo</th><th>Estado</th></tr></thead>
                <tbody>
                <?php foreach ($matriculas as $m): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($m['curso']) ?></strong></td>
                    <td><?= $m['ano_letivo'] ?></td>
                    <td><span class="badge estado-<?= $m['estado'] ?>"><?= ucfirst($m['estado']) ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="empty-state" style="padding:24px"><div class="empty-icon">📄</div><p>Nenhum pedido ainda. <a href="cursos.php" style="color:var(--accent)">Ver cursos →</a></p></div>
            <?php endif; ?>
        </div>

        <!-- Notas recentes -->
        <div class="card">
            <div class="card-header"><h3>As minhas Notas</h3><a href="notas.php" class="btn btn-secondary btn-sm">Ver todas</a></div>
            <?php if ($notas): ?>
            <table>
                <thead><tr><th>UC</th><th>Ano letivo</th><th>Época</th><th>Nota</th></tr></thead>
                <tbody>
                <?php foreach ($notas as $n): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($n['uc']) ?></strong></td>
                    <td><?= $n['ano_letivo'] ?></td>
                    <td><span class="badge estado-<?= $n['epoca'] ?>"><?= $n['epoca'] ?></span></td>
                    <td><strong style="color:<?= $n['nota']>=10?'var(--success)':'var(--danger)' ?>"><?= number_format($n['nota'],1) ?></strong></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="empty-state" style="padding:24px"><div class="empty-icon">📊</div><p>Nenhuma nota disponível ainda.</p></div>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
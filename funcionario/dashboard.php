<?php
require_once '../includes/config.php';
exigirPerfil('funcionario');
$db = getDB();

$pendentes  = $db->query("SELECT COUNT(*) FROM matriculas WHERE estado='pendente'")->fetchColumn();
$aprovadas  = $db->query("SELECT COUNT(*) FROM matriculas WHERE estado='aprovada'")->fetchColumn();
$total_pautas = $db->query("SELECT COUNT(*) FROM pautas")->fetchColumn();

$ultimas = $db->query("SELECT m.*,u.nome as aluno,c.nome as curso FROM matriculas m JOIN utilizadores u ON m.utilizador_id=u.id JOIN cursos c ON m.curso_id=c.id ORDER BY m.criado_em DESC LIMIT 6")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head><meta charset="UTF-8"><title>Dashboard – Serviços Académicos</title>
<link rel="stylesheet" href="../assets/css/style.css"></head>
<body>
<div class="layout">
    <?php include 'sidebar.php'; ?>
    <main class="main">
        <div class="topbar"><div><h1>Serviços Académicos</h1><p><?= date('d \d\e F \d\e Y') ?></p></div></div>
        <div class="stats-row">
            <div class="stat-card"><div class="stat-top"><div class="stat-icon amber">⏳</div></div><h3><?= $pendentes ?></h3><p>Matrículas pendentes</p></div>
            <div class="stat-card"><div class="stat-top"><div class="stat-icon green">✅</div></div><h3><?= $aprovadas ?></h3><p>Matrículas aprovadas</p></div>
            <div class="stat-card"><div class="stat-top"><div class="stat-icon blue">📝</div></div><h3><?= $total_pautas ?></h3><p>Pautas criadas</p></div>
        </div>
        <div class="card">
            <div class="card-header"><h3>Últimos Pedidos</h3><a href="matriculas.php" class="btn btn-secondary btn-sm">Ver todos</a></div>
            <?php if ($ultimas): ?>
            <table>
                <thead><tr><th>Aluno</th><th>Curso</th><th>Ano letivo</th><th>Estado</th></tr></thead>
                <tbody>
                <?php foreach ($ultimas as $m): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($m['aluno']) ?></strong></td>
                    <td><?= htmlspecialchars($m['curso']) ?></td>
                    <td><?= $m['ano_letivo'] ?></td>
                    <td><span class="badge estado-<?= $m['estado'] ?>"><?= ucfirst($m['estado']) ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="empty-state"><div class="empty-icon">📭</div><p>Nenhum pedido ainda.</p></div>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
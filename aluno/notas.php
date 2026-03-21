<?php
require_once '../includes/config.php';
exigirPerfil('aluno');
$db  = getDB();
$uid = $_SESSION['user_id'];

$notas = $db->prepare("
    SELECT n.*,u.nome as uc,u.creditos,p.epoca,p.ano_letivo,c.nome as curso
    FROM notas n
    JOIN pautas p ON n.pauta_id=p.id
    JOIN ucs u ON p.uc_id=u.id
    JOIN cursos c ON p.curso_id=c.id
    WHERE n.utilizador_id=?
    ORDER BY p.ano_letivo DESC, u.nome
");
$notas->execute([$uid]);
$notas = $notas->fetchAll();

// Agrupar por ano letivo
$por_ano = [];
foreach ($notas as $n) $por_ano[$n['ano_letivo']][] = $n;

$media = count($notas) ? array_sum(array_column(array_filter($notas,fn($n)=>$n['nota']!==null),'nota')) / max(1,count(array_filter($notas,fn($n)=>$n['nota']!==null))) : 0;
$aprovadas = count(array_filter($notas, fn($n)=>$n['nota']!==null && $n['nota']>=10));
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>As minhas Notas – StudyPlan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
    <?php include 'sidebar.php'; ?>
    <main class="main">
        <div class="topbar"><div><h1>As minhas Notas</h1><p>Histórico de avaliações.</p></div></div>

        <div class="stats-row" style="margin-bottom:24px;">
            <div class="stat-card"><div class="stat-top"><div class="stat-icon blue">📊</div></div><h3><?= count($notas) ?></h3><p>Avaliações totais</p></div>
            <div class="stat-card"><div class="stat-top"><div class="stat-icon green">✅</div></div><h3><?= $aprovadas ?></h3><p>Aprovações</p></div>
            <div class="stat-card"><div class="stat-top"><div class="stat-icon amber">⭐</div></div><h3><?= $media>0?number_format($media,1):'—' ?></h3><p>Média geral</p></div>
        </div>

        <?php if ($por_ano): ?>
            <?php foreach ($por_ano as $ano => $lista): ?>
            <div class="card" style="margin-bottom:18px;">
                <div class="card-header"><h3>📅 Ano Letivo <?= $ano ?></h3></div>
                <table>
                    <thead><tr><th>Unidade Curricular</th><th>Curso</th><th>Época</th><th>Créditos</th><th>Nota</th><th>Resultado</th></tr></thead>
                    <tbody>
                    <?php foreach ($lista as $n): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($n['uc']) ?></strong></td>
                        <td style="color:var(--muted)"><?= htmlspecialchars($n['curso']) ?></td>
                        <td><span class="badge estado-<?= $n['epoca'] ?>"><?= $n['epoca'] ?></span></td>
                        <td><?= $n['creditos'] ?> ECTS</td>
                        <td>
                            <?php if ($n['nota']!==null): ?>
                                <strong style="font-size:1.05rem;color:<?= $n['nota']>=10?'var(--success)':'var(--danger)' ?>"><?= number_format($n['nota'],1) ?></strong>
                            <?php else: ?>
                                <span style="color:var(--muted)">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($n['nota']===null): ?>
                                <span class="badge badge-stone">Sem nota</span>
                            <?php elseif ($n['nota']>=10): ?>
                                <span class="badge badge-green">Aprovado</span>
                            <?php else: ?>
                                <span class="badge badge-red">Reprovado</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state"><div class="empty-icon">📊</div><p>Nenhuma nota disponível ainda.</p></div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
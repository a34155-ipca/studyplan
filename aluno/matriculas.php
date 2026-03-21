<?php
require_once '../includes/config.php';
exigirPerfil('aluno');
$db  = getDB();
$uid = $_SESSION['user_id'];

$matriculas = $db->prepare("
    SELECT m.*,c.nome as curso,c.duracao_anos,d.nome as decidido_nome
    FROM matriculas m
    JOIN cursos c ON m.curso_id=c.id
    LEFT JOIN utilizadores d ON m.decidido_por=d.id
    WHERE m.utilizador_id=?
    ORDER BY m.criado_em DESC
");
$matriculas->execute([$uid]);
$matriculas = $matriculas->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Os meus Pedidos – StudyPlan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
    <?php include 'sidebar.php'; ?>
    <main class="main">
        <div class="topbar">
            <div><h1>Os meus Pedidos</h1><p>Acompanhe o estado das suas matrículas.</p></div>
            <a href="cursos.php" class="btn btn-primary" style="width:auto">+ Novo pedido</a>
        </div>

        <?php if ($matriculas): ?>
        <div style="display:flex;flex-direction:column;gap:14px;">
        <?php foreach ($matriculas as $m): ?>
            <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow);display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap">
                <div>
                    <h3 style="font-size:1rem;font-weight:700;margin-bottom:4px"><?= htmlspecialchars($m['curso']) ?></h3>
                    <p style="color:var(--muted);font-size:0.83rem">Ano letivo: <?= $m['ano_letivo'] ?> · Submetido em <?= date('d/m/Y',strtotime($m['criado_em'])) ?></p>
                    <?php if ($m['observacoes']): ?>
                        <p style="margin-top:8px;font-size:0.83rem;color:var(--muted)">💬 <?= htmlspecialchars($m['observacoes']) ?></p>
                    <?php endif; ?>
                    <?php if ($m['decidido_nome']): ?>
                        <p style="font-size:0.78rem;color:var(--muted);margin-top:4px">Decidido por <?= htmlspecialchars($m['decidido_nome']) ?> em <?= date('d/m/Y H:i',strtotime($m['decidido_em'])) ?></p>
                    <?php endif; ?>
                </div>
                <span class="badge estado-<?= $m['estado'] ?>" style="font-size:0.82rem;padding:6px 14px"><?= ucfirst($m['estado']) ?></span>
            </div>
        <?php endforeach; ?>
        </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📄</div>
                <p>Ainda não tem pedidos de matrícula. <a href="cursos.php" style="color:var(--accent)">Ver cursos →</a></p>
            </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
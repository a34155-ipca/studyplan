<?php
require_once '../includes/config.php';
exigirPerfil('gestor');
$db = getDB();

$total_cursos   = $db->query("SELECT COUNT(*) FROM cursos WHERE ativo=1")->fetchColumn();
$total_ucs      = $db->query("SELECT COUNT(*) FROM ucs")->fetchColumn();
$total_alunos   = $db->query("SELECT COUNT(*) FROM utilizadores WHERE perfil='aluno'")->fetchColumn();
$fichas_pendentes = $db->query("SELECT COUNT(*) FROM fichas_aluno WHERE estado='submetida'")->fetchColumn();

$fichas_recentes = $db->query("
    SELECT f.*, u.nome, c.nome as curso
    FROM fichas_aluno f
    JOIN utilizadores u ON f.utilizador_id=u.id
    LEFT JOIN cursos c ON f.curso_id=c.id
    WHERE f.estado='submetida'
    ORDER BY f.atualizado_em DESC LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head><meta charset="UTF-8"><title>Dashboard – Gestor</title>
<link rel="stylesheet" href="../assets/css/style.css"></head>
<body>
<div class="layout">
    <?php include 'sidebar.php'; ?>
    <main class="main">
        <div class="topbar">
            <div>
                <h1>Dashboard</h1>
                <p>Visão geral da plataforma — <?= date('d \d\e F \d\e Y') ?></p>
            </div>
        </div>

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-top"><div class="stat-icon green">🎓</div></div>
                <h3><?= $total_cursos ?></h3><p>Cursos ativos</p>
            </div>
            <div class="stat-card">
                <div class="stat-top"><div class="stat-icon blue">📖</div></div>
                <h3><?= $total_ucs ?></h3><p>Unidades Curriculares</p>
            </div>
            <div class="stat-card">
                <div class="stat-top"><div class="stat-icon stone">👥</div></div>
                <h3><?= $total_alunos ?></h3><p>Alunos registados</p>
            </div>
            <div class="stat-card">
                <div class="stat-top"><div class="stat-icon amber">📋</div></div>
                <h3><?= $fichas_pendentes ?></h3><p>Fichas para validar</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Fichas Submetidas para Validação</h3>
                <a href="fichas.php" class="btn btn-secondary btn-sm">Ver todas</a>
            </div>
            <?php if ($fichas_recentes): ?>
            <table>
                <thead><tr><th>Aluno</th><th>Curso pretendido</th><th>Submetido em</th><th>Ação</th></tr></thead>
                <tbody>
                <?php foreach ($fichas_recentes as $f): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($f['nome']) ?></strong></td>
                    <td><?= htmlspecialchars($f['curso'] ?? '—') ?></td>
                    <td style="color:var(--muted)"><?= date('d/m/Y H:i', strtotime($f['atualizado_em'])) ?></td>
                    <td><a href="fichas.php?ver=<?= $f['id'] ?>" class="btn btn-secondary btn-xs">Analisar</a></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="empty-state"><div class="empty-icon">✅</div><p>Nenhuma ficha pendente de validação.</p></div>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
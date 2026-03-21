<?php $p = basename($_SERVER['PHP_SELF'], '.php'); $ini = strtoupper(substr($_SESSION['user_nome']??'G',0,1)); ?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-icon">📚</div>
        <span class="sidebar-logo-text">StudyPlan</span>
    </div>
    <div class="sidebar-perfil">⚙ Gestor Pedagógico</div>
    <nav>
        <div class="nav-group">
            <div class="nav-label">Geral</div>
            <a href="dashboard.php" class="nav-item <?= $p==='dashboard'?'active':'' ?>"><span class="nav-icon">📊</span> Dashboard</a>
        </div>
        <div class="nav-group">
            <div class="nav-label">Conteúdo</div>
            <a href="cursos.php" class="nav-item <?= $p==='cursos'?'active':'' ?>"><span class="nav-icon">🎓</span> Cursos</a>
            <a href="ucs.php" class="nav-item <?= $p==='ucs'?'active':'' ?>"><span class="nav-icon">📖</span> Unidades Curriculares</a>
            <a href="plano_estudos.php" class="nav-item <?= $p==='plano_estudos'?'active':'' ?>"><span class="nav-icon">🗂</span> Plano de Estudos</a>
        </div>
        <div class="nav-group">
            <div class="nav-label">Alunos</div>
            <a href="fichas.php" class="nav-item <?= $p==='fichas'?'active':'' ?>"><span class="nav-icon">📋</span> Fichas de Aluno</a>
        </div>
    </nav>
    <div class="sidebar-user">
        <div class="user-avatar"><?= $ini ?></div>
        <div class="user-info">
            <strong><?= htmlspecialchars($_SESSION['user_nome']??'') ?></strong>
            <small><a href="../includes/logout.php" style="color:var(--muted)">Terminar sessão</a></small>
        </div>
    </div>
</aside>
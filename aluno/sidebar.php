<?php $p=basename($_SERVER['PHP_SELF'],'.php'); $ini=strtoupper(substr($_SESSION['user_nome']??'A',0,1)); ?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-icon">📚</div>
        <span class="sidebar-logo-text">StudyPlan</span>
    </div>
    <div class="sidebar-perfil">🎓 Aluno</div>
    <nav>
        <div class="nav-group">
            <div class="nav-label">Geral</div>
            <a href="dashboard.php" class="nav-item <?= $p==='dashboard'?'active':'' ?>"><span class="nav-icon">🏠</span> Início</a>
        </div>
        <div class="nav-group">
            <div class="nav-label">O meu perfil</div>
            <a href="ficha.php" class="nav-item <?= $p==='ficha'?'active':'' ?>"><span class="nav-icon">📋</span> Ficha de Aluno</a>
        </div>
        <div class="nav-group">
            <div class="nav-label">Matrículas</div>
            <a href="cursos.php" class="nav-item <?= $p==='cursos'?'active':'' ?>"><span class="nav-icon">🎓</span> Cursos disponíveis</a>
            <a href="matriculas.php" class="nav-item <?= $p==='matriculas'?'active':'' ?>"><span class="nav-icon">📄</span> Os meus pedidos</a>
        </div>
        <div class="nav-group">
            <div class="nav-label">Avaliação</div>
            <a href="notas.php" class="nav-item <?= $p==='notas'?'active':'' ?>"><span class="nav-icon">📊</span> As minhas notas</a>
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
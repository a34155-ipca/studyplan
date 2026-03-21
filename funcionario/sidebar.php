<?php $p=basename($_SERVER['PHP_SELF'],'.php'); $ini=strtoupper(substr($_SESSION['user_nome']??'F',0,1)); ?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-icon">📚</div>
        <span class="sidebar-logo-text">StudyPlan</span>
    </div>
    <div class="sidebar-perfil">🏛 Serviços Académicos</div>
    <nav>
        <div class="nav-group">
            <div class="nav-label">Geral</div>
            <a href="dashboard.php" class="nav-item <?= $p==='dashboard'?'active':'' ?>"><span class="nav-icon">📊</span> Dashboard</a>
        </div>
        <div class="nav-group">
            <div class="nav-label">Matrículas</div>
            <a href="matriculas.php" class="nav-item <?= $p==='matriculas'?'active':'' ?>"><span class="nav-icon">📋</span> Pedidos de Matrícula</a>
        </div>
        <div class="nav-group">
            <div class="nav-label">Avaliação</div>
            <a href="pautas.php" class="nav-item <?= $p==='pautas'?'active':'' ?>"><span class="nav-icon">📝</span> Pautas</a>
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
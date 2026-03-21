<?php
require_once 'includes/config.php';

if (logado()) {
    $destinos = ['aluno'=>'aluno/dashboard.php','funcionario'=>'funcionario/dashboard.php','gestor'=>'admin/dashboard.php'];
    header("Location: " . BASE_URL . "/" . ($destinos[perfil()] ?? 'login.php'));
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (!$email || !$senha) {
        $erro = 'Preencha todos os campos.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id, nome, senha, perfil FROM utilizadores WHERE email = ? AND ativo = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($senha, $user['senha'])) {
            $_SESSION['user_id']     = $user['id'];
            $_SESSION['user_nome']   = $user['nome'];
            $_SESSION['user_perfil'] = $user['perfil'];
            $destinos = ['aluno'=>'aluno/dashboard.php','funcionario'=>'funcionario/dashboard.php','gestor'=>'admin/dashboard.php'];
            header("Location: " . BASE_URL . "/" . $destinos[$user['perfil']]);
            exit;
        } else {
            $erro = 'E-mail ou senha incorretos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar – StudyPlan</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-banner">
        <div class="banner-logo">
            <span>📚</span> StudyPlan
        </div>
        <div class="banner-content">
            <h1>Gestão Académica Simplificada</h1>
            <p>Plataforma integrada para gestão de cursos, matrículas, fichas de aluno e pautas de avaliação.</p>
        </div>
        <div class="banner-badges">
            <span class="banner-badge">🎓 Alunos</span>
            <span class="banner-badge">🏛 Serviços Académicos</span>
            <span class="banner-badge">⚙ Gestão Pedagógica</span>
        </div>
    </div>
    <div class="auth-form-area">
        <div class="auth-form-box">
            <h2>Bem-vindo</h2>
            <p class="subtitle">Introduza as suas credenciais para aceder.</p>

            <?php if ($erro): ?>
                <div class="alert alert-error">⚠ <?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>E-mail institucional</label>
                    <input type="email" name="email" placeholder="utilizador@ipca.pt" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
                </div>
                <div class="form-group">
                    <label>Palavra-passe</label>
                    <input type="password" name="senha" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary">Entrar →</button>
            </form>

            <div class="auth-link">
                Novo aluno? <a href="registro.php">Criar conta</a>
            </div>

            <div style="margin-top:28px; padding:16px; background:var(--bg); border-radius:var(--radius); font-size:0.78rem; color:var(--muted);">
                <strong style="display:block; margin-bottom:6px; color:var(--text);">Contas de demonstração:</strong>
                Gestor: gestor@studyplan.com<br>
                Funcionário: func@studyplan.com<br>
                Senha: <strong>gestor123</strong>
            </div>
        </div>
    </div>
</div>
</body>
</html>
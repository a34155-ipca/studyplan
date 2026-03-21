<?php
require_once 'includes/config.php';

if (logado()) { header("Location: " . BASE_URL . "/aluno/dashboard.php"); exit; }

$erro = $sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $senha    = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if (!$nome || !$email || !$senha) $erro = 'Preencha todos os campos.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erro = 'E-mail inválido.';
    elseif (strlen($senha) < 6) $erro = 'A senha deve ter no mínimo 6 caracteres.';
    elseif ($senha !== $confirmar) $erro = 'As senhas não coincidem.';
    else {
        $db = getDB();
        $existe = $db->prepare("SELECT id FROM utilizadores WHERE email = ?");
        $existe->execute([$email]);
        if ($existe->fetch()) {
            $erro = 'Este e-mail já está registado.';
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $db->prepare("INSERT INTO utilizadores (nome, email, senha, perfil) VALUES (?,?,?,'aluno')")
               ->execute([$nome, $email, $hash]);
            $sucesso = 'Conta criada com sucesso! Pode fazer login.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Criar Conta – StudyPlan</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-banner">
        <div class="banner-logo"><span>📚</span> StudyPlan</div>
        <div class="banner-content">
            <h1>Comece a sua jornada académica</h1>
            <p>Crie a sua conta de aluno para aceder a cursos, submeter a ficha e acompanhar o seu percurso.</p>
        </div>
        <div class="banner-badges">
            <span class="banner-badge">✓ Gratuito</span>
            <span class="banner-badge">✓ Seguro</span>
            <span class="banner-badge">✓ Fácil de usar</span>
        </div>
    </div>
    <div class="auth-form-area">
        <div class="auth-form-box">
            <h2>Criar conta</h2>
            <p class="subtitle">Registo exclusivo para alunos.</p>

            <?php if ($erro): ?><div class="alert alert-error">⚠ <?= htmlspecialchars($erro) ?></div><?php endif; ?>
            <?php if ($sucesso): ?><div class="alert alert-success">✓ <?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Nome completo</label>
                    <input type="text" name="nome" placeholder="O seu nome" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email" placeholder="seu@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Senha</label>
                        <input type="password" name="senha" placeholder="Mín. 6 caracteres" required>
                    </div>
                    <div class="form-group">
                        <label>Confirmar</label>
                        <input type="password" name="confirmar" placeholder="Repita a senha" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Criar conta →</button>
            </form>
            <div class="auth-link">Já tem conta? <a href="login.php">Entrar</a></div>
        </div>
    </div>
</div>
</body>
</html>
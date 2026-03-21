<?php
require_once '../includes/config.php';
exigirPerfil('aluno');
$db  = getDB();
$uid = $_SESSION['user_id'];
$erro = $sucesso = '';

// Verificar ficha aprovada
$ficha = $db->prepare("SELECT estado FROM fichas_aluno WHERE utilizador_id=?");
$ficha->execute([$uid]);
$ficha = $ficha->fetch();

// Fazer pedido de matrícula
if ($_SERVER['REQUEST_METHOD']==='POST' && $_POST['acao']==='matricular') {
    if (!$ficha || $ficha['estado']!=='aprovada') {
        $erro = 'Precisa de ter a ficha aprovada para se matricular.';
    } else {
        $cid = (int)$_POST['curso_id'];
        $ano = trim($_POST['ano_letivo'] ?? date('Y').'/'.(date('Y')+1));
        $dup = $db->prepare("SELECT id FROM matriculas WHERE utilizador_id=? AND curso_id=? AND ano_letivo=?");
        $dup->execute([$uid,$cid,$ano]);
        if ($dup->fetch()) {
            $erro = 'Já tem um pedido para este curso neste ano letivo.';
        } else {
            $db->prepare("INSERT INTO matriculas (utilizador_id,curso_id,ano_letivo) VALUES (?,?,?)")
               ->execute([$uid,$cid,$ano]);
            $sucesso = 'Pedido de matrícula submetido! Aguarde aprovação dos Serviços Académicos.';
        }
    }
}

$cursos = $db->query("
    SELECT c.*,
        COUNT(DISTINCT pe.uc_id) as total_ucs,
        m.estado as minha_matricula, m.id as matricula_id
    FROM cursos c
    LEFT JOIN plano_estudos pe ON pe.curso_id=c.id
    LEFT JOIN matriculas m ON m.curso_id=c.id AND m.utilizador_id=$uid
    WHERE c.ativo=1
    GROUP BY c.id ORDER BY c.nome
")->fetchAll();

$ano_letivo = date('Y').'/'.(date('Y')+1);
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Cursos – StudyPlan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
    <?php include 'sidebar.php'; ?>
    <main class="main">
        <div class="topbar">
            <div><h1>Cursos Disponíveis</h1><p>Explore e submeta pedidos de matrícula.</p></div>
        </div>

        <?php if ($erro): ?><div class="alert alert-error">⚠ <?= htmlspecialchars($erro) ?></div><?php endif; ?>
        <?php if ($sucesso): ?><div class="alert alert-success">✓ <?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

        <?php if (!$ficha || $ficha['estado']!=='aprovada'): ?>
        <div class="alert alert-warning">
            ⚠ Para se matricular precisa de ter a ficha aprovada.
            <a href="ficha.php" style="color:var(--warning);font-weight:600">
                <?= !$ficha ? 'Preencher ficha →' : 'Ver estado da ficha →' ?>
            </a>
        </div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:18px;">
        <?php foreach ($cursos as $c): ?>
            <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius);padding:22px;box-shadow:var(--shadow);display:flex;flex-direction:column;gap:12px;transition:transform 0.2s" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform=''">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <h3 style="font-size:1rem;font-weight:700;line-height:1.3"><?= htmlspecialchars($c['nome']) ?></h3>
                    <span style="background:var(--accent3);color:var(--accent);font-size:0.72rem;font-weight:600;padding:3px 10px;border-radius:50px;white-space:nowrap;margin-left:8px"><?= $c['duracao_anos'] ?> anos</span>
                </div>
                <?php if ($c['descricao']): ?>
                    <p style="color:var(--muted);font-size:0.84rem;line-height:1.5"><?= htmlspecialchars(substr($c['descricao'],0,100)) ?><?= strlen($c['descricao'])>100?'...':'' ?></p>
                <?php endif; ?>
                <p style="color:var(--muted);font-size:0.8rem;">📖 <?= $c['total_ucs'] ?> Unidades Curriculares</p>

                <?php if ($c['minha_matricula']==='aprovada'): ?>
                    <span class="badge badge-green">✓ Matriculado</span>
                <?php elseif ($c['minha_matricula']==='pendente'): ?>
                    <span class="badge badge-amber">⏳ Pedido pendente</span>
                <?php elseif ($c['minha_matricula']==='rejeitada'): ?>
                    <span class="badge badge-red">✕ Pedido rejeitado</span>
                    <form method="POST">
                        <input type="hidden" name="acao" value="matricular">
                        <input type="hidden" name="curso_id" value="<?= $c['id'] ?>">
                        <input type="hidden" name="ano_letivo" value="<?= $ano_letivo ?>">
                        <button type="submit" class="btn btn-secondary btn-sm" style="width:100%;justify-content:center">Novo pedido</button>
                    </form>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="acao" value="matricular">
                        <input type="hidden" name="curso_id" value="<?= $c['id'] ?>">
                        <input type="hidden" name="ano_letivo" value="<?= $ano_letivo ?>">
                        <button type="submit" class="btn btn-primary btn-sm" <?= (!$ficha||$ficha['estado']!=='aprovada')?'disabled':'' ?>>
                            Pedir Matrícula
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        </div>
    </main>
</div>
</body>
</html>
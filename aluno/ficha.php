<?php
require_once '../includes/config.php';
exigirPerfil('aluno');
$db  = getDB();
$uid = $_SESSION['user_id'];
$erro = $sucesso = '';

// Buscar ficha existente
$stmt = $db->prepare("SELECT f.*,c.nome as curso_nome FROM fichas_aluno f LEFT JOIN cursos c ON f.curso_id=c.id WHERE f.utilizador_id=?");
$stmt->execute([$uid]);
$ficha = $stmt->fetch();

// Guardar rascunho ou submeter
if ($_SERVER['REQUEST_METHOD']==='POST' && in_array($_POST['acao'],['guardar','submeter'])) {
    if ($ficha && in_array($ficha['estado'],['aprovada','submetida'])) {
        $erro = 'Não pode editar a ficha neste estado.';
    } else {
        $curso_id    = (int)($_POST['curso_id'] ?? 0) ?: null;
        $nasc        = $_POST['data_nascimento'] ?? '';
        $tel         = trim($_POST['telefone'] ?? '');
        $morada      = trim($_POST['morada'] ?? '');
        $nat         = trim($_POST['naturalidade'] ?? '');
        $nac         = trim($_POST['nacionalidade'] ?? 'Portuguesa');
        $estado_novo = $_POST['acao']==='submeter' ? 'submetida' : 'rascunho';

        // Validação mínima para submeter
        if ($estado_novo==='submetida' && (!$curso_id || !$nasc || !$tel || !$morada)) {
            $erro = 'Para submeter preencha todos os campos obrigatórios: curso, data de nascimento, telefone e morada.';
        } else {
            // Upload de foto
            $foto_nome = $ficha['foto'] ?? null;
            if (!empty($_FILES['foto']['name'])) {
                $up = uploadFoto($_FILES['foto']);
                if (isset($up['erro'])) { $erro = $up['erro']; }
                else { $foto_nome = $up['ficheiro']; }
            }

            if (!$erro) {
                if ($ficha) {
                    $db->prepare("UPDATE fichas_aluno SET curso_id=?,data_nascimento=?,telefone=?,morada=?,naturalidade=?,nacionalidade=?,foto=?,estado=? WHERE utilizador_id=?")
                       ->execute([$curso_id,$nasc,$tel,$morada,$nat,$nac,$foto_nome,$estado_novo,$uid]);
                } else {
                    $db->prepare("INSERT INTO fichas_aluno (utilizador_id,curso_id,data_nascimento,telefone,morada,naturalidade,nacionalidade,foto,estado) VALUES (?,?,?,?,?,?,?,?,?)")
                       ->execute([$uid,$curso_id,$nasc,$tel,$morada,$nat,$nac,$foto_nome,$estado_novo]);
                }
                $sucesso = $estado_novo==='submetida' ? 'Ficha submetida para validação!' : 'Rascunho guardado!';
                // Recarregar ficha
                $stmt->execute([$uid]); $ficha=$stmt->fetch();
            }
        }
    }
}

$cursos = $db->query("SELECT * FROM cursos WHERE ativo=1 ORDER BY nome")->fetchAll();
$pode_editar = !$ficha || in_array($ficha['estado'], ['rascunho','rejeitada']);
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Ficha de Aluno – StudyPlan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
    <?php include 'sidebar.php'; ?>
    <main class="main">
        <div class="topbar">
            <div>
                <h1>Ficha de Aluno</h1>
                <p>Preencha os seus dados e submeta para validação.</p>
            </div>
            <?php if ($ficha): ?>
            <span class="badge estado-<?= $ficha['estado'] ?>" style="font-size:0.85rem;padding:8px 16px;">
                <?= ucfirst($ficha['estado']) ?>
            </span>
            <?php endif; ?>
        </div>

        <?php if ($erro): ?><div class="alert alert-error">⚠ <?= htmlspecialchars($erro) ?></div><?php endif; ?>
        <?php if ($sucesso): ?><div class="alert alert-success">✓ <?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

        <?php if ($ficha && $ficha['estado']==='rejeitada' && $ficha['observacoes']): ?>
        <div class="alert alert-error">
            <div>
                <strong>Ficha rejeitada:</strong><br>
                <?= htmlspecialchars($ficha['observacoes']) ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($ficha && $ficha['estado']==='aprovada'): ?>
        <div class="alert alert-success">✓ Ficha aprovada! Pode agora fazer pedidos de matrícula.</div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header"><h3>Dados Pessoais</h3></div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">

                    <!-- Foto -->
                    <div style="text-align:center; margin-bottom:24px;">
                        <?php if ($ficha && $ficha['foto']): ?>
                            <img src="../assets/uploads/<?= htmlspecialchars($ficha['foto']) ?>" class="foto-preview" id="preview-img">
                        <?php else: ?>
                            <div class="foto-placeholder" id="preview-placeholder">👤</div>
                            <img src="" class="foto-preview" id="preview-img" style="display:none">
                        <?php endif; ?>
                        <?php if ($pode_editar): ?>
                        <label style="display:inline-block;margin-top:8px;cursor:pointer;">
                            <span class="btn btn-secondary btn-sm" style="width:auto">📷 Alterar foto</span>
                            <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="previewFoto(this)">
                        </label>
                        <p style="color:var(--muted);font-size:0.75rem;margin-top:6px;">JPG, PNG ou WEBP · Máx. 2MB</p>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Curso Pretendido <span style="color:var(--danger)">*</span></label>
                        <select name="curso_id" <?= !$pode_editar?'disabled':'' ?> required>
                            <option value="">Selecione um curso...</option>
                            <?php foreach ($cursos as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($ficha['curso_id']??0)==$c['id']?'selected':'' ?>>
                                    <?= htmlspecialchars($c['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Data de Nascimento <span style="color:var(--danger)">*</span></label>
                            <input type="date" name="data_nascimento" value="<?= htmlspecialchars($ficha['data_nascimento']??'') ?>" <?= !$pode_editar?'disabled':'' ?> required>
                        </div>
                        <div class="form-group">
                            <label>Telefone <span style="color:var(--danger)">*</span></label>
                            <input type="tel" name="telefone" placeholder="912 345 678" value="<?= htmlspecialchars($ficha['telefone']??'') ?>" <?= !$pode_editar?'disabled':'' ?> required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Morada <span style="color:var(--danger)">*</span></label>
                        <textarea name="morada" placeholder="Rua, número, código postal, cidade" <?= !$pode_editar?'disabled':'' ?>><?= htmlspecialchars($ficha['morada']??'') ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Naturalidade</label>
                            <input type="text" name="naturalidade" placeholder="Ex: Braga" value="<?= htmlspecialchars($ficha['naturalidade']??'') ?>" <?= !$pode_editar?'disabled':'' ?>>
                        </div>
                        <div class="form-group">
                            <label>Nacionalidade</label>
                            <input type="text" name="nacionalidade" placeholder="Ex: Portuguesa" value="<?= htmlspecialchars($ficha['nacionalidade']??'Portuguesa') ?>" <?= !$pode_editar?'disabled':'' ?>>
                        </div>
                    </div>

                    <?php if ($pode_editar): ?>
                    <div style="display:flex;gap:12px;margin-top:8px;">
                        <button type="submit" name="acao" value="guardar" class="btn btn-secondary" style="flex:1;justify-content:center">
                            💾 Guardar Rascunho
                        </button>
                        <button type="submit" name="acao" value="submeter" class="btn btn-primary" style="flex:1" onclick="return confirm('Submeter a ficha para validação? Não poderá editar depois.')">
                            📤 Submeter para Validação
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info" style="margin-top:8px;">
                        ℹ A ficha está em estado <strong><?= $ficha['estado'] ?></strong> e não pode ser editada.
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
function previewFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.getElementById('preview-img');
            const ph  = document.getElementById('preview-placeholder');
            img.src = e.target.result;
            img.style.display = 'block';
            if (ph) ph.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>
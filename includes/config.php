<?php
session_start();

define('BASE_URL', '/hello-word/studyplan');
define('BASE_PATH', __DIR__ . '/..');
define('UPLOAD_PATH', BASE_PATH . '/assets/uploads/');
define('UPLOAD_URL', BASE_URL . '/assets/uploads/');

// Conexão PDO
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $host = 'localhost';
        $db   = 'studyplan';
        $user = 'root';
        $pass = '';
        $port = 8485; 
        try {
            $pdo = new PDO(
                "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
                $user, $pass,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            die("Erro na conexão: " . $e->getMessage());
        }
    }
    return $pdo;
}

// Funções de autenticação
function logado() {
    return isset($_SESSION['user_id']);
}

function perfil() {
    return $_SESSION['user_perfil'] ?? null;
}

function exigirLogin() {
    if (!logado()) {
        header("Location: " . BASE_URL . "/login.php");
        exit;
    }
}

function exigirPerfil(string|array $perfis) {
    exigirLogin();
    $perfis = (array) $perfis;
    if (!in_array(perfil(), $perfis)) {
        header("Location: " . BASE_URL . "/login.php");
        exit;
    }
}

// Upload de foto
function uploadFoto($file, $pasta = UPLOAD_PATH) {
    $permitidos = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize    = 2 * 1024 * 1024; // 2MB

    if ($file['error'] !== UPLOAD_ERR_OK)       return ['erro' => 'Erro no upload.'];
    if (!in_array($file['type'], $permitidos))   return ['erro' => 'Formato inválido. Use JPG, PNG ou WEBP.'];
    if ($file['size'] > $maxSize)                return ['erro' => 'Ficheiro demasiado grande. Máximo 2MB.'];

    $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nome = uniqid('foto_', true) . '.' . $ext;

    if (!is_dir($pasta)) mkdir($pasta, 0755, true);
    if (!move_uploaded_file($file['tmp_name'], $pasta . $nome))
        return ['erro' => 'Erro ao guardar ficheiro.'];

    return ['ficheiro' => $nome];
}
?>
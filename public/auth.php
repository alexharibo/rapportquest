<?php
declare(strict_types=1);
/**
 * Unified auth handler — handles both login and register POST.
 * Redirects back to the referring page on success/failure.
 */
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

session_start();

$action   = $_POST['action']   ?? '';
$redirect = $_POST['redirect'] ?? 'index.php';

// Whitelist redirect to prevent open redirect
if (!preg_match('/^[a-zA-Z0-9_\-\.]+\.php(\?[^<>"\']*)?$/', $redirect)) {
    $redirect = 'index.php';
}

function redirectBack(string $redirect, string $tab, string $error = ''): never {
    if ($error) {
        $_SESSION['auth_error'] = $error;
        $_SESSION['auth_tab']   = $tab;
    }
    header('Location: ' . $redirect);
    exit;
}

if ($action === 'login') {
    $csrf     = $_POST['csrf']     ?? '';
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    if (!hash_equals($_SESSION['csrf_auth'] ?? '', $csrf)) {
        redirectBack($redirect, 'login', 'Ugyldig anmodning. Prøv igen.');
    }
    if (!$email || !$password) {
        redirectBack($redirect, 'login', 'Udfyld e-mail og adgangskode.');
    }

    try {
        $pdo  = getDbConnection();
        $stmt = $pdo->prepare('SELECT id, username, password_hash, avatar FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['avatar']   = $user['avatar'];
            header('Location: ' . $redirect);
            exit;
        }
        redirectBack($redirect, 'login', 'Forkert e-mail eller adgangskode.');
    } catch (Exception $e) {
        redirectBack($redirect, 'login', 'Der opstod en fejl. Prøv igen.');
    }
}

if ($action === 'register') {
    $csrf      = $_POST['csrf']      ?? '';
    $username  = trim($_POST['username']  ?? '');
    $email     = trim($_POST['email']     ?? '');
    $password  = $_POST['password']  ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (!hash_equals($_SESSION['csrf_auth'] ?? '', $csrf)) {
        redirectBack($redirect, 'register', 'Ugyldig anmodning. Prøv igen.');
    }
    if (!$username || !$email || !$password) {
        redirectBack($redirect, 'register', 'Udfyld alle felter.');
    }
    if (strlen($username) < 2 || strlen($username) > 40) {
        redirectBack($redirect, 'register', 'Brugernavn skal være 2–40 tegn.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectBack($redirect, 'register', 'Ugyldig e-mailadresse.');
    }
    if (strlen($password) < 8) {
        redirectBack($redirect, 'register', 'Adgangskoden skal være mindst 8 tegn.');
    }
    if ($password !== $password2) {
        redirectBack($redirect, 'register', 'Adgangskoderne matcher ikke.');
    }

    try {
        $pdo   = getDbConnection();
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $check->execute([$email]);
        if ($check->fetch()) {
            redirectBack($redirect, 'register', 'E-mailen er allerede i brug.');
        }
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $ins  = $pdo->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
        $ins->execute([$username, $email, $hash]);
        session_regenerate_id(true);
        $_SESSION['user_id']  = (int)$pdo->lastInsertId();
        $_SESSION['username'] = $username;
        $_SESSION['avatar']   = '';
        header('Location: ' . $redirect);
        exit;
    } catch (Exception $e) {
        redirectBack($redirect, 'register', 'Der opstod en fejl. Prøv igen.');
    }
}

header('Location: index.php');
exit;

<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

session_start();

if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$AVATAR_BASE = 'https://raw.githubusercontent.com/alexharibo/rapportquest/main/Visuel%20guides/';
$LOGO_URL    = $AVATAR_BASE . 'ExamQuest%20logo%20med%20futuristisk%20design.png';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username']  ?? '');
    $email     = trim($_POST['email']     ?? '');
    $password  =       $_POST['password']  ?? '';
    $password2 =       $_POST['password2'] ?? '';
    $csrf      =       $_POST['csrf']      ?? '';

    if (!hash_equals($_SESSION['csrf_register'] ?? '', $csrf)) {
        $error = 'Ugyldig anmodning. Prøv igen.';
    } elseif (!$username || !$email || !$password) {
        $error = 'Udfyld alle felter.';
    } elseif (strlen($username) < 2 || strlen($username) > 40) {
        $error = 'Brugernavn skal være 2–40 tegn.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ugyldig e-mailadresse.';
    } elseif (strlen($password) < 8) {
        $error = 'Adgangskoden skal være mindst 8 tegn.';
    } elseif ($password !== $password2) {
        $error = 'Adgangskoderne matcher ikke.';
    } else {
        try {
            $pdo = getDbConnection();

            $check = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $check->execute([$email]);
            if ($check->fetch()) {
                $error = 'E-mailen er allerede i brug.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $ins  = $pdo->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
                $ins->execute([$username, $email, $hash]);
                $userId = (int)$pdo->lastInsertId();

                session_regenerate_id(true);
                $_SESSION['user_id']  = $userId;
                $_SESSION['username'] = $username;
                $_SESSION['avatar']   = '';
                header('Location: index.php');
                exit;
            }
        } catch (Exception $e) {
            $error = 'Der opstod en fejl. Prøv igen.';
        }
    }
}

$_SESSION['csrf_register'] = bin2hex(random_bytes(16));
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opret konto — ExamQuest</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { display:flex; align-items:center; justify-content:center; min-height:100vh; padding:1rem; }
        .auth-wrap { width:100%; max-width:420px; }
        .auth-logo { text-align:center; margin-bottom:2rem; }
        .auth-logo img { height:56px; width:auto; }
        .auth-card {
            background:var(--surface); border-radius:16px;
            border:1px solid var(--border);
            box-shadow:0 0 40px rgba(124,58,237,.2);
            padding:2.25rem 2rem;
        }
        .auth-card h1 { font-size:1.5rem; font-weight:900; color:#fff; margin-bottom:.35rem; }
        .auth-card p.sub { color:var(--text-muted); font-size:.9rem; margin-bottom:1.75rem; }
        .form-group { margin-bottom:1.1rem; }
        .form-group label { display:block; font-size:.82rem; font-weight:700; color:var(--text-muted); margin-bottom:.4rem; letter-spacing:.04em; text-transform:uppercase; }
        .form-group input {
            width:100%; padding:.75rem 1rem;
            background:var(--surface-2); border:1px solid var(--border);
            border-radius:8px; color:var(--text); font-size:.95rem; font-family:inherit;
            transition:border-color .15s, box-shadow .15s;
        }
        .form-group input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(124,58,237,.18); }
        .form-hint { font-size:.75rem; color:var(--text-muted); margin-top:.3rem; }
        .auth-error { background:rgba(239,68,68,.1); border:1px solid rgba(239,68,68,.35); color:#fca5a5; border-radius:8px; padding:.7rem 1rem; font-size:.88rem; margin-bottom:1.1rem; }
        .btn-auth { width:100%; padding:.85rem; margin-top:.5rem; }
        .auth-footer { text-align:center; margin-top:1.5rem; font-size:.875rem; color:var(--text-muted); }
        .auth-footer a { color:var(--primary); text-decoration:none; font-weight:600; }
        .auth-footer a:hover { text-decoration:underline; }
        .divider { display:flex; align-items:center; gap:.75rem; margin:1.25rem 0; }
        .divider span { flex:1; height:1px; background:var(--border); }
        .divider p { font-size:.78rem; color:var(--text-muted); white-space:nowrap; }
        .perks { display:flex; flex-direction:column; gap:.5rem; margin-bottom:1.75rem; }
        .perk { display:flex; align-items:center; gap:.6rem; font-size:.85rem; color:var(--text-muted); }
        .perk span { font-size:1rem; }
    </style>
</head>
<body>
<div class="auth-wrap">
    <div class="auth-logo">
        <a href="index.php"><img src="<?= $LOGO_URL ?>" alt="ExamQuest"></a>
    </div>

    <div class="auth-card">
        <h1>Opret gratis konto 🚀</h1>
        <p class="sub">Gem din fremgang og optjen badges på tværs af enheder.</p>

        <div class="perks">
            <div class="perk"><span>⚡</span> Gem XP og level på din konto</div>
            <div class="perk"><span>🏆</span> Optjen og vis dine badges</div>
            <div class="perk"><span>🔥</span> Hold styr på din daglige streak</div>
        </div>

        <?php if ($error): ?>
        <div class="auth-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_register'] ?>">

            <div class="form-group">
                <label for="username">Brugernavn</label>
                <input type="text" id="username" name="username" placeholder="DitNavn"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       maxlength="40" required autofocus>
            </div>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="din@email.dk"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Adgangskode</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
                <p class="form-hint">Mindst 8 tegn</p>
            </div>
            <div class="form-group">
                <label for="password2">Gentag adgangskode</label>
                <input type="password" id="password2" name="password2" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-submit btn-auth">Opret konto</button>
        </form>

        <div class="divider"><span></span><p>Har du allerede en konto?</p><span></span></div>

        <div class="auth-footer">
            <a href="login.php">Log ind →</a>
        </div>
    </div>

    <p style="text-align:center;margin-top:1.25rem;font-size:.8rem;color:var(--text-muted);">
        <a href="index.php" style="color:var(--text-muted);text-decoration:none;">← Tilbage til forsiden</a>
    </p>
</div>
</body>
</html>

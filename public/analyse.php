<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

$reportId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($reportId <= 0) {
    header('Location: index.php');
    exit;
}

try {
    $pdo  = getDbConnection();
    $stmt = $pdo->prepare('SELECT id, original_name, file_size, status, created_at FROM reports WHERE id = :id');
    $stmt->execute([':id' => $reportId]);
    $report = $stmt->fetch();
} catch (PDOException $e) {
    $report = null;
}

if (!$report) {
    header('Location: index.php');
    exit;
}

$fileSizeKB = round($report['file_size'] / 1024, 1);
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RapportQuest — Rapport uploadet</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header class="site-header">
            <div class="logo">
                <span class="logo-icon">📜</span>
                <h1>RapportQuest</h1>
            </div>
            <p class="tagline">Gør din rapport til et læringsforløb</p>
        </header>

        <main>
            <div class="upload-card" style="text-align:center;">
                <span style="font-size:3rem;display:block;margin-bottom:1rem;">✅</span>
                <h2>Rapport uploadet!</h2>
                <p class="upload-description" style="margin-top:.5rem;">
                    <strong><?= htmlspecialchars($report['original_name'], ENT_QUOTES) ?></strong>
                    (<?= $fileSizeKB ?> KB) er gemt og klar til analyse.
                </p>
                <p style="color:var(--text-muted);margin-top:1rem;font-size:.9rem;">
                    Rapport-ID: #<?= $report['id'] ?> &middot; Oprettet: <?= htmlspecialchars($report['created_at']) ?>
                </p>
                <div style="margin-top:2rem;">
                    <a href="index.php" class="btn-submit" style="display:inline-block;text-decoration:none;padding:.75rem 2rem;width:auto;">
                        Upload en ny rapport
                    </a>
                </div>
            </div>
        </main>

        <footer class="site-footer">
            <p>&copy; 2026 RapportQuest</p>
        </footer>
    </div>
</body>
</html>

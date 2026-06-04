<?php
/**
 * Shared top navigation bar.
 * Include after session_start() and DB connection.
 * Variables expected: $reportId (int, optional)
 */
$navReportId = isset($reportId) ? (int) $reportId : 0;
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');

$_navAvatar = $_SESSION['avatar'] ?? '';
$_navAvatarUrl = '';
if ($_navAvatar) {
    if (preg_match('/^avatar-(\d+)$/', $_navAvatar, $m) && (int)$m[1] >= 1 && (int)$m[1] <= 10) {
        $_navAvatarUrl = 'https://raw.githubusercontent.com/alexharibo/rapportquest/main/Visuel%20guides/Avatar%20' . $m[1] . '.png';
    }
}
$_navLoggedIn = !empty($_SESSION['user_id']);
$_navUsername = htmlspecialchars($_SESSION['username'] ?? '');
?>
<nav class="top-nav" aria-label="Hovednavigation">
    <a href="index.php" class="nav-brand">
        <img src="https://raw.githubusercontent.com/alexharibo/rapportquest/main/Visuel%20guides/ExamQuest%20logo%20med%20futuristisk%20design.png" alt="ExamQuest" style="height:56px;width:auto;vertical-align:middle;padding:10px 0;margin:10px 0;">
    </a>
    <div class="nav-links">
        <?php if ($navReportId > 0): ?>
        <a href="quiz.php?id=<?= $navReportId ?>"       class="nav-link <?= $currentPage === 'quiz.php'         ? 'active' : '' ?>">🎯 Quiz</a>
        <a href="cloze.php?id=<?= $navReportId ?>"      class="nav-link <?= $currentPage === 'cloze.php'        ? 'active' : '' ?>">✏️ Cloze</a>
        <a href="boss.php?id=<?= $navReportId ?>"       class="nav-link <?= $currentPage === 'boss.php'         ? 'active' : '' ?>">⚔️ Boss</a>
        <a href="dashboard.php?id=<?= $navReportId ?>"  class="nav-link <?= $currentPage === 'dashboard.php'    ? 'active' : '' ?>">📊 Dashboard</a>
        <?php else: ?>
        <a href="dashboard.php"  class="nav-link <?= $currentPage === 'dashboard.php'  ? 'active' : '' ?>">📊 Dashboard</a>
        <?php endif; ?>
        <a href="gamification.php" class="nav-link <?= $currentPage === 'gamification.php' ? 'active' : '' ?>">🏆</a>
        <a href="profile.php" class="nav-link <?= $currentPage === 'profile.php' ? 'active' : '' ?>" style="display:inline-flex;align-items:center;gap:.4rem;">
            <?php if ($_navAvatarUrl): ?>
            <img id="navAvatarImg" src="<?= $_navAvatarUrl ?>" alt="Avatar" style="width:26px;height:26px;border-radius:50%;object-fit:cover;border:2px solid var(--primary);">
            <?php else: ?>🧑‍💻<?php endif; ?>
            <?= $_navLoggedIn ? $_navUsername : 'Profil' ?>
        </a>
        <?php if ($_navLoggedIn): ?>
        <a href="logout.php" class="nav-link" style="color:var(--text-muted);">Log ud</a>
        <?php else: ?>
        <button class="nav-link" onclick="openAuthModal('login')"    style="cursor:pointer;background:none;border:none;font-family:inherit;font-size:.875rem;color:var(--text-muted);">Log ind</button>
        <button class="nav-link" onclick="openAuthModal('register')" style="cursor:pointer;background:var(--primary);color:#fff;border:none;font-family:inherit;font-size:.875rem;font-weight:700;">Opret konto</button>
        <?php endif; ?>
    </div>
</nav>
<?php require_once __DIR__ . '/auth_modal.php'; ?>

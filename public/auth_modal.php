<?php
/**
 * Auth modal — include once per page, after session_start().
 * Opens via JS: openAuthModal('login') or openAuthModal('register')
 */
$_authError   = $_SESSION['auth_error'] ?? '';
$_authTab     = $_SESSION['auth_tab']   ?? 'login';
$_authRedirect = basename($_SERVER['PHP_SELF'] ?? 'index.php');
if (!empty($_SERVER['QUERY_STRING'])) {
    $_authRedirect .= '?' . $_SERVER['QUERY_STRING'];
}

// Refresh CSRF token each page load
$_SESSION['csrf_auth'] = bin2hex(random_bytes(16));
unset($_SESSION['auth_error'], $_SESSION['auth_tab']);
?>
<!-- ══════ AUTH MODAL ══════ -->
<div class="auth-overlay" id="authOverlay" role="dialog" aria-modal="true" aria-label="Login / Opret konto">
    <div class="auth-modal">
        <button class="auth-modal-close" id="authModalClose" aria-label="Luk">✕</button>

        <!-- Tabs -->
        <div class="auth-tabs">
            <button class="auth-tab <?= $_authTab === 'login'    ? 'active' : '' ?>" data-tab="login">Log ind</button>
            <button class="auth-tab <?= $_authTab === 'register' ? 'active' : '' ?>" data-tab="register">Opret konto</button>
        </div>

        <?php if ($_authError): ?>
        <div class="auth-modal-error">⚠️ <?= htmlspecialchars($_authError) ?></div>
        <?php endif; ?>

        <!-- LOGIN PANEL -->
        <div class="auth-panel" id="panelLogin" <?= $_authTab !== 'login' ? 'style="display:none"' : '' ?>>
            <p class="auth-panel-sub">Velkommen tilbage 👋</p>
            <form method="POST" action="auth.php" novalidate>
                <input type="hidden" name="action"   value="login">
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($_authRedirect) ?>">
                <input type="hidden" name="csrf"     value="<?= $_SESSION['csrf_auth'] ?>">
                <div class="auth-field">
                    <label for="loginEmail">E-mail</label>
                    <input type="email" id="loginEmail" name="email" placeholder="din@email.dk" required autofocus>
                </div>
                <div class="auth-field">
                    <label for="loginPassword">Adgangskode</label>
                    <input type="password" id="loginPassword" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-submit auth-submit">Log ind</button>
            </form>
            <p class="auth-switch">Ingen konto? <button class="auth-switch-btn" data-tab="register">Opret gratis →</button></p>
        </div>

        <!-- REGISTER PANEL -->
        <div class="auth-panel" id="panelRegister" <?= $_authTab !== 'register' ? 'style="display:none"' : '' ?>>
            <p class="auth-panel-sub">Opret din gratis konto 🚀</p>
            <form method="POST" action="auth.php" novalidate>
                <input type="hidden" name="action"   value="register">
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($_authRedirect) ?>">
                <input type="hidden" name="csrf"     value="<?= $_SESSION['csrf_auth'] ?>">
                <div class="auth-field">
                    <label for="regUsername">Brugernavn</label>
                    <input type="text" id="regUsername" name="username" placeholder="DitNavn" maxlength="40" required>
                </div>
                <div class="auth-field">
                    <label for="regEmail">E-mail</label>
                    <input type="email" id="regEmail" name="email" placeholder="din@email.dk" required>
                </div>
                <div class="auth-field">
                    <label for="regPassword">Adgangskode</label>
                    <input type="password" id="regPassword" name="password" placeholder="•••••••• (min. 8 tegn)" required>
                </div>
                <div class="auth-field">
                    <label for="regPassword2">Gentag adgangskode</label>
                    <input type="password" id="regPassword2" name="password2" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-submit auth-submit">Opret konto</button>
            </form>
            <p class="auth-switch">Har du en konto? <button class="auth-switch-btn" data-tab="login">Log ind →</button></p>
        </div>
    </div>
</div>

<style>
.auth-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.75); z-index: 1000;
    align-items: center; justify-content: center;
    padding: 1rem;
}
.auth-overlay.open { display: flex; }
.auth-modal {
    background: var(--surface); border-radius: 16px;
    border: 1px solid var(--border-bright);
    box-shadow: 0 0 60px rgba(124,58,237,.35);
    padding: 2rem; width: 100%; max-width: 420px;
    position: relative;
    animation: authSlideIn .2s ease;
}
@keyframes authSlideIn {
    from { opacity: 0; transform: translateY(-16px) scale(.97); }
    to   { opacity: 1; transform: none; }
}
.auth-modal-close {
    position: absolute; top: 1rem; right: 1rem;
    background: none; border: none;
    color: var(--text-muted); font-size: 1.3rem; cursor: pointer;
    line-height: 1; transition: color .15s;
}
.auth-modal-close:hover { color: #fff; }

.auth-tabs {
    display: flex; gap: .25rem;
    background: var(--surface-2); border-radius: 8px;
    padding: .25rem; margin-bottom: 1.5rem;
}
.auth-tab {
    flex: 1; padding: .55rem; border: none; border-radius: 6px;
    background: none; color: var(--text-muted);
    font-size: .88rem; font-weight: 700; cursor: pointer;
    transition: all .15s; font-family: inherit;
}
.auth-tab.active {
    background: var(--primary); color: #fff;
    box-shadow: 0 0 12px rgba(124,58,237,.4);
}

.auth-modal-error {
    background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.35);
    color: #fca5a5; border-radius: 8px;
    padding: .65rem 1rem; font-size: .875rem; margin-bottom: 1.1rem;
}

.auth-panel-sub { color: var(--text-muted); font-size: .9rem; margin-bottom: 1.25rem; }

.auth-field { margin-bottom: .9rem; }
.auth-field label {
    display: block; font-size: .78rem; font-weight: 700;
    color: var(--text-muted); margin-bottom: .35rem;
    text-transform: uppercase; letter-spacing: .05em;
}
.auth-field input {
    width: 100%; padding: .7rem .95rem;
    background: var(--surface-2); border: 1px solid var(--border);
    border-radius: 8px; color: var(--text);
    font-size: .93rem; font-family: inherit;
    transition: border-color .15s, box-shadow .15s;
}
.auth-field input:focus {
    outline: none; border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(124,58,237,.18);
}

.auth-submit { width: 100%; margin-top: .25rem; }

.auth-switch {
    text-align: center; margin-top: 1.1rem;
    font-size: .85rem; color: var(--text-muted);
}
.auth-switch-btn {
    background: none; border: none; color: var(--primary);
    font-weight: 700; cursor: pointer; font-size: .85rem;
    font-family: inherit; text-decoration: underline; padding: 0;
}
.auth-switch-btn:hover { color: #a78bfa; }
</style>

<script>
(function () {
    const overlay  = document.getElementById('authOverlay');
    const tabs     = document.querySelectorAll('.auth-tab');
    const panels   = { login: document.getElementById('panelLogin'), register: document.getElementById('panelRegister') };

    window.openAuthModal = function (tab = 'login') {
        switchTab(tab);
        overlay.classList.add('open');
        const first = panels[tab]?.querySelector('input');
        if (first) setTimeout(() => first.focus(), 80);
    };

    function switchTab(tab) {
        tabs.forEach(t => t.classList.toggle('active', t.dataset.tab === tab));
        Object.entries(panels).forEach(([k, el]) => { if (el) el.style.display = k === tab ? '' : 'none'; });
    }

    tabs.forEach(t => t.addEventListener('click', () => switchTab(t.dataset.tab)));
    document.querySelectorAll('.auth-switch-btn').forEach(b => b.addEventListener('click', () => switchTab(b.dataset.tab)));

    document.getElementById('authModalClose').addEventListener('click', () => overlay.classList.remove('open'));
    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.classList.remove('open'); });

    <?php if ($_authError): ?>
    window.addEventListener('DOMContentLoaded', () => openAuthModal('<?= $_authTab ?>'));
    <?php endif; ?>
})();
</script>

<?= $this->extend('templates/default') ?>
<?= $this->section('title') ?>Invitation<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="ch-card mx-auto max-w-[640px]">
    <h1 class="text-2xl font-bold mb-4">Rejoindre <?= esc($invitation['home_title']) ?></h1>
    <p class="mb-3">Invitation pour <?= esc($invitation['email']) ?>, en tant que <?= esc(\App\Libraries\HomeAccess::label($invitation['role'])) ?>.</p>
    <?php if ($error): ?><p role="alert" class="mb-3 text-red-700"><?= esc($error) ?></p><?php endif; ?>
    <?php if (is_array($auth) && ! empty($auth['id'])): ?>
        <p class="mb-3">Compte connecte : <?= esc($auth['email'] ?? '') ?></p>
        <?php if (strcasecmp((string) ($auth['email'] ?? ''), $invitation['email']) === 0): ?>
            <form method="post">
                <?= csrf_field() ?>
                <button class="ch-btn ch-btn-primary">Accepter l'invitation</button>
            </form>
        <?php else: ?>
            <p class="mb-3">Connectez-vous avec l'email invite pour accepter.</p>
            <a class="ch-btn ch-btn-neutral" href="/login?backto=<?= rawurlencode('/invitation/' . $token) ?>">Se connecter avec un autre compte</a>
        <?php endif; ?>
    <?php elseif ($accountExists): ?>
        <a class="ch-btn ch-btn-primary" href="/login?backto=<?= rawurlencode('/invitation/' . $token) ?>">Se connecter pour accepter</a>
    <?php else: ?>
        <h2 class="text-xl font-bold mb-3">Creer votre compte et accepter</h2>
        <form method="post">
            <?= csrf_field() ?>
            <label class="ch-label" for="password">Mot de passe (8 caracteres minimum)</label>
            <input class="ch-input mb-3" id="password" type="password" name="password" minlength="8" autocomplete="new-password" required>
            <label class="ch-label" for="password_confirm">Confirmer le mot de passe</label>
            <input class="ch-input mb-3" id="password_confirm" type="password" name="password_confirm" minlength="8" autocomplete="new-password" required>
            <button class="ch-btn ch-btn-primary">Creer mon compte et rejoindre la maison</button>
        </form>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

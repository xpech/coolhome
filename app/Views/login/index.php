<?= $this->extend('templates/default') ?>

<?= $this->section('title') ?>Connexion<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="ch-card mx-auto max-w-md p-6">
    <h1 class="text-2xl font-bold">Connexion</h1>
    <?php if (! empty($error)): ?>
        <div class="mb-4 rounded-lg bg-red-100 p-2 text-red-700"><?= esc($error) ?></div>
    <?php endif; ?>
    <form method="post" autocomplete="off">
        <input type="hidden" name="secret" value="<?= esc($secret) ?>">
        <input type="hidden" name="backto" value="<?= esc($backto ?? '') ?>">
        <div class="mb-4">
            <label for="email" class="ch-label">Email</label>
            <input id="email" type="text" name="l_<?= esc($secret) ?>" required class="ch-input">
        </div>
        <div class="mb-4">
            <label for="pwd" class="ch-label">Mot de passe</label>
            <input id="pwd" type="password" name="p_<?= esc($secret) ?>" required class="ch-input">
        </div>
        <button class="ch-btn ch-btn-primary px-4" type="submit">Connexion</button>
    </form>
    <p class="mt-4">
        <a href="/login/recover" class="mr-4 text-blue-600 hover:text-blue-800">Mot de passe perdu</a>
        <a href="/login/create" class="text-blue-600 hover:text-blue-800">Creer un compte</a>
    </p>
</div>
<?= $this->endSection() ?>

<?= $this->extend('templates/default') ?>

<?= $this->section('title') ?>Recuperation du mot de passe<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="ch-card mx-auto max-w-xl p-6">
    <h1 class="mb-3 text-2xl font-bold">Recuperation du mot de passe</h1>
    <p class="mb-3 text-gray-600">Saisissez votre email pour recevoir un lien de reinitialisation.</p>
    <?php if (! empty($message)): ?><div class="mb-3 rounded-lg bg-green-100 p-2 text-green-700"><?= esc($message) ?></div><?php endif; ?>
    <?php if (! empty($error)): ?><div class="mb-3 rounded-lg bg-red-100 p-2 text-red-700"><?= esc($error) ?></div><?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label for="email" class="ch-label">Email</label>
            <input id="email" class="ch-input" type="email" name="email" value="<?= esc($email ?? '') ?>" required>
        </div>
        <button class="ch-btn ch-btn-primary" type="submit" name="action" value="recovermail">Envoyer la demande</button>
    </form>
    <p class="mt-4"><a href="/login" class="text-blue-600 hover:text-blue-800">Retour a la connexion</a></p>
</div>
<?= $this->endSection() ?>

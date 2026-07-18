<?= $this->extend('templates/default') ?>

<?= $this->section('title') ?>Creation de compte<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="ch-card mx-auto max-w-md p-6">
    <h1 class="text-2xl font-bold">Nouveau compte</h1>
    <?php if (! empty($message)): ?><div class="mb-4 rounded-lg bg-green-100 p-2 text-green-700"><?= esc($message) ?></div><?php endif; ?>
    <?php if (! empty($error)): ?><div class="mb-4 rounded-lg bg-red-100 p-2 text-red-700"><?= esc($error) ?></div><?php endif; ?>

    <form method="post">
        <div class="mb-4">
            <label for="email" class="ch-label">Email</label>
            <input id="email" type="email" name="email" value="<?= esc($email ?? '') ?>" required class="ch-input">
        </div>
        <div class="mb-4">
            <label for="securebot" class="ch-label">3 premieres lettres de votre email (anti-robot)</label>
            <input id="securebot" type="text" name="securebot" maxlength="3" required class="ch-input">
        </div>
        <button class="ch-btn ch-btn-primary px-4" type="submit" name="action" value="SendCreateMail">Recevoir le mail de creation</button>
    </form>
    <p class="mt-4"><a href="/login" class="text-blue-600 hover:text-blue-800">Retour a la connexion</a></p>
</div>
<?= $this->endSection() ?>

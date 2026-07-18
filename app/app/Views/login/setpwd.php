<?= $this->extend('templates/default') ?>

<?= $this->section('title') ?>Definition du mot de passe<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="ch-card mx-auto max-w-xl p-6">
    <h1 class="text-2xl font-bold">Mise a jour du mot de passe</h1>
    <?php if (! empty($error)): ?><div class="mb-3 rounded-lg bg-red-100 p-2 text-red-700"><?= esc($error) ?></div><?php endif; ?>
    <form method="post" id="setpwd-form">
        <div class="mb-3">
            <label class="ch-label">Email</label>
            <input type="text" readonly value="<?= esc($email) ?>" class="ch-input bg-gray-50">
        </div>
        <div class="mb-3">
            <label for="pwd1" class="ch-label">Nouveau mot de passe</label>
            <input id="pwd1" type="password" name="pwd1" required class="ch-input">
        </div>
        <div class="mb-3">
            <label for="pwd2" class="ch-label">Confirmer le mot de passe</label>
            <input id="pwd2" type="password" name="pwd2" required class="ch-input">
        </div>
        <button class="ch-btn ch-btn-primary px-4" type="submit" name="action" value="SendUpdatePwd">Enregistrer</button>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.getElementById('setpwd-form').addEventListener('submit', function (evt) {
        const p1 = document.getElementById('pwd1').value;
        const p2 = document.getElementById('pwd2').value;
        if (!p1 || p1 !== p2) {
            evt.preventDefault();
            alert('Le mot de passe est vide ou ne correspond pas.');
        }
    });
</script>
<?= $this->endSection() ?>

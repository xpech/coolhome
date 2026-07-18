<?= $this->extend('templates/default') ?>

<?= $this->section('title') ?>Recuperation du mot de passe<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="ch-card mx-auto max-w-xl p-6">
    <h1 class="mb-3 text-2xl font-bold">Recuperation du mot de passe</h1>
    <p class="text-gray-700"><?= esc($message ?? '') ?></p>
    <p class="mt-4"><a href="/login" class="text-blue-600 hover:text-blue-800">Retour a la connexion</a></p>
</div>
<?= $this->endSection() ?>

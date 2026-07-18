<?= $this->extend('templates/default') ?>

<?= $this->section('title') ?>Bienvenue<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="mx-auto max-w-3xl rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
    <p class="mb-2 text-sm font-semibold uppercase tracking-wide text-teal-700">CoolHome</p>
    <h1 class="mb-3 text-3xl font-bold tracking-tight text-gray-900">Migration CodeIgniter 4 active</h1>
    <p class="mb-6 text-gray-600">
        Le socle CI4 et Tailwind sont en place. Les vues applicatives utilisent maintenant un layout partage
        avec sections title, breadcrumb, content et scripts.
    </p>

    <div class="grid gap-3 sm:grid-cols-2">
        <a href="/homes" class="ch-btn ch-btn-primary px-4 py-3 text-center">Acceder a mes maisons</a>
        <a href="/migration" class="ch-btn ch-btn-neutral px-4 py-3 text-center">Voir l'etat migration</a>
    </div>
</div>
<?= $this->endSection() ?>

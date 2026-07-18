<?= $this->extend('templates/default') ?>

<?= $this->section('title') ?>Migration CI4<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="mx-auto max-w-[900px]">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <h1>Migration CodeIgniter 4 initialisee</h1>
        <div class="mb-4 mt-2 flex flex-wrap gap-2">
            <span class="inline-block rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800">Socle CI4 actif</span>
            <span class="inline-block rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-800">Code metier legacy a porter</span>
        </div>

        <div class="my-5 grid grid-cols-[repeat(auto-fit,minmax(220px,1fr))] gap-3">
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3"><strong>Version CI4</strong><br><?= esc($ci4Version) ?></div>
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3"><strong>Version PHP</strong><br><?= esc($phpVersion) ?></div>
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3"><strong>Etat legacy</strong><br>Accessible via /legacy.php</div>
        </div>

        <p>La base du projet tourne maintenant sur la derniere generation CodeIgniter. Les anciens controleurs/modele CI3 restent disponibles temporairement pendant le portage progressif.</p>

        <a class="ch-btn ch-btn-primary mt-3 inline-block px-4" href="/legacy.php">Ouvrir l'application legacy</a>
    </div>
</div>
<?= $this->endSection() ?>

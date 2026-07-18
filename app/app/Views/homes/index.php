<?= $this->extend('templates/default') ?>

<?= $this->section('title') ?>Mes maisons<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="mx-auto max-w-[980px]">
    <div class="flex justify-between items-center mb-5">
        <div>
            <h1 class="text-2xl font-bold mt-0">Mes maisons</h1>
            <p class="text-gray-500 mt-1.5">Connecte: <?= esc(trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''))) ?> (<?= esc($user['email'] ?? '') ?>)</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a class="ch-btn ch-btn-primary" href="/homes/add">Ajouter une maison</a>
            <a class="ch-btn ch-btn-neutral" href="/migration">Etat migration</a>
            <a class="ch-btn ch-btn-primary" href="/login/out">Se deconnecter</a>
        </div>
    </div>

    <div class="grid grid-cols-[repeat(auto-fit,minmax(260px,1fr))] gap-3.5">
        <?php foreach ($homes as $home): ?>
            <div class="ch-card">
                <div class="mb-2 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="font-bold text-lg"><?= esc($home['title']) ?></div>
                        <div class="text-sm text-gray-500">Mode: <?= esc(\App\Controllers\Homes::modeLabel((int) $home['mode'])) ?></div>
                    </div>
                    <form method="post" class="flex flex-wrap gap-1.5 sm:justify-end">
                        <button class="ch-btn <?= (int) $home['mode'] === -1 ? 'ch-btn-primary' : 'ch-btn-neutral' ?> py-1 text-sm" type="submit" name="mode" value="-1">Absence</button>
                        <button class="ch-btn <?= (int) $home['mode'] === 0 ? 'ch-btn-primary' : 'ch-btn-neutral' ?> py-1 text-sm" type="submit" name="mode" value="0">Auto</button>
                        <button class="ch-btn <?= (int) $home['mode'] === 1 ? 'ch-btn-primary' : 'ch-btn-neutral' ?> py-1 text-sm" type="submit" name="mode" value="1">Confort</button>
                        <input type="hidden" name="home_id" value="<?= (int) $home['id'] ?>">
                        <input type="hidden" name="action" value="UpdateHomeMode">
                    </form>
                </div>

                <div class="text-sm text-gray-500 mt-2">Confort / Absence</div>
                <div><?= esc((string) $home['confort_temp']) ?>° / <?= esc((string) $home['absence_temp']) ?>°</div>

                <div class="mt-3 flex gap-2 flex-wrap">
                    <a class="ch-btn ch-btn-neutral" href="/home/index/<?= (int) $home['id'] ?>">Details</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?= $this->endSection() ?>

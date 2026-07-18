<?= $this->extend('templates/default') ?>

<?= $this->section('title') ?>Satellites<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?><a href="/homes" class="ch-backlink">← Retour aux maisons</a><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="mx-auto max-w-[1100px]">
    <div class="ch-card mb-4">
        <div class="flex justify-between items-center gap-3">
            <h1 class="text-2xl font-bold mt-0">Satellites</h1>
            <a class="ch-btn ch-btn-primary" href="/devices/firmwares">Firmwares</a>
        </div>
        <div class="ch-table-wrap">
        <table class="ch-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>UID</th>
                    <th>Piece</th>
                    <th>Type</th>
                    <th>Reference</th>
                    <th>Actif</th>
                    <th>Acces local</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($devices as $dev): ?>
                    <tr>
                        <td><?= esc($dev['display_title']) ?></td>
                        <td class="text-gray-500"><?= esc((string) $dev['uuid']) ?></td>
                        <td><?= esc((string) ($dev['room_title'] ?? '')) ?></td>
                        <td><?= esc((string) ($dev['kind'] ?? '')) ?></td>
                        <td><?= (int) ($dev['reference'] ?? 0) ? 'Oui' : 'Non' ?></td>
                        <td class="<?= !empty($dev['active']) ? 'text-green-700 font-bold' : 'text-red-600 font-bold' ?>"><?= !empty($dev['active']) ? 'Oui' : 'Non' ?></td>
                        <td><a href="<?= esc($dev['local_url']) ?>" target="_blank" rel="noreferrer" class="text-blue-600 hover:text-blue-800">ouvrir</a></td>
                        <td><a href="/device/index/<?= (int) $dev['id'] ?>" class="text-blue-600 hover:text-blue-800">voir</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

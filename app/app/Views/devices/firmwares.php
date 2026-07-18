<?= $this->extend('templates/default') ?>

<?= $this->section('title') ?>Firmwares<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?><a href="/devices" class="ch-backlink">← Retour satellites</a><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="mx-auto max-w-[980px]">
    <div class="ch-card">
        <h1 class="text-2xl font-bold mt-0">Firmwares</h1>
        <div class="ch-table-wrap">
        <table class="ch-table">
            <thead><tr><th>Plateforme</th><th>Logiciel</th><th>Date</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($firmwares as $f): ?>
                    <tr>
                        <td><?= esc($f['platform']) ?></td>
                        <td><?= esc($f['file']) ?></td>
                        <td><?= $f['mtime'] ? esc(date('d/m/Y H:i:s', (int) $f['mtime'])) : '-' ?></td>
                        <td><a href="/devices/dl/<?= rawurlencode($f['platform']) ?>/<?= rawurlencode($f['file']) ?>" class="text-blue-600 hover:text-blue-800">telecharger</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

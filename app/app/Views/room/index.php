<?= $this->extend('templates/default') ?>

<?= $this->section('title') ?>Piece<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?><a href="/home/index/<?= (int) $room['home'] ?>" class="ch-backlink">← Retour a la maison</a><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="mx-auto max-w-[1060px]">

    <form class="ch-card mb-4" method="post">
        <h2 class="text-xl font-bold mt-0">Informations generales</h2>
        <p class="text-gray-500 mb-4">Maison: <?= esc($room['home_title']) ?></p>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(160px,1fr))] gap-3">
            <div class="field">
                <label for="title" class="ch-label">Nom de la piece</label>
                <input id="title" type="text" name="title" value="<?= esc($room['title']) ?>" class="ch-input">
            </div>
            <div class="field">
                <label for="mode" class="ch-label">Mode</label>
                <select id="mode" name="mode" class="ch-input">
                    <?php foreach ([-1 => 'Absent', 0 => 'Automatique', 1 => 'Confort'] as $k => $v): ?>
                        <option value="<?= $k ?>" <?= (int) $room['mode'] === $k ? 'selected' : '' ?>><?= esc($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <p class="mt-3">
            <span class="inline-block mr-3 p-2 rounded-lg bg-blue-50">Temperature: <?= $temp === null ? '-' : esc(number_format((float) $temp, 1)) . '°' ?></span>
            <span class="inline-block p-2 rounded-lg bg-blue-50">Consigne: <?= $target === null ? '-' : esc(number_format((float) $target, 1)) . '°' ?></span>
        </p>
        <div class="mt-4 flex gap-2.5">
            <button class="ch-btn ch-btn-primary" name="action" value="UpdateRoom">Enregistrer</button>
        </div>
    </form>

    <div class="ch-card mb-4">
        <h2 class="text-xl font-bold mt-0">Vue graphique</h2>
        <p class="mb-3 text-sm text-gray-500">Evolution de la temperature de la piece.</p>
        <canvas id="room-chart" height="110"></canvas>
    </div>

    <div class="ch-card mb-4">
        <h2 class="text-xl font-bold mt-0">Capteurs et actionneurs</h2>
        <div class="ch-table-wrap">
        <table class="ch-table">
            <thead><tr><th>Nom</th><th>Type</th><th>UUID</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($devices as $device): ?>
                    <tr>
                        <td><?= esc($device['title'] ?: ('Satellite ' . $device['uuid'])) ?></td>
                        <td><?= esc((string) ($device['kind'] ?? '-')) ?></td>
                        <td><?= esc((string) ($device['uuid'] ?? '-')) ?></td>
                        <td><a href="/device/index/<?= (int) $device['id'] ?>" class="text-blue-600 hover:text-blue-800">Details legacy</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <form class="ch-card" method="post">
        <h2 class="text-xl font-bold mt-0">Programmes</h2>
        <div class="ch-table-wrap">
        <table class="ch-table">
            <thead>
                <tr>
                    <th>Heure</th><th>Lun</th><th>Mar</th><th>Mer</th><th>Jeu</th><th>Ven</th><th>Sam</th><th>Dim</th><th>T°</th><th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($programs as $p): ?>
                    <tr>
                        <td><input class="ch-input min-w-28" type="time" name="p_start[<?= (int) $p['id'] ?>]" value="<?= esc(substr((string) $p['start'], 0, 5)) ?>"></td>
                        <?php for ($d = 1; $d <= 7; $d++): ?>
                            <td><input type="checkbox" name="p_weekday[<?= (int) $p['id'] ?>][]" value="<?= $d ?>" <?= (((int) $p['weekday']) & (2 << $d)) ? 'checked' : '' ?>></td>
                        <?php endfor; ?>
                        <td><input class="ch-input min-w-24" type="number" step="0.1" name="p_temp[<?= (int) $p['id'] ?>]" value="<?= esc((string) $p['temp']) ?>"></td>
                        <td><button type="button" onclick="this.closest('tr').remove();" class="text-red-600 hover:text-red-800">Suppr.</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td><input class="ch-input min-w-28" type="time" name="new_start"></td>
                    <?php for ($d = 1; $d <= 7; $d++): ?>
                        <td><input type="checkbox" name="new_weekday[]" value="<?= $d ?>"></td>
                    <?php endfor; ?>
                    <td><input class="ch-input min-w-24" type="number" step="0.1" name="new_temp"></td>
                    <td><button class="ch-btn ch-btn-primary" type="submit" name="action" value="AddProgram">+</button></td>
                </tr>
            </tfoot>
        </table>
        </div>
        <p class="mt-4"><button class="ch-btn ch-btn-primary" type="submit" name="action" value="UpdateProgram">Enregistrer les programmes</button></p>
    </form>

    <form class="ch-card mt-4 border-red-200 bg-red-50/60" method="post">
        <h2 class="mt-0 text-lg font-bold text-red-700">Zone de suppression</h2>
        <p class="mb-3 text-sm text-red-700">Cette action est irreversible et supprimera la piece avec ses programmes.</p>
        <button
            class="ch-btn ch-btn-danger"
            type="submit"
            name="action"
            value="DeleteRoom"
            onclick="return confirm('Confirmer la suppression definitive de cette piece ?');"
        >Supprimer cette piece</button>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3"></script>
<script>
(async function () {
    const chartEl = document.getElementById('room-chart');
    if (!chartEl) {
        return;
    }

    const res = await fetch('/room/datachart/<?= (int) $room['id'] ?>');
    const datasets = await res.json();
    const normalized = (Array.isArray(datasets) ? datasets : []).map((d) => ({
        label: d.label,
        data: d.data,
        parsing: { xAxisKey: 'x', yAxisKey: 'y' },
        borderColor: '#0d9488',
        backgroundColor: '#0d9488',
        pointRadius: 0,
        borderWidth: 2,
        tension: 0.2,
    }));

    new Chart(chartEl, {
        type: 'line',
        data: { datasets: normalized },
        options: {
            responsive: true,
            scales: {
                x: { type: 'time', time: { unit: 'day' } },
            },
        },
    });
})();
</script>
<?= $this->endSection() ?>

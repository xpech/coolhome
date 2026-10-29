<?= $this->extend('templates/default') ?>

<?= $this->section('title') ?>Device<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?><a href="/devices" class="ch-backlink">← Retour satellites</a><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="mx-auto max-w-[1100px]">

    <form class="ch-card mb-4" method="post">
        <h1 class="text-2xl font-bold mt-0"><?= esc($displayTitle) ?> [<?= esc((string) $device['uuid']) ?>]</h1>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(160px,1fr))] gap-3">
            <div class="field">
                <label for="title" class="ch-label">Nom</label>
                <input id="title" type="text" name="title" value="<?= esc((string) $device['title']) ?>" class="ch-input">
            </div>
            <div class="field">
                <label for="kind" class="ch-label">Type</label>
                <select id="kind" name="kind" class="ch-input">
                    <?php foreach (['HEATER' => 'Radiateur', 'LIGHT' => 'Lumiere', 'SWITCH' => 'Interrupteur', 'WATERING' => 'Arrosage', 'FAN' => 'Ventilation', '' => 'Indefini'] as $k => $v): ?>
                        <option value="<?= esc($k) ?>" <?= ((string) $device['kind'] === (string) $k) ? 'selected' : '' ?>><?= esc($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="room" class="ch-label">Piece</label>
                <select id="room" name="room" class="ch-input">
                    <option value="0">-</option>
                    <?php foreach ($homes as $home): ?>
                        <optgroup label="<?= esc((string) $home['title']) ?>">
                            <?php foreach ($roomsByHome[(int) $home['id']] as $room): ?>
                                <option value="<?= (int) $room['id'] ?>" <?= ((int) $device['room'] === (int) $room['id']) ? 'selected' : '' ?>><?= esc((string) $room['title']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field flex items-end gap-2">
                <label class="flex items-center"><input type="checkbox" name="reference" value="1" <?= (int) $device['reference'] ? 'checked' : '' ?> class="mr-2"> Reference (temperature)</label>
            </div>
        </div>
        <div class="mt-4 flex gap-2.5">
            <button class="ch-btn ch-btn-danger" name="action" value="DeleteDevice" onclick="return confirm('Supprimer ce satellite ?');">Supprimer</button>
            <a class="ch-btn ch-btn-neutral" href="<?= esc($localUrl) ?>" target="_blank" rel="noreferrer">Acces local</a>
            <button class="ch-btn ch-btn-primary" name="action" value="UpdateDevice">Enregistrer</button>
        </div>
    </form>

    <div class="ch-card">
        <h2 class="text-xl font-bold mt-0">Evolution</h2>
        <canvas id="chart" height="110"></canvas>
    </div>

    <div class="ch-card">
        <h2 class="text-xl font-bold mt-0">Donnees brutes</h2>
        <div class="ch-table-wrap">
        <table id="raw-table" class="ch-table">
            <thead><tr><th>Date</th><th>Capteur</th><th>Type</th><th>Valeur</th></tr></thead>
            <tbody></tbody>
        </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3"></script>
<script>
(async function () {
    const chartEl = document.getElementById('chart');
    const res = await fetch('/device/datachart/<?= (int) $device['id'] ?>');
    const datasets = await res.json();

    const colors = ['#0f766e', '#1d4ed8', '#b45309', '#be185d', '#6d28d9', '#15803d'];
    const normalized = (Array.isArray(datasets) ? datasets : []).map((d, i) => ({
        label: d.label,
        data: d.data,
        parsing: { xAxisKey: 'x', yAxisKey: 'y' },
        borderColor: colors[i % colors.length],
        backgroundColor: colors[i % colors.length],
        pointRadius: 0,
        borderWidth: 2,
        tension: 0.15,
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

(async function () {
    const form = new FormData();
    form.append('draw', '1');
    form.append('start', '0');
    form.append('length', '100');

    const res = await fetch('/device/datasfordt/<?= (int) $device['id'] ?>', {
        method: 'POST',
        body: form,
    });
    const json = await res.json();
    const tbody = document.querySelector('#raw-table tbody');

    for (const row of (json.data || [])) {
        const tr = document.createElement('tr');
        tr.innerHTML = '<td>' + (row.created ?? '') + '</td><td>' + (row.sensor ?? '') + '</td><td>' + (row.kind ?? '') + '</td><td>' + (row.value ?? '') + '</td>';
        tbody.appendChild(tr);
    }
})();
</script>
<?= $this->endSection() ?>

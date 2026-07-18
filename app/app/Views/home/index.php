<?= $this->extend('templates/default') ?>

<?= $this->section('title') ?>Maison<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?><a href="/homes" class="ch-backlink">← Retour aux maisons</a><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="mx-auto max-w-[980px]">

    <div class="ch-card mb-4">
        <div class="mb-2 text-sm font-semibold text-gray-600">Mode rapide</div>
        <form method="post" class="flex flex-wrap gap-2">
            <button class="ch-btn <?= (int) $home['mode'] === -1 ? 'ch-btn-primary' : 'ch-btn-neutral' ?>" type="submit" name="mode" value="-1">Absence</button>
            <button class="ch-btn <?= (int) $home['mode'] === 0 ? 'ch-btn-primary' : 'ch-btn-neutral' ?>" type="submit" name="mode" value="0">Automatique</button>
            <button class="ch-btn <?= (int) $home['mode'] === 1 ? 'ch-btn-primary' : 'ch-btn-neutral' ?>" type="submit" name="mode" value="1">Confort</button>
            <input type="hidden" name="action" value="UpdateHomeMode">
        </form>
    </div>

    <form class="ch-card mb-4" method="post">
        <h2 class="text-xl font-bold mt-0">Informations generales</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(160px,1fr))] gap-3">
            <div class="field">
                <label for="title" class="ch-label">Nom de la maison</label>
                <input id="title" type="text" name="title" value="<?= esc($home['title']) ?>" class="ch-input">
            </div>
            <div class="field">
                <label for="confort_temp" class="ch-label">Temperature confort</label>
                <input id="confort_temp" type="number" step="0.1" name="confort_temp" value="<?= esc((string) $home['confort_temp']) ?>" class="ch-input">
            </div>
            <div class="field">
                <label for="absence_temp" class="ch-label">Temperature absence</label>
                <input id="absence_temp" type="number" step="0.1" name="absence_temp" value="<?= esc((string) $home['absence_temp']) ?>" class="ch-input">
            </div>
            <div class="field">
                <label for="mode" class="ch-label">Mode</label>
                <select id="mode" name="mode" class="ch-input">
                    <?php foreach ([-1 => 'Absent', 0 => 'Automatique', 1 => 'Confort'] as $k => $v): ?>
                        <option value="<?= $k ?>" <?= (int) $home['mode'] === $k ? 'selected' : '' ?>><?= esc($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="mt-4 flex gap-2.5">
            <button class="ch-btn ch-btn-primary" type="submit" name="action" value="UpdateHome">Enregistrer</button>
        </div>
    </form>

    <div class="ch-card mb-4">
        <h2 class="text-xl font-bold mt-0">Vue graphique</h2>
        <p class="mb-3 text-sm text-gray-500">Evolution des temperatures par piece.</p>
        <form id="home-chart-filters" class="mb-3 grid gap-2 sm:grid-cols-[180px,1fr,1fr,auto,auto]">
            <select id="home-chart-range" class="ch-input">
                <option value="24h">Dernieres 24h</option>
                <option value="7d" selected>Derniers 7 jours</option>
                <option value="30d">Derniers 30 jours</option>
                <option value="all">Tout l'historique</option>
                <option value="custom">Personnalise</option>
            </select>
            <input id="home-chart-from" type="date" class="ch-input" disabled>
            <input id="home-chart-to" type="date" class="ch-input" disabled>
            <button id="home-chart-apply" type="submit" class="ch-btn ch-btn-primary">Appliquer</button>
            <button id="home-chart-reset" type="button" class="ch-btn ch-btn-neutral">Reset</button>
        </form>
        <canvas id="home-chart" height="110"></canvas>
    </div>

    <div class="ch-card">
        <h2 class="text-xl font-bold mt-0">Pieces</h2>
        <div class="ch-table-wrap">
        <table class="ch-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Mode</th>
                    <th>Consigne</th>
                    <th>Temperature</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rooms as $room): ?>
                    <tr>
                        <td><a href="/room/index/<?= (int) $room['id'] ?>" class="text-blue-600 hover:text-blue-800"><?= esc($room['title']) ?></a></td>
                        <td>
                            <?php
                                $modes = [-1 => 'Absent', 0 => 'Automatique', 1 => 'Confort'];
                                echo esc($modes[(int) $room['mode']] ?? '?');
                            ?>
                        </td>
                        <td><?= $room['target'] === null ? '-' : esc(number_format((float) $room['target'], 1)) . '°' ?></td>
                        <td><?= $room['temp'] === null ? '-' : esc(number_format((float) $room['temp'], 1)) . '°' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <p class="mt-4"><a class="ch-btn ch-btn-primary" href="/home/addroom/<?= (int) $home['id'] ?>">Ajouter une piece</a></p>
    </div>

    <div class="ch-card mt-4 border-red-200 bg-red-50/60">
        <h2 class="mt-0 text-lg font-bold text-red-700">Zone de suppression</h2>
        <p class="mb-3 text-sm text-red-700">Cette action est irreversible et supprimera la maison.</p>
        <a
            class="ch-btn ch-btn-danger"
            href="/homes/remove/<?= (int) $home['id'] ?>"
            onclick="return confirm('Confirmer la suppression definitive de cette maison ?');"
        >Supprimer cette maison</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3"></script>
<script>
(async function () {
    const chartEl = document.getElementById('home-chart');
    if (!chartEl) {
        return;
    }

    const rangeEl = document.getElementById('home-chart-range');
    const fromEl = document.getElementById('home-chart-from');
    const toEl = document.getElementById('home-chart-to');
    const filtersEl = document.getElementById('home-chart-filters');
    const resetEl = document.getElementById('home-chart-reset');

    const palette = ['#0d9488', '#2563eb', '#b45309', '#be185d', '#7c3aed', '#16a34a'];
    let chart = null;

    function syncFilterInputs() {
        const custom = rangeEl.value === 'custom';
        fromEl.disabled = !custom;
        toEl.disabled = !custom;
        if (!custom) {
            fromEl.value = '';
            toEl.value = '';
        }
    }

    async function loadChart() {
        const url = new URL('/home/datachart/<?= (int) $home['id'] ?>', window.location.origin);

        if (rangeEl.value === 'custom') {
            if (fromEl.value) {
                url.searchParams.set('from', fromEl.value);
            }
            if (toEl.value) {
                url.searchParams.set('to', toEl.value);
            }
            if (!fromEl.value && !toEl.value) {
                url.searchParams.set('range', '7d');
            }
        } else {
            url.searchParams.set('range', rangeEl.value);
        }

        const res = await fetch(url.toString());
        const datasets = await res.json();
        const normalized = (Array.isArray(datasets) ? datasets : []).map((d, i) => ({
            label: d.label,
            data: d.data,
            parsing: { xAxisKey: 'x', yAxisKey: 'y' },
            borderColor: palette[i % palette.length],
            backgroundColor: palette[i % palette.length],
            pointRadius: 0,
            borderWidth: 2,
            tension: 0.2,
        }));

        if (chart) {
            chart.destroy();
        }

        chart = new Chart(chartEl, {
            type: 'line',
            data: { datasets: normalized },
            options: {
                responsive: true,
                interaction: {
                    mode: 'nearest',
                    intersect: false,
                },
                scales: {
                    x: { type: 'time', time: { unit: 'day' } },
                },
            },
        });
    }

    rangeEl.addEventListener('change', function () {
        syncFilterInputs();
        if (rangeEl.value !== 'custom') {
            loadChart();
        }
    });

    filtersEl.addEventListener('submit', function (evt) {
        evt.preventDefault();
        loadChart();
    });

    resetEl.addEventListener('click', function () {
        rangeEl.value = '7d';
        syncFilterInputs();
        loadChart();
    });

    syncFilterInputs();
    await loadChart();
})();
</script>
<?= $this->endSection() ?>

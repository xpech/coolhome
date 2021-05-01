<?

?>
<form action="" method="post">
	<div class="card">
		<div class="card-header"><?= $device->info() ?> [<?= $device->uuid ?>]</div>
		<div class="card-body">
			<div class="form-row">
				<div class="form-group col-3">
					<label for="title">Nom</label>
					<input type="text" class="form-control" name="title" value="<?= $device->title ?>"/>
				</div>
				<div class="form-group col-3">
					<label for="title">Type</label>
					<select name="kind" class="form-control">
						<? foreach(['HEATER' => 'Radiateur','LIGHT' => 'Lumière','SWITCH' => 'Interupteur','' => 'INDEFINI'] as $k => $v) { ?>
						<option value="<?= $k ?>" <? CHtml::selected($k == $device->kind) ?>><?= $v ?></option>
						<? } ?>
					</select>
				</div>
				<div class="form-group col-3">
					<label for="title">Pièces</label>
					<select name="room" class="form-control">
						<? foreach(CHome::forUser() as $h) { ?>
							<optgroup label="<?= $h->info() ?>">
								<? foreach($h->rooms() as $v) { ?>
								<option value="<?= $v->id ?>" <? CHtml::selected($v->id == $device->room) ?>><?= $v->info() ?></option>
								<? } ?>								
							</optgroup><? } ?>
					</select>
				</div>

				<div class="col-3">
					<label class="label">Référence (température)</label><br/>
					<label class="c-switch c-switch-3d c-switch-success">
						<input class="c-switch-input" type="checkbox" name="reference" <? CHtml::checked($device->reference) ?>><span class="c-switch-slider"></span>
					</label>		
				</div>


			</div>
		</div>
		<div class="card-footer d-flex justify-content-between">
			<button class="btn btn-danger" name="action" value="DeleteDevice"><i class="far fa-trash-alt"></i> Supprimer</button>
			<a class="btn btn-secondary" href="<?= $device->url() ?>">local</a>
			<button class="btn btn-success" name="action" value="UpdateDevice"><i class="far fa-save"></i> Enregistrer</button>
		</div>

	</div>
	
</form>
<script src="https://d3js.org/d3-color.v1.min.js"></script>
<script src="https://d3js.org/d3-interpolate.v1.min.js"></script>
<script src="https://d3js.org/d3-scale-chromatic.v1.min.js"></script>
<div class="card">
	<div class="card-header">Evolution</div>
	<div class="card-body">
		  <canvas id="myChart"></canvas>
		  <script type="text/javascript">
		  	myChart = null;
		  	jQuery(function(){
		  		moment.locale('fr');
		  		var from = moment().startOf('day');
		  		var to = moment();

				var myChart = new Chart(
					document.getElementById('myChart'),
					{
						type: 'line',
						options: {
							scales: {
								x: {
									type: 'time',
									time: {
										unit: 'hour',
										displayFormats: {
											time: 'dd YYYY',
											quarter: 'MMM YYYY',
											hour: 'L LT'
										}
									}
								}
							}
						},
						actions: [{
							name: 'Randomize',
							handler(chart) {
								chart.data.datasets.forEach(dataset => {
									dataset.data = Utils.numbers({count: chart.data.labels.length, min: -100, max: 100});
								});
								chart.update();
								}
						}],
						data: {}
					});
				$('[role=char-btn]').click(function(evt){
					console.log(this);
					var mode = $(this).attr('chart');
					from = moment();
					to = moment();
					if (mode=="today") { from.startOf('day');}
					if (mode=="yesterday") { from.startOf('day');}
					if (mode=="week") { from .startOf('week'); to = moment();}
					if (mode=="month") { from.startOf('month'); to = moment();}
					updateChart();
				});
				var updateChart = function(){
					$.ajax({
						url: '/device/ajax',
						data: {
							action: 'GetDataChart',
							id_device: <?= $device->id ?>,
							from: from.format(),
							to: to.format()
						},
						success: function(datas)
						{
							console.log('Update datas');
							console.log(myChart);
							var colors = d3.schemeAccent;
							for(var i = 0; i < datas.length; i++)
							{
								datas[i].backgroundColor = colors[i];
							}

							myChart.data.datasets = datas;
					  		moment.locale('fr');
							myChart.update();
						}
					});
				};
				updateChart();

		  	});
		  </script>
	</div>
	<div class="card-footer d-flex justify-content-between">
		<button type="button" role="char-btn" chart="today" class="btn btn-secondary">Ajourd'hui</button>
		<button type="button" role="char-btn" chart="yesterday" class="btn btn-secondary">Hier</button>
		<button type="button" role="char-btn" chart="week" class="btn btn-secondary">7j</button>
		<button type="button" role="char-btn" chart="month" class="btn btn-secondary">30j</button>
	</div>
</div>
<div class="card">
	<div class="card-header">Données brutes</div>
	<div class="card-body">
		<table class="table table-sm datatable">
			<thead>
				<tr>
					<th>Date</th>
					<th>Capteur</th>
					<th>Type</th>
					<th>Value</th>
				</tr>
			</thead>
			<tbody>
				<? /* foreach(CData::forDevice($device->id) as $dev) { ?>
				<tr>
					<td><?= $dev->created() ?></td>
					<td><?= $dev->sensor ?></td>
					<td><?= $dev->kind ?></td>
					<td class="text-right"><?= $dev->value ?></td>
				</tr>

				<? } */ ?>
			</tbody>
		</table>
	</div>
</div>
<script>
	jQuery(function(){
		$('table.datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "/device/datasfordt/<?= $device->id ?>",
				type: "POST"
			},
			"columns": [
				{ "data": "created" },
				{ "data": "sensor" },
				{ "data": "kind" },
				{ "data": "value" }]
		});

	});
</script>

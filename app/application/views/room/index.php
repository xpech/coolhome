
<script src="https://d3js.org/d3-color.v1.min.js"></script>
<script src="https://d3js.org/d3-interpolate.v1.min.js"></script>
<script src="https://d3js.org/d3-scale-chromatic.v1.min.js"></script>
<form method="POST">
	<div class="card">
		<div class="card-header">Information générales</div>
		<div class="card-body">
			<div class="form-row">
				<div class="form-group col-4">
					<label for="title">Nom de la pièce</label>
					<input type="text" class="form-control" name="title" value="<?= $room->title ?>"/>
				</div>

				<div class="form-group col-3">
					<label for="title">Mode</label>
					<select name="mode" class="form-control">
						<? foreach([-1 => 'Absent', 0 => 'Automatique', 1 => 'Confort'] as $k => $v) {?>
							<option value="<?= $k ?>" <? CHtml::selected($k == $room->mode) ?>><?= $v ?></option><? } ?>
					</select>
				</div>
				<? $target = $room->target();
				$temp = $room->temp();
				?>
				<div class="col-2 border-light text-center <?=($temp > $target ? 'bg-success' : 'bg-danger') ?> text-white">
					<div class="text-value-xl"><?= number_format($temp,1) ?>°</div>
					<div class="text-uppercase  small">Température</div>
				</div>
				<div class="col-2 border-light text-center bg-info theme-color text-white">
					<div class="text-value-xl"><?= number_format($target,1) ?>°</div>
					<div class="text-uppercase  small">Consigne</div>
				</div>

			</div>

		</div>
		<div class="card-footer d-flex justify-content-between">
			<button class="btn btn-danger" name="action" value="DeleteRoom"><i class="far fa-trash-alt"></i> Supprimer</button>
			<button class="btn btn-success" name="action" value="UpdateRoom"><i class="far fa-save"></i> Enregistrer</button>
		</div>
	</div>
</form>
	<div class="row">
		<div class="card col-4">
			<div class="card-header">Capteurs et actionneurs</div>
			<div class="card-body p-0">
				<table class="table table-sm ">
					<tbody>
						<? foreach($room->devices() as $r) { ?>
						<tr>
							<th><?= $r->info() ?></th>
							<td><?= $r->kind ?></td>
							<td><a href="/device/index/<?= $r->id ?>">détails</a></td>
						</tr>
						<? } ?>
					</tbody>
				</table>
			</div>
		</div>
		<div class="col-10 card col-md-6">
			<div class="card-body">
			  <canvas id="myChart"></canvas>
			</div>
		</div>
		<div class="col-2">
			<button class="btn btn-secondary w-100 mb-2" role="char-btn" chart="today">24h</button>
			<button class="btn btn-secondary w-100 mb-2" role="char-btn" chart="week">7j</button>
			<button class="btn btn-secondary w-100 mb-2" role="char-btn" chart="month">Mois</button>

			  <script>
			  	myChart = null;
			  	jQuery(function(){
			  		moment.locale('fr');
			  		var from = moment().startOf('day');
			  		var to = moment();

					var myChart = new Chart(
						document.getElementById('myChart'),
						{
							type: 'line',
//							locale: 'fr-Latn-FR-u-nu-mong',
							locale: 'fr-Latn-FR-u-nu-mong',
							options: {
								scales: {
									x: {
										display: false,
										type: 'time',
										time: {
											unit: 'hour',
											tooltipFormat: 'DD MMM YYYY HH:mm:ss',

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
							url: '/room/ajax',
							data: {
								action: 'GetDataChart',
								id_room: <?= $room->id ?>,
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

		</div>
	</div>

<form method="POST">
	<div class="card">
		<div class="card-header">Programmes</div>
		<div class="card-body">
			<table class="table">
				<thead>
					<tr>
						<th>Heure</th>
						<th>Lun.</th>
						<th>Mar.</th>
						<th>Mer.</th>
						<th>Jeu.</th>
						<th>Ven.</th>
						<th>Sam.</th>
						<th>Dim.</th>
						<th>T°</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<? foreach($room->programs() as $r) { ?>
					<tr>
						<td><input type="time" value="<?= $r->start ?>" name="p_start[<?= $r->id ?>]" class="form-control" /></td>
						<? for($d = 1; $d <= 7; $d++) { ?> 
						<td><input type="checkbox" value="<?= $d ?>" name="p_weekday[<?= $r->id ?>][]" class="form-control" <? CHtml::checked($r->weekday($d)) ?> /></td><? } ?>
						<td><input type="number" value="<?= $r->temp ?>" name="p_temp[<?= $r->id ?>]" class="form-control" /></td>
						<td role="DeleteRow"><i class="fas fa-trash-alt"></i></td>
					</tr>
					<? } ?>
				</tbody>
				<thead>
					<tr>
						<td><input type="time" value="" name="new_start" class="form-control" /></td>
						<? for($d = 1; $d <= 7; $d++) { ?> 
						<td><input type="checkbox" value="<?= $d ?>" name="new_weekday[]" class="form-control" /></td><? } ?>
						<td><input type="number" value="" name="new_temp" class="form-control" /></td>
						<td><button type="submit" class="btn btn-success" name="action" value="AddProgram">+</button></td>
					</tr>
				</thead>
			</table>
		</div>
		<div class="card-footer d-flex justify-content-between">
			<button type="submit" class="btn btn-success" name="action" value="UpdateProgram">Enregistrer les programmes</button>
		</div>
	</div>
</form>
<script>
	jQuery(function(){
		$('[role=DeleteRow]').click(function(evt){
			console.log(this);
			$(this).closest('tr').hide('slow').remove();

		});

	});
</script>
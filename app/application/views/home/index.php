<?
?>
<form method="POST">
	<div class="card">
		<div class="card-header">Information générales</div>
		<div class="card-body">
			<div class="form-row">
				<div class="form-group col-4">
					<label for="title">Nom de la maison</label>
					<input type="text" class="form-control" name="title" value="<?= $home->title ?>"/>
				</div>
				<div class="form-group col-3">
					<label for="title">T° confort</label>
					<input type="number" class="form-control text-right" name="confort_temp" value="<?= $home->confort_temp ?>"/>
				</div>
				<div class="form-group col-3">
					<label for="title">T° absence</label>
					<input type="number" class="form-control text-right" name="absence_temp" value="<?= $home->absence_temp ?>"/>
				</div>
				<div class="form-group col-2">
					<label for="title">Mode</label>
					<select name="mode" class="form-control">
						<? foreach([-1 => 'Absent', 0 => 'Automatique', 1 => 'Confort'] as $k => $v) {?>
							<option value="<?= $k ?>" <? CHtml::selected($k == $home->mode) ?>><?= $v ?></option><? } ?>
					</select>
				</div>
			</div>

		</div>
		<div class="card-footer d-flex justify-content-between">
			<a href="/homes/remove/<?= $home->id ?>" class="btn btn-danger" role="remove"><i class="far fa-trash-alt"></i> Supprimer</a>
			<button class="btn btn-success" type="submit" name="action" value="UpdateHome"><i class="far fa-save"></i> Enregistrer</button>
		</div>
	</div>
</form>
	<div class="card">
		<div class="card-header">Pièces</div>
		<div class="card-body p-0">
			<table class="table">
				<thead>
					<tr>
						<th>Nom</th>
						<th>Mode</th>
						<th>Consigne</th>
						<th>T°</th>
						<th>Hr</th>
					</tr>
				</thead>
				<tbody>
					<? foreach($home->rooms() as $r) { ?>
					<tr>
						<td><a href="/room/index/<?= $r->id ?>"><?= $r->info() ?></a></td>
						<td><?= $r->mode() ?></td>
						<td class="text-right"><?= number_format($r->target(),2) ?>°</td>
						<td class="text-right"><?= number_format($r->temp(),2) ?>°</td>
						<td class="text-right"><?= number_format($r->hum(),2) ?>%</td>
						<td></td>
					</tr>
					<? } ?>
				</tbody>
			</table>
		</div>
		<div class="card-footer d-flex justify-content-between">
			<a href="/home/addroom/<?= $home->id ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Ajouter une pièce</a>
		</div>
	</div>

<? foreach($homes as $home) { ?>
<div class="card">
	<div class="card-header"><i class="fas fa-home"></i> <?= $home->title ?>
	<? /*
		<span class="badge badge-pill badge-danger float-right">
				<div class=""><?= $home->confort_temp ?>°</div>
				<div class="text-uppercase small">Confort</div>
		</span>
		<span class="badge badge-pill badge-primary float-right">
				<div class=""><?= $home->absence_temp ?>°</div>
				<div class="text-uppercase small">Absence</div>
		</span> */ ?>

		<span class="badge badge-pill badge-secondary float-right">
			<?= $home->mode() ?>
		</span>

	</div>

	<div class="card-body">
		<div class="row d-flex ">
			<? foreach($home->rooms() as $room) { ?>
				<div class="col-sm-6 col-md-4">
					<div class="card">
						<div class="card-header"><?= $room->title ?></div>
						<div class="card-body row text-center">
							<div class="col">
								<div class="text-value-xl"><i class="fas fa-thermometer-half"></i> <?= number_format($room->temp(),1) ?>°</div>
								<div class="text-uppercase text-muted small">Température</div>
	 						</div>
							<div class="c-vr"></div>
							<div class="col">
								<div class="text-value-xl"><i class="fas fa-sort"></i> <?= $room->target() ?>°</div>
								<div class="text-uppercase text-muted small">Consigne</div>
							</div>
						</div>
						<div class="card-footer"><a href="/room/index/<?= $room->id ?>" class="btn btn-secondary"><i class="far fa-eye"></i> détails</a></div>
					</div>
				</div>
			<? } ?>
		</div>
	</div>
	<div class="card-footer d-flex justify-content-between">
		<a href="/homes/remove/<?= $home->id ?>" class="btn btn-danger"><i class="far fa-trash-alt"></i> Supprimer la maison</a>
		<a href="/home/index/<?= $home->id ?>" class="btn btn-primary"><i class="far fa-eye"></i> Détails</a>
	</div>
</div>
<? } ?>


	<? 
	$intervenant = ($value ? CIntervenant::objectWithId($value) : new CIntervenant());
	$refid = uniqid('form_');
	?>
	<div class="form-group <? if (isset($class)) echo $class; ?>" allow-new="new">
		<label for="<?= $refid ?>"><?= $title ?></label>
		<input type="hidden" 
			id="<?= $refid ?>"
			value="<?= $intervenant->id ?>" name="<?= $name ?>"/>
		<div class="input-group">
			<input type="text"
				 id="<?= $refid ?>_txt"
				 class="form-control"
				 readonly value="<?= $intervenant->info() ?>"/>
			<div class="input-group-append">
				<button class="btn btn-secondary" type="button" role="SelectPopup" url="/intervenants/popup?interne=1"  target-text="#<?= $refid ?>_txt"  title="Selectionner <?= $title ?>" target-val="#<?= $refid ?>"><i class="fas fa-search"></i></button>
				<? if ($intervenant->id):?>
				<a class="btn btn-secondary" href="/annuaire/contact/<?= $intervenant->id ?>" title="Aller sur la fiche" <? if (!$intervenant->id) echo 'disabled'; ?>>
					<i class="fas fa-arrow-right"></i></a>
				<? endif; ?>
			</div>
		</div>
	</div>

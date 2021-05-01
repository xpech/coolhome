	<? 
	$contact = ($value ? CContact::objectWithId($value) : new CContact());
	$refid = uniqid('form_');
	?>
	<div class="form-group <? if (isset($class)) echo $class; ?>" role="ContactChooser" allow-new="new">
		<label for="<?= $refid ?>"><?= $title ?></label>
		<input type="hidden" 
			id="<?= $refid ?>"
			value="<?= $contact->id ?>" name="<?= $name ?>"/>
		<div class="input-group">
			<input type="text"
				 id="<?= $refid ?>_txt"
				 class="form-control bg-white font-weight-bold"
				 readonly
				 value="<?= $contact->info() ?>"/>
			<div class="input-group-append">
				<button class="btn btn-secondary" type="button" role="SelectPopup" url="/annuaire/popup"  target-text="#<?= $refid ?>_txt"  title="Selectionner <?= $title ?>" target-val="#<?= $refid ?>"><i class="fas fa-search"></i></button>
				<? if ($contact->id):?>
				<a class="btn btn-secondary" href="/annuaire/contact/<?= $contact->id ?>" title="Aller sur la fiche" <? if (!$contact->id) echo 'disabled'; ?>>
					<i class="fa fa-arrow-right"></i></a>
				<? endif; ?>
			</div>
		</div>
	</div>


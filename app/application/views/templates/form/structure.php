	<? 
	$structure = ($value ? CStructure::objectWithId($value) : new CStructure());
	if (!$structure) $structure = new CStructure();

	$refid = uniqid('form_');
	?>
	<div class="form-group <? if (isset($class)) echo $class; ?>" allow-new="new">
		<label for="<?= $refid ?>"><?= $title ?></label>
		<input type="hidden" 
			id="<?= $refid ?>"
			value="<?= $structure->id ?>" name="<?= $name ?>"/>
		<div class="input-group input-group-lg">
			<input type="text"
				 id="<?= $refid ?>_txt"
				 class="form-control bg-white"
				 readonly value="<?= $structure->info() ?>"/>
			<div class="input-group-append">
				<button class="btn btn-secondary" type="button" role="SelectPopup" url="/annuaire/popup/structure"  target-text="#<?= $refid ?>_txt"  title="Selectionner <?= $title ?>" target-val="#<?= $refid ?>"><i class="fas fa-search"></i></button>
				<? if ($structure->id):?>
				<a class="btn btn-secondary" href="/annuaire/structure/<?= $structure->id ?>" title="Aller sur la fiche" <? if (!$structure->id) echo 'disabled'; ?>>
					<i class="fas fa-arrow-right"></i></a>
				<? endif; ?>
			</div>
		</div>
	</div>

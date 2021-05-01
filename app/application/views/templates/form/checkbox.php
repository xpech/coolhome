	<div class="custom-control custom-switch <? if (isset($class)) echo $class; ?>">
		<input type="checkbox" class="custom-control-input" id="<?= $name ?>" name="<?= $name ?>" <? if ($checked) echo 'checked'; ?>
			target="#<?= $name ?>"
			/>
		<label class="custom-control-label" for="<?= $name ?>"><?= $title ?></label>
	</div>


	<div class="custom-control custom-checkbox <? if (isset($class)) echo $class; ?>">
		<input type="radio" class="custom-control-input" id="<?= $name ?>_<?= $value ?>" name="<?= $name ?>" <? if ($checked) echo 'checked'; ?> value="<?= $value ?>"
			target="#<?= $name ?>_<?= $value ?>"/>
		<label class="custom-control-label" for="<?= $name ?>_<?= $value ?>"><?= $title ?></label>
	</div>

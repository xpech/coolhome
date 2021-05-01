	<div class="form-group <? if (isset($class)) echo $class; ?>">
		<label class="" for="<?= $name ?>"><?= $title ?></label>
		<textarea class="form-control" id="<?= $name ?>" name="<?= $name ?>"><?= $value ?></textarea>
	</div>

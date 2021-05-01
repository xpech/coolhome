	<div class="form-group <? if (isset($class)) echo $class; ?>">
		<label class="" for="<?= $name ?>"><?= $title ?></label>
		<input type="number" step="0.01" class="form-control text-right" id="<?= $name ?>" name="<?= $name ?>" value="<?= $value ?>"/>
	</div>

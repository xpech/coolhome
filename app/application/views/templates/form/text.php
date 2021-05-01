	<div class="form-group <? if (isset($class)) echo $class; ?>">
		<label class="" for="<?= $name ?>"><?= $title ?></label>
		<input type="text" class="form-control" id="<?= $name ?>" name="<?= $name ?>" value="<?= $value ?>"/>
	</div>

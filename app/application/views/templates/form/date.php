	<div class="form-group <? if (isset($class)) echo $class; ?>">
		<label for="<?= $name ?>"><?= $title ?></label>
		<input type="date" name="<?= $name ?>" 
		value="<? CHtml::fmtDateInput($value) ?>"
			id="<?= $name ?>" class="form-control"/>
	</div>

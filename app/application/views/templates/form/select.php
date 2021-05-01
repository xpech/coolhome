	<div class="form-group <? if (isset($class)) echo $class; ?>">
		<label class="" for="<?= $name ?>"><?= $title ?></label>
		<select  id="<?= $name ?>" name="<?= $name ?>" class="form-control">
			<? foreach($options as $k => $g):
				if (is_object($g))
				{ ?>
				<option value="<?= $g->id ?>" <? CHtml::selected(($g->id == $value ))?>><?= $g->getInfo() ?></option><? } else { ?> 
				<option value="<?= $k ?>" <? CHtml::selected(($k == $value ))?>><?= $g ?></option>
				<? } ?>
			<? endforeach; ?>
		</select>
	</div>

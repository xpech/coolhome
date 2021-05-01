<? foreach($objects as $object) { 
	if ($object) { ?>
<form>
	<div class="card">
		<div class="card-header"><?= $object->info() ?> [<?= get_class($object) ?> - <?= $object->dbtable() ?>]</div>
		<div class="card-body form-row">		
			<? foreach($object->dbFields() as $field => $type) {
				switch($type) {

					default:
					$this->view('templates/form/text',[
						'title' => $field,
						'name' => $field,
						'value' => $object->$field,
						'class' => 'col-3'
					]);
				}
			} ?>
		</div>
	</div>
</form>
<? }}
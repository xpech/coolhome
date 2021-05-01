<?

?>
<div class="card">
	<div class="card-header">Fimwares</div>
	<div class="card-body">
		<table class="table datatable table-sm">
			<thead>
				<tr>
					<th>Plateforme</th>
					<th>Logiciel</th>
					<th>Date</th>
					<th></th>
				</tr>
				
			</thead>
			<tbody>
				<? 
				$path = APPPATH.'/firmwares/';
				foreach(scandir($path) as $ptf) { 
					$sp = $path.$ptf;
					if (substr($ptf,0,1) != '.' && is_dir($path.$ptf)) {
						foreach(scandir($sp) as $firm) { 
							if (is_file($sp.'/'.$firm)) {
								$df = filemtime($sp.'/'.$firm);
					?>
				<tr>
					<td><?= $ptf ?></td>
					<td><?= $firm ?></td>
					<td><?= date("d/m/Y H:i:s",$df) ?></td>
					<td><a href="/devices/dl/<?= $ptf ?>/<?= $firm ?>">télécharger</a></td>
				</tr>
				<? }}}} ?>
			</tbody>
		</table>		
	</div>
</div>

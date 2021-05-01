<?

?>
<div class="card">
	<div class="card-header">Satellites</div>
	<div class="card-body">
		<table class="table datatable table-sm">
			<thead>
				<tr>
					<th>Nom</th>
					<th>Uid</th>
					<th>Pièce</th>
					<th>Type</th>
					<th>Référence</th>
					<th>Actif</th>
					<th>Acces local</th>
					<th>Détails</th>
				</tr>
				
			</thead>
			<tbody>
				<? foreach(CDevice::forUser(CUser::user()->id) as $dev) { ?>
				<tr>
					<td><a href="/device/index/<?= $dev->id ?>"><?= $dev->title ?></a></td>
					<td><?= $dev->uuid ?></td>
					<td><? if ($r= $dev->room()) echo $r->info(); ?></td>
					<td><?= $dev->kind ?></td>
					<td><? if ($dev->reference) { ?><i class="fas fa-check"></i><? } ?></td>
					<td><?= $dev->isActive() ?></td>
					<td><a href="http://sweethome_sat_<?= $dev->uuid ?>.local" target="_blanck"><i class="fas fa-network-wired"></i></a></td>
					<td><a href="/device/index/<?= $dev->id ?>"><i class="fas fa-eye"></i></a></td>
				</tr>
				<? } ?>
			</tbody>
		</table>		
	</div>
</div>

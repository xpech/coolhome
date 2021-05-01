<div class="card">
	<div class="card-body">
		<table class="table">
			<thead>
				<tr>
					<th>actions</th>
					<th>email</th>
					<th>actions</th>
				</tr>
			</thead>
			<tbody>
				<? foreach(CUser::objects() as $u) { ?>
				<tr>
					<th><?= $u->info() ?></th>
					<th><?= $u->email ?></th>
					<td><a href="/admin/ubiquity/<?= $u->id ?>">ubiquity</a></td>
				</tr>

				<? } ?>
			</tbody>
			
		</table>
	</div>
</div>
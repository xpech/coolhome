<main class="main">
	<?php if ($breadcrumb) { ?>
	<nav class="breadcrumb position-fixed " style="width: 100%; z-index: 1000;">
		<? foreach($breadcrumb as $item) { ?>
		<a class="breadcrumb-item" href="<?= $item['url'] ?>" <? if (isset($item['title'])) { ?>title="<?= $item['title'] ?>"<? } ?>><?= $item['text'] ?></a>
		<?php } ?>

		<?php if ($breadcrumbmenus && count($breadcrumbmenus) > 0) { ?>
		<li class="breadcrumb-menu d-md-down-none">
		<div class="btn-group" role="group" aria-label="Button group">
			<?	foreach($breadcrumbmenus as $m) { ?>
				<a class="btn" href="<?= $m['url'] ?>" <? if (isset($m['title'])) { ?>title="<?= $m['title'] ?>"<? } ?>>
					<? if ($m['icon']) echo $m['icon'] .  "&nbsp;"; 
					echo $m['text']; ?>
					</a>
			<?	} ?>
		</div>
		</li>
		<? } ?>
	</nav>
	<?php } ?>
	<div class="container-fluid" style="margin-top: 55px;">
		<?php if ($messages) {
			if (true) {
				foreach($messages as $m)
					{ ?><div class="alert <?= $m['class'] ?>" role="alert"><?=  $m['txt'] ?></div><?php } 
			} else { ?>
			<script>
				jQuery(function(){ <?php
					foreach($messages as $m) { ?>
						toastr["<?= $m['toast']  ?>"]("<?=  $m['txt'] ?>"); <? } ?>
					});
			</script><? }}  ?>
					
		<?php if ($tabmenu) {  ?>
			
			<ul class="nav nav-tabs" id="" role="tablist">
				<? foreach($tabmenu as $k => $m) { ?>
					<? if (isset($m['subitems'])) { ?>
				<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle <? if ($k == $current_tabmenus) echo "active";  ?>"
							href="#" id="<?= $k ?>_btn"
							role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<? if (isset($m['icon'])) echo $m['icon']; ?> <?= $m['text'] ?></a>

						<div class="dropdown-menu" aria-labelledby="<?= $k ?>_btn">
							<? foreach($m['subitems'] as $s) { ?>
							<a class="dropdown-item" href="<?= $s['url'] ?>"><?= $s['text'] ?></a><? } ?>
						</div>
					<? } else { ?>
				<li class="nav-item">
					<a class="nav-link <? if ($k == $current_tabmenus) echo "active";  ?>"
						id="tab_menu_<?= $k ?>"
						href="<?= $m['url'] ?>"
						role="tab"
						title="<?= (isset($m['title']) ? $m['title'] : $m['text']) ?>"
						aria-controls="<?= $k ?>"
						aria-selected="true">
						<? if (isset($m['icon'])) echo $m['icon']; ?> <?= $m['text'] ?></a>
					<? } ?>
				</li><? } ?>
			</ul>
		<?php } ?>
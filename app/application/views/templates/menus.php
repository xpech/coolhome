<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!-- MENUS -->
	<div class="sidebar sidebar-dark sidebar-fixed sidebar-self-hiding-md" id="sidebar">
      <div class="sidebar-brand d-lg-down-none">
        <svg class="sidebar-brand-full" width="118" height="46" alt="CoreUI Logo">
          <use xlink:href="assets/brand/coreui.svg#full"></use>
        </svg>
        <svg class="sidebar-brand-narrow" width="46" height="46" alt="CoreUI Logo">
          <use xlink:href="assets/brand/coreui.svg#signet"></use>
        </svg>
      </div>
      <ul class="sidebar-nav" data-coreui="navigation">
		<?php 

		if (false) {foreach($menus as $top) {
			if (isset($top['subs'])) { ?>
		<li class="nav-item nav-dropdown  <?php if ($main['name'] == $top['name']) echo ' open active bg-irts'; if (isset($top['class'])) echo ' '.$top['class'];  ?>">
			<a class="nav-link nav-dropdown-toggle <?php if (isset($top['class'])) echo $top['class']; ?>" 
				href="<?= (isset($top['url']) ? $top['url'] : '#') ?>">
				<?php if (isset($top['icon'])) echo $top['icon']; ?><?= $top['name'] ?></a>
			<ul class="nav-dropdown-items">
				<?php foreach($top['subs'] as $m) { ?>
				<li class="nav-item <?php if (isset($menu) && $menu['name'] == $top['name']) echo ' active'; else "notactive"; ?>">
					<a class="nav-link <?php if (isset($m['class'])) echo $m['class']; ?>"
						<?php if (isset($m['title'])) echo 'title="' .$m['title']. '"'; ?> href="<?= $m['url'] ?>">
						<?php if (isset($m['icon'])) echo $m['icon']; ?>  <?= $m['name'] ?></a>
				</li><?php } ?>
			</ul>
		</li>
		<?php } else { ?>
		<li class="nav-item">
			<a class="nav-link <?php if (isset($top['class'])) echo $top['class']; ?>" href="<?= $top['url'] ?>">
				<?php if (isset($top['icon'])) echo $top['icon']; ?> <?=  $top['name'] ?>
			</a>
		</li>
		<?php }}} ?>



        <li class="nav-item"><a class="nav-link" href="index.html">
            <svg class="nav-icon">
              <use xlink:href="node_modules/@coreui/icons/sprites/free.svg#cil-speedometer"></use>
            </svg> Dashboard<span class="badge badge-sm bg-info ms-auto">NEW</span></a></li>
        <li class="nav-title">Theme</li>
        <li class="nav-item"><a class="nav-link" href="colors.html">
            <svg class="nav-icon">
              <use xlink:href="node_modules/@coreui/icons/sprites/free.svg#cil-drop"></use>
            </svg> Colors</a></li>
        <li class="nav-item"><a class="nav-link" href="typography.html">
            <svg class="nav-icon">
              <use xlink:href="node_modules/@coreui/icons/sprites/free.svg#cil-pencil"></use>
            </svg> Typography</a></li>
        <li class="nav-group"><a class="nav-link nav-group-toggle" href="#">
            <svg class="nav-icon">
              <use xlink:href="node_modules/@coreui/icons/sprites/free.svg#cil-cursor"></use>
            </svg> Buttons</a>
          <ul class="nav-group-items">
            <li class="nav-item"><a class="nav-link" href="buttons/buttons.html"><span class="nav-icon"></span> Buttons</a></li>
            <li class="nav-item"><a class="nav-link" href="buttons/button-group.html"><span class="nav-icon"></span> Buttons Group</a></li>
            <li class="nav-item"><a class="nav-link" href="buttons/dropdowns.html"><span class="nav-icon"></span> Dropdowns</a></li>
          </ul>
        </li>
        <li class="nav-item"><a class="nav-link" href="charts.html">
            <svg class="nav-icon">
              <use xlink:href="node_modules/@coreui/icons/sprites/free.svg#cil-chart-pie"></use>
            </svg> Charts</a></li>
        <li class="nav-group"><a class="nav-link nav-group-toggle" href="#">
            <svg class="nav-icon">
              <use xlink:href="node_modules/@coreui/icons/sprites/free.svg#cil-star"></use>
            </svg> Icons</a>
          <ul class="nav-group-items">
            <li class="nav-item"><a class="nav-link" href="icons/coreui-icons-free.html"> CoreUI Icons<span class="badge badge-sm bg-success ms-auto">Free</span></a></li>
            <li class="nav-item"><a class="nav-link" href="icons/coreui-icons-brand.html"> CoreUI Icons - Brand</a></li>
            <li class="nav-item"><a class="nav-link" href="icons/coreui-icons-flag.html"> CoreUI Icons - Flag</a></li>
          </ul>
        </li>
        <li class="nav-group"><a class="nav-link nav-group-toggle" href="#">
            <svg class="nav-icon">
              <use xlink:href="node_modules/@coreui/icons/sprites/free.svg#cil-bell"></use>
            </svg> Notifications</a>
          <ul class="nav-group-items">
            <li class="nav-item"><a class="nav-link" href="notifications/alerts.html"><span class="nav-icon"></span> Alerts</a></li>
            <li class="nav-item"><a class="nav-link" href="notifications/badge.html"><span class="nav-icon"></span> Badge</a></li>
            <li class="nav-item"><a class="nav-link" href="notifications/modals.html"><span class="nav-icon"></span> Modals</a></li>
          </ul>
        </li>
        <li class="nav-item"><a class="nav-link" href="widgets.html">
            <svg class="nav-icon">
              <use xlink:href="node_modules/@coreui/icons/sprites/free.svg#cil-calculator"></use>
            </svg> Widgets<span class="badge badge-sm bg-info ms-auto">NEW</span></a></li>
        <li class="nav-divider"></li>
        <li class="nav-title">Extras</li>
        <li class="nav-item mt-auto"><a class="nav-link nav-link-success" href="https://coreui.io" target="_top">
            <svg class="nav-icon">
              <use xlink:href="node_modules/@coreui/icons/sprites/free.svg#cil-cloud-download"></use>
            </svg> Download CoreUI</a></li>
        <li class="nav-item"><a class="nav-link nav-link-danger" href="https://coreui.io/pro/" target="_top">
            <svg class="nav-icon">
              <use xlink:href="node_modules/@coreui/icons/sprites/free.svg#cil-layers"></use>
            </svg> Try CoreUI
            <div class="fw-semibold">PRO</div>
          </a></li>
      </ul>

      <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button>
    </div>
<!-- /MENUS -->

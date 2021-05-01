<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
		<header class="app-header navbar" <? if (CDebug::on()) { ?>style="background-color: #f5a162" <? } ?>>
			<button class="navbar-toggler sidebar-toggler d-lg-none mr-auto" type="button" data-toggle="sidebar-show">
				<span class="navbar-toggler-icon"></span>
			</button>
			<a class="navbar-brand" href="/" title="Retour à l'accueil">
				<img class="navbar-brand-full" src="/img/brand/logo.svg" width="89" height="25" alt="LiiNA">
				<img class="navbar-brand-minimized" src="/img/brand/sygnet.svg" width="30" height="30" alt="LiiNA">
			</a>
			<button class="navbar-toggler sidebar-toggler d-md-down-none" type="button" data-toggle="sidebar-lg-show"
				title="Masquer / Afficher le menu de gauche">
				<span class="navbar-toggler-icon"></span>
			</button>
			
			<ul class="nav navbar-nav d-md-down-none" id="nav-bookmarks">
				<? if (CDebug::on()) { ?>
				<li class="nav-item px-3  text-white font-weight-bold">
					DEBUG
				</li>
				<? } ?>
				
				
			</ul>	
			
			<ul class="nav navbar-nav ml-auto">
				<li class="nav-item dropdown">
					<input class="form-control mr-sm-2" type="search" placeholder="Rechercher" aria-label="Search"
						data-toggle="dropdown"
						id="globalsearch"/>
					<div class="dropdown-menu dropdown-menu-right" id="globalsearchresults" aria-labelledby="#globalsearch"></div>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link" data-toggle="dropdown" href="#" role="button"
						 title="Raccourcis"
						 aria-haspopup="true" aria-expanded="false">
						<i class="far fa-bookmark"></i>
					</a>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" 
						title="Paramètres du compte utilisateur"
						aria-expanded="false">
						<i class="far fa-user"></i>
					</a>
					<div class="dropdown-menu dropdown-menu-right">
						<div class="dropdown-header text-center">
							<strong>Paramètres</strong>
						</div>
						<a class="dropdown-item" href="/login//">
							<i class="fa fa-user"></i> <?= CUser::user()->getInfo() ?></a>
						<a class="dropdown-item" href="/login/exit">
							<i class="fa fa-lock"></i> Déconnexion</a>
					</div>
				</li>
			</ul>
			<div>
			<button class="navbar-toggler aside-menu-toggler d-md-down-none" type="button" data-toggle="aside-menu-lg-show">
				<span class="navbar-toggler-icon"></span>
			</button>
			<button class="navbar-toggler aside-menu-toggler d-lg-none" type="button" data-toggle="aside-menu-show">
				<span class="navbar-toggler-icon"></span>
			</button>
		</header>
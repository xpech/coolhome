<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-md-8">
					<div class="card-group">
						<div class="card mt-auto">
							<form method="post" autocomplete="new-password">
								<input type="hidden" name="secret" value="<?= $secret ?>" id="secret"/>
								<div class="card-header bg-irts">
									<img src="/img/logo.svg" style="width: 100%"/>
								</div>
								<div class="card-body">
									<h1>Connexion</h1>
									<p class="text-muted"></p>
									<div class="input-group mb-3">
										<div class="input-group-prepend">
											<span class="input-group-text">
												<i class="fas fa-user"></i>
											</span>
										</div>
										<input class="form-control" type="text" name="l_<?= $secret ?>" placeholder="email" autocomplete="off"/>
									</div>
									<div class="input-group mb-4">
										<div class="input-group-prepend">
											<span class="input-group-text">
												<i class="fas fa-key"></i>
											</span>
										</div>
										<input class="form-control" type="password"  name="p_<?= $secret ?>" placeholder="Mot de passe" autocomplete="new-password" id="pwd"/>
										<div class="input-group-append" onclick="$('#pwd').attr('type','text'); setTimeout(function(){$('#pwd').attr('type','password')},1500);">
											<span class="input-group-text">
												<i class="fas fa-eye"></i>
											</span>
										</div>
									</div>
									<div class="row border border-light">
										<div class="col-4">
											<a class="btn btn-link" href="/login/recover">Mot de passe perdu ?</a>
										</div>
										<div class="col-4">
											<a class="btn btn-link" href="/login/create">Créer un compte</a>
										</div>
										<div class="col-4">
											<button class="btn btn-primary px-4" type="submit">Connexion</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>

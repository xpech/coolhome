<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-md-8">
					<div class="card-group">
						<div class="card p-4">
							<form method="post">
								<div class="card-body">
									<h1>Demande de reinitialisation du mot de passe</h1>
									<p class="text-muted">Pour demander un nouveau mot de passe, saississez votre email :</p>
									<div class="input-group mb-3">
										<div class="input-group-prepend">
											<span class="input-group-text">
												<i class="icon-user"></i>
											</span>
										</div>
										<input class="form-control" type="text" name="email" placeholder="votre email"
										 value="<?= CHtml::val('email')?>"
										/>
									</div>
									<div class="row">
										<div class="col-6">
											<button class="btn btn-primary px-4" type="submit" name="action" value="recovermail">Envoyer la demande</button>
										</div>
									</div>
									<? if (CHtml::val('action') == 'recovermail')  { 
										$u = CUtilisateur::objectWithEmail(CHtml::val('email'));
										if ($u)
										{
											
											echo "OK";
										} else echo "Mail inconnu";
									}?>
									
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>

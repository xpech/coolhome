<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
	<form method="post">
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-md-8">
					<div class="card-group">
						<div class="card m-4">
							<div class="card-header">Mise à jour du mot de passe</div>
							<div class="card-body">
								<input type="hidden" name="seckey" value="<?= $key ?>"/>
								<div class="form-group">
									<label for="email">email :</label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text">
												<i class="fas fa-at"></i>
											</span>
										</div>
										<input class="form-control" type="text" name="email" readonly
											id="email" placeholder="votre email"
										 value="<?= $email ?>"/>
									</div>
								</div>
								<div class="form-group">
									<label for="pwd1">Nouveau mot de passe</label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text">
												<i class="fas fa-key"></i>
											</span>
										</div>
										<input class="form-control" type="password" name="pwd1" placeholder="" id="pwd1"
										 value=""/>
									</div>
								</div>
								<div class="form-group">
									<label for="pwd2">Confirmer le mot de passe</label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text">
												<i class="fas fa-key"></i>
											</span>
										</div>
										<input class="form-control" type="password" name="pwd2" placeholder="" id="pwd2"
										 value=""/>
									</div>
								</div>
								<div class="row">
									<div class="col-6">
									</div>
								</div>
							</div>
							<div class="card-footer">
								<button class="btn btn-primary px-4" type="submit" id="submit" name="action" value="SendUpdatePwd">Enregistrer</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
	<script>
		jQuery(function(){
			$('#submit').click(function(evt){
				if ($('#pwd1').val() === '' || ($('#pwd1').val() != $('#pwd2').val()))
				{
					console.log('ici');
					evt.stopPropagation();
					$('#pwd1').addClass('is-invalid');
					$('#pwd2').addClass('is-invalid');
					return false;
				}

			});
		});
	</script>

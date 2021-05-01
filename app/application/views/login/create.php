<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
	<form method="post">
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-md-8">
					<div class="card-group">
						<div class="card m-4">
							<div class="card-header">Nouveau compte</div>
							<div class="card-body">
								<div class="form-group">
									<label for="email">Saississez votre email :</label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text">
												<i class="fas fa-at"></i>
											</span>
										</div>
										<input class="form-control" type="text" name="email" 
											id="email" placeholder="votre email"
										 value="<?= CHtml::val('email')?>"/>
									</div>
								</div>
								<div class="form-group">
									<label for="securebot">Trois première lettre de votre email (anti robot)</label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text">
												<i class="fas fa-robot"></i>
											</span>
										</div>
										<input class="form-control" type="text" name="securebot" placeholder="les trois première lettre de votre email" id="securebot"
										 value=""/>
									</div>
								</div>
								<div class="row">
									<div class="col-6">
									</div>
								</div>
							</div>
							<div class="card-footer">
								<button class="btn btn-primary px-4" type="submit" name="action" value="SendCreateMail">Recevoir le mail de création</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>

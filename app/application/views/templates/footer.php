<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
          			</div>
        		</main>
				<?php if ($showfooter) { ?>
				<div class="modal" id="global_spinner" data-backdrop="static" data-keyboard="false" tabindex="-1">
					<div class="modal-dialog modal-sm">
						<div class="modal-content d-flex justify-content-center p-5">
							<div class="col text-center">
								Traitement en cours<br/>
								<div class="spinner-border text-primary" role="status">
									<span class="sr-only">Traitement en cours</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal" id="global_modal" data-backdrop="static" data-keyboard="false" tabindex="-1">
					<div class="modal-dialog modal-lg"></div>
				</div>
					
				<footer class="c-footer">
					<div class="ml-auto">
						<span>Ecrit par </span>
						<a href="http://www.expert-solutions.fr">&copy; experts-solutions sarl</a>
					</div>
				</footer>
				<?php } ?> 
			</div>
	    </div>
		<!-- Plugins and scripts required by this view-->
		<script src="/vendor/coreui/coreui/dist/js/coreui.bundle.min.js"></script>
		
		<script src="/vendor/moment/moment/moment.js"></script>
		<script src="/vendor/moment/moment/locale/fr.js"></script>


		<script src="https://cdn.jsdelivr.net/npm/chart.js@3.1.1/dist/chart.min.js" integrity="sha256-lISRn4x2bHaafBiAb0H5C7mqJli7N0SH+vrapxjIz3k=" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment"></script>


		<script src="/vendor/datatables/datatables/media/js/jquery.dataTables.min.js"></script>
		<script src="/vendor/datatables/datatables/media/js/dataTables.bootstrap4.min.js"></script>
		<script src="/vendor/fortawesome/font-awesome/js/all.js"></script>
		<script src="/js/main.js"></script>
		<?php if ($messages) {
			if (true) {
				foreach($messages as $m)
					{ ?><div class="alert <?= $m['class'] ?>" role="alert"><?=  $m['txt'] ?></div><?php } 
			} else { ?>
		<script>
			jQuery(function(){ <?php
				foreach($messages as $m) { ?>
					toastr["<?= $m['toast']  ?>"]("<?=  $m['txt'] ?>"); <?php } ?>
				});
		</script><?php }}  ?>
	</body>
</html>

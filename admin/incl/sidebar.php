<?php function sidebar_row($page, $icon, $label, $clr = ''){ ?>
	<li class="nav-item">
		<a href="<?=$page?>" class="nav-link py-3 px-2 <?php if(MENU_SEL == $page){ ?>active<?php } ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-original-title="<?=$label?>">
			<i class="<?=$clr?> bi bi-<?=$icon?> fs-1"></i>
		</a>
	</li>
<?php } ?>

<div class="col-sm-auto bg-light sticky-top">
  <div class="d-flex flex-sm-column flex-row flex-nowrap bg-light sticky-top">
    <!--<a href="/" class="d-block p-3 link-dark text-decoration-none" title="" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-original-title="Icon-only">
      <i class="bi-bootstrap fs-1">Buckets</i>
    </a>
	  <hr>-->
		<p>&nbsp;</p>
		 <div class="d-flex flex-sm-column flex-row flex-nowrap bg-light align-items-center sticky-top">        
        <ul class="nav nav-pills nav-flush flex-sm-column flex-row flex-nowrap mb-auto mx-auto text-center align-items-center">
					<?php
						sidebar_row('index.php', 			'house', 						'Home');
						sidebar_row('access.php', 		'people', 					'Access', 'text-info');
						sidebar_row('databases.php', 	'database', 				'Databases', 'text-info');
						sidebar_row('services.php', 	'app-indicator', 					'Services', 'text-info');
						sidebar_row('layers.php',			'layers',						'Layers', 'text-info');
						sidebar_row('../index.php',		'box-arrow-right',	'Front End', 'text-info');
						sidebar_row('../logout.php',	'door-closed', 			'Logout', 'text-info');
					?>
        </ul>
		</div>
	</div>
</div>
<script>
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
})
</script>




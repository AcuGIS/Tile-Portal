<?php
  session_start(['read_and_close' => true]);
	require('incl/const.php');
  require('class/database.php');
	require('class/table.php');
	require('class/user.php');
	require('class/access_group.php');

	if(!isset($_SESSION[SESS_USR_KEY]) || $_SESSION[SESS_USR_KEY]->accesslevel != 'Admin') {
    header('Location: ../login.php');
    exit;
  }
		
	$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
	$dbconn = $database->getConn();	
	
	$groups = null;
	$grp_obj = new access_group_Class($dbconn, $_SESSION[SESS_USR_KEY]->id);
	
	$tab = empty($_GET['tab']) ? 'user' : $_GET['tab'];
	$obj = null;
	
	switch($tab){
		case 'user':	$obj = new user_Class($dbconn,					$_SESSION[SESS_USR_KEY]->id);
									$groups = $grp_obj->getArr();																					break;
		case 'group': $obj = new access_group_Class($dbconn,	$_SESSION[SESS_USR_KEY]->id); break;
		default:		die('Error: Invalid tab'); break;
	}
	$rows = $obj->getRows();
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<title>Quail Layer Server</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">	

	<?php include("incl/meta.php"); ?>
	<link href="dist/css/table.css" rel="stylesheet">

	<script type="text/javascript">
	var edit_row = null;
	$(document).ready(function() {
		$('[data-toggle="tooltip"]').tooltip();
		
		$(document).on("click", ".add-modal", function() {
			edit_row = null;
			$('#addnew_modal').modal('show');
			$('#btn_create').html('Create');
			
			$('#id').val(0);
			
			if($('#group_id').length > 0){	// if user tab
				$('#group_id').trigger('change');	// trigger change to reload groups
			}
		});
	});
	</script>
	<script src="dist/js/access_<?=$tab?>.js"></script>
</head>
 
<body>

	<div class="container-fluid">
		<div class="row">
			<div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 bg-dark">
				<?php const MENU_SEL = 'access.php';
					include("incl/sidebar.php");
				?>
			</div>

			<div class="col-sm p-3 min-vh-100">
				<div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
						<div class="row align-items-center">
								<div class="col-6">
									<h2 class="mb-0">Access</h2>
								</div>
								<div class="col-6">
										<div class="text-end upgrade-btn">
											<a class="btn btn-primary text-white add-modal" role="button" aria-pressed="true"><i class="bi bi-plus-square"></i> Add New</a>
									</div>
								</div>
						</div>
				</div>
				
				<ul class="nav nav-tabs">
					<li class="nav-item"><a class="nav-link <?php if($tab == 'user') { ?> active <?php } ?>" href="access.php?tab=user"><i class="bi bi-arrow-right-square"></i>&nbsp;&nbsp;Users</a> </li>
					<li class="nav-item"><a class="nav-link <?php if($tab == 'group') { ?> active <?php } ?>" href="access.php?tab=group"><i class="bi bi-arrow-right-square"></i>&nbsp;&nbsp;Groups</a></li>
				</ul>
				
	<?php
		if(is_file('incl/access_'.$tab.'.php')){
			require('incl/access_'.$tab.'.php');
		}else{
			die('Error: Invalid tab!');
		}
	?>

		<footer class="footer text-center"></footer>
	</div>
</div>
<script>var sortTable = new DataTable('#sortTable', { paging: false });</script>
</body>

</html>

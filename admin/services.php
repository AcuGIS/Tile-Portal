<?php
  session_start(['read_and_close' => true]);
	require('incl/const.php');
  require('class/database.php');
	require('class/table.php');
	require('class/service.php');
	require('class/backend.php');
	require('class/pglink.php');
	require('class/access_group.php');

	if(!isset($_SESSION[SESS_USR_KEY]) || $_SESSION[SESS_USR_KEY]->accesslevel != 'Admin') {
    header('Location: ../login.php');
    exit;
  }

	$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
	$dbconn = $database->getConn();
	
	$bknd = new backend_Class();
	
	$obj 		 = new service_Class($dbconn, $_SESSION[SESS_USR_KEY]->id);
	$rows = $obj->getRows();
	
	$pg_obj = new pglink_Class($dbconn, $_SESSION[SESS_USR_KEY]->id);
	$pgrows = $pg_obj->getArr();
	
	$grp_obj = new access_group_Class($dbconn, $_SESSION[SESS_USR_KEY]->id);
	$groups = $grp_obj->getArr();
	
	$tab = empty($_GET['tab']) ? 'pg_tileserv' : $_GET['tab'];
	$obj = null;
	
	switch($tab){
		case 'pg_tileserv':			break;
		case 'pg_featureserv': 	break;
		default:		die('Error: Invalid tab'); break;
	}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
	<head>
	<title>PG Tile Services</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">

	<?php include("incl/meta.php"); ?>
	<link href="dist/css/table.css" rel="stylesheet">
	<script src="dist/js/service.js"></script>
</head>
 
<body>
	<div class="container-fluid">
		<div class="row">
			<div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 bg-dark">
				<?php const MENU_SEL = 'services.php';
					include("incl/sidebar.php");
				?>
			</div>

			<div class="col-sm p-3 min-vh-100">
				<div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
						<div class="row align-items-center">
								<div class="col-6">
									<h2 class="mb-0">Services</h2>
								</div>
								<div class="col-6">
										<div class="text-end upgrade-btn">
											<a class="btn btn-primary text-white add-modal" role="button" aria-pressed="true"><i class="bi bi-plus-square"></i> Add New</a>
									</div>
								</div>
						</div>
				</div>
				
				<ul class="nav nav-tabs">
					<li class="nav-item"><a class="nav-link <?php if($tab == 'pg_tileserv') { ?> active <?php } ?>" href="services.php?tab=pg_tileserv"><i class="bi bi-arrow-right-square"></i>&nbsp;&nbsp;TileServ</a> </li>
					<li class="nav-item"><a class="nav-link <?php if($tab == 'pg_featureserv') { ?> active <?php } ?>" href="services.php?tab=pg_featureserv"><i class="bi bi-arrow-right-square"></i>&nbsp;&nbsp;FeatureServ</a></li>
				</ul>
				
					<div class="table-responsive">
						<table class="table table-bordered" id="sortTable">

							<thead>
								<tr>
									<th data-name="name">Name</th>
									<th data-name="enabled">Enabled</th>
									<th data-name="active">Active</th>
									<th data-name="pid">PID</th>
									<th data-name="cpu">CPU</th>
									<?php if($tab == 'pg_featureserv'){ ?>
									<th data-name="port">Port</th>
									<?php } ?>
									<th data-editable='false' data-action='true'>Actions</th>
								</tr>
							</thead>

							<tbody> <?php while($row = pg_fetch_object($rows)) {
									$svc = $tab.'@'.$row->id;
									$status = $bknd->service_status($svc);
									$enabled = 'disabled';
									if(!empty($status['enabled'])){
										$enabled = (strstr($status['enabled'], 'enabled') !== false) ? 'checked' : '';
									}
								?>
								<tr data-id="<?=$row->id?>" data-svc="<?=$svc?>"align="left">
									<td><?=$row->name?></td>
									<td>
										<input type="checkbox" class="disable" <?=$enabled?>/>
									</td>
									<td><?=$status['active']?></td>
									<?php if(strstr($status['active'], 'running')){ ?>
										<td><?=$status['main pid']?></td>
										<td><?=$status['cpu']?></td>
										<?php if($tab == 'pg_featureserv'){ ?>
										<td><?=9000 + $row->id?></td>
										<?php } ?>
										<td>
											<a class="stop" 		title="Stop"		data-toggle="tooltip">	<i class="text-danger bi bi-stop-fill"></i></a>
											<a class="restart"	title="Restart" data-toggle="tooltip">	<i class="text-primary bi bi-bootstrap-reboot"></i></a>
									<?php }else { ?>
										<td></td>
										<td></td>
										<?php if($tab == 'pg_featureserv'){ ?>
										<td><?=9000 + $row->id?></td>
										<?php } ?>
										<td>
											<a class="start" 	title="Start"		data-toggle="tooltip">	<i class="text-success bi bi-play-fill"></i></a>
									<?php } ?>
										
											<a class="edit" href="edit_toml.php?svc=<?=$tab?>&id=<?=$row->id?>" title="Edit" data-toggle="tooltip"><i class="text-warning bi bi-pencil-square"></i></a>
											<a class="delete" title="Delete" data-toggle="tooltip"><i class="text-danger bi bi-x-square"></i></a>
										</td>
								</tr> <?php } ?>
							</tbody>
						</table>

					    <div class="col-4"><p>&nbsp;</p>

								<div class="alert alert-success">
								   <strong>Note:</strong> Control PG tile/feature services. <a href="https://tile-portal.docs.acugis.com/en/latest/services.html" target="_blank"> Documentation</a>
								</div>
							</div>
					</div>
				</div>
		</div>
	</div>

<div id="addnew_modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Create Service</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			
			<div class="modal-body" id="addnew_modal_body">
				<form id="service_form" class="border shadow p-3 rounded"
							action=""
							method="post"
							enctype="multipart/form-data"
							style="width: 450px;">

					<input type="hidden" name="action" value="save"/>
					<input type="hidden" name="id" id="id" value="0"/>
					
					<div class="form-group">
						<label for="name" class="form-label">Name</label>
						<input class="form-control" type="text" name="name" id="name" required>
					</div>
					
					<div class="form-group">
						<div class="input-group">
							<select name="pglink_id" id="pglink_id" required>
								<?php $sel = 'selected';
								foreach($pgrows as $k => $v){ ?>
									<option value="<?=$k?>" <?=$sel?>><?=$v?></option>
								<?php $sel = ''; } ?>
							</select>
							<span class="input-group-text"><i class="bi bi-database">Database</i></span>
						</div>
					</div>
					
					<div class="form-group">
						<div class="input-group">
							<select name="group_id[]" id="group_id" multiple required>
								<?php $sel = 'selected';
								foreach($groups as $k => $v){ ?>
									<option value="<?=$k?>" <?=$sel?>><?=$v?></option>
								<?php $sel = ''; } ?>
							</select>
							<span class="input-group-text"><i class="bi bi-shield-lock">Access Groups</i></span>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="activate btn btn-secondary" id="btn_create" data-dismiss="modal">Create</button>
			</div>
		</div>
	</div>
</div>


<script>var sortTable = new DataTable('#sortTable', { paging: false });</script>
</body>
</html>

<?php
  session_start(['read_and_close' => true]);
	require('incl/const.php');
  require('class/database.php');
	require('class/table.php');
	require('class/layer.php');
	require('class/service.php');
	require('class/access_group.php');

	if(!isset($_SESSION[SESS_USR_KEY]) || $_SESSION[SESS_USR_KEY]->accesslevel != 'Admin') {
    header('Location: ../login.php');
    exit;
  }
		
	$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
	$dbconn = $database->getConn();	
	
	$obj 		 = new layer_Class($dbconn,				 $_SESSION[SESS_USR_KEY]->id);
	$svc_obj = new service_Class($dbconn,				 $_SESSION[SESS_USR_KEY]->id);
	$grp_obj = new access_group_Class($dbconn, $_SESSION[SESS_USR_KEY]->id);
	
	$rows   = $obj->getRows();
	$groups = $grp_obj->getArr();
	$svcs   = $svc_obj->getArr();
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<title>Quail Layer Server</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">	

	<?php include("incl/meta.php"); ?>
	<link href="dist/css/table.css" rel="stylesheet">
	<script src="dist/js/layer.js"></script>
</head>
 
<body>

	<div class="container-fluid">
		<div class="row">
			<div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 bg-dark">
				<?php const MENU_SEL = 'layers.php';
					include("incl/sidebar.php");
				?>
			</div>

			<div class="col-sm p-3 min-vh-100">
				<div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
						<div class="row align-items-center">
								<div class="col-6">
									<h2 class="mb-0">Layers</h2>
								</div>
								<div class="col-6">
										<div class="text-end upgrade-btn">
											<a class="btn btn-primary text-white add-modal" role="button" aria-pressed="true"><i class="bi bi-plus-square"></i> Add New</a>
									</div>
								</div>
						</div>
				</div>
				
					<div class="table-responsive">
			<table class="table table-bordered" id="sortTable">
				<thead>
					<tr>
						<th data-name="id" data-editable='false'>ID</th>
						<th data-name="svc_id">Service</th>
						<th data-name="name">Name</th>
						<th data-name="public">Public</th>
						<th data-name="group_id" data-type="select">Access Group</th>
						<th data-editable='false' data-action='true'>Actions</th>
					</tr>
				</thead>

				<tbody> <?php while($row = pg_fetch_object($rows)) {
					$row_grps = $grp_obj->getByKV('layer', $row->id);
					$public = ($row->public == 't') ? 'yes' : 'no';
					?>
					<tr data-id="<?=$row->id?>" align="left">
						<td><?=$row->id?></td>
						<td data-value="<?=$row->svc_id?>"><?=$svcs[$row->svc_id]?></td>
						<td data-order="<?=$row->name?>"><a href="../<?=$row->name?>.html"><?=$row->name?></a></td>
						<td><?=$public?></td>
						<td data-value="<?=implode(',', array_keys($row_grps))?>">
							<?=implode(',', array_values($row_grps))?>
						</td>

						<td>
							<a class="edit" title="Edit" data-toggle="tooltip"><i class="text-warning bi bi-pencil-square"></i></a>
							<a class="delete" title="Delete" data-toggle="tooltip"><i class="text-danger bi bi-x-square"></i></a>
						</td>
					</tr> <?php } ?>
				</tbody>
			</table>           
		</div>

		<div class="row">
		    <div class="col-8"><p>&nbsp;</p>

					<div class="alert alert-success">
					   <strong>Note:</strong> Manage your layers from here. <a href="https://tile-portal.docs.acugis.com/en/latest/layers.html" target="_blank">Documentation</a>
					</div>
				</div>
		</div>

		<footer class="footer text-center"></footer>
	</div>
</div>
</div>

<div id="addnew_modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Create Layer</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			
			<div class="modal-body" id="addnew_modal_body">
				<form id="layer_form" class="border shadow p-3 rounded"
							action=""
							method="post"
							enctype="multipart/form-data"
							style="width: 450px;">

					<input type="hidden" name="action" value="save"/>
					<input type="hidden" name="id" id="id" value="0"/>
					
					<div class="form-group">
						<label for="svc_id" class="form-label">Service</label>
						<select class="form-select" name="svc_id" id="svc_id" required>
							<?php $sel = 'selected';
							foreach($svcs as $k => $v){ ?>
								<option value="<?=$k?>" <?=$sel?>><?=$v?></option>
							<?php $sel = ''; } ?>
						</select>
					</div>
					
					<div class="form-group">
						<label for="name" class="form-label">Name</label>
						<select class="form-select" name="name" id="name" required>
						</select>
					</div>

					<div class="form-group">
						<input type="checkbox" name="public" id="public" value="t"/>
						<label for="public" class="form-label">Public</label>						
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

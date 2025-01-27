<?php
  session_start(['read_and_close' => true]);
	require('incl/const.php');
	require('incl/app.php');
  require('class/database.php');
	require('class/table.php');
	require('class/access_group.php');
	require('class/pglink.php');

	function return_bytes($val)
	{
	    $val = trim($val);
	    $num = (int) rtrim($val, 'KMG');
	    $last = strtolower($val[strlen($val) - 1]);

	    switch ($last) {
	        // The 'G' modifier is available
	        case 'g':
	            $num = $num * 1024 * 1024 * 1024;
	            break;
	        case 'm':
	            $num = $num * 1024 * 1024;
	            break;
	        case 'k':
	            $num *= 1024;
	            break;
	    }

	    return $num;
	}

	if(!isset($_SESSION[SESS_USR_KEY]) || $_SESSION[SESS_USR_KEY]->accesslevel != 'Admin') {
    header('Location: ../login.php');
    exit;
  }
		
	$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
	$dbconn = $database->getConn();
	
	$grp_obj = new access_group_Class($dbconn, $_SESSION[SESS_USR_KEY]->id);
	$groups = $grp_obj->getArr();
	
	$obj = new pglink_Class($dbconn, 	$_SESSION[SESS_USR_KEY]->id);
	$rows = $obj->getRows();
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
	<head>
		<title>PG Tile Databases</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
	
		<?php include("incl/meta.php"); ?>
		<link href="dist/css/table.css" rel="stylesheet">

		<script type="text/javascript">
		const post_max_size = <?=return_bytes(ini_get('post_max_size'))?>;
		</script>
		<script src="dist/js/html5_uploader.js"></script>
		<script src="dist/js/database.js"></script>

		<style>
.mt-2 {
    margin-top: .0rem !important;
}
		</style>
	</head>

	<body>
		<div class="container-fluid">
			<div class="row">
				<div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 bg-dark">
					<?php const MENU_SEL = 'databases.php';
						include("incl/sidebar.php");
					?>
				</div>

				<div class="col-sm p-3 min-vh-100">
					<div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
							<div class="row align-items-center">
									<div class="col-6">
										<h2 class="mb-0">Databases</h2>
									</div>
									<div class="col-6">
											<div class="text-end upgrade-btn">
												<a class="btn btn-warning text-white import-modal" role="button" aria-pressed="true"><i class="bi bi-box-arrow-in-up"></i> Create</a>
												<a class="btn btn-primary text-white add-modal" role="button" aria-pressed="true"><i class="bi bi-plus-square"></i> Add New</a>
										</div>
									</div>
							</div>
					</div>

					<div class="table-responsive">
						<table class="table table-bordered" id="sortTable">
							<thead>
								<tr>
									<!--<th data-name="id" data-editable='false'>ID</th>-->
									<th data-name="name">Name</th>
									<th data-name="group_id" data-type="select">Access Group</th>
									<th data-name="host">Host</th>
									<th data-name="port">Port</th>
									<th data-name="schema">Schema</th>
									<th data-name="dbname">Database</th>
									<th data-name="username">Username</th>
									<th data-name="password">Password</th>
									<th data-editable='false' data-action='true'>Actions</th>
								</tr>
							</thead>

							<tbody> <?php while($row = pg_fetch_object($rows)) {
								$row_grps = $grp_obj->getByKV('pglink', $row->id);
								
								$pg_dir = DATA_DIR.'/pg/'.$row->id;
								$name_icon = '';
								if(is_pid_running($pg_dir.'/clone.pid')){					$name_icon = '<a href="tmp_filep.php?f=clone.out" title="Clone in progress ..." data-toggle="tooltip"><i class="text-info bi bi-copy"></i></a>';
								}else if(is_pid_running($pg_dir.'/restore.pid')){	$name_icon = '<a href="tmp_filep.php?f=restore.out" title="Restore in progress ..." data-toggle="tooltip"><i class="text-info bi bi-box-arrow-in-up"></i></a>';
								}else if(is_pid_running($pg_dir.'/backup.pid')){	$name_icon = '<a href="tmp_filep.php?f=backup.out" title="Backup in progress ..." data-toggle="tooltip"><i class="text-info bi bi-box-arrow-down-square"></i></a>';
								}
							?>
								<tr data-id="<?=$row->id?>" align="left">
									<!--<td><?=$row->id?></td>-->
									<td><?=$row->name?><?=$name_icon?></td>
									<td data-value="<?=implode(',', array_keys($row_grps))?>">
										<?=implode(',', array_values($row_grps))?>
									</td>
									<td><?=$row->host?></td>
									<td><?=$row->port?></td>
									<td><?=$row->schema?></td>
									<td><?=$row->dbname?></td>
									<td><?=$row->username?></td>
									<td>******</td>
									<td>						
										<a class="conn_info" title="Show Connection" data-toggle="tooltip"><i class="text-info bi bi-info-circle"></i></a>
										<a class="pwd_vis" title="Show Password" data-toggle="tooltip"><i class="text-secondary bi bi-eye"></i></a>
										<?php if(($row->owner_id == $_SESSION[SESS_USR_KEY]->id) || ($_SESSION[SESS_USR_KEY]->id == SUPER_ADMIN_ID)){ ?>
										<a class="edit" title="Edit" data-toggle="tooltip"><i class="text-warning bi bi-pencil-square"></i></a>
										<a class="delete" title="Delete" data-toggle="tooltip"><i class="text-danger bi bi-x-square"></i></a>
										<a class="clone" title="Clone" data-toggle="tooltip"><i class="text-info bi bi-copy"></i></a>
										<a class="restore" title="Restore" data-toggle="tooltip"><i class="text-info bi bi-box-arrow-in-up"></i></a>
										<a class="backup" title="Backup" data-toggle="tooltip"><i class="text-info bi bi-arrow-down-square"></i></a>
									<?php } ?>
									</td>
								</tr> <?php } ?>
							</tbody>
						</table>
				</div>
				<div class="row">
				    <div class="col-8"><p>&nbsp;</p>

							<div class="alert alert-success">
							   <strong>Note:</strong> Create or connect to PostGIS databases. Zip archives are supported. <a href="https://tile-portal.docs.acugis.com/en/latest/createdb.html" target="_blank"> Documentation</a>

							</div>
						</div>
				</div>

				<div class="row">
					<pre id='import_output'></pre>
				</div>
		</div>
	</div>

<div id="conn_modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Connection Information</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body" id="modal-body"><p>Connection string.</p></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary copy">Copy</button>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<div id="clone_modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Clone Database</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body" id="modal-body">
				<form id="clone_form" class="border shadow p-3 rounded"
							action=""
							method="post"
							enctype="multipart/form-data"
							style="width: 450px;">

					<input type="hidden" name="clone" value="1"/>
					<input type="hidden" name="id" id="clone_id" value="0"/>

					<div class="form-group">
						<label for="dst_name" class="form-label">Name</label>
						<input type="text" class="form-control" name="dst_name" id="dst_name" value="" required/>
						
						<input type="checkbox" name="locally" value="1" checked>
						<label for="locally" class="form-label">Clone on localhost</label>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="activate btn btn-secondary" id="clone_pglink" data-bs-dismiss="modal">Clone</button>
			</div>
		</div>
	</div>
</div>

<div id="backup_modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Backup Database</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body" id="modal-body">
				<form id="backup_form" class="border shadow p-3 rounded"
							action=""
							method="post"
							enctype="multipart/form-data"
							style="width: 450px;">

					<input type="hidden" name="backup" value="1"/>
					<input type="hidden" name="id" id="backup_id" value="0"/>

					<div class="form-group">
						<label for="backup_prefix" class="form-label">Name Prefix</label>
						<input type="text" class="form-control" name="backup_prefix" id="backup_prefix" value="" required/>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="activate btn btn-secondary" id="clone_pglink" data-bs-dismiss="modal">Backup</button>
			</div>
		</div>
	</div>
</div>

<div id="restore_modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Restore Database</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body" id="modal-body">
				<form id="restore_form" class="border shadow p-3 rounded"
							action=""
							method="post"
							enctype="multipart/form-data"
							style="width: 450px;">

					<input type="hidden" name="restore" value="1"/>
					<input type="hidden" name="id" id="restore_id" value="0"/>

					<div class="form-group">
						<label for="dump_file" class="form-label">Dump File</label>
						<select name="dump_file" id="dump_file" required/>
						</select>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="activate btn btn-danger" id="delete_dump">Delete</button>
				<button type="button" class="activate btn btn-secondary" id="restore_pglink" data-bs-dismiss="modal">Restore</button>
			</div>
		</div>
	</div>
</div>

<div id="addnew_modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">PostGIS Link</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			
			<div class="modal-body" id="addnew_modal_body">
				<form id="pglink_form" class="border shadow p-3 rounded"
							action=""
							method="post"
							enctype="multipart/form-data"
							style="width: 450px;">

					<input type="hidden" name="save" value="1"/>
					<input type="hidden" name="id" id="id" value="0"/>

					<div class="form-group">
						<label for="name" class="form-label">Name</label>
						<input type="text" class="form-control" name="name" id="name" value="" required/>
					</div>
					
					<div class="form-group">
						<label for="host" class="form-label">Host</label>
						<input type="text" class="form-control" name="host" id="host" value="" required/>
						
						<label for="port" class="form-label">Port</label>
						<input type="number" class="form-control" name="port" id="port" value="5432" required/>
						
						<label for="username" class="form-label">Username</label>
						<input type="text" class="form-control" name="username" id="username" value="" required/>
						
						<label for="password" class="form-label">Password</label>
						<div class="input-group">
							<input type="password" class="form-control" name="password" id="password" value="" required/>
							<span class="input-group-text"><a class="visibility" title="Toggle password visibility" data-toggle="tooltip"><i class="bi bi-eye"></i></a></span>
						</div>
					</div>
					
					<div class="form-group">
						<label for="dbname" class="form-label">Schema</label>
						<input type="text" class="form-control" name="schema" id="schema" value="public" required/>
						
						<label for="dbname" class="form-label">Database</label>
						<div class="input-group">
							<input type="text" class="form-control" name="dbname" id="dbname" value="" required/>
							<span class="input-group-text"><a class="list_databases" title="Load database names" data-toggle="tooltip"><i class="bi bi-database-gear"></i></a></span>
						</div>
					</div>
					
					<div class="form-group">
						<div class="input-group">
							<select name="group_id[]" id="group_id" multiple required>
								<?php foreach($groups as $k => $v){ ?>
									<option value="<?=$k?>"><?=$v?></option>
								<?php } ?>
							</select>
							<span class="input-group-text"><i class="bi bi-shield-lock">Access Groups</i></span>
						</div>
					</div>

				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="activate btn btn-secondary" id="submit_pglink" data-bs-dismiss="modal">Save</button>
			</div>
		</div>
	</div>
</div>

<div id="import_modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Create/Import Database</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			
			<div class="modal-body" id="import_modal_body">
				<form id="import_form" class="border shadow p-3 rounded"
							action=""
							method="post"
							enctype="multipart/form-data"
							style="width: 450px;">

					<?php if (isset($_GET['error'])) { ?>
						<div class="alert alert-danger" role="alert"><?=$_GET['error']?></div>
					<?php } else if(isset($_GET['success'])) { ?>
						<div class="alert alert-success" role="alert"><?=$_GET['success']?></div>
					<?php } ?>

					<div class="form-group">
						<label for="dbname" class="form-label">Database Name</label>
						<input type="text" class="form-control" name="dbname" id="dbname" value="" />
					</div>
					
					<div class="form-group">
						<div class="input-group">
							<input type="radio" class="form-check-input" id="src_file_radio" name="store_source" value="1" checked/>
							<input type="file" class="form-control" name="source[]" id="import_file" accept=".gpkg,.shp,.zip,.sql,.dump" multiple required />
							<span class="input-group-text" title="File Upload"><i class="bi bi-file-arrow-up"></i></span>
						</div>
						<div class="input-group">
							<input type="radio" class="form-check-input" id="src_url_radio" name="store_source" value="1"/>
							<input type="text" class="form-control" name="src_url" id="src_url" value="" disabled required/>
							<span class="input-group-text" title="URL Upload"><i class="bi bi-link"></i></span>
						</div>
					</div>
					
					<div class="form-group">
						<input type="checkbox" class="form-checkbox" name="create_only" id="create_only" value="1"/>
						<label for="create_only" class="form-label">Create only</label>
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

				<div class="progress">
					<div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
				</div>
				
			</div>
			<div class="modal-footer">
				<button type="button" class="activate btn btn-secondary" id="btn_import" data-dismiss="modal">Import</button>
			</div>
		</div>
	</div>
</div>

</div>

					
	<script>var sortTable = new DataTable('#sortTable', { paging: false });</script>
	</body>
</html>

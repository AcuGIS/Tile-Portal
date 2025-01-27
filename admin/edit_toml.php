<?php
  session_start(['read_and_close' => true]);
	require('incl/const.php');

	function find_toml_snapshots($dirname, $prefix){		
		$rv = array();
		$entries = scandir($dirname);
		foreach($entries as $e){
			$filename = $dirname.'/'.$e;
			if(is_file($filename) && str_starts_with($e, $prefix)){
				list ($name, $v) = explode('_', $e);
				array_push($rv, $v);
			}
		}
		return $rv;
	}

	if(!isset($_SESSION[SESS_USR_KEY]) || $_SESSION[SESS_USR_KEY]->accesslevel != 'Admin') {
    header('Location: ../login.php');
    exit;
  }
	
	$svc = $_GET['svc'];
	$id = $_GET['id'];
	$_GET['name'] = $svc.$id;

	$toml_file = $_GET['name'].'.toml';
	$v = empty($_GET['v']) ? '' : $_GET['v'];
	if(is_file('/opt/'.$svc.'/config/'.$toml_file.'_'.$v) ){
		$toml_file .= '_'.$v;
	}
	
	$toml_snapshots = find_toml_snapshots('/opt/'.$svc.'/config', $_GET['name'].'.toml');
?>

<!DOCTYPE html>
<html dir="ltr" lang="en" >

<head>
	<title>Quail Layer Server</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/codemirror.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/addon/hint/show-hint.min.css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/codemirror.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/toml/toml.min.js"></script>
		
	<?php include("incl/meta.php"); ?>
	<style>
	.CodeMirror {
	  border: 1px solid #eee;
	  height: auto;
	}
	</style>
	
	<script>
$(document).ready(function() {
	
	$('#toml_snapshot').on("change", function() {
		let v = $(this).val();
		let url = 'edit_toml.php';
		if(v != ''){
			url += '?v=' + v;
		}
		window.location.href = url;
	});
});
	</script>
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
									<h2 class="mb-0">Service Config Editor</h2>
								</div>
						</div>
				</div>
				
				<div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
						<div class="row align-items-center">
								<div class="col-6">
									<nav aria-label="breadcrumb"></nav><p>&nbsp;</p>
									<label for="toml_snapshot" class="form-label">Version:</label>
									<select id="toml_snapshot" name="toml_snapshot">
										<option value="" <?php if($v == '') {?>selected<?php } ?>>Latest</option>
										<?php foreach($toml_snapshots as $vv){ ?>
											<option value="<?=$vv?>" <?php if($vv == $v) {?>selected<?php } ?>><?=$vv?></option>
										<?php }?>
									</select>
								</div>
						</div>
				</div>
			
			<form method="post" action="action/edit_toml.php">
				<input type="hidden" name="svc" value="<?=$_GET['svc']?>">
				<input type="hidden" name="id" value="<?=$_GET['id']?>">
				<textarea name="config_toml" id="config_toml" rows="60" cols="150"><?php readfile('/opt/'.$svc.'/config/'.$toml_file); ?></textarea>
				<?php if(empty($_GET['v'])){ ?>
					<input type="submit" name="action" class="btn btn-primary" value="Submit">
				<?php } else { ?>
					<input type="submit" name="action" class="btn btn-primary" value="Restore">
					<input type="hidden" name="v" value="<?=$_GET['v']?>"/>
					<input type="submit" name="action" class="btn btn-danger" value="Delete">
				<?php } ?>
			</form>
			</div>
		</div>
</div>
<script>	
	var editor1 = CodeMirror.fromTextArea(document.getElementById("config_toml"), {
		extraKeys: {"Ctrl-Space": "autocomplete"}
	});
</script>
</body>
</html>

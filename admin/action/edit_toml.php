<?php
	session_start(['read_and_close' => true]);
	require('../incl/const.php');

  if(isset($_SESSION[SESS_USR_KEY]) && $_SESSION[SESS_USR_KEY]->accesslevel == 'Admin') {
		$svc   = empty($_POST['svc']) ? '' : $_POST['svc'];
		$id   = empty($_POST['id']) ? '' : $_POST['id'];
		
		$name = $svc.$id;
		$action = empty($_POST['action']) ? '' : $_POST['action'];
		$v = empty($_POST['v']) ? '' : $_POST['v'];
		
		if($action == 'Restore'){
			if(is_file('/opt/'.$svc.'/config/'.$name.'.toml_'.$v)){
				copy('/opt/'.$svc.'/config/'.$name.'.toml_'.$v, '/opt/'.$svc.'/config/'.$name.'.toml');
			}
			header('Location: ../services.php');
		}else if($action == 'Submit'){
			// make a snapshot of old value
			copy('/opt/'.$svc.'/config/'.$name.'.toml', '/opt/'.$svc.'/config/'.$name.'.toml_'.date("Y-m-d-H-i-s"));
			
			file_put_contents('/opt/'.$svc.'/config/'.$name.'.toml', $_POST['config_toml']);
			header('Location: ../services.php');
		} else if($action == 'Delete') {
			
			if(is_file('/opt/'.$svc.'/config/'.$name.'.toml_'.$v)){
				header('Location: ../edit_toml.php?name='.$name);
				unlink('/opt/'.$svc.'/config/'.$name.'.toml_'.$v);
			}else{
				http_response_code(400);	// Bad Request
				die(400);
			}
		}else{
			http_response_code(400);	// Bad Request
			die(400);
		}
  }else{
		http_response_code(405);	//not allowed
		die(405);
	}
?>
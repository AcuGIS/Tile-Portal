<?php
	session_start(['read_and_close' => true]);
	require('../incl/const.php');
	require('../class/database.php');
	require('../class/table.php');
	require('../class/service.php');
	require('../class/pglink.php');
	require('../class/backend.php');
	
	$result = ['success' => false, 'message' => 'Error while processing your request!'];

  if(isset($_SESSION[SESS_USR_KEY]) && $_SESSION[SESS_USR_KEY]->accesslevel == 'Admin') {
		
		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$obj = new service_Class($database->getConn(), $_SESSION[SESS_USR_KEY]->id);
		$id = isset($_POST['id']) ? intval($_POST['id']) : -1;
		$action = empty($_POST['action']) ? 0 : $_POST['action'];
		
		$svc_types = ['pg_tileserv', 'pg_featureserv'];
						
		if(($id > 0) && !$obj->isOwnedByUs($id)){
			$result = ['success' => false, 'message' => 'Action not allowed!'];

		}else if($action == 'save') {
				$newId = 0;
				
				$pg_obj = new pglink_Class($database->getConn(), $_SESSION[SESS_USR_KEY]->id);
				$pg_res = $pg_obj->getById($_POST['pglink_id']);
				$pg_row = pg_fetch_object($pg_res);
				pg_free_result($pg_res);

				$search = ['# DbConnection = "postgresql://username:password@host/dbname"'];
				$replace= ['DbConnection = "postgresql://'.$pg_row->username.':'.$pg_row->password.'@'.$pg_row->host.'/'.$pg_row->dbname.'"'];
				

				
				if($id > 0) { // update
					$newId = $obj->update($_POST) ? $id : 0;
					
				} else { // insert
					$newId = $obj->create($_POST);
					
					array_push($search, '7800', '9000');
					array_push($replace, 7800 + $newId, 9000 + $newId);
					
					foreach($svc_types as $svc){
						shell_exec('sudo /usr/local/bin/pgt_svc_ctl.sh enable '.$svc.'@'.$newId);
					}
				}

				if($newId > 0){
					
					# create/update service configs
					foreach($svc_types as $svc){
						$toml = file_get_contents('/opt/'.$svc.'/config/'.$svc.'.toml.example');
						$toml = str_replace($search, $replace, $toml);
						file_put_contents('/opt/'.$svc.'/config/'.$svc.$newId.'.toml', $toml);
						chmod('/opt/'.$svc.'/config/'.$svc.$newId.'.toml', 0660);
					}

					# pg_featureserv requires layer name in UrlBase
					$toml = file_get_contents('/opt/'.$svc.'/config/'.$svc.$newId.'.toml');
					$toml = preg_replace('/UrlBase = "([^"]*)"/', 'UrlBase - "${1}'.$pg_row->schema.'.'.$pg_row->dbname.'"', $toml);
					file_put_contents('/opt/'.$svc.'/config/'.$svc.$newId.'.toml', $toml);
					
					$result = ['success' => true, 'message' => 'Service successfully created!', 'id' => $newId];
				}else{
					$result = ['success' => false, 'message' => 'Failed to save service!'];
				}
		
		} else if($action == 'delete') {
			
			if($obj->remove_access($id) && $obj->delete($id)){
				
				foreach($svc_types as $svc){
					shell_exec('sudo /usr/local/bin/pgt_svc_ctl.sh stop '.$svc.'@'.$id);
					shell_exec('sudo /usr/local/bin/pgt_svc_ctl.sh disable '.$svc.'@'.$id);
					
					unlink('/opt/'.$svc.'/config/'.$svc.$id.'.toml');
				}
				
				$result = ['success' => true, 'message' => 'Service successfully deleted!'];
			}else{
				$result = ['success' => false, 'message' => 'Failed to delete service!'];
			}

		}else{
			$svc = empty($_POST['svc']) ? '' : $_POST['svc'];
			$action = empty($_POST['action']) ? '' : $_POST['action'];
			$bknd = new backend_Class();
			
			switch($action){
				case 'start':
				case 'stop':
				case 'restart':
				case 'enable':
				case 'disable':
					$bknd->systemd_ctl($svc, $action);
					$result = ['success' => true, 'message' => 'Success!'];
					break;
				default:
					$result = ['success' => false, 'message' => 'Invalid command!'];
					break;
			}
		}
	}
	
	echo json_encode($result);
?>

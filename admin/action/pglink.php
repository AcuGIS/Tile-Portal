<?php
    session_start(['read_and_close' => true]);
		require('../incl/const.php');
		require('../incl/app.php');
    require('../class/database.php');
		require('../class/table.php');
		require('../class/pglink.php');
		require('../class/access_group.php');
		
		function bg_cmd($run_dir, $name, $cmd){
			if(!is_dir($run_dir)){
				mkdir($run_dir);
			}
			
			$pid_file = $run_dir.'/'.$name.'.pid';
			if(is_file($pid_file) && is_pid_running($pid_file)){
				return false;
			}else{
				shell_exec($cmd.' 1>'.$run_dir.'/'.$name.'.out 2>&1 & echo $! >'.$pid_file);
				return true;
			}
		}

    $result = ['success' => false, 'message' => 'Error while processing your request!'];

    if(isset($_SESSION[SESS_USR_KEY]) && $_SESSION[SESS_USR_KEY]->accesslevel == 'Admin') {
				$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
				$obj = new pglink_Class($database->getConn(), $_SESSION[SESS_USR_KEY]->id);
				$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			
				if(($id > 0) && !$obj->isOwnedByUs($id)){
					$result = ['success' => false, 'message' => 'Action not allowed!'];
				
        }else if(isset($_POST['save'])) {
            $newId = 0;
						$svc_created = true;
						
						$_POST['svc_name'] = $_POST['name'];
						
            if($id) {
							
							$pg_res = $obj->getById($id);
							$pgr = pg_fetch_assoc($pg_res);
							pg_free_result($pg_res);
							
							$cmd = null;
							if(!empty($_POST['svc_name'])){
								$cmd = empty($pgr['svc_name']) ? 'add' : 'edit';

								if($obj->pg_service_ctl($cmd, $_POST) != 0){
									$svc_created = false;
								}

							}else if(!empty($pgr['svc_name'])){
								if($obj->pg_service_ctl('del', $pgr) != 0){
									$svc_created = false;
								}
							}
							
              if($svc_created && $obj->update($_POST)){
								$newId = $id;
								$obj->remove_access($id);
								$obj->create_access($id, $_POST['group_id']);
							}
            } else {
							if(!empty($_POST['svc_name']) && $obj->pg_service_ctl('add', $_POST) != 0){
								$svc_created = false;
							}
							
							if($svc_created){
              	$newId = $obj->create($_POST);
								if($newId > 0){
								    $pg_dir = DATA_DIR.'/pg/'.$newId;
									mkdir($pg_dir);
									$obj->create_access($newId, $_POST['group_id']);
								}
							}
            }
						
						if($newId == 0){							
							$result = ['success' => false, 'message' => 'PG Link create/update failed!'];
						}else{
							$pg_dir = DATA_DIR.'/pg/'.$newId;		
							$result = ['success' => true, 'message' => 'PG Link successfully created/updated!', 'id' => $newId];
						}
        
				} else if(isset($_POST['delete'])) {
						
						$pg_res = $obj->getById($id);
						$pgr = pg_fetch_assoc($pg_res);
						pg_free_result($pg_res);
					
						$ref_ids = array();
						$tbls = array('layer');
						
						# TODO: change layer to service
						/*foreach($tbls as $k){
							$rows = $database->getAll('public.'.$k, 'layer_id = '.$id);							
							foreach($rows as $row){
								$ref_ids[] = $row['layer_id'];
							}
							
							if(count($ref_ids) > 0){
								$ref_name = $k;
								break;
							}
						}*/
						
						if(count($ref_ids) > 0){
							$result = ['success' => false, 'message' => 'Error: Can\'t delete store because it is used in '.count($ref_ids).' '.$ref_name.'(s) with ID(s) ' . implode(',', $ref_ids) . '!' ];
						}else if(!$obj->drop_access($id) || !$obj->delete($id)){
							$result = ['success' => false, 'message' => 'PG Link Not Deleted!'];
						}else{
							if(isset($pgr['svc_name'])){
								$obj->pg_service_ctl('del', $pgr);
							}
							
							if(isset($_POST['drop'])){
								$database->drop($pgr['dbname']);
							}
							
							if(is_dir(DATA_DIR.'/pg/'.$id)){
								rrmdir(DATA_DIR.'/pg/'.$id);
							}
							
							$result = ['success' => true, 'message' => 'PG Link Successfully Deleted!'];
						}
        } else if(isset($_POST['pwd_vis'])) {
					
					$proj_pass = $obj->getPassword($id);
					if($proj_pass === FALSE){
						$result = ['success' => false, 'message' => 'Failed to get password!'];
					}else{
						$result = ['success' => true, 'message' => $proj_pass];
					}
				} else if(isset($_POST['conn_info'])) {
					
					$conn_info = $obj->getConnInfo($id);
					if($conn_info === FALSE){
						$result = ['success' => false, 'message' => 'Failed to get conn_info!'];
					}else{
						if(str_starts_with($conn_info, 'host=localhost ')){
							$conn_info = 'host='.gethostname(). ' '.substr($conn_info, 15);
						}
						$result = ['success' => true, 'message' => $conn_info];
					}
	
				} else if(isset($_POST['schemas'])) {
					$pg_res = $obj->getById($id);
					$pgr = pg_fetch_assoc($pg_res);
					pg_free_result($pg_res);
					
					$proj_db = new Database($pgr['host'], $pgr['dbname'], $pgr['username'], $pgr['password'], $pgr['port'], "public");
					list($schemas, $err) = $proj_db->getSchemas($pgr['dbname'], $pgr['username']);
					$result = ['success' => true, 'schemas' => $schemas];
				
				} else if(isset($_POST['tables'])) {
					$pg_res = $obj->getById($id);
					$pgr = pg_fetch_assoc($pg_res);
					pg_free_result($pg_res);
					
					$proj_db = new Database($pgr['host'], $pgr['dbname'], $pgr['username'], $pgr['password'], $pgr['port'], $pgr['schema']);
					list($tables, $err) = $proj_db->getTables($pgr['schema']);
					$result = ['success' => true, 'tables' => $tables];
				
				} else if(isset($_POST['columns'])) {
					$pg_res = $obj->getById($id);
					$pgr = pg_fetch_assoc($pg_res);
					pg_free_result($pg_res);
					
					$proj_db = new Database($pgr['host'], $pgr['dbname'], $pgr['username'], $pgr['password'], $pgr['port'], $pgr['schema']);
					
					list($cols, $err)	= $proj_db->getColumns($pgr['schema'], $_POST['tbl']);
					list($geoms, $err)	= $proj_db->getGeoms($pgr['dbname'], $pgr['schema'], $_POST['tbl']);
					$result = ['success' => true, 'columns' => $cols, 'geoms' => $geoms];
								
				} else if(isset($_POST['list_databases'])) {
					
					$proj_db = new Database($_POST['host'], 'postgres', $_POST['username'], $_POST['password'], $_POST['port'], $_POST['schema']);
					list($databases, $err) = $proj_db->getDatabases($_POST['username']);
					if($err){
						$result = ['success' => false, 'message' => $err];
					}else if(count($databases) == 0){
						$result = ['success' => false, 'message' => 'Info: User doesn\'t own any databases'];
					}else{
						$result = ['success' => true, 'databases' => $databases];
					}
				
				} else if(isset($_POST['list_backups'])) {
					$pg_dir = DATA_DIR.'/pg/'.$id;
					if(is_dir($pg_dir)){
						$result = ['success' => true, 'dump_files' => find_dumps($pg_dir)];
					}else{
						$result = ['success' => false, 'message' => 'Error: No dump files'];
					}

				} else if(isset($_POST['clone'])) {
					
					$pg_res = $obj->getById($id);
					$src = pg_fetch_assoc($pg_res);
					pg_free_result($pg_res);
					
					$dst = $src;
					$dst['dbname'] = $database->get_unique_dbname($_POST['dst_name']);
					$dst['svc_name'] = $dst['dbname'];
					$dst['name'] = $dst['dbname'];
					if(isset($_POST['locally'])){
						$dst['host'] = DB_HOST;
						$dst['port'] = DB_PORT;
						$dst['username'] = $_SESSION[SESS_USR_KEY]->ftp_user;
						$dst['password'] = $_SESSION[SESS_USR_KEY]->pg_password;
					}
					
					$grp_obj = new access_group_Class($database->getConn(), $_SESSION[SESS_USR_KEY]->id);
					$row_grps = $grp_obj->getByKV('store', $id);
					$dst['group_id'] = array_keys($row_grps);
									 
					// create new database entry
					if($obj->pg_service_ctl('add', $dst) == 0){

						$dst['id'] = $obj->create($dst);

						if($dst['id'] == 0){
							$obj->pg_service_ctl('del', $dst);
						}else{
						
							// clone database
							$cmd  = 'export PGSERVICEFILE="'.PG_SERVICE_CONF.'";';
							if($src['host'] != $dst['host']){
								// create database
								$dst_db = new Database($dst['host'], 'postgres', $dst['username'], $dst['password'], $dst['port'], $dst['schema']);
								if($dst_db->create_user_db($dst['dbname'], $dst['username'], $dst['password'])){
									// copy database
									$cmd .=' pg_dump service='.$src['svc_name'].' | psql service='.$dst['svc_name'];
								}else{
									$cmd = null;
									$result = ['success' => false, 'message' => 'Error: Failed to create target db'];
								}
							}else{
								// create and copy in one step, on same host
								$cmd .=' psql service='.$src['svc_name'].' -ec "CREATE DATABASE '.$_POST['dst_name'].' WITH TEMPLATE '.$src['dbname'].' OWNER '.$dst['username'].'"';
							}
							
							if($cmd){
								$pg_dir = DATA_DIR.'/pg/'.$dst['id'];
								
								mkdir($pg_dir);								

								if(bg_cmd($pg_dir, 'clone', $cmd)){
									$dst['success'] = true;
									$result = $dst;
								}else{
									$result = ['success' => false, 'message' => 'Error: Clone already in progress!'];
								}
							}
						}
						
					}else{
						$result = ['success' => false, 'message' => 'Error: Failed to create service entry'];
					}

				} else if(isset($_POST['backup'])) {
					
					$pg_res = $obj->getById($id);
					$src = pg_fetch_assoc($pg_res);
					pg_free_result($pg_res);
					
					$backup_name = $_POST['backup_prefix'].'_'.date("Y-m-d_H-i-s").'.dump';
					$pg_dir = DATA_DIR.'/pg/'.$id;
					$cmd  = 'export PGSERVICEFILE="'.PG_SERVICE_CONF.'";';
					$cmd .=' pg_dump service='.$src['svc_name'].' --clean --create -Fc -f '.$pg_dir.'/'.$backup_name;
					
					if(bg_cmd($pg_dir, 'backup', $cmd)){
						$result = ['success' => true, 'message' => 'Backup stored in '.$pg_dir.'/'.$backup_name];
					}else{
						$result = ['success' => false, 'message' => 'Error: Backup already in progress!'];
					}
					

				} else if(isset($_POST['restore'])) {
					
					$pg_res = $obj->getById($id);
					$src = pg_fetch_assoc($pg_res);
					pg_free_result($pg_res);

					$pg_dir = DATA_DIR.'/pg/'.$id;
					$cmd  = 'export PGSERVICEFILE="'.PG_SERVICE_CONF.'";';
					$cmd .= 'export PGSERVICE='.$src['svc_name'].';';
					$cmd .=' pg_restore --verbose --clean --create -Fc -d postgres '.$pg_dir.'/'.$_POST['dump_file'].'.dump';
					
					if(bg_cmd($pg_dir, 'restore', $cmd)){
						$result = ['success' => true, 'message' => 'Restore started'];
					}else{
						$result = ['success' => false, 'message' => 'Error: Restore already in progress!'];
					}
					
				
				} else if(isset($_POST['delete_dump'])) {
					$pg_dir = DATA_DIR.'/pg/'.$id;
					$dump_file = $pg_dir.'/'.$_POST['dump_file'].'.dump';
					
					if(is_file($dump_file)){
						unlink($dump_file);
						$result = ['success' => true, 'message' => 'Removed'];
					}else{
						$result = ['success' => false, 'message' => 'Not found'];
					}
				}
    }

    echo json_encode($result);
?>

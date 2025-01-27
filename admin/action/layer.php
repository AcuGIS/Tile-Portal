<?php
  session_start(['read_and_close' => true]);
	require('../incl/const.php');
  require('../class/database.php');
	require('../class/table.php');
  require('../class/layer.php');

  $result = ['success' => false, 'message' => 'Error while processing your request!'];

  if(isset($_SESSION[SESS_USR_KEY]) && $_SESSION[SESS_USR_KEY]->accesslevel == 'Admin') {
			$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
			$obj = new layer_Class($database->getConn(), $_SESSION[SESS_USR_KEY]->id);
			$id = isset($_POST['id']) ? intval($_POST['id']) : -1;
			$action = empty($_POST['action']) ? 0 : $_POST['action'];
			
			if(($id > 0) && !$obj->isOwnedByUs($id)){
				$result = ['success' => false, 'message' => 'Action not allowed!'];
			
      }else if($action == 'save') {
          $newId = 0;
					
					if(!isset($_POST['public'])){
						$_POST['public'] = 'f';
					}

				  if($id > 0) { // update
            $newId = $obj->update($_POST) ? $id : 0;
						
          } else { // insert
            $newId = $obj->create($_POST);
          }
					
					if($newId > 0){
						$result = ['success' => true, 'message' => 'Layer successfully created!', 'id' => $newId];
					}else{
						$result = ['success' => false, 'message' => 'Failed to save layer!'];
					}
      
			} else if($action == 'delete') {
				
				if($obj->remove_access($id) && $obj->delete($id)){
        	$result = ['success' => true, 'message' => 'Layer successfully deleted!'];
				}else{
					$result = ['success' => false, 'message' => 'Failed to delete layer!'];
				}
			} else if($action == 'get_layers') {
				$svc_id = $_POST['svc_id'];
				$port = 7800 + intval($svc_id);
				
				$layers = array();
				$json = file_get_contents('http://localhost:'.$port.'/index.json');
				if(strlen($json) <= 0){
					$result = ['success' => false, 'message' => 'Error: Empty index.json'];
				}else{
					$json_arr = json_decode($json);
					foreach($json_arr as $j){
						array_push($layers, $j->id);
					}
					$result = ['success' => true, 'layers' => $layers];
				}
			}
  }

  echo json_encode($result);
?>

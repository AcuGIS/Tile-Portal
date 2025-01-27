<?php
session_start(['read_and_close' => true]);
require('admin/incl/const.php');
require('admin/class/database.php');
require('admin/class/table.php');
require('admin/class/layer.php');

if(empty($_GET['name'])){
	http_response_code(404);	//no name param
	die('Sorry, no layer name!');
}
	
$user_id = (isset($_SESSION[SESS_USR_KEY])) ? $_SESSION[SESS_USR_KEY]->id : SUPER_ADMIN_ID;

$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
$obj = new layer_Class($database->getConn(), $user_id);

$result = $obj->getByName($_GET['name']);
if(!$result || (pg_num_rows($result) == 0)){
	http_response_code(404);	//not found
	die('Sorry, layer '.$_GET['name'].' not found!');
}

$row = pg_fetch_object($result);
pg_free_result($result);

if($row->public != 't'){
	
	if(!isset($_SESSION[SESS_USR_KEY])) {
		header('Location: ../../login.php');
		exit;
	}
	
	// check if user can access the resource
	$allow = $database->check_user_tbl_access('layer', $row->id, $_SESSION[SESS_USR_KEY]->id);
	if(!$allow){
		http_response_code(405);	//not allowed
		die('Sorry, access not allowed!');
	}
}

if(isset($_GET['r'])){  // rest for pg_featureserv
    $port = 9000 + $row->svc_id;
    header('Content-Type: application/json');
    readfile('http://localhost:'.$port.'/'.$_GET['r']);
}else if(isset($_GET['q'])){    // query for pg_tile 
    $port = 7800 + $row->svc_id;
    readfile('http://localhost:'.$port.'/'.$_GET['q']);
}
?>

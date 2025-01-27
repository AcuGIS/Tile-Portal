<?php
class backend_Class
{			
	function parseSystemd($output){
		$rv = [];
		foreach($output as $l){
			if(preg_match('/^\s+([\w\s]+):\s(.*)/is', $l, $m) === 1){
				$k = strtolower($m[1]);
				if($k == 'loaded'){
					$t = explode(';', $m[2]);
					$rv['enabled'] = trim($t[1]);
				}
				$rv[$k] = $m[2];
			}
		}
		return $rv;
	}
	
	function service_status($name){
		exec('sudo /usr/local/bin/pgt_svc_ctl.sh status '.$name, $output, $retval);
		return $this->parseSystemd($output);
	}
	
	function systemd_ctl($name, $action){
		shell_exec('sudo /usr/local/bin/pgt_svc_ctl.sh '.$action.' '.$name);
	}
}
?>
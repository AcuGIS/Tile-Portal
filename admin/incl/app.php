<?php
	function dir_size($dirname){
		$size = 0;
		if (is_dir($dirname)) {
		 $objects = scandir($dirname);
		 foreach ($objects as $object) {
			 if ($object != "." && $object != "..") {
				 if (is_dir($dirname. DIRECTORY_SEPARATOR .$object) && !is_link($dirname."/".$object)){
					 $size += dir_size($dirname. DIRECTORY_SEPARATOR .$object);
				 }else{
					 $st = stat($dirname.'/'.$object);
					 $size += $st['size']; 
				 }
					 
			 }
		 }
	 }
	 return $size;
	}
	
	function human_size($bytes){
		if ($bytes == 0){
			return "0.00";
		}
		$labels = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
		$e = floor(log($bytes, 1024));
		return round($bytes / pow(1024, $e), 2).' '.$labels[$e];
	}
	
	function is_pid_running($pid_file){
		
		if(!is_file($pid_file)){
			return 0;
		}
		
		$pid = file_get_contents($pid_file);
		$pid = intval($pid);

		if(is_dir('/proc/'.$pid)){
			return $pid;
		}else{
			unlink($pid_file);
		}
		return 0;
	}
?>
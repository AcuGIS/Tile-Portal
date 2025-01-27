<?php
    class user_Class extends table_Class
    {

				function __construct($dbconn, $owner_id) {
					parent::__construct($dbconn, $owner_id, 'user');
				}
				
				public static function randomPassword() {
						$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
						$pass = array(); //remember to declare $pass as an array
						$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
						for ($i = 0; $i < 10; $i++) {
								$n = rand(0, $alphaLength);
								$pass[] = $alphabet[$n];
						}
						return implode($pass); //turn the array into a string
				}

        function create($data, $isHashed = false)
        {		
						 if(!$isHashed){
						 	$data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
						 }
						
             $sql = "INSERT INTO PUBLIC." .$this->table_name."
             (name,email,password,accesslevel,owner_id) "."VALUES('".
             $this->cleanData($data['name'])."','".
             $this->cleanData($data['email'])."','".
             									$data['password']."','".
             $this->cleanData($data['accesslevel'])."',".
						 $this->owner_id.") RETURNING id";
						 
						 $result = pg_query($this->dbconn, $sql);
						 if(!$result){
		 					return 0;
		 				 }
            $row = pg_fetch_object($result);
						pg_free_result($result);

            if($row) {
							$this->create_access($row->id, $data['group_id']);
              return $row->id;
            }
            return 0;
        }

				function loginCheck($pwd, $email){

	        $sql ="select * from public.user where email = '".$this->cleanData($email)."'";
	        $result = pg_query($this->dbconn,$sql);
					if(!$result || pg_num_rows($result) == 0){
 					 return null;
 				 }
				 $row = pg_fetch_object($result);
					pg_free_result($result);
					
					if (password_verify($pwd, $row->password)) {
						return $row;
					}
	        return null;
				}

				function getByEmail($email){

            $sql ="select * from public.".$this->table_name." where email='".$email."'";
            $result = pg_query($this->dbconn, $sql);
						if(!$result){
							return false;
						}
						
						$row = pg_fetch_object($result);
						pg_free_result($result);
            return $row;
        }

       function update($data=array())
       {

          $id = intval($data['id']);
					$result = $this->getById($id);
				 	$row = pg_fetch_object($result);
					pg_free_result($result);
					
          $sql = "update public.user set name='".$this->cleanData($data['name'])."'";
					
					if($row->password != $data['password']){	# if password is changed
						$hashpassword = password_hash($data['password'], PASSWORD_DEFAULT);
          	$sql .= ", password='".$hashpassword."'";
					}

          $sql .= ", accesslevel='".$this->cleanData($data['accesslevel']).
								 	"' where id = '".$id."'";
					
					$result = pg_query($this->dbconn, $sql);
					if(!$result){
						return 0;
					}
					
					$rv = pg_affected_rows($result);
					if($rv > 0){
						$this->remove_access($row->id);
						$this->create_access($row->id, $data['group_id']);
					}
					pg_free_result($result);

					return $rv;
       }
			 
			 function admin_self_own($id){
				 $sql ="update public.".$this->table_name." set owner_id = ".$id." where id='".$id."'";
				 $result = pg_query($this->dbconn, $sql);
				 if(!$result){
					 return false;
				 }
				 pg_free_result($result);
				 return true;
			 }
	}

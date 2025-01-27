<?php		
    class layer_Class extends table_Class
    {				
				function __construct($dbconn, $owner_id) {
					parent::__construct($dbconn, $owner_id, 'layer');
				}
				
				function create($data){
					$sql = "INSERT INTO PUBLIC.".$this->table_name." (name,public,svc_id,owner_id) VALUES('".
						$this->cleanData($data['name'])."','".
						$this->cleanData($data['public'])."',".
						$this->cleanData($data['svc_id']).",".
						$this->owner_id.") RETURNING id";
					 
					$result = pg_query($this->dbconn, $sql);
					if(!$result){
						return 0;
					}
					
					$row = pg_fetch_object($result);
					if($row) {
						pg_free_result($result);
						$this->create_access($row->id, $data['group_id']);
						return $row->id;
					}
											
					return 0;
				}
				
				function update($data=array())
				{

					 $id = intval($data['id']);
					 $result = $this->getById($id);
					 $row = pg_fetch_object($result);
					 pg_free_result($result);
					 
					 $sql = "update public.".$this->table_name." set name='".$this->cleanData($data['name'])."'".
					        ", svc_id=".$this->cleanData($data['svc_id']).
							", public='".$this->cleanData($data['public']).
							"' where id=".$id;
					 
					 $result = pg_query($this->dbconn, $sql);
					 if(!$result){
						 return 0;
					 }
					 
					 $rv = pg_affected_rows($result);
					 pg_free_result($result);
					 if($rv > 0){
						 $this->remove_access($row->id);
						 $this->create_access($row->id, $data['group_id']);
					 }

					 return $rv;
				}
	}
?>

<?PHP
    class Database {
        private $connection;

        function __construct($db_host, $db_name, $db_user, $db_pass, $db_port, $db_schema) {
          $this->connection = pg_connect("dbname={$db_name} user={$db_user} password={$db_pass} host={$db_host} port={$db_port}");
        }

				function is_connected(){
					if($this->connection === false){
						return false;
					}
					return (pg_connection_status($this->connection) == PGSQL_CONNECTION_OK);
				}
				
	    	function modify($str) {
	        		return ucwords(str_replace("_", " ", $str));
	    	}

				function getConn() {
					return $this->connection;
				}
        function getAll($table, $where = '', $orderby = '') {
            $orderby = $orderby ? 'ORDER BY '.$orderby : '';
            $where = $where ? 'WHERE '.$where : '';

            $query = "SELECT * FROM {$table} {$where} {$orderby}";
            $result = pg_query($this->connection, $query);

            if (!$result) {
                echo "An error occurred executing the query.\n";
                exit;
            }

            // Fetch all rows
            $rows = array();
            while ($row = pg_fetch_assoc($result)) {
                $rows[] = $row;
            }
						pg_free_result($result);

            return $rows;
        }


        function get($table, $where = '') {
            if(is_numeric($where)) {
                $where = "id = ".intval($where);
            }
            else if (empty($where)) {
                $where = "1";
            }

            $query = "SELECT * FROM {$table} WHERE $where";
            $result = pg_query($this->connection, $query);

            if (!$result) {
                echo "An error occurred executing the query.\n";
                exit;
            }

            // Fetch one rows
            $row = pg_fetch_assoc($result);
						pg_free_result($result);

            return $row;
        }


        /* Select Query */
        function select($query) {
            $result = pg_query($this->connection, $query);

            if (!$result) {
                echo "An error occurred executing the query.\n";
                exit;
            }

            // Fetch all rows
            $rows = array();
            while ($row = pg_fetch_assoc($result)) {
                $rows[] = $row;
            }
						pg_free_result($result);

            return $rows;
        }
				
				function select1($field, $query) {
						$rows = array();
            $result = pg_query($this->connection, 'SELECT '.$field.' '.$query);

            if (!$result) {
              return [$rows, pg_last_error($this->connection)];
            }

            // Fetch all rows
            while ($row = pg_fetch_assoc($result)) {
                $rows[] = $row[$field];
            }
						pg_free_result($result);

            return [$rows, ''];
        }
				
				function getDatabases($owner = null){
					$sql = " from pg_database where datname NOT LIKE 'template%'";
					if($owner){
						$sql .= " AND datdba = (SELECT usesysid from pg_user WHERE usename = '".$owner."')";
					}
					return $this->select1('datname', $sql);
				}
				
				function getTables($schema_name){
					return $this->select1('table_name', " FROM information_schema.tables WHERE table_schema = '".$schema_name."'");
				}
				
				function getColumns($schema_name, $table_name){
					return $this->select1('column_name', "FROM information_schema.columns WHERE table_schema = '".$schema_name."' AND table_name   = '".$table_name."'");
				}
				
					function getGeoms($dbname, $schema_name, $table_name){
					return $this->select1('f_geometry_column', "FROM geometry_columns WHERE f_table_catalog = '".$dbname."' AND f_table_schema = '".$schema_name."' AND f_table_name   = '".$table_name."'");
				}
				
				function create_user($dbuser, $pass) {
					$sql = 'CREATE USER "'.$dbuser.'" WITH PASSWORD \''.$pass.'\'';
					$result = pg_query($this->connection, $sql);
					if (!$result) {
						return false;
					}
					pg_free_result($result);
					return true;
				}
				
				function create_user_db($dbname, $dbuser, $pass) {
					
					$sqls = array('CREATE DATABASE "'.$dbname.'" WITH OWNER "'.$dbuser.'"',
												'GRANT all privileges on database "'.$dbname.'" to "'.$dbuser.'"');

					foreach($sqls as $sql){
					 $result = pg_query($this->connection, $sql);
					 if (!$result) {
						 return false;
					 }
					 pg_free_result($result);
					}

					return true;
				}
				
				function create_extensions($extensions){
					foreach($extensions as $ext){
					 $result = pg_query($this->connection, 'CREATE EXTENSION IF NOT EXISTS '.$ext);
					 if (!$result) {
						 return false;
					 }
					 pg_free_result($result);
					}
					return true;
				}
				
				function get_unique_dbname($name){
					
					$name = str_replace('.', '_', $name);
					$dbname = str_replace(' ', '_', $name);
					
					list($dbs,$err) = $this->getDatabases();
					
					$i = 1;
					while(in_array($dbname, $dbs)){
						$i = $i + 1;
						$dbname = $name.$i;
					}
					
					return $dbname;
				}
				
				function drop($dbname){
					$result = pg_query($this->connection, 'DROP DATABASE "'.$dbname.'"');
					pg_free_result($result);
				}
				
				function drop_user($username){
					$result = pg_query($this->connection, 'DROP USER "'.$username.'"');
					pg_free_result($result);
				}
				
				function check_user_tbl_access($prefix, $id, $user_id){
					if($user_id == SUPER_ADMIN_ID){
						return true;
					}
					
					$sql = 'SELECT '.$prefix.'_id from public.'.$prefix.'_access WHERE '.$prefix.'_id = '.$id.' AND access_group_id in (SELECT access_group_id from public.user_access where user_id='.$user_id.')';
					$result = pg_query($this->connection, $sql);
					if(!$result){
						return false;
					}
					$access_granted = pg_num_rows($result);
					pg_free_result($result);
					
					return ($access_granted > 0);
				}
    }
?>

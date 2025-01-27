<?php
    session_start();
		require('incl/const.php');
    require('class/database.php');

    if(!isset($_SESSION[SESS_USR_KEY]) || ($_SESSION[SESS_USR_KEY]->accesslevel != 'Admin') ){
      header('Location: ../login.php');
      exit;
    }
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
	<?php include("incl/meta.php"); ?>
<style type="text/css">
a {
	text-decoration:none!important;
}
</style>


</head>

<body>
   
	<div class="container-fluid">
		<div class="row align-items-start">
			<div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 bg-dark">
				<?php const MENU_SEL = 'index.php';
					include("incl/sidebar.php");
				?>
			</div>
       
			 <div class="col">
	 		 	<div id="content">
      <div class="col-6" style="padding-top:10px!important">
									<h2 class="mb-0">Administration</h2>
								</div>
			
					<div class="card" style="width:50%">
				    <div class="card-body">
				      <h4 class="card-title">Users</h4>
				      <p class="card-text">Users and Groups</p>
				      <a href="access.php" class="card-link">Manage</a>
				      <a href="https://tile-portal.docs.acugis.com/en/latest/users.html" class="card-link" target="_blank">Documentation</a>
				    </div>
					</div>

					


<br>
					    
					<div class="card" style="width:50%">
						<div class="card-body">
							<h4 class="card-title">Databases</h4>
							<p class="card-text">PostGIS Databases</p>
							<a href="databases.php" class="card-link">Manage</a>
							<a href="https://tile-portal.docs.acugis.com/en/latest/createdb.html" class="card-link" target="_blank">Documentation</a>
						</div>
					</div>



<br>



					<div class="card" style="width:50%">
						<div class="card-body">
							<h4 class="card-title">Services</h4>
							<p class="card-text">Manage Services</p>
							<a href="services.php" class="card-link">Manage</a>
							<a href="https://tile-portal.docs.acugis.com/en/latest/services.html" class="card-link" target="_blank">Documentation</a>
						</div>
					</div>					    
					

					

					    
<br>	
<div class="card" style="width:50%">
						<div class="card-body">
							<h4 class="card-title">Layers</h4>
							<p class="card-text">Tile Layers</p>
							<a href="layers.php" class="card-link">Manage</a>
							<a href="https://tile-portal.docs.acugis.com/en/latest/layers.html" class="card-link" target="_blank">Documentation</a>
						</div>
					</div>				

 
    	</div>
			<footer class="footer text-center"></footer>   
		</div>
	</div>
</div>
</body>

</html>

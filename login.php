<?php
	require('admin/incl/const.php');
	require('admin/class/database.php');

	session_start();
  if(isset($_SESSION['user'])) {
    header("Location: index.php");
		die(0);
  }
?>
<!doctype html>
<html lang="en">
  <head>
  	<title>pg_tile_portal</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="admin/dist/css/login-style.css">

	</head>
	<body>
	<section class="ftco-section">
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-md-6 text-center mb-5">
					<h2 class="heading-section">tileserv_portal</h2>
				</div>
			</div>
			<div class="row justify-content-center">
				<div class="col-md-7 col-lg-5">
					<div class="login-wrap p-4 p-md-5">
		      	<div class="icon d-flex align-items-center justify-content-center" style="background:#fff!important">
		      		<!--<span class="fa fa-database"></span>-->
				<img src="assets/layers/login-small.png">
		      	</div>
		      	<br>
						<form method="post" action="admin/action/login.php"  class="login-form">
		      		<div class="form-group">
	
		      			<input type="email" class="form-control rounded-left" name="email" placeholder="name@example.com" required>
		      		</div>
	            <div class="form-group d-flex">
		      <input type="password" class="form-control" name="pwd" id="pwd" value="" placeholder="Password" required>
	
	            </div>
	            <div class="form-group">
	            	<button type="submit" name="submit" value="Login" class="form-control btn btn-primary rounded submit px-3">Login</button>

	            </div>
	            <!--<div class="form-group d-md-flex">
	            	<div class="w-50">
	            		<label class="checkbox-wrap checkbox-primary">Remember Me
									  <input type="checkbox" checked>
									  <span class="checkmark"></span>
									</label>
								</div>
								<div class="w-50 text-md-right">
									<a href="#">Forgot Password</a>
								</div>
	            </div>
	          </form>-->
	        </div>
				</div>
			</div>
		</div>
	</section>

 

</body>
</html>


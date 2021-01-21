<?php
	// if the user is already logged in
	if(is_user_logged_in()){
		$dashboard_url = get_permalink( get_page_by_path( 'dashboard' ) );
		wp_redirect($dashboard_url);
	}

	$Users_module = new Users_module();
	// $current_user = $Users_module->get_current_user();
	// print_r($current_user);


	if($_POST["username_email"] && $_POST["password"]){
		$email = $_POST["username_email"];
		$password = $_POST["password"];

		//first login/verify the user to the api 
		
		$login_status = $Users_module->user_login($email,$password);

		//if the user is authenticated to the api now login to the wordpress
		//if the user exists in api but doesn't exists in wordpress then we create new user in wp and then login to that user
		if($login_status==true){
			$is_user_exists_in_wp = email_exists($email);
			//if user exist login to wp
			if($is_user_exists_in_wp){
				$user = get_user_by('email', $email );
					// Redirect URL //
					if ( !is_wp_error( $user ) )
					{
					    wp_clear_auth_cookie();
					    wp_set_current_user ( $user->ID );
					    wp_set_auth_cookie  ( $user->ID );
					    // if($current_user["role_slug"]){
					    // 	$current_user = $Users_module->get_current_user();
					    // 	echo "you are logged in as ".$current_user["role_slug"];
					    // }
					    $redirect_to = get_permalink( get_page_by_path( 'dashboard' ) );
					    wp_safe_redirect( $redirect_to );
					    // exit();
					}
			}
			//if not create an new user then login to user
			else{
				echo "registration";
			}
		}
		else{
			echo "the email/password is not valid";
		}
	}
?>

<div id="content" class="site-content"><div style="padding:20px 0;">
	<div class="row">
		<div class="col-xs-12 col-md-12 padding60">
			<h2 style=" text-align: center; font-size: 42px; ">Welcome to Academic<br>Group School Console</h2>
			<h3 style=" font-size: 14px; text-align: center; ">Enter your credentials below to access your dashboard</h3>
		</div>
	</div>

	<form action="" method="post">
		  <div class="form-group">
		    <label for="exampleInputEmail1">Username</label>
		    <input type="text" name="username_email" class="form-control"aria-describedby="emailHelp" placeholder="Enter email">
		    
		  </div>
		  <div class="form-group">
		    <label for="exampleInputPassword1">Password</label>
		    <input type="password" name="password" class="form-control" id="exampleInputPassword1" placeholder="Password">
		  </div>
		  <!-- <div class="form-check">
		    <input type="checkbox" class="form-check-input" id="exampleCheck1">
		    <label class="form-check-label" for="exampleCheck1">Check me out</label>
		  </div> -->
		  <button type="submit" class="btn btn-primary">Login</button>

		  <?php
		  		$register_url = get_permalink( get_page_by_path( 'register' ) );
			?>
		  <a href="<?php echo $register_url;?>" class="btn btn-success" type="button">Register</a>
	</form>
</div>	

<?php
	
	// global $woocommerce;
	// $items = $woocommerce->cart->get_cart();

	// foreach($items as $item => $values) { 
	// 	echo "<pre>";
	//     print_r($values);
	// } 

?>



<script>
    //stop from multiple submission on reload the tab
    if ( window.history.replaceState ) {
      window.history.replaceState( null, null, window.location.href );
    }
</script>

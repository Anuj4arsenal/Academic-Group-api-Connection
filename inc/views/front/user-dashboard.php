<?php
// if the user is not logged in
	if(!is_user_logged_in()){
		$login_url = get_permalink( get_page_by_path( 'login-2' ) );
		wp_redirect($login_url);
	}

	$Users_module = new Users_module();
	$current_user = $Users_module->get_current_user();
?>

<div class="container" style="padding: 30px 15px;">
        <div class="row">
            <div class="col-sm-6">
                <b>You are Logged In as:</b> <?php echo $current_user["role_slug"];?>
            </div>
            <div class="col-sm-6  text-right">
               <strong>Hi, <?php echo $current_user["display_name"];?></strong> |
			   <a href="<?php echo wp_logout_url();?>">Logout</a>
            </div>
    </div>
</div>
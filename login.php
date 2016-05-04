<script src="http://demobuilderux.com/updates/wp-content/plugins/builderux/assets/js/jquery-1.11.3.min.js"></script>
<script>
var userdata = {
			global_subdivisionid: 0,
			global_planid: 0,
			global_unitid: 0,
			global_elevationid: 0,
			islogin: false,
			userid: ''
		};
function login1(){
		var luname = $('#log_username1').val();	
		var lpassw = $('#log_password1').val();
		if(luname.length > 0 && lpassw.length > 0){
			$.post("http://demobuilderux.com/updates/wp-content/plugins/builderux/frontend/trans/trans_subdivisionplan.php?funct=login",
			{username: luname, password: lpassw, subdivisionid: userdata.global_subdivisionid},
		    function(data){
			    if(data.success){
				    userdata.islogin = true;
				    userdata.userid = data.email;
				   
				  
				    $('#loginstatus').html('Account: '+ data.email +' | <a href="javascript: void(0)" onClick="logMeOut()">Logout</a>');
				     window.location.href = data.link;
				    
				  
			    }else{
				    $('#log_error1').html(data.msg);
			    }
		    },
		    'json'
		);
		}else{
			$('#log_error1').html('Please fill the username and password correctlyaaaaaa.');
		}
	}
	function logMeOut(){
		$.post("http://demobuilderux.com/updates/wp-content/plugins/builderux/frontend/trans/trans_subdivisionplan.php?funct=logout",
			    function(data){
			        if(data.success){
				        window.location.href = data.link;
			        }
			    },
			    'json'
		);
			
	}
	</script>
<?php

/**
 * template part for header toolbar login form. views/header/toolbar
 *
 * @author 		Artbees
 * @package 	jupiter/views
 * @version     5.0.0
 */

global $wp, $mk_options;

		if ($mk_options['header_toolbar_login'] != 'true') {
			return false;
		}

		$current_url = home_url($wp->request);
		@session_start(); 
		if(@$_SESSION['islogin']){?>
			
			
			<div class="mk-header-login">
    	<?php	echo 'Account: '.$_SESSION['useremail'].' | <a href="javascript: void(0)" onClick="logMeOut()">Logout</a>';
			$current_user = wp_get_current_user();?>
    		<div class="mk-login-register mk-box-to-trigger user-profile-box">
<?php
$user_ID = get_current_user_id();
			echo get_avatar($user_ID, 48);?>
    			<a href="<?php echo get_edit_user_link();?>"><?php _e('Edit Profile', 'mk_framework');?></a>
    			<a href="<?php echo wp_logout_url(mk_current_page_url());?>" title="Logout"><?php _e('Logout', 'mk_framework');?></a>
    		</div>
    		</div>
<?php } else {
			?>
	<div class="mk-header-login">
    <a href="#" id="mk-header-login-button" class="mk-login-link mk-toggle-trigger"><i class="mk-moon-user-8"></i><?php _e('Login', 'mk_framework');?></a>
	<div class="mk-login-register mk-box-to-trigger">

		<div id="mk-login-panel">
				<form id="mk_login_form" name="mk_login_form" method="post" class="mk-login-form" action="javascript:void(0);">
					<span class="form-section">
					<label for="log"><?php _e('Username', 'mk_framework');?></label>
					<input type="text" name="log_username" id="log_username1" class="text-input">
					</span>
					<span class="form-section">
						<label for="pwd"><?php _e('Password', 'mk_framework');?></label>
						<input type="password" name="log_password" id="log_password1" class="text-input">
					</span>
<?php do_action('login_form');?>
					<label class="mk-login-remember">
						<input type="checkbox" name="rememberme" id="rememberme" value="forever"><?php _e(" Remember Me", 'mk_framework');?>
					</label>

					 <input type="submit" class="shop-flat-btn shop-skin-bt" value="Login" onClick="login1()">

					<div class="register-login-links">
							<a href="#" class="mk-forget-password"><?php _e("Forget?", 'mk_framework');?></a>
<?php if (get_option('users_can_register')) {?>
							<a href="#" class="mk-create-account"><?php _e("Register", 'mk_framework');?></a>
<?php }?>
</div>
					<div class="clearboth"></div>
					<p id="log_error1" style="color: red;">&nbsp;</p>
					
				</form>
		</div>

<?php if (get_option('users_can_register')) {?>
			<div id="mk-register-panel">
					<div class="mk-login-title"><?php _e("Create Account", 'mk_framework');?></div>

					<form id="register_form" name="login_form" method="post" class="mk-form-regsiter" action="<?php echo site_url('wp-login.php?action=register', 'login_post')?>">
						<span class="form-section">
							<label for="log"><?php _e('Username', 'mk_framework');?></label>
							<input type="text" id="reg-username" name="user_login" class="text-input">
						</span>
						<span class="form-section">
							<label for="user_email"><?php _e('Your email', 'mk_framework');?></label>
							<input type="text" id="reg-email" name="user_email" class="text-input">
						</span>
						<span class="form-section">
							<input type="submit" id="signup" name="submit" class="shop-flat-btn shop-skin-btn" value="<?php _e("Create Account", 'mk_framework');?>">
						</span>
<?php do_action('register_form');?>
						<input type="hidden" name="user-cookie" value="1" />
						<input type="hidden" name="redirect_to" value="<?php echo $current_url;?>?register=true" />
						<div class="register-login-links">
							<a class="mk-return-login" href="#"><?php _e("Already have an account?", 'mk_framework');?></a>
						</div>
					</form>
			</div>
<?php }?>

		<div class="mk-forget-panel">
				<span class="mk-login-title"><?php _e("Forget your password?", 'mk_framework');?></span>
				<form id="forgot_form" name="login_form" method="post" class="mk-forget-password-form" action="<?php echo site_url('wp-login.php?action=lostpassword', 'login_post')?>">
					<span class="form-section">
							<label for="user_login"><?php _e('Username or E-mail', 'mk_framework');?></label>
						<input type="text" id="forgot-email" name="user_login" class="text-input">
					</span>
					<span class="form-section">
						<input type="submit" id="recover" name="submit" class="shop-flat-btn shop-skin-btn" value="<?php _e("Get New Password", 'mk_framework');?>">
					</span>
					<div class="register-login-links">
						<a class="mk-return-login" href="#"><?php _e("Remember Password?", 'mk_framework');?></a>
					</div>
				</form>

		</div>
	</div>
</div>
<?php
}
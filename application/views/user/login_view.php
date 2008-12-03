<?php echo $this->load->view('_common/header'); ?>
<div>
	<ul>
		<li><?php echo anchor('user/register', 'Sign Up For A New Account'); ?></li>
  </ul>
</div>
<div id="content">
	<h1><?php echo $heading ?></h1>
	<?php
	 if($error){
		echo "<div class=\"error\">".$error."</div>";
		}; 
	?>
	<?php echo form_open('user/verify')."\n"; ?>
		<div>
			  <div class="login-input">
				<label>Email Address</label>
				<input class="input" type="text" name="email"/>
			  </div>
			  <div class="login-input">
				<label>Password <?php echo anchor('user/reset_password_request', '(I Forgot My Password)', array('style' => 'font-size:11px;')); ?></label>
				<input class="input" type="password" name="password"/>
			  </div>
			  <input class="submit" type="submit" name="submit" value="Login" />
		</div>
	</form>
</div><!-- end div content -->
<!-- load the footer -->
<?php echo $this->load->view('_common/footer'); ?>

<?php echo $this->load->view('_common/header'); ?>

<div id="content">
	<h1><?php echo $heading ?></h1>
	
	<?php if ($success): ?>
			<p><?php echo $success; ?></p>
			<?php echo anchor('user/index', 'Return to Login'); ?>
	<?php else: ?>
	<?php echo $this->validation->error_string; ?>
	<?php echo form_open('user/reset_password_request/'.$hash)."\n"; ?>
	<div>
		<label>Reset Password for <strong><?php echo $email ?></strong>.</label>
		<div class="login-input">
		  <label>Password</label>
			<input class="input" type="password" name="password" id="password" value="" value="<?php echo $this->validation->passconf;?>" size="25" />
		  </label>
		</div>
		<div class="login-input">
		  <label>Password Confirm</label>
			<input class="input" type="password" name="passconf" value="" value="<?php echo $this->validation->passconf;?>" size="25" />
		  </label>
		</div>
		<input class="submit" type="submit" name="submit" value="Reset Password" />
	</div>
	</form>
	<?php endif ?>
</div><!-- end div content -->
<!-- load the footer -->
<?php echo $this->load->view('_common/footer'); ?>

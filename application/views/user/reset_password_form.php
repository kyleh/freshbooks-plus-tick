<?php echo $this->load->view('_common/header'); ?>
<div id="content">
	<ul>
		<li><?php echo anchor('user/index', 'Return to Login'); ?></li>
	</ul>
	<h1><?php echo $heading ?></h1>
	<?php if ($success): ?>
		<p><?php echo $success; ?></p>
	<?php else: ?>
	<p>Submit this form and we will email you a link that will allow you to reset your password.</p>
	<?php echo $this->validation->error_string; ?>
	<?php echo form_open('user/reset_password_request')."\n"; ?>
		<div>
			  <div class="login-input">
				<label>Email Address</label>
				<input class="input" type="text" name="email" value="<?php echo $this->validation->email;?>"/>
			  </div>
			  <input class="submit" type="submit" name="submit" value="Request Password Reset" />
		</div>
	</form>
	<?php endif ?>
</div><!-- end div content -->
<!-- load the footer -->
<?php echo $this->load->view('_common/footer'); ?>

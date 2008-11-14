<?php echo $this->load->view('_common/header'); ?>
<div id="content">
	<h1><?php echo $heading ?></h1>
		
		<p>To use this application you need to configure your FreshBooks settings and Tick settings. You can get these settings from inside your FreshBooks account. Please follow the directions below if you aren't exactly sure on how to activate the FreshBooks API.</p>
	
	<h3>Log into your Freshbooks Account</h3> 
	<p>Click <strong>Settings</strong> then <strong>Enable FreshBooks API</strong>. Once you've enabled the API, you will see <strong>Your API URL</strong> & <strong>Your Authentication Token</strong> in the middle of that page. You'll need to enter those here to continue. </p>
	<h3>Tick Settings</h3>
	<p>Your Tick email and password were sent to you when you opened your account.</p>
	<img src="<?php echo(base_url()); ?>public/images/settings.jpg" alt="FreshBooks screenshot of API settings page." style="float:right; border: 3px solid rgb(201, 201, 201); margin-left: 50px; margin-top: 10px;" />
	
	<?php echo form_open('settings')."\n"; ?>
	<div id="apiform">
		<div class="api-input">
          <label>FreshBooks API URL</label>
			<input class="input" type="text" name="fburl" value="<?php echo $fburl ? $fburl : $this->validation->fburl; ?>" size="50" />
        </div>
		<div class="api-input">
          <label>FreshBooks Token</label>
			<input class="input" type="text" name="fbtoken" value="<?php echo $fbtoken ? $fbtoken : $this->validation->fbtoken; ?>" size="50" />
        </div>
		<div class="api-input">
          <label>Tick Email Address</label>
			<input class="input" type="text" name="tsemail" value="<?php echo $tsemail ? $tsemail : $this->validation->tsemail; ?>" size="50" />
        </div>
		<div class="api-input">
          <label>Tick Password</label>
			<input class="input" type="password" name="tspassword" value="<?php echo $tspassword ? $tspassword : $this->validation->tspassword; ?>" size="50" />
        </div>
		<input class="submit" type="submit" name="submit" value="<?php echo $submitname ?>" />
	</div>
	</form>
</div><!-- end div content -->
<!-- load the footer -->
<?php echo $this->load->view('_common/footer'); ?>
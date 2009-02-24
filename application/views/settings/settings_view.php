<?php echo $this->load->view('_common/header'); ?>
<div id="content">
	<h1><?php echo $heading ?></h1>
		
	<div style="width: 50%;float: left;">
		<p>We just need your FreshBooks API settings.</p>
		<p>Log into your FreshBooks account. Click <strong>Settings</strong> then <strong>Enable FreshBooks API</strong>. Once you've enabled the API, you will see <strong>Your API URL</strong> & <strong>Your Authentication Token</strong> in the middle of that page. You'll need to enter those here to continue.</p>
	</div>
	
	<img src="<?php echo(base_url()); ?>public/images/settings.jpg" alt="FreshBooks screenshot of API settings page." style="float:right; border: 3px solid rgb(201, 201, 201); margin-left: 10px; margin-top: 10px;" />
	
	<?php echo form_open('settings', array('id' => 'settings-form', 'class' => 'form_button'))."\n"; ?>
	<div id="apiform">
		<div class="api-input">
          <label>FreshBooks API URL</label>
			<input class="input" type="text" name="fburl" value="<?php echo $fburl ? $fburl : $this->validation->fburl; ?>" size="50" />
    </div>
		<div class="api-input">
          <label>FreshBooks Token</label>
			<input class="input" type="text" name="fbtoken" value="<?php echo $fbtoken ? $fbtoken : $this->validation->fbtoken; ?>" size="50" />
        </div>

		<input type="hidden" name="tickurl" value="<?= $tickurl ?>" />
		<input type="hidden" name="tickemail" value="<?= $tickemail ?>" />
		<input type="hidden" name="tickpassword" value="<?= $tickpassword ?>" />

		<span style="padding-left: 10px;"></span><button type="submit" name="submit"><?php echo $submitname ?></button>
	</div>
	</form>
</div><!-- end div content -->
<!-- load the footer -->
<?php echo $this->load->view('_common/footer'); ?>

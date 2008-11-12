<?php echo $this->load->view('_common/header'); ?>
<div id="content">
	<h1><?php echo $heading ?></h1>

	<?php echo form_open('settings')."\n"; ?>
	<div id="apiform">
		<div class="api-input">
          <label>FreshBooks API URL</label>
			<input class="input" type="text" name="fburl" value="<?php echo $fburl ? $fburl : $this->validation->fburl; ?>" size="50" />
		<div class="form-note">
            <p><strong>To Add Your FreshBooks API URL:</strong>  1. Login to FreshBooks 2. Click the 'Settings' link at the top of the header. 3. Under 'Step 3: Third Party Services' click the 'Enable FreshBooks API' link. 4. Check the 'Yes, I agree to the API terms of service' checkbox and your API URL and Token will be available.  Copy and paste the API URL into the form field.</p>
          </div>
        </div>
		<div class="api-input">
          <label>FreshBooks Token</label>
			<input class="input" type="text" name="fbtoken" value="<?php echo $fbtoken ? $fbtoken : $this->validation->fbtoken; ?>" size="50" />
		<div class="form-note">
            <p><strong>To Add Your FreshBooks API Token:</strong>  1. Login to FreshBooks 2. Click the 'Settings' link at the top of the header. 3. Under 'Step 3: Third Party Services' click the 'Enable FreshBooks API' link. 4. Check the 'Yes, I agree to the API terms of service' checkbox and your API URL and Token will be visible. Copy and paste the API token into this form field.</p>
          </div>
        </div>
		<div class="api-input">
          <label>Tick Email Address</label>
			<input class="input" type="text" name="tsemail" value="<?php echo $tsemail ? $tsemail : $this->validation->tsemail; ?>" size="50" />
		<div class="form-note">
            <p><strong>To Add Your Tick Email Address:</strong></p>
          </div>
        </div>
		<div class="api-input">
          <label>Tick Password</label>
			<input class="input" type="text" name="tspassword" value="<?php echo $tspassword ? $tspassword : $this->validation->tspassword; ?>" size="50" />
		<div class="form-note">
            <p><strong>To Add Your Tick Password:</strong></p>
          </div>
        </div>
		<input class="submit" type="submit" name="submit" value="<?php echo $submitname ?>" />
	</div>
	</form>
</div><!-- end div content -->
<!-- load the footer -->
<?php echo $this->load->view('_common/footer'); ?>
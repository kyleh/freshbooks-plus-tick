<?php echo $this->load->view('_common/login_header'); ?>

<div class="login-window">
	<table class="window" cellspacing="0">
		<tr class="one">
			<td class="one"></td>
			<td class="two"></td>
			<td class="three"></td>	
		</tr>
		<tr class="two">

			<td class="one"></td>
			<td class="two">
				<div class="bg_blue">
					<div style="min-height: 300px;" class="span-20 bg_white">
						<img src="<?php echo(base_url()); ?>public/images/logos.gif" alt="Freshbooks + Tick" width="352" height="75" />
						
						<div class="login-form">
							<div>
								
								<?php echo form_open('user/verify')."\n"; ?>

								<label for="url" class="login-label">Tick URL</label> <input type="text" name="tickurl" value="<?= $tickurl ?>" /> <br />
								<label for="email" class="login-label">Tick Email</label> <input type="text" name="tickemail" value="<?= $tickemail ?>" /> 
								<br />
								<label for="password" class="login-label">Tick Password</label> <input type="password" name="tickpassword" />
								
								<button value="submit"><span><span>Login</span></span></button>
								</form>
							</div>
							<div class="login-form-footer"></div>
								<?php
								 if($error){
									echo '<span style="padding-left: 10px; color: red;">' . $error . '</span>';
									}; 
								?>
						</div>
					</div>

					<div class="span-9 prepend-1 white">
						<h2>FreshBooks + Tick</h2>
						<p>
							You can use <a href="http://www.tickspot.com" style="color: white;">Tick</a> to track time on all your projects. Then, when it's time to invoice your clients, use this tool to generate invoices in <a href="http://www.freshbooks.com" style="color: white;">FreshBooks</a>.
						</p>
					</div>

					<div class="clear"></div>
				</div>
			</td>
			<td class="three"></td>	
		</tr>
		<tr class="three">
			<td class="one"></td>
			<td class="two"></td>
			<td class="three"></td>	
		</tr>

	</table>

</div>











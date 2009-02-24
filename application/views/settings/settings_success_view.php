<?php echo $this->load->view('_common/header'); ?>
<div id="content">
	<h1><?php echo $heading ?></h1>
	
	<div id="message">
		<h2>Your settings were saved successfully.</h2>
		<p><?php echo anchor('tick/index', 'Proceed to project list -->'); ?></p>
	</div><!-- end div message -->

</div><!-- end div content -->
<!-- load the footer -->
<?php echo $this->load->view('_common/footer'); ?>

<!-- load the header -->
<?php echo $this->load->view('_common/header'); ?>

<div id="content">
	<h1><?php echo $heading ?></h1>
	
	<div id="invoice_results" class="border">
		<?php if ($error): ?>
			<p><?php echo $error; ?></p>
		<?php endif ?>
		
		<?php if ($invoice_results): ?>
			<p><?php echo $invoice_results; ?></p>
		<?php endif ?>
	</div><!-- end div invoice_results -->
	
	<!-- TODO: Derek - style these to make them stand out... -->
	<p><?php echo anchor('tick/index', 'Create Another Invoice').'  |  '; ?>
		<?php echo anchor("$invoice_url", 'View Invoice In FreshBooks', array('target' =>  '_blank')) ?></p>
	
</div><!-- end div content -->

<!-- load the footer -->
<?php echo $this->load->view('_common/footer'); ?>
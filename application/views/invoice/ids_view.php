<!-- load the header -->
<?php echo $this->load->view('_common/header'); ?>

<div id="content">
	<h1><?php echo $heading ?></h1>
	<?php var_dump($jt_status) ?>
	
	<div id="invoice_results" class="border">
		<?php if ($error): ?>
			<p><?php echo $error; ?></p>
		<?php endif ?>
		
		<?php foreach ($ids as $id): ?>
			<p><?php echo $id->fb_invoice_id; ?></p>
		<?php endforeach ?>
		
		<pre><?php var_dump($ids); ?></pre>

</div><!-- end div content -->

<!-- load the footer -->
<?php echo $this->load->view('_common/footer'); ?>
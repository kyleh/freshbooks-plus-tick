<!-- load the header -->
<?php echo $this->load->view('_common/header'); ?>

<div id="content">
	<h1><?php echo $heading ?></h1>
	
<?php if ($error): ?>
	<div id="invoice_results" class="border">
			<p><?php echo $error; ?></p>
	</div><!-- end div invoice_results-->	
	<?php echo anchor('tick/index', 'Create Another Invoice');?>
<?php endif //end error?>

<?php if ($no_client_match): ?>
	<div id="invoice_results" class="border">
			<p><?php echo $no_client_match; ?></p>
	</div><!-- end div invoice_results-->	


	<?php echo form_open('invoice/create_invoice', array('class' => 'form_button')); ?>
		<input type="hidden" name="invoice_type" value="<?php echo $post_data['invoice_type']; ?>" />
		<input type="hidden" name="client_name" value="<?php echo $post_data['client_name']; ?>" />
		<input type="hidden" name="total_hours" value="<?php echo $post_data['total_hours']; ?>" />
		<input type="hidden" name="entry_ids" value="<?php echo $post_data['entry_ids']; ?>" />
		<input type="hidden" name="project_name" value="<?php echo $post_data['project_name']; ?>" />
    <button type="submit" name="submit_invoice" onclick="dis(this);">Re-Submit<br />Invoice</button>
	</form>
<?php endif ?>	

<?php if ($invoice_results): ?>
	<div id="invoice_results" class="border">	
			<p><?php echo $invoice_results; ?></p>
	</div><!-- end div invoice_results -->	

	<p><?php echo anchor('tick/index', 'Create Another Invoice').'  |  '; ?>
		<?php echo anchor("$invoice_url", 'View Invoice In FreshBooks', array('target' =>  '_blank')) ?></p>
<?php endif //invoice results?>
	
	
</div><!-- end div content -->

<!-- load the footer -->
<?php echo $this->load->view('_common/footer'); ?>
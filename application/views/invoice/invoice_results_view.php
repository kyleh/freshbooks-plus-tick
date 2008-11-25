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
		<?php foreach ($post_data as $key => $value): ?>
			<input type="hidden" name="<?php echo $key ?>" value="<?php echo $value; ?>" />
		<?php endforeach ?>
		<?php if ($line_items): ?>
			<?php $num = 1; ?>
			<!-- line items -->
			<?php foreach ($line_items as $item): ?>
				<?php foreach ($item as $key => $value): ?>
					<input type="hidden" name="<?php echo $key.'_'.$num; ?>" value="<?php echo $value; ?>" />
				<?php endforeach ?>
			<?php $num++; ?>
			<?php endforeach ?>
			<input type="hidden" name="num_line_items" value="<?php echo $num_line_items; ?>" />
		<?php endif ?>
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
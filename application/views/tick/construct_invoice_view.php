<?php
//sort multidimential array by entry date
function date_sort($x, $y){
	return strcasecmp($x['entry_date'], $y['entry_date']);
}
if ($ts_entries) {
	usort($ts_entries, 'date_sort');
}
?>

<!-- load the header -->
<?php echo $this->load->view('_common/header'); ?>

<div id="content">
	<h1><?php echo $heading ?></h1>
	<div id="invoice_range" class="border">
		<div class="left_info">
			<p class="client"><?php echo $project_name; ?> - <?php echo $client_name; ?>
            <br class="clear" />
            <br class="clear" />
			<? echo anchor('tick/select_project', 'Select A Different Project'); ?></p>
    </div><!-- end div left_info -->
		<div class="right_info">
       <!-- TODO All Entries Button KEEP CLASS -->
	   	<div class="border_button">
				
				<?php 
				$attributes = array('class' => 'form_button');
				echo form_open('tick/construct_invoice', $attributes)."\n"; 
				?>
					<input type="hidden" name="project_name" value="<?php echo $project_name ?>" />
					<input type="hidden" name="client_name" value="<?php echo $client_name ?>" />
					<input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
					<input type="hidden" name="filter" value="get_all" />
					<button type="submit" name="all_entries">Use All<br />Entries</button>
				</form>
	    </div><!-- end div border button -->
	    <!-- TODO Refresh Entries Button KEEP CLASS -->
	    <div class="border_button">
	    	<?php 
				$attributes = array('class' => 'form_button');
				echo form_open('tick/construct_invoice', $attributes)."\n"; 
				?>
					<button type="submit" name="refresh_entries">Refresh<br />Entries</button>
					<input type="hidden" name="filter" value="refresh" />
					<input type="hidden" name="project_name" value="<?php echo $project_name ?>" />
					<input type="hidden" name="client_name" value="<?php echo $client_name ?>" />
					<input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
	    </div><!-- end div border button -->
	<!-- TODO Date Pickers -->
					<div class="border_picker"> 
						<span>
						<input type="text" class="divider-slash format-m-d-y no-transparency range" name="options[start_date]" id="start_date" value="<?php echo $start_date == '' ? date('m/d/Y', strtotime($ts_entries[0]['entry_date'])): $start_date; ?>" />
						</span> 
						<span>
						<input type="text" class="divider-slash format-m-d-y no-transparency range" name="options[end_date]" id="end_date" value="<?php echo $end_date ?>" />
						</span> 
						<br class="clear" />
					</div><!-- end div border picker -->
				</form>
		</div><!-- end div right info -->
		<div class="clear"></div>
	</div><!-- end div invoice rangs -->
	
	<div class="border">
	<?php if ($ts_entries): ?>
	<table>
		<tr>
			<th>Date</th>
			<th>Projects</th>
			<th>Task Name</th>
			<th>Notes</th>
			<th class="hours">Hours</th>
		</tr>
	<?php $num = 0; ?>
	<?php foreach ($ts_entries as $entry): ?>
		<?php
		$date = date('M j Y', strtotime($entry['entry_date']));
		$alt = ($num + 2) % 2;
			if ($alt != 0) {
				echo "<tr class=\"alt\">\n";
			}else{
				echo "<tr>\n";
			}
		?>
			<td><?php echo $date; ?></td>
			<td><?php echo $entry['project_name']; ?></td>
			<td><?php echo $entry['task_name']; ?></td>
			<td><?php echo $entry['notes']; ?></td>
			<td class="hours"><?php echo $entry['hours']; ?></td>
		</tr>
		<?php $entry_ids .= $entry['entry_id'].','; ?>
		<?php $num++; ?>
	<?php endforeach ?>
	<tr>
		<td colspan="5" class="total_hours"><strong>Total Hours: <?php echo $total_hours; ?></strong></td>
	</tr>
	
	</table>
	<?php else: ?>
		<p>There were no entries that matched your selected timeframe. Please select a new timeframe or use all available entries to construct your invoice.</p>
	<?php endif ?>
	</div>
	<?php if ($ts_entries): ?>
	<h2>Create an Invoice in FreshBooks</h2>
	<div id="create_invoice" class="border">
   	  <div class="left_info_bottom" style="padding-right:10px; width: 365px;">
  	  		<p style="width: 250px;"><span>Detailed Line Items:</span> <br />
            All Line Items created for this Invoice will be summarized by <strong>Task</strong>.</p>
            <div class="border_button">
			<?php echo form_open('invoice/create_invoice', array('class' => 'form_button')); ?>
				<input type="hidden" name="invoice_type" value="detailed" />
				<input type="hidden" name="client_name" value="<?php echo $ts_entries[0]['client_name']; ?>" />
				<input type="hidden" name="total_hours" value="<?php echo $total_hours; ?>" />
				<input type="hidden" name="entry_ids" value="<?php echo $entry_ids; ?>" />
				<input type="hidden" name="project_name" value="<?php echo $ts_entries[0]['project_name']; ?>" />
				<!-- line item fields -->
				<?php $num = 0; ?>
				<?php foreach ($ts_entries as $entry): ?>
					<?php $num++; ?>
					<?php $date = date('M j Y', strtotime($entry['entry_date'])); ?>
					<!-- line item -->
					<input type="hidden" name="<?php echo 'date_'.$num; ?>" value="<?php echo $date; ?>" />
					<input type="hidden" name="<?php echo 'task_'.$num; ?>" value="<?php echo $entry['task_name']; ?>" />
					<input type="hidden" name="<?php echo 'note_'.$num; ?>" value="<?php echo $entry['notes']; ?>" />
					<input type="hidden" name="<?php echo 'hour_'.$num; ?>" value="<?php echo $entry['hours']; ?>" />
					<!-- end line item -->
				<?php endforeach ?>
				<input type="hidden" name="num_line_items" value="<?php echo $num; ?>" />
        <button type="submit" name="submit_invoice" onclick="dis(this);">Create<br />Invoice</button>
			</form>
            </div>
      </div>
	<div class="right_info_bottom left_border">
		  <p style="width: 250px; padding-left: 10px"><span>Summarized Line Items:</span> <br />
            All Line Items created for this Invoice will be summarized by <strong>Project</strong>.</p>
            <div class="border_button">
			<?php echo form_open('invoice/create_invoice', array('class' => 'form_button')); ?>
				<input type="hidden" name="invoice_type" value="summary" />
				<input type="hidden" name="client_name" value="<?php echo $ts_entries[0]['client_name']; ?>" />
				<input type="hidden" name="total_hours" value="<?php echo $total_hours; ?>" />
				<input type="hidden" name="entry_ids" value="<?php echo $entry_ids; ?>" />
				<input type="hidden" name="project_name" value="<?php echo $ts_entries[0]['project_name']; ?>" />
        <button type="submit" name="submit_invoice" onclick="dis(this);">Create<br />Invoice</button>
			</form>
            </div>
        </div>  
	<div class="clear"></div>
	</div><!-- end div create_invoice -->
	<?php endif ?>	
</div><!-- end div content -->

<!-- load the footer -->
<?php echo $this->load->view('_common/footer'); ?>
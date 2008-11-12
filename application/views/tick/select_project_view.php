<!-- load the header -->
<?php echo $this->load->view('_common/header'); ?>

<div id="content">
	<h1><?php echo $heading ?></h1>
	
	<div class="border">
	<?php if ($error): ?>
		<p><?php echo $error; ?></p>
	<?php endif ?>	
		
	<?php if ($projects): ?>
		<table cellspacing="0" cellpadding="0">
			<tr>
				<th>Project Name</th>
				<th>Client</th>
				<th></th>
			</tr>
			<?php $num = 0; ?>
			<?php foreach ($projects as $project): ?>
				<?php
					$alt = ($num + 2) % 2;
						if ($alt != 0) {
							echo "<tr class=\"alt\">\n";
						}else{
							echo "<tr>\n";
						}
				?>
				
				<td><?php echo $project['project']; ?></td>
				<td><?php echo $project['client']; ?></td>
				<td class="project_submit"><?php echo form_open('tick/construct_invoice')."\n"; ?>
				<input class="submit" type="submit" value="Select Project for Invoice" />
				<input type="hidden" name="client_name" value="<?php echo $project['client']; ?>" />
				<input type="hidden" name="project_name" value="<?php echo $project['project']; ?>" />
				<input type="hidden" name="project_id" value="<?php echo $project['project_id']; ?>" />
				</form></td>
				
			</tr>
			<?php $num++; ?>
			<?php endforeach ?>
		</table>
	<?php else: ?>	
		<p>There are currently no projects with open entries.</p>
	<?php endif ?>
	</div>

</div><!-- end div content -->

<!-- load the footer -->
<?php echo $this->load->view('_common/footer'); ?>
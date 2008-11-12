<?php foreach ($ts_projects as $project): ?>
	<h5><?php echo $project->name ?></h5>
	<?php foreach ($project->task as $task): ?>
		<p style="margin-left:10px;"><?php echo $task->name; ?></p>
	<?php endforeach ?>
<?php endforeach ?>





<?php echo form_open('tickspot/generate')."\n"; ?>
<table>
	<tr>
		<th>Date</th>
		<th>Projects</th>
		<th>Task Name</th>
		<th>Notes</th>
		<th>Hours</th>
	</tr>
<?php $num = 1; ?>
<?php foreach ($ts_entries as $entry): ?>
	<input type="hidden" name="<?php echo 'entry_id_'.$num; ?>" value="<?php echo $entry->id; ?>" id="some_name">
	<tr>
			<td><?php echo $entry->date; ?></td>
			<td><?php echo $entry->project_name; ?></td>
			<td><?php echo $entry->task_name; ?></td>
			<td><?php echo $entry->notes; ?></td>
			<td><?php echo $entry->hours; ?></td>
	</tr>
	<?php $num++; ?>
<?php endforeach ?>
</table>
</form>



function processEntries($entries)
{
	$processed_entries = array();
		foreach ($entries => $entry) {
			$dataset = array(
				'entry_id' => $entry->id,
				'entry_date' => (string)$entry->date,
				'project_name' => (string)$entry->project_name,
				'task_name' => (string)$entry->task_name,
				'task_id' => $entry->task_id,
				'notes' => (string)$entry->notes,
				'hours' => $entry->hours,
				);
			$processed_entries[] = $dataset;
		}
	return $processed_entries;
}



		array_multisort($processed_entries, $processed_entries['entry_date'],$processed_entries['task_name'],$processed_entries['client_name'],$processed_entries['hours'],$processed_entries['enrty_id'],$processed_entries['task_id'],$processed_entries['notes'],$processed_entries['project_name']);
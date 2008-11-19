<?php
Class Entries_model extends Model{
	
	function __construct()
	{
	  // Call the Model constructor
	  parent::Model();
	}
	
	function getInvoiceIds()
	{
		$userid = $this->session->userdata('userid');
		$this->db->select('fb_invoice_id');
		$this->db->where('user_id', $userid);
		$this->db->from('entries');
		$this->db->distinct();
		$query = $this->db->get();
		
		if ($query->num_rows > 0)
		{
			return $query->result();
		}
		else
		{
			return FALSE;
		}
	}

	function getEntriesIds($invoice_id)
	{
		$userid = $this->session->userdata('userid');
		$this->db->select('ts_entry_id');
		$this->db->where('user_id', $userid);
		$this->db->where('fb_invoice_id', $invoice_id);
		$this->db->from('entries');
		$query = $this->db->get();
		
		if ($query->num_rows > 0)
		{
			return $query->result();
		}
		else
		{
			return FALSE;
		}
	}
	
	function insertEntries($entry_keys, $fb_invoice_id)
	{
		$user_id = $this->session->userdata('userid');
		
		foreach ($entry_keys as $entry) {
			$data = array(
				'user_id' => $user_id,
				'ts_entry_id' => $entry,
				'fb_invoice_id' => $fb_invoice_id,
				);
				    
			$this->db->insert('entries', $data);
		}
	}
	
	function deleteEntry($entry_id)
	{
		$userid = $this->session->userdata('userid');
		$this->db->where('user_id', $userid);
		$this->db->where('ts_entry_id', $entry_id);
		$this->db->from('entries');
		$query = $this->db->delete();
		return $query;
	}
	
}
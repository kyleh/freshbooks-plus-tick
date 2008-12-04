<?php
/**
 * Model for managing database transactions in the entries table.
 *
 * @package Entries Controller
 * @author Kyle Hendricks kyleh@mendtechnologies.com
 **/

Class Entries_model extends Model
{
	
	function __construct()
	{
	  // Call the Model constructor
	  parent::Model();
	}
	
	/**
	 * Selects distinct invoice ids from the entries table.
	 *
	 * @return distinct invoice ids if records exit, False on no records
	 **/
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
	
	/**
	 * Selects entry id for a FreshBooks invoice id
	 *
	 * @param $invoice_id, integer - FreshBooks invoice id
	 * @return entry ids if records exit, False on no records
	 **/
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
	
	/**
	 * Selects entry id for a FreshBooks invoice id
	 *
	 * @param $entry_keys, array - Tick entry keys
	 * @param $fb_invoice_id, integer - FreshBooks invoice id
	 * @return bool - True on success, False on fail
	 **/
	function insertEntries($entry_keys, $fb_invoice_id)
	{
		$user_id = $this->session->userdata('userid');
		
		foreach ($entry_keys as $entry)
		{
			$data = array(
				'user_id' => $user_id,
				'ts_entry_id' => $entry,
				'fb_invoice_id' => $fb_invoice_id,
				);
				    
			$this->db->insert('entries', $data);
		}
	}
	
	/**
	 * Selects entry id for a FreshBooks invoice id
	 *
	 * @param $entry_id, integer - entry table record id
	 * @return bool - True on success, False on fail
	 **/
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
/* End of file entries_model.php */
/* Location: /application/models/entries_model.php */
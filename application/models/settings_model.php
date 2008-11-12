<?php
Class Settings_model extends Model {

	function __construct()
	{
	  // Call the Model constructor
	  parent::Model();
	}
        
	function getSettings()
	{
		$userid = $this->session->userdata('userid');
		$this->db->where('userid', $userid);
		$this->db->from('apisettings');
		$query = $this->db->get();
		if ($query->num_rows > 0) {
			return $query->row();
		}else{
			return FALSE;
		}
	}

	function insert_settings()
	{
		$data = array(
			'userid' => $this->session->userdata('userid'),
			'fburl' => $this->input->post('fburl'),
			'fbtoken' => $this->input->post('fbtoken'),
			'tsemail' => $this->input->post('tsemail'),
			'tspassword' => $this->input->post('tspassword')
			);
	    
		$this->db->insert('apisettings', $data);
	}
	
	function update_settings()
	{
		$userid = $this->session->userdata('userid');
		$data = array(
			'fburl' => $this->input->post('fburl'),
			'fbtoken' => $this->input->post('fbtoken'),
			'tsemail' => $this->input->post('tsemail'),
			'tspassword' => $this->input->post('tspassword')
			);
		
		$this->db->where('userid', $userid);
		$this->db->update('apisettings', $data);
	}

}
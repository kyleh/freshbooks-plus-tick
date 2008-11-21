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
			'tickurl' => $this->input->post('tickurl'),
			'tickemail' => $this->input->post('tickemail'),
			'tickpassword' => $this->input->post('tickpassword')
			);
	    
		$this->db->insert('apisettings', $data);
	}
	
	function update_settings()
	{
		$userid = $this->session->userdata('userid');
		$data = array(
			'fburl' => $this->input->post('fburl'),
			'fbtoken' => $this->input->post('fbtoken'),
			'tickurl' => $this->input->post('tickurl'),
			'tickemail' => $this->input->post('tickemail'),
			'tickpassword' => $this->input->post('tickpassword')
			);
		
		$this->db->where('userid', $userid);
		$this->db->update('apisettings', $data);
	}

}
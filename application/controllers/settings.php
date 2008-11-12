<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

Class Settings extends Controller
{
	function __construct()
	{
		parent::Controller();
		$this->load->helper(array('form', 'url'));
	}
	
	function index()
	{
		//check for login
		$loggedin = $this->session->userdata('loggedin');
		if (!$loggedin) {
		redirect('user/index');
		$data['navigation'] = FALSE;
		}else{
		$data['navigation'] = TRUE;	
		}
		//set page specific variables
		$data['title'] = 'Tick to FreshBooks Invoice Generator :: API Settings';
		$data['heading'] = 'Tick to FreshBooks Invoice Generator API Settings';
		$data['submitname'] = 'Save API Settings';
		$data['name'] = $this->session->userdata('name');
		$data['fburl']   = '';
		$data['fbtoken'] = '';
		$data['tsemail']   = '';
		$data['tspassword'] = '';
		
		//check for settings
		$this->load->model('Settings_model', 'settings');
		$current_settings = $this->settings->getSettings();
		if ($current_settings) {
			$data['submitname'] = 'Update API Settings';
			//set form fields
			$data['fburl']   = $current_settings->fburl;
			$data['fbtoken'] = $current_settings->fbtoken;
			$data['tsemail']   = $current_settings->tsemail;
			$data['tspassword'] = $current_settings->tspassword;
		}

		$this->load->library('validation');
		$this->validation->set_error_delimiters('<div class="error">', '</div>');
		
		//validation rules
		$rules['fburl']		= "required";
		$rules['fbtoken']	= "required";
		$rules['tsemail']		= "required";
		$rules['tspassword']	= "required";
		$this->validation->set_rules($rules);
		//set form fields
		$fields['fburl']	= 'Freshbooks URL';
		$fields['fbtoken']	= 'Freshbooks Token';
		$fields['tsemail']	= 'Tick Email Address';
		$fields['tspassword']	= 'Tick Password';
		$this->validation->set_fields($fields);

		if ($this->validation->run() == FALSE){
			$this->load->view('settings/settings_view', $data);
		}else{
			$this->load->model('Settings_model', 'settings');
			if ($_POST['submit']  == 'Update API Settings') {
				$this->settings->update_settings();
			}else{
				$this->settings->insert_settings();
			}
			$this->load->view('settings/settings_success_view', $data);
		}
	}
	
}
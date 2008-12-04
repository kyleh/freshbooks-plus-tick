<?php
/**
 * Controller for managing API settings.
 *
 * @package Settings Controller
 * @author Kyle Hendricks kyleh@mendtechnologies.com
 **/
Class Settings extends Controller
{
	function __construct()
	{
		parent::Controller();
		$this->load->helper(array('form', 'url'));
	}
	
	/**
	 * Default controller action. Adds and Updates user API settings.
	 *
	 * @return views/settings/settings_view.php and views/settings/settings_success_view.php
	 **/
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
		$data['tickurl'] = '';
		$data['tickemail']   = '';
		$data['tickpassword'] = '';
		
		//check for settings
		$this->load->model('Settings_model', 'settings');
		$current_settings = $this->settings->getSettings();
		if ($current_settings) {
			$data['submitname'] = 'Update API Settings';
			//set form fields
			$data['fburl']   = $current_settings->fburl;
			$data['fbtoken'] = $current_settings->fbtoken;
			$data['tickurl']   = $current_settings->tickurl;
			$data['tickemail']   = $current_settings->tickemail;
			$data['tickpassword'] = $current_settings->tickpassword;
		}

		$this->load->library('validation');
		$this->validation->set_error_delimiters('<div class="error">', '</div>');
		
		//validation rules
		$rules['fburl']		= "required";
		$rules['fbtoken']	= "required";
		$rules['tickurl']		= "required";
		$rules['tickemail']		= "required";
		$rules['tickpassword']	= "required";
		$this->validation->set_rules($rules);
		//set form fields
		$fields['fburl']	= 'Freshbooks URL';
		$fields['fbtoken']	= 'Freshbooks Token';
		$fields['tickurl']		= "Tick URL";
		$fields['tickemail']	= 'Tick Email Address';
		$fields['tickpassword']	= 'Tick Password';
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
/* End of file settings.php */
/* Location: /application/controllers/settings.php */
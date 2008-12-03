<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

Class User extends Controller {
	
	function __construct()
	{
		parent::Controller();
		$this->load->helper(array('form', 'url', 'html'));
	}
	
	function index()
	{
		//load page specific variables
		$loggedin = $this->session->userdata('loggedin');
		$data['title'] = 'FreshBooks + Tick :: Login';
		$data['heading'] = 'FreshBooks + Tick Login';
		$data['error'] = FALSE;
		$data['navigation'] = FALSE;
		//check to see if user is logged in
		if (!$loggedin) {
		$this->load->view('user/login_view',$data);
		}else{
			redirect('settings/index');
		}
	}

	function register()
	{
		//check to see if user is logged in
		$loggedin = $this->session->userdata('loggedin');
		if ($loggedin) {
			redirect('settings/index');
		}
		//load page specific variables
		$data['title'] = 'Tick to FreshBooks Invoice Generator::Register for a New Account';
		$data['heading'] = 'Sign Up For A New Account';
		$data['error'] = '';
		$data['navigation'] = FALSE;
		//form validation
		$this->load->library('validation');
		$this->validation->set_error_delimiters('<div class="error">', '</div>');
	
		$rules['name']		= "required";
		$rules['email']		= "required|valid_email|callback_email_check";
		$rules['password']	= "required|matches[passconf]";
		$rules['passconf']	= "required";
	
		$this->validation->set_rules($rules);
	
		$fields['name'] = 'Full Name';
		$fields['password'] = 'Password';
		$fields['passconf'] = 'Password Confirmation';
		$fields['email'] = 'Email Address';
	
		$this->validation->set_fields($fields);
	
		if ($this->validation->run() == FALSE){
			$this->load->view('user/register_view', $data);
		}else{
			$this->load->model('User_model', 'user');
			//input user data
			$this->user->insert_user();
			//get user data to set session variables 
			$user = $this->user->getuser($this->input->post('email'));
			//set up session ans set session vars
			$userinfo = array('userid' => $user->id, 'loggedin' => TRUE, 'username' => $user->email, 'name' => $user->name);
			$this->session->set_userdata($userinfo); 
			redirect('settings/index');
		}
	}
	
	function email_check($str)
	{
		$this->load->model('User_model', 'email');
		$mail_db = $this->email->check_for_email($str);
	
		if ($mail_db > 0) {
			$this->validation->set_message('email_check', 'The %s field  is already in use please use another email address.');
			return FALSE;
		}else{
			return TRUE;
		}
	}
	
	function email_exist($str)
	{
		$this->load->model('User_model', 'user');
		$mail_db = $this->user->check_for_email($str);
	
		if ($mail_db > 0) {
			return TRUE;
		}else{
			$this->validation->set_message('email_exist', 'The %s provided could not be found.  Please check your email address and try again.');
			return FALSE;
		}
	}
	
	function verify()
	{
		//load page specific variables
		$data['title'] = 'Tick to FreshBooks Invoice Generator::Login';
		$data['heading'] = 'Tick to FreshBooks Invoice Generator Login';
		$data['error'] = FALSE;
		$data['navigation'] = FALSE;
		
		$this->load->model('User_model', 'user');
		//get user from database by email
		$user = $this->user->getuser($this->input->post('email'));
		$password = md5($this->input->post('password'));
		//if user exists check for password match
		if ($user) {
			if ($user->password == $password) {
				//start session - set vars
				$userinfo = array('userid' => $user->id, 'loggedin' => TRUE, 'username' => $user->email, 'name' => $user->name);
				$this->session->set_userdata($userinfo);
				//check for settings if user has settings send to sync page otherwise send to settings page
				$this->load->model('Settings_model', 'settings');
				$got_settings = $this->settings->getSettings();
				if ($got_settings) {
					redirect('tick/index');
				}else{
					redirect('settings/index');
				}
			}else{
				//if password does not match return with error message
				$data['error'] = "Invalid Password - Please Try Again.";
			}
		}else{
			//if user email not found in database return with error message
			$data['error'] = "Your Email Address Was Not Found";
		}
		$this->load->view('user/login_view',$data);
	}
	
	function reset_password_request($reset_hash='')
	{
		//load page specific variables
		$loggedin = $this->session->userdata('loggedin');
		if ($loggedin) {
			redirect('settings/index');
		}
		
		$data['title'] = 'FreshBooks + Tick :: Password Reset';
		$data['heading'] = 'FreshBooks + Tick Password Reset';
		$data['error'] = '';
		$data['navigation'] = FALSE;
		$data['success'] = '';
		
		if ($reset_hash)
		{
			$hash = $reset_hash;
			$this->load->model('User_model', 'user');
			$user_email = $this->user->get_email_from_hash($hash);
			if($user_email == FALSE)
			{
				redirect('user/reset_password_request');
			}
			$email = $user_email[0]->email;
			$data['email'] = $email;
			$data['hash'] = $hash;
			$data['success'] = '';
			
			//form validation
			$this->load->library('validation');
			$this->validation->set_error_delimiters('<div class="error">', '</div>');

			$rules['password']	= "required|matches[passconf]";
			$rules['passconf']	= "required";
			$this->validation->set_rules($rules);
			$fields['password'] = 'Password';
			$fields['passconf'] = 'Password Confirmation';
			$this->validation->set_fields($fields);
			if ($this->validation->run() == FALSE) {
				$this->load->view('user/new_password_form.php', $data);
				return;
			}
			else
			{
				$update_password = $this->user->update_password($email);
				$delete_temp_record = $this->user->delete_password_reset($email);
				$data['success'] = 'Your password was updated successfully';
				$this->load->view('user/new_password_form.php', $data);
				return;
			}
			// $this->load->view('user/new_password_form.php', $data);
			// return;
		}
		
		
		//set form validation helper settings
		$this->load->library('validation');
		$this->validation->set_error_delimiters('<div class="error">', '</div>');
		
		$rules['email'] = "required|valid_email|callback_email_exist";
		$this->validation->set_rules($rules);
		$fields['email'] = 'Email Address';
		$this->validation->set_fields($fields);
		//run validation
		if ($this->validation->run() == FALSE) {
			$this->load->view('user/reset_password_form.php', $data);
			return;
		}
		else
		{
			//generate hash
			$email = $this->input->post('email');
			$salt = 'tieoffmkfdewoijfej498fj45r9848rfj48erfj4i8';
			$shuffle = str_shuffle($salt.$email);
			$hash = sha1($shuffle);
			//insert into database
			$this->load->model('User_model', 'user');
			$insert = $this->user->insert_temp_user($hash);
			$url = base_url()."index.php/user/reset_password_request/".$hash;
			// //send email with link
			$this->load->library('email');
			
			$this->email->from('kyleh@mendtechnologies.com', 'FreshBooks + Tick');
			$this->email->to($email);
			
			$this->email->subject('FreshBooks + Tick Password Reset Request');
			$this->email->message('Please click the following link to reset your FreshBooks + Tick password: '."\n". $url);
			
			$this->email->send();
			
			$data['success'] = 'Your request to reset your password was successful.  Please follow the link from your email to reset you password.';
			$this->load->view('user/reset_password_form.php', $data);
			return;
		}
		
		//$this->load->view('user/reset_password_form.php', $data);
		
	}
	
	function logout()
	{
		$this->session->sess_destroy();
		redirect('user/index');
	}

}
?>
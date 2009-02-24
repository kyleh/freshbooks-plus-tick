<?php
/**
 * Controller for managing user login, new account setup and password reset functionality.
 *
 * @package User Controller
 * @author Kyle Hendricks kyleh@mendtechnologies.com
 **/
Class User extends Controller 
{
	
	function __construct()
	{
		parent::Controller();
		$this->load->helper(array('form', 'url', 'html'));
	}
	
	/**
	 * Default controller action. Login page.
	 *
	 * @return views/user/login_view.php
	 **/
	function index()
	{
		//load page specific variables
		$loggedin = $this->session->userdata('loggedin');
		$data['title'] = 'FreshBooks + Tick :: Login';
		$data['heading'] = 'FreshBooks + Tick Login';
		$data['error'] = FALSE;
		$data['navigation'] = FALSE;

		$data['tickurl'] = (isset($_POST['tickurl'])) ? $_POST['tickurl'] : ((isset($_COOKIE['fbplustick-url'])) ? $_COOKIE['fbplustick-url'] : 'https://xxxxx.tickspot.com');
		$data['tickemail'] = (isset($_POST['tickemail'])) ? $_POST['tickemail'] : ((isset($_COOKIE['fbplustick-email'])) ? $_COOKIE['fbplustick-email'] : '');

		//check to see if user is logged in
		if (!$loggedin) {
		$this->load->view('user/login_view',$data);
		}else{
			redirect('settings/index');
		}
	}

	/**
	 * Verifies login and redirects user accordingly.
	 *
	 * @return views/user/login_view.php - settings/index controller - tick/index controller
	 **/
	function verify()
	{
		//load page specific variables
		$data['title'] = 'Tick to FreshBooks Invoice Generator::Login';
		$data['heading'] = 'Tick to FreshBooks Invoice Generator Login';
		$data['error'] = FALSE;
		$data['navigation'] = FALSE;
		
		$data['tickurl'] = (isset($_POST['tickurl'])) ? $_POST['tickurl'] : ((isset($_COOKIE['fbplustick-url'])) ? $_COOKIE['fbplustick-url'] : 'https://xxxxx.tickspot.com');
		$data['tickemail'] = (isset($_POST['tickemail'])) ? $_POST['tickemail'] : ((isset($_COOKIE['fbplustick-email'])) ? $_COOKIE['fbplustick-email'] : '');

		// assemble credentials for integration check
		$url = $this->input->post('tickurl');
		$email = $this->input->post('tickemail');
		$password = $this->input->post('tickpassword');

		$params = array(
			'tickemail' => $email,
			'tickpassword' => $password,
			'tickurl' => $url,
			'fburl' => '',	// default for now
			'fbtoken' => ''	// default for now
		);

		$this->load->library('Invoice_api', $params);
		$this->load->model('User_model', 'user');

		// get a result from tickspot using these credentials
		if ($this->invoice_api->tickspot_login())
		{
			$user = $this->user->getuser($email);

			if ($user)
			{
				//start session - set vars
				$userinfo = array('userid' => $user->id, 'loggedin' => TRUE, 'username' => $user->email, 'name' => $user->name);
				$this->session->set_userdata($userinfo); 

				// update our password in the db just to be safe (since we're piggy-backing tickspot accounts as valid)
				$this->user->update_password($email, $password);

				//check for settings if user has settings send to sync page otherwise send to settings page
				$this->load->model('Settings_model', 'settings');
				$got_settings = $this->settings->getSettings();

				// check if cookies exist before attempting to set them
				if (!isset($_COOKIE['fbplustick-email']) or !isset($_COOKIE['fbplustick-url']))
				{
					setcookie('fbplustick-email',$email,time()+3600*24*365,'/','.' . $_SERVER['SERVER_NAME']);
					setcookie('fbplustick-url',$url,time()+3600*24*365,'/','.' . $_SERVER['SERVER_NAME']);
				}

				if ($got_settings) 
				{
					redirect('tick/index');
				}
				else
				{
					redirect('settings/index');
				}
			}
			else
			{
				// hack up an insert
				$this->user->insert_user($email, $email, $password);

				//get user data to set session variables 
				$user = $this->user->getuser($email);

				//set up session ans set session vars
				$userinfo = array('userid' => $user->id, 'loggedin' => TRUE, 'username' => $user->email, 'name' => $user->name);
				$this->session->set_userdata($userinfo); 

				// set up some cookie stuff
				setcookie('fbplustick-email',$email,time()+3600*24*365,'/','.' . $_SERVER['SERVER_NAME']);
				setcookie('fbplustick-url',$url,time()+3600*24*365,'/','.' . $_SERVER['SERVER_NAME']);

				// redirect to populate more settings
				redirect('settings/index');
			}
		}
		else
		{
			$data['error'] = "Invalid Tickspot account - please try again.";
		}

		$this->load->view('user/login_view',$data);
	}
	
	/**
	 * Logout user.
	 *
	 * @return user/index controller.
	 **/
	function logout()
	{
		$this->session->sess_destroy();
		redirect('user/index');
	}
}
/* End of file user.php */
/* Location: /application/controllers/user.php */

<?php

Class Invoice extends Controller
{
	
	function __construct()
	{
		parent::Controller();
		$this->load->helper(array('form', 'url', 'html'));
		$this->output->enable_profiler(TRUE);
		
		//load API library class
		$params = $this->_get_settings();
		$this->load->library('Invoice_api', $params);
		
	}
	
	/*
	/ Private Functions
	*/
	function _check_login()
	{
		$loggedin = $this->session->userdata('loggedin');
		if ( ! $loggedin)
		{
			redirect('user/index');
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
	
	function _get_settings()
	{
		$this->load->model('Settings_model','settings');
		$settings = $this->settings->getSettings();
		if ( ! $settings)
		{
			redirect('settings/index');
		}
		else
		{
			return array(
							'ts_email' => $settings->tsemail, 
							'ts_password' => $settings->tspassword,
							'fburl' => $settings->fburl,
							'fbtoken' => $settings->fbtoken,
							);
		}
	}
	
	/*
	/ Functions accessable via URL request
	*/
	public function index()
	{
		redirect('tick/select_project');
	}
	
	public function create_invoice()
	{
		//check for login
		if ($this->_check_login())
		{
			$data['navigation'] = TRUE;	
		}
		
		//load page specific variables
		$data['title'] = 'Tick Invoice Generator';
		$data['heading'] = 'Create Invoice Results';
		$data['error'] = '';
		$data['invoice_results'] = '';
		$data['no_client_match'] = '';
		$data['invoice_url'] = '';
		
		//get FB clients
		$fbclients = $this->invoice_api->get_fb_clients();
		//exit on API error
		if (preg_match("/Error/", $fbclients))
		{
			$data['error'] = $fbclients;
			$this->load->view('invoice/invoice_results_view.php', $data);
			return;
		}
		//use match client method to match client name from TS to client name from FB
		$ts_client_name = trim($this->input->post('client_name'));
		//if match returns FB client id else returns false
		$client_id = $this->invoice_api->match_clients($fbclients, $ts_client_name);
		//exit on API error
		if (preg_match("/Error/", $client_id))
		{
			$data['error'] = $client_id;
			$this->load->view('invoice/invoice_results_view.php', $data);
			return;
		}
		
		if ( ! $client_id)
		{
			$data['no_client_match'] = 'No Client Match Found - Your Tick client was not found in FreshBooks.  Please make sure that you use the same client name for both FreshBooks and Tick.';
			$post_data = array(
					'client_name' => $this->input->post('client_name'),
					'project_name' => $this->input->post('project_name'),
					'total_hours' => $this->input->post('total_hours'),
					'entry_ids' => $this->input->post('entry_ids'),
					'invoice_type' => $this->input->post('invoice_type'),
				);
			$data['post_data'] = $post_data;
			$this->load->view('invoice/invoice_results_view.php', $data);
			return;
		}
		//Set project rate
		$project_name = trim($this->input->post('project_name'));
		$project_rate = $this->invoice_api->get_project_rate($client_id, $project_name);
		//exit on API error
		if (preg_match("/Error/", $project_rate))
		{
			$data['error'] = $project_rate;
			$this->load->view('invoice/invoice_results_view.php', $data);
			return;
		}
		
		//TODO:create invoice based on summary or detailed
		switch ($this->input->post('invoice_type')) 
		{
			case 'summary':
				$client_data = array(
					'client_id' => $client_id, 
					'client_name' => $this->input->post('client_name'), 
					'total_hours' => $this->input->post('total_hours'),
					'project_name' => $this->input->post('project_name'),
					'project_rate' => $project_rate,
					);
				//attempt to create invoice in FB	
				$create_invoice = $this->invoice_api->create_summary_invoice($client_data);
				//exit on API error
				if (preg_match("/Error/", $create_invoice))
				{
					$data['error'] = $create_invoice;
					$this->load->view('invoice/invoice_results_view.php', $data);
					return;
				}
				else
				{
					$data['invoice_results'] = "Your invoice was created successfully.";
				}
				
				break;
			
			case 'detailed':
				//get entries
				
				break;
		}
		
		//add entry id to join table
		$this->load->model('Entries_model', 'entries');
		$invoice_id = (integer)$create_invoice->invoice_id;
		//create entries array to pass to entries model
		$entries = explode(',', $this->input->post('entry_ids',TRUE));
		array_pop($entries);
		$insert_entries = $this->entries->insertEntries($entries, $invoice_id);
		//Mark entries as billed=true in tick
		foreach ($entries as $entry) 
		{
			$this->invoice_api->change_billed_status('true', $entry);
		}
		
		// get invoice data to create link to FB invoice
		$invoice_by_id = $this->invoice_api->get_invoice($invoice_id);
		if (preg_match("/Error/", $invoice_by_id)) 
		{
			$data['error'] = $invoice_by_id;
			$this->load->view('invoice/invoice_results_view.php', $data);
			return;
		}
		 
		$data['invoice_url'] = (string)$invoice_by_id->invoice->auth_url;
		$this->load->view('invoice/invoice_results_view.php', $data);
	}//end function
}
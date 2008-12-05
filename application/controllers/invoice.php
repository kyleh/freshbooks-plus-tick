<?php
/**
 * Controller for creating invoices in FreshBooks. 
 *
 * @package Invoice Controller
 * @author Kyle Hendricks kyleh@mendtechnologies.com
 **/

Class Invoice extends Controller
{
	
	function __construct()
	{
		parent::Controller();
		$this->load->helper(array('form', 'url', 'html'));
		//$this->output->enable_profiler(TRUE);
		
		//load API library class
		$params = $this->_get_settings();
		$this->load->library('Invoice_api', $params);
	}
	
	/**
	 * Private Functions prefixed by _ in CodeIgniter
	 **/
	
	/**
	 * Checks user login status.
	 *
	 * @return bool	True on success, False and redirect to login on fail
	 **/
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
	
	/**
	 * Gets API settings from database.
	 *
	 * @return array Array of API settings on success, redirect to settings page on fail
	 **/
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
				'tickemail' => $settings->tickemail, 
				'tickpassword' => $settings->tickpassword,
				'tickurl' => $settings->tickurl,
				'fburl' => $settings->fburl,
				'fbtoken' => $settings->fbtoken,
				);
		}
	}
	
	/**
	 * Sorts multidimentional of entries by task
	 *
	 **/
	function _task_sort($x, $y)
	{
		return strcasecmp($x['task'], $y['task']);
	}
	
	/**
	 * Public Functions accessable via URL request
	 **/
	
	/**
	 * Default controller action redirects to select_project method in tick controller.
	 *
	 **/
	public function index()
	{
		redirect('tick/select_project');
	}
	
	/**
	 * Creates invoice in FreshBooks from post data passed from the construct_invoice() method in the tick controller.
	 *
	 * @return displays invoice results views/invoice/invoice_results_view.php
	 **/
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
		
		//process post data and set variables
		$client_name = trim($this->input->post('client_name'));
		$project_name = trim($this->input->post('project_name'));
		$total_hours = $this->input->post('total_hours');
		$entry_ids = $this->input->post('entry_ids');
		$invoice_type = $this->input->post('invoice_type');
		//process entries line items from post data into line_items array
		$line_items = array();
		$num_line_items = $this->input->post('num_line_items');
		for ($i = 1; $i <= $num_line_items; $i++)
		{ 
			$date_index = 'date_'.$i;
			$task_index = 'task_'.$i;
			$note_index = 'note_'.$i;
			$hour_index = 'hour_'.$i;
			$items = array(
				'date' => $this->input->post($date_index),
				'task' => $this->input->post($task_index),
				'note' => $this->input->post($note_index),
				'hour' => $this->input->post($hour_index)
				);
			$line_items[] = $items;
		}
		
		//get FB clients
		$fbclients = $this->invoice_api->get_fb_clients();
		//exit on API error
		if (preg_match("/Error/", $fbclients))
		{
			$data['error'] = $fbclients;
			$this->load->view('invoice/invoice_results_view.php', $data);
			return;
		}
		//check for client match on first page
		$client_id = $this->invoice_api->match_clients($fbclients, $client_name);
		if (preg_match("/Error/", $client_id))
		{
			$data['error'] = $client_id;
			$this->load->view('invoice/invoice_results_view.php', $data);
			return;
		}
		
		//deal with multiple pages from FreshBooks request
		$num_pages = (integer)$fbclients->clients->attributes()->pages;
		if ( ! $client_id && $num_pages > 1) {
			$page = 2;
			while ($page <= $num_pages)
			{
				//get FB clients
				$fbclients = $this->invoice_api->get_fb_clients($page);
				//exit on API error
				if (preg_match("/Error/", $fbclients))
				{
					$data['error'] = $fbclients;
					$this->load->view('invoice/invoice_results_view.php', $data);
					return;
				}
				//if match returns FB client id else returns false
				$client_id = $this->invoice_api->match_clients($fbclients, $client_name);
				//exit on API error
				if (preg_match("/Error/", $client_id))
				{
					$data['error'] = $client_id;
					$this->load->view('invoice/invoice_results_view.php', $data);
					return;
				}
				//check for client match before continuing
				if ($client_id) 
				{
					$page = $num_pages + 1;
				}
				else
				{
					$page++;
				}
			}
		}

		//if Tick client is not fornd in FB send Message to add client to FB and send along necessary
		//form data to re create invoice once they add client to FB
		if ( ! $client_id)
		{
			//prepare data to recreate entries data for resubmit after adding client to FB
			$data['no_client_match'] = 'No Client Match Found - Your Tick client was not found in FreshBooks.  Please make sure that you use the same client name for both FreshBooks and Tick.  Client is FreshBooks should be <strong>'.$client_name.'</strong>.';
			
			$post_data = array(
					'client_name' => $client_name,
					'project_name' => $project_name,
					'total_hours' => $total_hours,
					'entry_ids' => $entry_ids,
					'invoice_type' => $invoice_type,
				);
			$data['post_data'] = $post_data;
			$data['line_items'] = $line_items;
			$data['num_line_items'] = $num_line_items;
			
			$this->load->view('invoice/invoice_results_view.php', $data);
			return;
		}
		
		//get project details for invoice data
		$project_details = $this->invoice_api->get_billing_details($client_name, $project_name);
		//returns array of billing details by default if string return is error message
		if (is_string($project_details))
		{
			$data['error'] = $project_details;
			$this->load->view('invoice/invoice_results_view.php', $data);
			return;
		}
		
		$bill_method = $project_details['bill_method'];
		$project_rate = $project_details['bill_rate'];
		$project_id = $project_details['project_id'];
		//set new client id if necessary - allows for client name duplication in FB
		//takes first client match in FB by default and changes if project is under a different
		//instance of the same client in FB
		if ($project_details['client_id'] != NULL) {
			$client_id = $project_details['client_id'];
		}
		
		//prepare client data for invoice
		$client_data = array(
			'client_id' => $client_id, 
			'client_name' => $client_name, 
			'total_hours' => $total_hours,
			'project_name' => $project_name,
			'project_rate' => $project_rate,
			'project_id' => $project_id,
			'bill_method' => $bill_method,
			);
		
		//Process individual line items into summarized line items
		//add z_Complete task line item to array to flag end of array
		//TODO: Refractor into loop as last element check rather than tag array
		$line_items[] = array('task' => 'z_Complete', 'hour' => '');
		//sort array by task using private task_sort method
		usort($line_items, array("Invoice", '_task_sort'));
		
		$task_name = 'Initialize';
		$hours = 0;
		$summary = array();
		foreach ($line_items as $item) 
		{
				if ($task_name != $item['task']) 
				{
					$sum_line = array(
						'task' => $task_name,
						'hours' => $hours,
						);
					$summary[] = $sum_line;
					$task_name = $item['task'];
					$hours = $item['hour'];
				}
				else
				{
					$hours += $item['hour'];
				}
		}
		//remove z_Complete flag from line item summary array
		array_splice($summary, 0, 1);
		//attempt to create invoice in FB
		if($invoice_type == 'summary')
		{
			$create_invoice = $this->invoice_api->create_summary_invoice($client_data, $summary);
		}
		else
		{
			$create_invoice = $this->invoice_api->create_detailed_invoice($client_data, $summary);
	  }
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
	}//end method
}
/* End of file invoice.php */
/* Location: /application/controllers/invoice.php */
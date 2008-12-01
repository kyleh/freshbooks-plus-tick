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
				'tickemail' => $settings->tickemail, 
				'tickpassword' => $settings->tickpassword,
				'tickurl' => $settings->tickurl,
				'fburl' => $settings->fburl,
				'fbtoken' => $settings->fbtoken,
				);
		}
	}
	
	function _task_sort($x, $y)
	{
		return strcasecmp($x['task'], $y['task']);
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
		
		//process post data and set variables
		$client_name = trim($this->input->post('client_name'));
		$project_name = trim($this->input->post('project_name'));
		$total_hours = $this->input->post('total_hours');
		$entry_ids = $this->input->post('entry_ids');
		$invoice_type = $this->input->post('invoice_type');
		//if invoice type is detailed process line item data into line_items array
		if($invoice_type == 'detailed')
		{
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
		
		//if Tick client is not fornd in FB send Message to add client to FB and send along necessary
		//form data to re create invoice once they add client to FB
		if ( ! $client_id)
		{
			$data['no_client_match'] = 'No Client Match Found - Your Tick client was not found in FreshBooks.  Please make sure that you use the same client name for both FreshBooks and Tick.  Client is FreshBooks should be <strong>'.$client_name.'</strong>.';
			$post_data = array(
					'client_name' => $client_name,
					'project_name' => $project_name,
					'total_hours' => $total_hours,
					'entry_ids' => $entry_ids,
					'invoice_type' => $invoice_type,
				);
			$data['post_data'] = $post_data;
			
			if($invoice_type == 'summary')
			{
				$data['line_items'] = '';
			}
			else
			{
				$data['line_items'] = $line_items;
				$data['num_line_items'] = $num_line_items;
			}
			
			$this->load->view('invoice/invoice_results_view.php', $data);
			return;
		}
		
		//Set project rate
		$project_rate = $this->invoice_api->get_project_rate($client_id, $project_name);
		//exit on API error
		if (preg_match("/Error/", $project_rate))
		{
			$data['error'] = $project_rate;
			$this->load->view('invoice/invoice_results_view.php', $data);
			return;
		}
		
		//prepare client data for invoice
		$client_data = array(
			'client_id' => $client_id, 
			'client_name' => $client_name, 
			'total_hours' => $total_hours,
			'project_name' => $project_name,
			'project_rate' => $project_rate,
			);
		
		//create detailed or summary invoice depending on selected invoice type
		if($invoice_type == 'summary')
		{
			$create_invoice = $this->invoice_api->create_summary_invoice($client_data);
		}
		else
		{
			//Process individual line items into summarized line items
			//add complete element to array for summarizing by task
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

			array_splice($summary, 0, 1);
			
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
	}//end function
}
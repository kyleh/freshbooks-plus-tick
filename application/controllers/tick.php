<?php


Class Tick extends Controller{
	
	function __construct()
	{
		parent::Controller();
		$this->load->helper(array('form', 'url', 'html'));
		//$this->output->enable_profiler(TRUE);
		
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

	function _updateJoinTable()
	{
		$this->load->model('Entries_model', 'entries');
		$fb_ids = $this->entries->getInvoiceIds();
		
		if ($fb_ids)
		{
			foreach ($fb_ids as $id)
			{
				$invoice_id = $id->fb_invoice_id;
				$status = $this->invoice_api->check_invoice_status($invoice_id);
				//exit on API error
				if (preg_match("/Error/", $status))
				{
					throw new Exception($status);
				}
				
				$entries_ids = $this->entries->getEntriesIds($invoice_id);
				if ($status == 'deleted')
				{//if deleted change billing status to false and delete join record
					foreach ($entries_ids as $entry_id)
					{
						$mark_not_billed = $this->invoice_api->change_billed_status('false', (integer)$entry_id->ts_entry_id);
						//exit on API error
						if (preg_match("/Error/", $mark_not_billed))
						{
							throw new Exception($mark_not_billed);
						}
						
						$deleted_entries = $this->entries->deleteEntry((integer)$entry_id->ts_entry_id);
					}//endforeach
				}
				elseif(!$status == 'draft')
				{//if status is not draft delete join record
					foreach ($entries_ids as $entry_id)
					{
						$deleted_entries = $this->entries->deleteEntry((integer)$entry_id->ts_entry_id);
					}//endforeach
				}//endif
			}//end foreach
		}//endif
	}

	function _date_sort($x, $y)
	{
		return strcasecmp($x['entry_date'], $y['entry_date']);
	}
	
	/*
	/ Functions accessable via URL request
	*/
	function index()
	{
		redirect('tick/select_project');
	}
	
	function select_project()
	{
		//check for login
		if ($this->_check_login())
		{
			$data['navigation'] = TRUE;	
		}
		
		//load page specific variables
		$data['title'] = 'Tick Invoice Generator';
		$data['heading'] = 'Tick Projects with Open Entries';
		$data['projects'] = '';
		$data['error'] = '';
		
		//Get Invoice Id's from join table delete records if invoice sent
		//if invoice deleted mark entries as not billed and delete records 
		try 
		{
			$update_join_table = $this->_updateJoinTable();
		}
		catch (Exception $e) 
		{
			$data['error'] = $e->getMessage();
			$this->load->view('tick/select_project_view', $data);
			return;
		}
		
		//get open entries in tickspot - group by project - remove duplicates
		$ts_entries = $this->invoice_api->get_all_open_entries();
		
		//exit on API error
		if (preg_match("/Error/", $ts_entries))
		{
			$data['error'] = $ts_entries;
			$this->load->view('tick/select_project_view', $data);
			return;
		}
		
		//filter open entries for unique projects
		$projects_with_entries = array();
		foreach ($ts_entries as $entry)
		{
			$project = array(
				'project'=>(string)$entry->project_name,
				'project_id'=>(string)$entry->project_id,
				'client'=>(string)$entry->client_name,
				);
			if ( ! in_array($project, $projects_with_entries, FALSE))
			{
				$projects_with_entries[] = $project;
			}
		}
		
		//assign unique projects array element
		$data['projects'] = $projects_with_entries;
		$this->load->view('tick/select_project_view.php', $data);
	}
	
	function construct_invoice()
	{
		//check for login
		if ($this->_check_login()) {
			$data['navigation'] = TRUE;	
		}
		//load page specific variables
		$data['title'] = 'Tick Invoice Generator';
		$data['heading'] = 'Construct Invoice for FreshBooks';
		$data['entry_ids'] = '';
		//set post variables
		$data['project_name'] = $this->input->post('project_name');
		$data['client_name'] = $this->input->post('client_name');
		$project_id = $this->input->post('project_id');
		//set default start and end date values
		$start_date = '';
		$end_date = date('m').'/'.date('t').'/'.date('Y');
				
		if ($this->input->post('filter') == 'refresh')
		{
			$date = $this->input->post('options');
			$start_date = $date['start_date'];
			$end_date = $date['end_date'];
			$ts_entries = $this->invoice_api->get_open_entries($project_id,$start_date,$end_date);
		}
		else
		{
			$ts_entries = $this->invoice_api->get_all_open_entries($project_id);
		}
		//exit on API error
		if (preg_match("/Error/", $ts_entries))
		{
			$data['error'] = $ts_entries;
			$this->load->view('tick/select_project', $data);
			return;
		}
		//process entries into mulitdimential array for sorting
		$ts_entries_to_array = $this->invoice_api->process_entries($ts_entries);
		//sort array by date using private date_sort method
		usort($ts_entries_to_array, array("Tick", '_date_sort'));
		
		$data['ts_entries'] = $ts_entries_to_array;//TODO: change name to sorted_entries in view
		//calculate total hours for invoice
		$total_hours = 0;
		foreach ($ts_entries_to_array as $entry)
		{
			$total_hours = $total_hours + $entry['hours'];
		}
		//set project selection variables
		$data['total_hours'] = $total_hours;
		$data['start_date'] = $start_date;
		$data['end_date'] = $end_date;
		$data['project_id'] = $project_id;
		
		$this->load->view('tick/construct_invoice_view.php', $data);
	}

}
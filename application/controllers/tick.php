<?php
/**
 * Controller for managing and organizing Tick entries data.
 * Organizes entries data into projects with open entries and 
 * organizes project entries data into invoicable line items
 *
 * @package Tick Controller
 * @author Kyle Hendricks kyleh@mendtechnologies.com
 **/
Class Tick extends Controller
{
	function __construct()
	{
		parent::Controller();
		$this->load->helper(array('form', 'url', 'html'));
		//$this->output->enable_profiler(TRUE);
		
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
	 * Checks invoice status in FreshBooks of previously created invoices.
	 * If status is deleted it removes entries from join table and marks entries as not billed in Tick.
	 * If status is draft it does nothing.
	 * If status is anything else it deletes entries in join table.
	 *
	 * @return string returns error details on fail
	 **/
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
	
	/**
	 * Sorts multidimentional of entries by entry date
	 *
	 **/
	function _date_sort($x, $y)
	{
		return strcasecmp($x['entry_date'], $y['entry_date']);
	}
	
	/**
	 * Public Functions accessable via URL request
	 **/
	
	/**
	 * Default controller action redirects to select_project method.
	 *
	 **/
	function index()
	{
		redirect('tick/select_project');
	}
	
	/**
	 * Loads all open entries and organizes them into project with open items.
	 *
	 * @return displays project with opne entries to views/tick/select_project_view.php
	 **/
	function select_project()
	{
		//check for login
		if ($this->_check_login())
		{
			$data['navigation'] = TRUE;	
		}
		
		//load page specific variables
		$data['title'] = 'Tick Invoice Generator';
		$data['heading'] = 'Tick projects with unbilled hours';
		$data['projects'] = '';
		$data['error'] = '';
		
		// navigation hack
		$data['projectsActive'] = array('class' => 'active');
		$data['settingsActive'] = '';

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
	
	/**
	 * Constructs detailed line items organized by Tick task.  Allow user to select date range
	 * of invoice and create detailed or summarized invoice in FreshBooks.
	 *
	 * @return displays project as line items organized by Tick task in views/tick/construct_invoice_view.php
	 **/
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
				
		// navigation hack
		$data['projectsActive'] = array('class' => 'active');
		$data['settingsActive'] = '';

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
/* End of file tick.php */
/* Location: /application/controllers/tick.php */

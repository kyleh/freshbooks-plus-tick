<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Handles all API requests for Tick and FreshBooks.
 *
 * @package Invoice_api
 * @author Kyle Hendricks kyleh@mendtechnologies.com
 **/

Class Invoice_api
{
	/**
	 * FreshBooks API URL.
	 *
	 * @var string
	 **/
	private $fburl;
	
	/**
	 * FreshBooks API token.
	 *
	 * @var string
	 **/
	private $fbtoken;
	
	/**
	 * Tick API URL.
	 *
	 * @var string
	 **/
	private $tickurl;
	
	/**
	 * Tick email and password combined into get request string.
	 *
	 * @var string
	 **/
	private $auth;
	
	function __construct($params)
	{
		$this->fburl = $params['fburl'];
		$this->fbtoken = $params['fbtoken'];
		$this->tickurl = $params['tickurl'];
		$this->auth = "email=".$params['tickemail']."&password=".$params['tickpassword'];
	}
	
	/**
	 * Sends requests to Tick API.
	 *
	 * @param $url string
	 * @return string/object	string containing error desc on error, xmlobject on success 
	 **/
	private function loadxml($url, $useless = false)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->auth);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,15);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		$result = curl_exec($ch);
		curl_close($ch);
		
		if (preg_match("/not valid/", $result) OR $result == FALSE)
		{
			// make a mostly useless check of the tickspot account credentials
			if ($useless)
			{
				return false;
			}
			else
			{
				return 'Tick Error: '.$result.' Please check you Tick settings and try again.';
			}
		}
		elseif(preg_match("/xml/", $result))
		{
			return simplexml_load_string($result);
		}
		else
		{
			return $result;
		}
	}
	
	/**
	 * Sends XML requests to FreshBooks API.
	 *
	 * @param $xml string
	 * @return string/object	string containing error desc on error, xmlobject on success 
	 **/
	private function send_xml_request($xml)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->fburl);
		curl_setopt($ch, CURLOPT_USERPWD, $this->fbtoken);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);
		$result = curl_exec($ch);
		curl_close ($ch);
		
		//check for non xml result
		if($result == FALSE)
		{
			return 'Error: Unable to connect to FreshBooks API.';
		}
		elseif(preg_match("/404 Error: Not Found/", $result) || preg_match("/DOCTYPE/", $result))
		{
			return "Error: <strong>404 Error: Not Found</strong>. Please check you FreshBooks API URL setting and try again.  The FreshBooks API url is different from your FreshBooks account url.";
		}
		
		//if xml check for FB status
		if(preg_match("/<?xml/", $result))
		{
			$fbxml = simplexml_load_string($result);
			if($fbxml->attributes()->status == 'fail')
			{
				return 'Error: The following FreshBooks error occurred: '.$fbxml->error;
			}
			else
			{
				return $fbxml;
			}
		}
	}

	/**
	 * Gets FreshBooks projects.
	 *
	 * @param $page(optional), int FreshBooks page number
	 * @param $client_id string, FreshBooks client id
	 * @return string/object	string containing error desc on error, xmlobject on success 
	 **/
	private function get_fb_projects($client_id, $page=1)
	{
		$xml =<<<EOL
			<?xml version="1.0" encoding="utf-8"?>
			<request method="project.list">
				<client_id>{$client_id}</client_id>
			  <page>{$page}</page>
			  <per_page>100</per_page>
			</request>
EOL;

		return $this->send_xml_request($xml); 
	}
	
	/**
	 * Gets all FreshBooks items.
	 *
	 * @param $page(optional), int FreshBooks page number
	 * @return string/object	string containing error desc on error, xmlobject on success 
	 **/
	private function get_all_items($page=1)
	{
		$xml=<<<EOL
			<?xml version="1.0" encoding="utf-8"?>
			<request method="item.list">
			  <page>{$page}</page>
			  <per_page>100</per_page>
			</request>
EOL;

	return $this->send_xml_request($xml); 
	}
	
	/**
	 * Gets all FreshBooks tasks or tasks assigned to projects if optional project_id is provided.
	 *
	 * @param $page(optional), int FreshBooks page number
	 * @param $project_id(optional) string, FreshBooks project id
	 * @return string/object	string containing error desc on error, xmlobject on success 
	 **/
	private function get_all_tasks($project_id=0, $page=1)
	{
		
		$xml=<<<EOL
			<?xml version="1.0" encoding="utf-8"?>
			<request method="task.list">
EOL;
		
		if ($project_id != 0) {
			$xml .= "<project_id>{$project_id}</project_id>";
		}
		
		$xml.=<<<EOL
			<page>{$page}</page>
			  <per_page>100</per_page>
			</request>
EOL;

	return $this->send_xml_request($xml); 
	}
	
	/**
	 * Determine billing rate for FreshBooks project task-rate billing method also if no project exists in FreshBooks.
	 *
	 * @param $tick_task, string - Tick task description
	 * @param $project_id, string - FreshBooks project id
	 * @return float - Unit cost if task/item match else 0
	 **/
	private function task_rate_billing($tick_task, $project_id)
	{
		//check for matching task
		$tick_task_name = trim($tick_task);
		$tasks = $this->get_all_tasks($project_id);
		if (preg_match("/Error/", $tasks))
		{
			return 0;
		}
		
		foreach ($tasks->tasks->task as $task)
		{
			if($task->name == $tick_task_name)
			{
				$unit_cost = $task->rate;
				return $unit_cost;
			}
		}
		
		//chcek for multiple task pages
		$num_pages = (integer)$tasks->tasks->attributes()->pages;
		$page = 2;
		if ($num_pages > 1) {
			while ($page <= $num_pages)
			{
				$tasks = $this->get_all_tasks($project_id, $page);
				if (preg_match("/Error/", $tasks))
				{
					return 0;
				}

				foreach ($tasks->tasks->task as $task)
				{
					if($task->name == $tick_task_name)
					{
						$unit_cost = $task->rate;
						return $unit_cost;
					}
				}
				$page++;
			}
		}
		
		//check for matching item
		//FB items have a 15 character limit
		$task_length = strlen($tick_task_name);
		if($task_length <= 15)
		{
			$items = $this->get_all_items();
			if (preg_match("/Error/", $items))
			{
				return 0;
			}
			
			foreach($items->items->item as $item)
			{
				if($item->name == $tick_task_name)
				{
					$unit_cost = $item->unit_cost;
					return $unit_cost;
				}
			}
		
			//check for multiple task pages
			$num_pages = (integer)$items->items->attributes()->pages;
			$page = 2;
			if ($num_pages > 1) {
				while ($page <= $num_pages)
				{
					$items = $this->get_all_items();
					if (preg_match("/Error/", $items))
					{
						return 0;
					}

					foreach($items->items->item as $item)
					{
						if($item->name == $tick_task_name)
						{
							$unit_cost = $item->unit_cost;
							return $unit_cost;
						}
					}//end foreach
					$page++;
				}//end while
			}//end if
		}//end if

		//default to zero if no task/item found
		return 0;
	}
	
	/**
	 * Returns billing rate to create invoice methods.
	 *
	 * @param $bill_method, string - FreshBooks project billing method
	 * @param $tick_task, string - Tick task description
	 * @param $project_rate, string - FreshBooks project rate
	 * @param $project_id, string - FreshBooks project id
	 * @return float - Unit cost if task/item match else 0
	 **/
	private function get_billing_rate($bill_method, $tick_task, $project_rate, $project_id)
	{
		//check bill method to determine line item rate
		switch ($bill_method) {
			case 'flat-rate':
				$unit_cost = 0;
				break;
			case 'task-rate':
				$unit_cost = $this->task_rate_billing($tick_task, $project_id);
				break;
			case 'project-rate':
				$unit_cost = $project_rate;
				break;
			case 'staff-rate':
				$unit_cost = $project_rate;
				break;
			case 'no-project-found':
				$unit_cost = $this->task_rate_billing($tick_task, $project_id);
				break;
		}
		
		return $unit_cost;
	}
	
	/**
	 * Checks account credentials at Tickspot .. pass no constraints
	 *
	 * @return string/object	string containing error desc on error, xmlobject on success 
	 **/
	public function tickspot_login()
	{
		return $this->loadxml($this->tickurl . '/api/clients', true);
	}

	/**
	 * Returns all open Tick entries for the past 5 years.
	 *
	 * @param $id(optional), string - Tick project id
	 * @return string/object	string containing error desc on error, xmlobject on success 
	 **/
	public function get_all_open_entries($id = 0)
	{
		$all_entries = date("m/d/Y", mktime(0, 0, 0, date("m"), date("d"),   date("Y")-5));
		
		if ($id == 0)
		{
			$url = $this->tickurl."/api/entries?updated_at={$all_entries}&entry_billable=true&billed=false";
		}
		else
		{
			$url = $this->tickurl."/api/entries?updated_at={$all_entries}&entry_billable=true&billed=false&project_id={$id}";
		}
		
		return $this->loadxml($url);
	}

	/**
	 * Returns open Tick entries with start date, end date and optional Tick project id.
	 *
	 * @param $id(optional), string - Tick project id
	 * @param $start_date(optional), string - defaults to first day of current month
 	 * @param $end_date(optional), string - defaults to current day of current month
	 * @return string/object	string containing error desc on error, xmlobject on success 
	 **/
	public function get_open_entries($id = 0, $start_date = '', $end_date = '')
	{
		if ( ! $start_date)
		{
			$start_date = date("m").'/'.'01'.'/'.date("Y");
			$end_date = date("m/d/Y");
		}
		
		if ($id === 0)
		{
			$url = $this->tickurl."/api/entries?start_date={$start_date}&end_date={$end_date}&entry_billable=true&billed=false";
		}
		else
		{
			$url = $this->tickurl."/api/entries?start_date={$start_date}&end_date={$end_date}&entry_billable=true&billed=false&project_id={$id}";
		}
		
		return $this->loadxml($url);
	}
	
	/**
	 * Change Tick entries billing status.
	 *
	 * @param $status, bool - true or false
	 * @param $id, string - Tick entries id
	 * @return string/object	string containing error desc on error, xmlobject on success 
	 **/
	public function change_billed_status($status, $id)
	{
		
		$url = $this->tickurl."/api/update_entry?id={$id}&billed={$status}";
		return $this->loadxml($url);
	}
	
	/**
	 * Creates multidimential array from Tick entries xml object.
	 *
	 * @param $entries, XML Object of Tick entries
	 * @return array	multidimential array of tick entries
	 **/
	public function process_entries($entries)
	{
		$processed_entries = array();
			foreach ($entries as $entry)
			{
				$dataset = array(
					'entry_id' => (integer)$entry->id,
					'entry_date' => (string)$entry->date,
					'client_name' => (string)$entry->client_name,
					'project_name' => (string)$entry->project_name,
					'project_id' => (string)$entry->project_id,
					'task_name' => (string)$entry->task_name,
					'task_id' => (integer)$entry->task_id,
					'notes' => (string)$entry->notes,
					'hours' => (float)$entry->hours,
					);
				$processed_entries[] = $dataset;
			}
		return $processed_entries;
	}
	
	/**
	 * Returns FreshBooks invoice given invoice id.
	 *
	 * @param $id, FreshBooks invoice id
	 * @return string/object	string containing error desc on error, xmlobject on success 
	 **/
	public function get_invoice($id)
	{
		$xml =<<<EOL
			<?xml version="1.0" encoding="utf-8"?>
			<request method="invoice.get">
			<invoice_id>{$id}</invoice_id>
			</request>
EOL;

		return $this->send_xml_request($xml); 
	}
	
	/**
	 * Returns FreshBooks invoice status given invoice id.
	 *
	 * @param $id, FreshBooks invoice id
	 * @return string/object	string containing error desc on error, xmlobject on success 
	 **/
	public function check_invoice_status($id)
	{
		$invoice_info = $this->get_invoice($id);
		if (preg_match("/Invoice not found/", $invoice_info))
		{
			return 'deleted';
		}
		else
		{
			return (string)$invoice_info->invoice->status;
		}
	}

	/**
	 * Returns FreshBooks clients.
	 *
	 * @param $page(optional), int FreshBooks page number
	 * @return string/object	string containing error desc on error, xmlobject on success 
	 **/
	public function get_fb_clients($page=1)
	{
		$xml =<<<EOL
		<?xml version="1.0" encoding="utf-8"?>
		<request method="client.list">
		  <page>{$page}</page>
		  <per_page>100</per_page>
		</request>
EOL;

		return $this->send_xml_request($xml); 
	}
	
	/**
	 * Checks for a match between a Tick client name and all FreshBooks client names.
	 *
	 * @param $fbclients, object - object containing FreshBooks clients
	 * @param $ts_client_name, string - Tick client name
	 * @return integer/bool	integer containing FreshBooks client id on success False on fail
	 **/
	public function match_clients($fbclients, $ts_client_name)
	{
		foreach ($fbclients->clients->client as $client)
		{
			$fb_client_name = trim((string)$client->organization);
			if (strcasecmp($fb_client_name, $ts_client_name) == 0)
			{
				$client_id = $client->client_id;
				return $client_id;
			}
		}
		return FALSE;
	}
	
	/**
	 * Returns FreshBooks project details array given a Tick client and project.
	 * Allows fo multiple instances of the same client in FreshBooks.
	 *
	 * @param $ts_project_name, string - Tick project name
	 * @param $ts_client_name, string - Tick client name
	 * @return array/string	FreshBooks project details array or string with error details on fail
	 **/
	public function get_billing_details($ts_client_name, $ts_project_name)
	{
		//get FB clients
		$fbclients = $this->get_fb_clients();
		//check for FB error
		if (preg_match("/Error/", $fbclients))
		{
			return $fbclients;
		}
		
		foreach ($fbclients->clients->client as $client)
		{
			$fb_client_name = trim((string)$client->organization);
			if (strcasecmp($fb_client_name, $ts_client_name) == 0)
			{
				//get FB projects for client
				$fb_projects = $this->get_fb_projects($client->client_id);
				//check for FB error
				if (preg_match("/Error/", $fb_projects))
				{
					return $fb_projects;
				}
				//loop through projects looking for match
				foreach ($fb_projects->projects->project as $project)
				{
					$fb_project_name = trim((string)$project->name);
					$fb_project_id = (integer)$project->project_id;
					$fb_project_billmethod = trim((string)$project->bill_method);
					$ts_project_name = $ts_project_name;
					//if match find bill method and type
					if (strcasecmp($fb_project_name, $ts_project_name) == 0)
					{
						$bill_rate = (float)$project->rate;
						$client_id = (integer)$client->client_id;
						$bill_details = array('bill_method' => $fb_project_billmethod, 'bill_rate' => $bill_rate, 'client_id' => $client_id, 'project_id' => $fb_project_id);
						return $bill_details;
					}//endif
				}//end foreach
			}//endif
		}//end foreach
		
		//loop through multiple FreshBooks response pages if necessary
		$num_pages = (integer)$fbclients->clients->attributes()->pages;
		if ($num_pages > 1) 
		{
			$page = 2;
			while ($page <= $num_pages)
			{
				//get FB clients
				$fbclients = $this->get_fb_clients($page);
				//check for FB error
				if (preg_match("/Error/", $fbclients))
				{
					return $fbclients;
				}
				
				foreach ($fbclients->clients->client as $client)
				{
					$fb_client_name = trim((string)$client->organization);
					if (strcasecmp($fb_client_name, $ts_client_name) == 0)
					{
						//get FB projects for client
						$fb_projects = $this->get_fb_projects($client->client_id);
						//check for FB error
						if (preg_match("/Error/", $fb_projects))
						{
							return $fb_projects;
						}
						//loop through projects looking for match
						foreach ($fb_projects->projects->project as $project)
						{
							$fb_project_name = trim((string)$project->name);
							$fb_project_id = (integer)$project->project_id;
							$fb_project_billmethod = trim((string)$project->bill_method);
							$ts_project_name = $ts_project_name;
							//if match find bill method and type
							if (strcasecmp($fb_project_name, $ts_project_name) == 0)
							{
								$bill_rate = (float)$project->rate;
								$client_id = (integer)$client->client_id;
								$bill_details = array('bill_method' => $fb_project_billmethod, 'bill_rate' => $bill_rate, 'client_id' => $client_id, 'project_id' => $fb_project_id);
								return $bill_details;
							}//endif
						}//end foreach
					}//endif
				}//end foreach
				
				$page++;
			}//end while
		}
		return $bill_details = array('bill_method' => 'no-project-found', 'bill_rate' => 0, 'client_id' => NULL, 'project_id' => 0);
	}
	
	/**
	 * Creates summary invoice in FreshBooks.
	 *
	 * @param $client_data, array - array containing general invoice data
	 * @param $line_item_summary, array - array containing detailed line item invoice data
	 * @return string/object	string containing error desc on error, xmlobject on success 
	 **/
	public function create_summary_invoice($client_data, $line_item_summary)
	{
		$client_id = $client_data['client_id'];
		$client_name = $client_data['client_name'];
		$total_hours = $client_data['total_hours'];
		$project_name = $client_data['project_name'];
		$project_id = $client_data['project_id'];
		$project_rate = $client_data['project_rate'];
		$bill_method = $client_data['bill_method'];
		
		$xml =<<<EOL
			<?xml version="1.0" encoding="utf-8"?>
			<request method="invoice.create">
			  <invoice>
			    <client_id>{$client_id}</client_id>
			    <status>draft</status>
			    <organization>{$client_name}</organization>

			    <lines>
EOL;

		//if bill method is flat rate append line with flat rate
		if ($bill_method == 'flat-rate')
		{
			$xml .=<<<EOL
			  <line>
	        <description>[{$project_name}] Flat Rate</description>
	        <unit_cost>{$project_rate}</unit_cost>
	        <quantity>1</quantity>
	      </line>
	    </lines>
	  </invoice>
   </request>
	
EOL;
		}
		else
		{
			//determine unit cost by cumulating hours
			$unit_cost_summary = 0;
			foreach ($line_item_summary as $item) 
			{
				//set hours
				$hours = $item['hours'];
				//set unit cost
				$tick_task = $item['task'];
				$unit_cost = $this->get_billing_rate($bill_method, $tick_task, $project_rate, $project_id);
				$unit_cost_summary += ($hours * $unit_cost);
			}//end foreach
			
			$xml .=<<<EOL
		    <line>
		        <description>[{$project_name}]</description>
		        <unit_cost>{$unit_cost_summary}</unit_cost>
		        <quantity>1</quantity>
		      </line>
		    </lines>
		  </invoice>
     </request>
EOL;
		}
		
		return $this->send_xml_request($xml);
	}

	/**
	 * Creates detailed line item invoice in FreshBooks.
	 *
	 * @param $client_data, array - array containing general invoice data
	 * @param $line_item_summary, array - array containing detailed line item invoice data
	 * @return string/object	string containing error desc on error, xmlobject on success 
	 **/
	public function create_detailed_invoice($client_data, $line_item_summary)
	{
		$client_id = $client_data['client_id'];
		$client_name = $client_data['client_name'];
		$total_hours = $client_data['total_hours'];
		$project_name = $client_data['project_name'];
		$project_id = $client_data['project_id'];
		$project_rate = $client_data['project_rate'];
		$bill_method = $client_data['bill_method'];

		//open xml file with core data
		$xml =<<<EOL
			<?xml version="1.0" encoding="utf-8"?>
			<request method="invoice.create">
			  <invoice>
			    <client_id>{$client_id}</client_id>
			    <status>draft</status>
			    <organization>{$client_name}</organization>
					
					<lines>
EOL;
		
		foreach ($line_item_summary as $item) 
		{
			//set hours
			$hours = $item['hours'];
			//set description
			$description = '['.$project_name.']  ';
			if ($item['task'] != 'No Task Selected') 
			{
				$description .= $item['task'];
			}
			//set unit cost
			$tick_task = $item['task'];
			$unit_cost = $this->get_billing_rate($bill_method, $tick_task, $project_rate, $project_id);
	
			$xml .=<<<EOL
		      <line>
		        <name></name>
		        <description>{$description}</description>
		        <unit_cost>{$unit_cost}</unit_cost>
		        <quantity>{$hours}</quantity>
		      </line>
EOL;
		}//end foreach
		
		//if bill method is flat rate append line with flat rate
		if ($bill_method == 'flat-rate')
		{
			$xml .=<<<EOL
			  <line>
	        <name></name>
	        <description>[{$project_name}] Flat Rate</description>
	        <unit_cost>{$project_rate}</unit_cost>
	        <quantity>1</quantity>
	      </line>
EOL;
		}
		
		$xml .=<<<EOL
				    </lines>
				  </invoice>
		    </request>
EOL;

		//send invoice create request to FB
		return $this->send_xml_request($xml);
	}

}
/* End of file Invoice_api.php */
/* Location: /application/libraries/Invoice_api.php */ 

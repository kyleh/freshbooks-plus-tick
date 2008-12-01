<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*! @header Tickspot to FreshBooks Invoice Generater - November 2008
    @abstract a application that invoices in FreshBooks from time data in TickSpot
		@author - FreshBooks
 */

Class Invoice_api
{
	
	private $fburl;
	private $fbtoken;
	private $tickurl;
	private $auth;
	
	function __construct($params)
	{
		$this->fburl = $params['fburl'];
		$this->fbtoken = $params['fbtoken'];
		$this->tickurl = $params['tickurl'];
		$this->auth = "email=".$params['tickemail']."&password=".$params['tickpassword'];
	}
	
	private function loadxml($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->auth);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		$result = curl_exec($ch);
		curl_close($ch);
		
		if (preg_match("/not valid/", $result) OR $result == FALSE)
		{
			return 'Tick Error: '.$result.' Please check you Tick settings and try again.';
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

	//gets FB projects given a FB client id
	//TODO: create loop to allow for multiple pages of projects
	private function get_fb_projects($client_id)
	{
		$xml =<<<EOL
			<?xml version="1.0" encoding="utf-8"?>
			<request method="project.list">
				<client_id>{$client_id}</client_id>
			  <page>1</page>
			  <per_page>100</per_page>
			</request>
EOL;

		return $this->send_xml_request($xml); 
	}
	
	//get items from FB
	private function get_all_items()
	{
		$xml=<<<EOL
			<?xml version="1.0" encoding="utf-8"?>
			<request method="item.list">
			  <page>1</page>
			  <per_page>100</per_page>
			</request>
EOL;

	return $this->send_xml_request($xml); 
	}

	//Returns all open entries for past 5 years - takes optional project id
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
	
	public function change_billed_status($status, $id)
	{
		
		$url = $this->tickurl."/api/update_entry?id={$id}&billed={$status}";
		return $this->loadxml($url);
	}
	
	//creates multidimential array from entries xml object
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
	
	//returns FB invoice given invoice id
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
	//gets FB clients
	//TODO: create loop to allow for multiple pages of clients
	public function get_fb_clients()
	{
		$xml =<<<EOL
		<?xml version="1.0" encoding="utf-8"?>
		<request method="client.list">
		  <page>1</page>
		  <per_page>100</per_page>
		</request>
EOL;

		return $this->send_xml_request($xml); 
	}
	
	//checks for client match given FB client xml object and TS client name
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

	// public function get_project_rate($ts_client_name, $ts_project_name)
	// {
	// 	//get FB clients
	// 	$fbclients = $this->get_fb_clients();
	// 	//check for FB error
	// 	if (preg_match("/Error/", $fbclients))
	// 	{
	// 		return $fbclients;
	// 	}
	// 	
	// 	foreach ($fbclients->clients->client as $client)
	// 	{
	// 		$fb_client_name = trim((string)$client->organization);
	// 		if (strcasecmp($fb_client_name, $ts_client_name) == 0)
	// 		{
	// 			//get FB projects for client
	// 			$fb_projects = $this->get_fb_projects($client->client_id);
	// 			//check for FB error
	// 			if (preg_match("/Error/", $ts_projects))
	// 			{
	// 				return $ts_projects;
	// 			}
	// 			//loop through projects looking for match
	// 			foreach ($fb_projects->projects->project as $project)
	// 			{
	// 				$fb_project_name = trim((string)$project->name);
	// 				$fb_project_billmethod = trim((string)$project->bill_method);
	// 				$ts_project_name = $ts_project_name;
	// 				//if match find bill method and type
	// 				if (strcasecmp($fb_project_name, $ts_project_name) == 0)
	// 				{
	// 					switch ($fb_project_billmethod) 
	// 					{
	// 						case 'project-rate':
	// 							$bill_rate = (float)$project->rate;
	// 							return $bill_rate;
	// 							break;
	// 						case 'flat-rate':
	// 							$bill_rate = (float)$project->rate;
	// 							return $bill_rate;
	// 							break;
	// 						case 'task-rate':
	// 							# code...
	// 							break;
	// 						case 'staff-rate':
	// 							$bill_rate = 0;
	// 							break;
	// 						default:
	// 							$bill_rate = 0;
	// 							break;
	// 					}//end switch
	// 				}//endif
	// 			}//end foreach
	// 			
	// 			
	// 			
	// 			
	// 			return $client_id;
	// 		}
	// 	}
	// 	
	// 	
	// 	
	// }
	
	//sets initial bill rate to 0 - uses get_fb_projects to get all FB projects given a FB client id
	//compares FB project names to TS project names - if match and FB bill method is project uses project rate else 0
	public function get_project_rate($client_id, $project_name)
	{
		$bill_rate = 0;
		$ts_projects = $this->get_fb_projects($client_id);
		//check for FB error
		if (preg_match("/Error/", $ts_projects))
		{
			return $ts_projects;
		}
		
		foreach ($ts_projects->projects->project as $project)
		{
			$fb_project_name = trim((string)$project->name);
			$fb_project_billmethod = trim((string)$project->bill_method);
			$ts_project_name = $project_name;
			if (strcasecmp($fb_project_name, $ts_project_name) == 0 && $fb_project_billmethod == 'project-rate')
			{
				$bill_rate = (float)$project->rate;
			}
		}
		return $bill_rate;
	}
	
	//creates a invoice in FB using an array of client data constructed in invoice controller
	public function create_summary_invoice($client_data)
	{
		$client_id = $client_data['client_id'];
		$client_name = $client_data['client_name'];
		$total_hours = $client_data['total_hours'];
		$project_name = $client_data['project_name'];
		$project_rate = $client_data['project_rate'];
		
		$xml =<<<EOL
			<?xml version="1.0" encoding="utf-8"?>
			<request method="invoice.create">
			  <invoice>
			    <client_id>{$client_id}</client_id>
			    <status>draft</status>
			    <organization>{$client_name}</organization>

			    <lines>
			      <line>
			        <description>{$project_name}</description>
			        <unit_cost>{$project_rate}</unit_cost>
			        <quantity>{$total_hours}</quantity>
			      </line>
			    </lines>
			  </invoice>
       </request>
EOL;
	
		return $this->send_xml_request($xml); 
	}

	//creates a detailed invoice in FB using an array of client data and line items constructed in invoice controller
	public function create_detailed_invoice($client_data, $line_item_summary)
	{
		$client_id = $client_data['client_id'];
		$client_name = $client_data['client_name'];
		$total_hours = $client_data['total_hours'];
		$project_name = $client_data['project_name'];
		$project_rate = $client_data['project_rate'];

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
			
			
			
			
			$unit_cost = $project_rate;
			//if no project rate and task name exist check for match to FB item
			$task_length = strlen($item['task']);
			
			
			//check for FB item match to use item rate
			//FB items have a 15 character limit
			$task_length = strlen($item['task']);
			if($task_length < 15 && $project_rate == 0)
			{
				$items = $this->get_all_items();
				$t_task = trim($item['task']);
				foreach($items->items->item as $item)
				{
					if($item->name == $t_task)
					{
						$unit_cost = $item->unit_cost;
						break;
					}
				}
			}
	
			$xml .=<<<EOL
		      <line>
		        <name></name>
		        <description>{$description}</description>
		        <unit_cost>{$unit_cost}</unit_cost>
		        <quantity>{$hours}</quantity>
		      </line>
EOL;
		}//end foreach
		
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
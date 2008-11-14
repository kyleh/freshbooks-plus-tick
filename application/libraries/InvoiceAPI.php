<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*! @header Tickspot to Freshbooks Invoice Generater - October 2008
    @abstract a application that invoices in Freshbooks from time data in TickSpot
		@author - Kyle Hendricks - Mend Technologies - kyleh@mendtechnologies.com - www.mendtechnologies.com
 */


Class InvoiceAPI 
{
	
	private $fburl;
	private $fbtoken;
	private $auth;
	
	function __construct($params)
	{
		$this->auth = "email=".$params['ts_email']."&password=".$params['ts_password'];
		$this->fburl = $params['fburl'];
		$this->fbtoken = $params['fbtoken'];
		
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
		
		if (preg_match("/not valid/", $result)) {
			return 'Tick Error: '.$result.' Please check you Tick settings and try again.';
		}elseif(preg_match("/xml/", $result)){
			return simplexml_load_string($result);
		}else{
			return $result;
		}
	}

	private function sendXMLRequest($xml)
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
		if($result == FALSE){
			return 'Error: Unable to connect to FreshBooks API.';
		}elseif(preg_match("/404 Error: Not Found/", $result) || preg_match("/DOCTYPE/", $result)){
			return "Error: <strong>404 Error: Not Found</strong>. Please check you FreshBooks API URL setting and try again.  The FreshBooks API url is different from your FreshBooks account url.";
		}
		
		//if xml check for FB status
		if(preg_match("/<?xml/", $result)){
			$fbxml = simplexml_load_string($result);
			if ($fbxml->attributes()->status == 'fail') {
				return 'Error: The following FreshBooks error occurred: '.$fbxml->error;
			}else{
				return $fbxml;
			}
		}
	}
	
	//Returns all open entries - takes optional project id
	function getAllOpenEntries($id = 0)
	{
		$all_entries = date("m/d/Y", mktime(0, 0, 0, date("m"), date("d"),   date("Y")-5));
		
		if ($id == 0) {
			$url = "http://mendtechnologies.tickspot.com/api/entries?updated_at={$all_entries}&entry_billable=true&billed=false";
		}else{
			$url = "http://mendtechnologies.tickspot.com/api/entries?updated_at={$all_entries}&entry_billable=true&billed=false&project_id={$id}";
		}
		
		return $this->loadxml($url);
	}

	function getOpenEntries($id = 0, $start_date = '', $end_date = '')
	{
		if (!$start_date) {
			$start_date = date("m").'/'.'01'.'/'.date("Y");
			$end_date = date("m/d/Y");
		}
		
		if ($id == 0) {
			$url = "http://mendtechnologies.tickspot.com/api/entries?start_date={$start_date}&end_date={$end_date}&entry_billable=true&billed=false";
		}else{
			$url = "http://mendtechnologies.tickspot.com/api/entries?start_date={$start_date}&end_date={$end_date}&entry_billable=true&billed=false&project_id={$id}";
		}
		
		return $this->loadxml($url);
	}
	
	function changeBilledStatus($status, $id)
	{
		
		$url = "http://mendtechnologies.tickspot.com/api/update_entry?id={$id}&billed={$status}";
		return $this->loadxml($url);
	}
	
	//creates multidimential array from entries xml object
	function processEntries($entries)
	{
		$processed_entries = array();
			foreach ($entries as $entry) {
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
	
	function getInvoice($id)
	{
		$xml =<<<EOL
			<?xml version="1.0" encoding="utf-8"?>
			<request method="invoice.get">
			<invoice_id>{$id}</invoice_id>
			</request>
EOL;

		return $this->sendXMLRequest($xml); 
	}
	
	function checkInvoiceStatus($id)
	{
		$invoice_info = $this->getInvoice($id);
		if (preg_match("/Invoice not found/", $invoice_info)) {
			return 'deleted';
		}else{
			return (string)$invoice_info->invoice->status;
		}
	}
	//gets FB clients
	//TODO: create loop to allow for multiple pages of clients
	function getFBclients()
	{
		$xml =<<<EOL
		<?xml version="1.0" encoding="utf-8"?>
		<request method="client.list">
		  <page>1</page>
		  <per_page>100</per_page>
		</request>
EOL;

		return $this->sendXMLRequest($xml); 
	}
	
	//checks for client match given FB client xml object and TS client name
	function matchClients($fbclients, $ts_client_name)
	{
		foreach ($fbclients->clients->client as $client) {
			$fb_client_name = trim((string)$client->organization);
			if (strcasecmp($fb_client_name, $ts_client_name) == 0) {
				$client_id = $client->client_id;
				return $client_id;
			}
		}
		return false;
	}
	
	//gets FB projects given a FB client id
	//TODO: create loop to allow for multiple pages of projects
	function getFBprojects($client_id)
	{
		$xml =<<<EOL
			<?xml version="1.0" encoding="utf-8"?>
			<request method="project.list">
				<client_id>{$client_id}</client_id>
			  <page>1</page>                        # The page number to show (Optional)
			  <per_page>15</per_page>               # Number of results per page, default 25 (Optional)
			</request>
EOL;

		return $this->sendXMLRequest($xml); 
	}
	
	//sets initial bill rate to 0 - uses getFBprojects to get all FB projects given a FB client id
	//compares FB project names to TS project names - if match and FB bill method is project uses project rate else 0
	function getProjectRate($client_id, $project_name)
	{
		$bill_rate = 0;
		$ts_projects = $this->getFBprojects($client_id);
		//check for FB error
		if (preg_match("/Error/", $ts_projects)) {
			return $ts_projects;
		}
		
		foreach ($ts_projects->projects->project as $project) {
			$fb_project_name = trim((string)$project->name);
			$fb_project_billmethod = trim((string)$project->bill_method);
			$ts_project_name = $project_name;
			if (strcasecmp($fb_project_name, $ts_project_name) == 0 & $fb_project_billmethod == 'project-rate') {
				$bill_rate = (float)$project->rate;
			}
		}
		return $bill_rate;
	}
	
	//creates a invoice in FB using an array of client data constructed in createinvoice controller
	function createSummaryInvoice($client_data)
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
			    <client_id>{$client_id}</client_id>              # Client being invoiced

			    <status>draft</status>                 # One of sent, viewed, paid, or draft [default]
			    <date></date>                # If not supplied, defaults to today's date (Optional)
			    <po_number></po_number>            # Purchase order number (Optional)
			    <discount></discount>                # Percent discount (Optional)
			    <notes></notes>       # Notes (Optional)
			    <terms></terms> # Terms (Optional)

			    <first_name></first_name>          # (Optional)
			    <last_name></last_name>           # (Optional)
			    <organization>{$client_name}</organization>  # (Optional)

			    <lines>                                # Specify one or more line elements (Optional)
			      <line>
			        <name></name>                     # (Optional)
			        <description>{$project_name}</description> # (Optional)
			        <unit_cost>{$project_rate}</unit_cost>                  # Default is 0
			        <quantity>{$total_hours}</quantity>                     # Default is 0
			        <tax1_name></tax1_name>                 # (Optional)
			        <tax2_name></tax2_name>                 # (Optional)
			        <tax1_percent></tax1_percent>             # (Optional)
			        <tax2_percent></tax2_percent>             # (Optional)
			      </line>
			    </lines>
			  </invoice>
       </request>
EOL;
	
		return $this->sendXMLRequest($xml); 
	}

	
}
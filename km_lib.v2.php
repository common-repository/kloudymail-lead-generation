<?php

/**
 * Wekloud S.r.l.
 * Kloudymail API library
 *
 * This is a library that allow you to use almost all the API calls that Kloudymail has,
 * it's still a work in progress ;)
 *
 * Version: 2.0
 */

class KmApi
{
    /*
     * You can find the full list of updated API calls you can do at the following page:
     * https://panel.kloudymail.com/api/v2/docs/
     */
	private $ch;
	private $uri;
	public $code;

	function __construct($key, $uri='https://panel.kloudymail.com')
	{
	    /*
	     * @param string $key The api key which you can find/create in the preferences section of your Kloudymail account
	     * @param string $uri Base link to where the API calls should be made, this is optional and used for debugging on local servers.
	     * @return void
	     */
		$this->ch = curl_init();
		$this->uri = $uri;
		$this->key = $key;
	}

	function request($method, $url, $data=null)
	{
		/*
		 * @param string $method
		 * @param string $url
		 * @param array $data
		 * @return Array
		 */
		$header=array(
			'Content-type: application/json; charset=UTF-8',
			'Authorization: Bearer '.$this->key);

		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($this->ch, CURLOPT_HTTPGET, true);
		curl_setopt($this->ch, CURLOPT_URL, $url);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
		try
		{
			if($method !=='GET')
			{
				curl_setopt($this->ch,  CURLOPT_CUSTOMREQUEST, $method);
				curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
			}
			$result = curl_exec($this->ch);
			$info = curl_getinfo($this->ch);
			$this->code = $info['http_code'];
			if(!$result){
				throw new Exception("Connection Failure");
			}
			curl_close($this->ch);
		}
		catch(Exception $e){
			return $e->getMessage();
		}

		return json_decode($result, true);
	}
	// get account_list using request method
	function accounts_list($offset, $limit)
	{
		$url = sprintf('%s/api/v2/accounts/?offset=%s&limit=%s', $this->uri, $offset, $limit);
		return $this->request('GET', $url);
	}
	// get list of list using request method
	function list_list($account, $offset, $limit)
	{
		$url = sprintf('%s/api/v2/%s/lists/?offset=%s&limit=%s', $this->uri, $account, $offset, $limit);
		return $this->request('GET',$url);
	}
	//get list of detail using request method
	function list_details($account, $list)
	{
		$url = sprintf('%s/api/v2/%s/lists/%s/', $this->uri, $account, $list);
		return $this->request('GET', $url);
	}
	//create list using request method
	function list_create($account, $data)
	{
		$url = sprintf('%s/api/v2/%s/lists/', $this->uri, $account);
		return $this->request('POST', $url, $data);
	}
	//existing list update usnig request method
	function list_update($account, $listcode, $data)
	{
		$url = sprintf('%s/api/v2/%s/lists/%s/', $this->uri, $account, $listcode);
		return $this->request('PATCH', $url, $data);
	}
	//existing list delete usnig request method
	function list_delete($account, $listcode)
	{
		$url = sprintf('%s/api/v2/%s/lists/%s/', $this->uri, $account, $listcode);
		return $this->request('DELETE', $url);
	}
	//create subscriber addto blacklist usnig request method
	function subscriber_addto_blacklist($account, $data)
	{
		$url = sprintf('%s/api/v2/%s/lists/blacklist/subscribers/', $this->uri, $account);
		return $this->request('POST', $url, $data);
	}
	//get sub accounts list usnig request method
	function sub_accounts_list($account, $offset, $limit)
	{
		$url = sprintf('%s/api/v2/%s/accounts/sub/?offset=%s&limit=%s', $this->uri, $account, $offset, $limit);
		return $this->request('GET',$url);
	}
	//create sub accounts  usnig request method
	function sub_accounts_create($account,$data)
	{
		$url = sprintf('%s/api/v2/%s/accounts/sub/', $this->uri, $account);
		return $this->request('POST',$url,$data);
	}
	//existing sub accounts update  usnig request method
	function sub_accounts_update($account,$subaccount,$data)
	{
		$url = sprintf('%s/api/v2/%s/accounts/sub/%s/', $this->uri, $account, $subaccount);
		return $this->request('PATCH', $url, $data);
	}
	//delete sub accounts  usnig request method
	function sub_accounts_delete($account,$subaccount)
	{
		$url = sprintf('%s/api/v2/%s/accounts/sub/%s/', $this->uri, $account, $subaccount);
		return $this->request('DELETE',$url);
	}
	//get sub accounts details usnig request method
	function sub_accounts_details($account,$subaccount)
	{
		$url = sprintf('%s/api/v2/%s/accounts/sub/%s/', $this->uri, $account, $subaccount);
		return $this->request('GET',$url);
	}
	//get virtual list usnig request method
	function virtual_list_list($account,$offset,$limit)
	{
		$url = sprintf('%s/api/v2/%s/lists/virtual/?offset=%s&limit=%s', $this->uri, $account, $offset, $limit);
		return $this->request('GET',$url);
	}
	//get virtual list details using request method
	function virtual_list_details($account,$code)
	{
		$url = sprintf('%s/api/v2/%s/lists/virtual/%s/', $this->uri, $account, $code);
		return $this->request('GET',$url);
	}
	//get virtual list using request method
	function virtual_list_create($account,$data)
	{
		$url = sprintf('%s/api/v2/%s/lists/virtual/', $this->uri, $account);
		return $this->request('POST', $url,$data);
	}
	//existing virtual list update  usnig request method
	function virtual_list_update($account,$code,$data)
	{
		$url = sprintf('%s/api/v2/%s/lists/virtual/%s/', $this->uri, $account, $code);
		return $this->request('PATCH', $url, $data);
	}
	//existing virtual list delete usnig request method
	function virtual_list_delete($account,$code)
	{

		$url = sprintf('%s/api/v2/%s/lists/virtual/%s/', $this->uri, $account, $code);
		return $this->request('DELETE', $url);
	}
	//get list field list using request method
	function list_field_list($account,$listcode,$offset,$limit)
	{
		$url = sprintf('%s/api/v2/%s/lists/%s/fields/?offset=%s&limit=%s', $this->uri, $account, $listcode, $offset, $limit);
		return $this->request('GET',$url);
	}
	//create list field using request method
	function list_field_create($account,$code,$data)
	{

		$url = sprintf('%s/api/v2/%s/lists/%s/fields/', $this->uri, $account, $code);
		return $this->request('POST',$url,$data);
	}
	//get list field detail using request method
	function list_field_details($account,$code,$variable)
	{

		$url = sprintf('%s/api/v2/%s/lists/%s/fields/%s/', $this->uri, $account, $code, $variable);
		return $this->request('GET',$url);
	}
	//existing list field update usnig request method
	function list_field_update($account,$code,$variable,$data)
	{
		$url = sprintf('%s/api/v2/%s/lists/%s/fields/%s/', $this->uri, $account, $code, $variable);
		return $this->request('PATCH',$url,$data);
	}
	//existing list field delete usnig request method
	function list_field_delete($account,$code,$variable)
	{
		$url = sprintf('%s/api/v2/%s/lists/%s/fields/%s/', $this->uri, $account, $code, $variable);
		return $this->request('DELETE',$url);
	}
	//get subscribe list using request method
	function subscribre_list($account,$code,$offset,$limit)
	{
		$url = sprintf('%s/api/v2/%s/lists/%s/subscribers/?offset=%s&limit=%s', $this->uri, $account, $code, $offset, $limit);
		return $this->request('GET',$url);
	}
	//create subscribe using request method
	function subscriber_insert($account,$code,$data)
	{
		$url = sprintf('%s/api/v2/%s/lists/%s/subscribers/', $this->uri, $account, $code);
		return $this->request('POST',$url,$data);
	}
	//existing subscribe update usnig request method
	function subscriber_update($account,$code,$data)
	{
		$url = sprintf('%s/api/v2/%s/lists/%s/subscribers/', $this->uri, $account, $code);
		return $this->request('PATCH',$url,$data);
	}
	//existing subscribe delete usnig request method
	function subscriber_delete($account,$code)
	{
		$url = sprintf('%s/api/v2/%s/lists/%s/subscribers/', $this->uri, $account, $code);
		return $this->request('DELETE',$url);
	}
	//search subscribe using request method
	function subscriber_search($account,$code,$data)
	{
		$url = sprintf('%s/api/v2/%s/lists/%s/subscribers/actions/search/', $this->uri, $account, $code);
		return $this->request('POST',$url,$data);
	}
	//subscriber to unsubscribe using request method
	function subscriber_unsubscribe($account,$code,$data)
	{
		$url = sprintf('%s/api/v2/%s/lists/%s/subscribers/actions/unsubscribe/', $this->uri, $account, $code);
		return $this->request('POST',$url,$data);
	}
	//get campaign list using request method
	function campaign_list($account,$offset,$limit)
	{
		$url = sprintf('%s/api/v2/%s/campaigns/?offset=%s&limit=%s', $this->uri, $account, $code, $offset,$limit);
		return $this->request('GET', $url);
	}
	//get campaign detail using request method
	function campaign_details($account,$code,$offset,$limit)
	{
		$url = sprintf('%s/api/v2/%s/campaigns/%s/?offset=%s&limit=%s', $this->uri, $account, $code,$offset,$limit);
		return $this->request('GET', $url);
	}
	//get timezone list using request method
	function timezone_list($account,$offset,$limit)
	{
		$url = sprintf('%s/api/v2/%s/timezones/?offset=%s&limit=%s', $this->uri, $account, $code, $offset,$limit);
		return $this->request('GET', $url);
	}
	//get template list using request method
	function template_list($account,$offset,$limit)
	{
		$url=sprintf('%s/api/v2/%s/templates/?offset=%s&limit=%s', $this->uri, $account, $code, $offset,$limit);
		return $this->request('GET', $url);
	}
	//get template details using request method
	function template_details($account,$code)
	{
		$url=sprintf('%s/api/v2/%s/templates/%s/', $this->uri, $account, $code);
		return $this->request('GET', $url);
	}
	//create templete using request method
	function template_create($account,$data)
	{
		$url = sprintf('%s/api/v2/%s/templates/', $this->uri, $account);
		return $this->request('POST', $url, $data);
	}
	//existing templete delete usnig request method
	function template_delete($account,$code)
	{
		$url = sprintf('%s/api/v2/%s/templates/%s/', $this->uri, $account, $code);
		return $this->request('DELETE', $url);
	}
	//existing templete update usnig request method
	function template_update($account,$code,$data)
	{
		$url = sprintf('%s/api/v2/%s/templates/%s/', $this->uri, $account, $code);
		return $this->request('PATCH', $url, $data);
	}
	//get automations list  usnig request method
	function automations_list($account)
	{
		$url = sprintf('%s/api/v2/%s/automations/', $this->uri, $account);
		return $this->request('GET', $url);
	}
	//create automations usnig request method
	function automations_create($account,$data)
	{
		$url = sprintf('%s/api/v2/%s/automations/actions/external/trigger/', $this->uri, $account);
		return $this->request('POST', $url, $data);
	}
	//get automations list  usnig request method
	function campaign_draft_list($account,$offset,$limit)
	{
		$url = sprintf('%s/api/v2/%s/campaigns/draft/?offset=%s&limit=%s', $this->uri, $account, $offset,$limit);
		return $this->request('GET', $url);
	}
	//create campaign draft usnig request method
	function campaign_draft_create($account,$data)
	{
		$url = sprintf('%s/api/v2/%s/campaigns/draft/', $this->uri, $account);
		return $this->request('POST', $url, $data);
	}
	//get campaign draft details usnig request method
	function campaign_draft_details($account,$code)
	{
		$url = sprintf('%s/api/v2/%s/campaigns/draft/%s/', $this->uri, $account, $code);
		return $this->request('GET', $url);
	}
	//existing campaign draft update usnig request method
	function campaign_draft_update($account,$code,$data)
	{
		$url = sprintf('%s/api/v2/%s/campaigns/draft/%s/', $this->uri, $account, $code);
		return $this->request('PATCH', $url, $data);
	}
	//existing campaign draft delete usnig request method
	function campaign_draft_delete($account,$code)
	{
		$url = sprintf('%s/api/v2/%s/campaigns/draft/%s/', $this->uri, $account, $code);
		return $this->request('DELETE', $url);
	}
	//send campaign usnig request method
	function campaign_send($account,$code,$data)
	{
		$url = sprintf('%s/api/v2/%s/campaigns/%s/actions/send/', $this->uri, $account, $code);
		return $this->request('POST', $url, $data);
	}
	//create campaign unsubscribe usnig request method
	function campaign_unsubscribe($account,$code)
	{
		$url = sprintf('%s/api/v2/%s/campaigns/%s/actions/unsubscribe/', $this->uri, $account, $code);
		return $this->request('POST', $url);
	}
	//create user using request method
	function create_user($account,$data)
	{
		$url = sprintf('%s/api/v2/%s/users/', $this->uri, $account);
		return $this->request('POST', $url, $data);
	}
	//get user detail using request method
	function user_details($account,$uname)
	{
		$url = sprintf('%s/api/v2/%s/users/%s/', $this->uri, $account, $uname);
		return $this->request('GET',$url);
	}
	//existing user update usnig request method
	function user_update($account,$uname,$data)
	{
		$url = sprintf('%s/api/v2/%s/users/%s/', $this->uri, $account, $uname);
		return $this->request('PATCH', $url,$data);
	}
	//existing user delete usnig request method
	function user_delete($account,$uname)
	{
		$url = sprintf('%s/api/v2/%s/users/%s/', $this->uri, $account, $uname);
		return $this->request('DELETE', $url);
	}
}
?>
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
date_default_timezone_set("America/Mexico_City");

class BM_Controller extends CI_Controller
{
	/**
	 *  Author:
	 *  Roberto Novelo
	 *
	 *  Description:
	 *
	 *  Class to be extended by web app controllers. Contains the services security
	 *  and encryption methods and other helper methods such as debugging options.
	 *  It also allows to set debugging mode and has a function to send error messages
	 *  when in this mode.
	 *
	 *  Creation Date:
	 *
	 *  April 14, 2016
	 *
	 */

	// If true, error messages shall be displayed using $this->stop($msg);
	private  $debug = false;
	// If true, only requests with valid timestamps shall be accepted
	private  $validateTimestamp = false;

	//If true, no access validation will be performed on the calling user.
	private $debugSuperUser = TRUE;

	protected  $httpData = false;
	protected  $httpParams = false;


	protected  $userID = 0;

	public function __construct()
	{
		parent::__construct();

		header('Content-Type: application/json');
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: User-Agent,Keep-Alive,Content-Type");
		header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT");

		set_error_handler(array($this, 'error_handler'));

		//Used to display development errors when desired for any request.
		if($this->debug)
		{
			$this->set_debugging();
		}

		//$this->load->model("base/base_log_model");
		//$this->load->model("base/base_auth_model");

		$uri = explode('/', $this->uri->uri_string());

		//Load http request raw data and decoded params to class
		$this->httpData = $this->input->post('data');

		if(!$this->httpData)
		{
			$this->stop();
		}

		if((!is_string($this->httpData)) && ((is_array($this->httpData)) || (is_object($this->httpData))))
		{
			$this->httpParams = json_decode(json_encode($this->httpData));
		}
		else
		{
			$this->httpParams = json_decode($this->httpData);
		}

		if(null == $this->httpParams)
		{
			$this->stop();
		}

		// if($this->httpParams && isset($this->httpParams->public_key))
		// {
		// 	$this->userID = $this->base_auth_model->get_user_id_by_public_key($this->httpParams->public_key);
		// 	if(!$this->userID)
		// 	{
		// 		$this->stop();
		// 	}
		// }

		//$this->base_log_model->save_request($this->uri->uri_string(),json_encode($this->input->post()),$this->userID);

		//Every request sent to the server shall be validated.
		if($this->validateTimestamp)
		{
			//To protect from MITM attacks.
			$this->validate_timestamp();
		}

		if(!$this->debugSuperUser)
		{
			if(!$this->base_auth_model->validate_user_permissions($this->userID, $this->uri->uri_string()))
			{
				$this->stop();
			}
		}
	}

	protected function get_user_public_key($forceNewKey=false)
	{
		return $this->base_auth_model->get_user_public_key($this->userID,$forceNewKey);
	}

	public function success($response,$msg="Success")
	{
		$response = json_decode( json_encode($response),true);

		if(!isset($response['response_message']))
		{
			$response['response_message'] = $msg;
		}

		if($this->debug)
		{
			$response['debug'] = array();
			$response['debug']['request_uri'] = $this->uri->uri_string();
			$response['debug']['request_timestamp'] = false;
			if(isset($this->httpParams->timestamp))
			{
				$response['debug']['request_timestamp'] = $this->httpParams->timestamp;
			}
			$dateTime = new DateTime();
			$response['debug']['server_timestamp'] = $dateTime->getTimestamp();
			$response['debug']['request_input'] = json_encode($this->input->post());
		}

		//End execution.
		die(json_encode($response));
	}

	public function stop($response=[],$msg="Invalid Request")
	{
		$response = json_decode(json_encode($response),true);

		if(!isset($response['response_code']))
		{
			$response['response_code'] = 400;
		}

		if(!isset($response['response_message']))
		{
			$response['response_message'] = $msg;
		}

		if($this->debug)
		{
			$response['debug'] = array();
			$response['debug']['request_uri'] = $this->uri->uri_string();
			$response['debug']['request_timestamp'] = false;
			if(isset($this->httpParams->timestamp))
			{
				$response['debug']['request_timestamp'] = $this->httpParams->timestamp;
			}
			$dateTime = new DateTime();
			$response['debug']['server_timestamp'] = $dateTime->getTimestamp();
			$response['debug']['caller'] = debug_backtrace()[1]['function'];
			$response['debug']['request_input'] = json_encode($this->input->post());
		}

		//End execution.
		die(json_encode($response));
	}


	public function error_handler($errno, $errstr, $errfile, $errline)
	{
		$msg['errno']   = $errno;
		$msg['errstr']  = $errstr;
		$msg['errfile'] = $errfile;
		$msg['errline'] = $errline;

		$this->stop($msg);
	}

	private function set_debugging()
	{
		//Display PHP error messages.
		error_reporting(E_ALL);
		ini_set('display_errors', TRUE);
		ini_set('display_startup_errors', TRUE);
	}

	private function validate_timestamp()
	{
		if(isset($this->httpParams->timestamp))
		{
			//Load date helper for timestamp validation.
			$this->load->helper('date');

			//Compare http request timestamp to current server's timestamp.
			$serverTime = time();

			$validation = $serverTime - $this->httpParams->timestamp;

			//Requests may only be 20 seconds older than server time
			if(($validation < 1200) && ($this->httpParams->timestamp < $serverTime))
			{
				return true;
			}

			//Prevents service execution since timestamp value is invalid.
			$timestamp = $this->httpParams->timestamp;

			$this->stop();
		}

		$this->stop();
	}

	protected function validate_params($paramsArr)
	{
		if((!is_string($this->httpData)) && ((is_array($this->httpData)) || (is_object($this->httpData))))
		{
			$json = json_decode(json_encode($this->httpData),TRUE);
		}
		else
		{
			$json = json_decode($this->httpData,TRUE);
		}

		//If it was successfully converted
		if((is_object($json)) || (is_array($json)))
		{
			//If recieved request has the expected keys
			if (count(array_diff($paramsArr, array_keys($json))) == 0)
			{
				return true;
			}

			//Stop code execution
			$this->stop();
		}
		else
		{
			//Stop code execution
			$this->stop();
		}
	}

}
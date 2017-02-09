<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends BM_Controller
{
	
	public function user_create ()
	{

		$this->validate_params(array("name","midname","surname","username","password","email","type","code"));

		$user = array(
			'name' 		=> $this->httpParams->name,
			'midname' 	=> $this->httpParams->midname,
			'surname' 	=> $this->httpParams->surname,
			'username' 	=> $this->httpParams->username,
			'password' 	=> password_hash($this->httpParams->password,PASSWORD_DEFAULT),
			'email' 	=> $this->httpParams->email,
			'type' 		=> $this->httpParams->type,
			'code' 		=> $this->httpParams->code
			);

		if(filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {

			$this->load->model('user/user_model');

			$username_already_exist = $this->user_model->validated_user_name($user['username']);
			$email_already_exist 	= $this->user_model->validated_email($user['email']);


			if (count($username_already_exist) === 0) 
			{	
				if (count($email_already_exist) === 0) 
				{
					if($this->user_model->insert_user($user))
					{
						$response['response_message'] 	= "Success";
						$response['response_code'] 		= 200;
						$this->success($response);
					}
				}
				else
				{
					$response['response_message'] 	= "Email already exist";
					$response['response_code'] 		= 422;
					$this->success($response);

				}
				
			}
			else 
			{
				$response['response_message'] 	= "The username already exist";
				$response['response_code'] 		= 422;
				$this->success($response);
			}


		}

		else
		{
			$response['response_message'] 	= "Invalid email Format ";
			$response['response_code'] 		= 422;
			$this->success($response);
		}


	}

	public function get_user_by_id()
	{

		$this->validate_params(array("user_id"));
		$userID=$this->httpParams->user_id;

    	$this->load->model("user/user_model");

    	$user=$this->user_model->finding_by_user_ID($userID);

    	if ($user != NULL) 
    	{
			$response['response_message'] 	= "Success";
			$response['response_code'] 		= 200;
			$response["user_data"]= $user;


		}
		else
		{	
			$response['response_message'] 	= "Error: User not found";
			$response['response_code'] 		= 422;
			$response["user_data"]= [];

		}

		$this->success($response);

	}

	public function update_db()
	{

		$this->validate_params(array("user_id"));
		$user = [];
		
		if(isset($this->httpParams->name))
		{
			$user["name"] = $this->httpParams->name;
		}
		if(isset($this->httpParams->midname))
		{
			$user["midname"] = $this->httpParams->midname;
		}
		if(isset($this->httpParams->surname))
		{
			$user["surname"] = $this->httpParams->surname;
		}
		if(isset($this->httpParams->username))
		{
			$user["username"] = $this->httpParams->username;
		}
		if(isset($this->httpParams->password))
		{
			$user["password"] = password_hash($this->httpParams->password,PASSWORD_DEFAULT);

		}
		if(isset($this->httpParams->email))
		{
			if(filter_var($this->httpParams->email, FILTER_VALIDATE_EMAIL))
			{
				$user["email"] = $this->httpParams->email;
			}
			else
			{	
				$response['response_message'] 	= "Invalid Email format";
				$response['response_code'] 		= 422;
				$this->stop($response);
			}	
		}
		if(isset($this->httpParams->type))
		{
			$user["type"] = $this->httpParams->type;
		}
		if(isset($this->httpParams->code))
		{
			$user["code"] = $this->httpParams->code;
		}


		$this->load->model("user/user_model");
		$idExist = $this->user_model->update($this->httpParams->user_id,$user);

	
		if($idExist) 
    	{
			$response['response_message'] 	= "Success";
			$response['response_code'] 		= 200;

		}
		else
		{	
			$response['response_message'] 	= "User not updated";
			$response['response_code'] 		= 422;
		}



		$this->success($response);

	}

	public function delete_db()
	{
		$this->validate_params(array("user_id"));
		$this->load->model("user/user_model");
		$userID = $this->user_model->delete($this->httpParams->user_id);

		if($userID) 
    	{
			$response['response_message'] 	= "Success";
			$response['response_code'] 		= 200;

		}
		else
		{	
			$response['response_message'] 	= "User not deleted";
			$response['response_code'] 		= 422;
		}

		$this->success($response);



	}
}

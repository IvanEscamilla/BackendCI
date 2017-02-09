<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class user_model extends CI_Model {


    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        //Connect to database
        $this->load->database();
    }

    public function insert_user($data)
    {
        
        $this->db->insert('user_data',$data);
        return $this->db->affected_rows();

    }

    public function validated_user_name($user_name)
    {
        $this->db->where('username', $user_name);
        $user = $this->db->get('user_data')->result();
        return $user;

    }    

    public function validated_email($email)
    {
        $this->db->where('email', $email);
        $email = $this->db->get('user_data')->result();
        return $email;

    } 

     public function finding_by_user_ID($ID)
    {
        $this->db->select("name,midname,surname,username,email,code");
        $this->db->where("id ", $ID);
        $userID = $this->db->get('user_data')->row();
        return $userID;

    }

    public function update ($userID,$userData)
    {
        $this->db->where("id ",$userID);
        $this->db->update("user_data",$userData);
        return $this->db->affected_rows() > 0;

    }

    public function delete($userID)
    {
        $this->db->where("id",$userID);
        $this->db->delete("user_data");
        return $this->db->affected_rows() > 0;
    }

}

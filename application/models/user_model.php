<?php
/**
 * Model for managing user transactions in the user and password reset tables.
 *
 * @package User Model
 * @author Kyle Hendricks kyleh@mendtechnologies.com
 **/

Class User_model extends Model 
{

	function __construct()
	{
		// Call the Model constructor
		parent::Model();
	}
    
	/**
	 * Checks to see if email is already in database.
	 *
	 * @param $str, string - email address from post field
	 * @return int 1 if exist, 0 if no exist
	 **/
	function check_for_email($str)
	{
		$this->db->where('email', $str);
		$this->db->from('users');
		$query = $this->db->get();
		return $query->num_rows(); 
	}
    
	/**
	 * Gets user given an email address.
	 *
	 * @param $email, string - email address
	 * @return object of user info on success, False on fail
	 **/
	function getuser($email)
	{
		$this->db->where('email', $email);
		$this->db->from('users');
		$query = $this->db->get();
			if ($query->num_rows > 0) 
			{
				return $query->row();
			}else{
				return FALSE;
			}
    }

	/**
	 * Insert user.
	 *
	 * @param $email, string - email address
	 * @return bool - True on success, False on fail
	 **/
	function insert_user($name, $email, $password)
	{
		//prepare user data for input
		$data = array(
			'name' => $name,
			'email' => $email,
			'password' => $password
			);
		
		return $this->db->insert('users', $data);
	}
	
	/**
	 * Gets all user.
	 *
	 * @param $email, string - email address
	 * @return object/bool - Object of users on success, False on fail
	 **/
	function get_all_users()
	{
		$query = $this->db->get('users');
		return $query->result();
	}	

	/**
	 * Inserts user email and hash into password_reset table
	 *
	 * @param $hash, string - dynamically generated unique hash
	 * @return bool - True on success, False on fail
	 **/
	function insert_temp_user($hash)
	{
		$data = array(
			'email' => $this->input->post('email'),
			'hash' => $hash,
			);
		
		return $this->db->insert('password_reset', $data);
	}

	/**
	 * Selects user email and from given hash from password_reset table
	 *
	 * @param $hash, $string hash extracted from uri of link to reset password
	 * @return object/bool - object containing user email on success, False on fail
	 **/
	function get_email_from_hash($hash)
	{
		$this->db->select('email');
		$this->db->where('hash', $hash);
		$this->db->from('password_reset');
		$query = $this->db->get();
		if ($query->num_rows > 0) 
		{
			return $query->result();
		}else{
			return FALSE;
		}
	}

	/**
	 * Updates password in user table
	 *
	 * @param $email, string - user email
	 * @return bool - True on success, False on fail
	 **/
	function update_password($email, $password)
	{
		$data = array('password' => $password);
		$this->db->where('email', $email);
		$this->db->update('users', $data);
	}

	/**
	 * Deletes row of user email and hash from password_reset table
	 *
	 * @param $email, string - user email
	 * @return bool - True on success, False on fail
	 **/
	function delete_password_reset($email)
	{
		$this->db->where('email', $email);
		$this->db->delete('password_reset');
	}
}
/* End of file user_model.php */
/* Location: /application/models/user_model.php */

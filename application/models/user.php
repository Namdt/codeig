<?php
class User extends CI_Model {

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	public function getUser($conditions = array()){
		$users = $this->db->get_where( 'user',$conditions)  ;
		return $users->row_array();	
	}
	public function updateUser($conditions = null, $value = null)
	{
		$this->db->where($conditions);
		$this->db->update('User',$value);
	}
}
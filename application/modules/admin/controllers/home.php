<?php
class Home extends MX_Controller{
	public function index()
	{
		$this->load->view('admin/home/index');
	}	
	public function login()
	{
		$this->load->view('admin/home/login');
	}
	
}

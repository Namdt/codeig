<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/** load the CI class for Modular Extensions **/
require dirname(__FILE__).'/Base.php';

/**
 * Modular Extensions - HMVC
 *
 * Adapted from the CodeIgniter Core Classes
 * @link	http://codeigniter.com
 *
 * Description:
 * This library replaces the CodeIgniter Controller class
 * and adds features allowing use of modules and the HMVC design pattern.
 *
 * Install this file as application/third_party/MX/Controller.php
 *
 * @copyright	Copyright (c) 2011 Wiredesignz
 * @version 	5.4
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 **/
class MX_Controller 
{
	public $autoload = array();
	
	public function __construct() 
	{
		$class = str_replace(CI::$APP->config->item('controller_suffix'), '', get_class($this));
		log_message('debug', $class." MX_Controller Initialized");
		Modules::$registry[strtolower($class)] = $this;	
		
		/* copy a loader instance and initialize */
		$this->load = clone load_class('Loader');
		$this->load->initialize($this);	
		
		/* autoload module items */
		$this->load->_autoloader($this->autoload);
		
		/*admin area*/
		$segment = $this->uri->segment_array();
		if ( $segment[1] == 'admin' )
		{
			//$this->_checkPermission( array( 'admin' => true ) );
			
			if ( $this->router->method != 'login' && !$this->session->userdata('admin_login') )
			{
				redirect( '/admin/home/login' );
				exit;
			}
			
			if ( $this->session->userdata('admin_login') )
				$this->session->set_userdata(array('admin_login' => 1)); 
		}
	}
	
	/**
	 * Check if user has permission to view page
	 * @param array $options - array( 'roles' => array of role id to check
	 * 								  'confirm' => boolean to check email confirmation
	 * 								  'admins' => array of user id to check ownership
	 * 								  'admin' => boolean to check if logged in user is admin
	 * 								  'super_admin' => boolean to check if logged in user is super admin
     *                                'aco' => string of aco to check against user's role
	 * 								 )
	 */
	protected function _checkPermission( $options = array() )
	{
		$cuser 		= $this->_getUser();
		$authorized = true;
		$hash 		= '';
		//$return_url = '/return_url:' . base64_encode( current_url() );
		
        // check aco
        if ( !empty( $options['aco'] ) )
        {
            $acos = $this->_getUserRoleParams();                
            
            if ( !in_array( $options['aco'], $acos ) )
            {
                $authorized = false;
                $msg        = 'Access denied';
            }
        }
        else
        {
    		// check login
    		if ( !$cuser )
    		{
    			$authorized = false;
    			$msg 		= 'Please login or register';			
    		}
    		else
    		{
    			// check role
    			if ( !empty( $options['roles'] ) && !in_array( $cuser['role_id'], $options['roles'] ) )
    			{
    				$authorized = false;
    				$msg 		= 'Access denied';
    			}
                
                // check admin
                if ( !empty( $options['admin'] ) && !$cuser['Role']['is_admin'] )
                {
                    $authorized = false;
                    $msg        = 'Access denied';
                }
    
                // check super admin
                if ( !empty( $options['super_admin'] ) && !$cuser['Role']['is_super'] )
                {
                    $authorized = false;
                    $msg        = 'Access denied';
                }
    			
    			// check confirmation
    			if ( !empty( $options['confirm'] ) && !$cuser['confirmed'] )
    			{
    				$authorized = false;
    				$msg 		= 'You have not confirmed your email address! Check your email (including junk folder) and click on the validation link to validate your email address';
    			}
    			
    			// check owner
    			if ( !empty( $options['admins'] ) && !in_array( $cuser['id'], $options['admins'] ) && !$cuser['Role']['is_admin'] )
    			{					
    				$authorized = false;
    				$msg 		= 'Access denied';
    			}
    		}
    	}
		
		if ( !$authorized )
		{		
			if ( !empty( $msg ) )
				$this->session->set_flashdata('error',$msg );
			
			redirect( '/pages/no_permission','refresh' );
		
			exit;
		}
	}
	
	/**
	 * Get the current logged in user
	 * @return array
	 */
	protected function _getUser()
	{
		$uid = $this->session->userdata('uid');
		$cuser = array();
		if ( !empty( $uid ) ) // logged in users
		{
			$this->load->model('User');
			
			$user = $this->User->getUser(array('id' => $uid ));
			if ( !$user['User']['active'] )
			{
				$this->session->unset_userdata('uid');			
				$this->session->set_flashdata( 'error','This account has been disabled' );
				
				return;
			}
            
            $cuser = $user['User'];
            $cuser['Role'] = $user['Role'];            
		}

		return $cuser;			
	}
	/**
	 * Log the user in
	 * @param string $email - user's email
	 * @param string $password - user's password
	 * @param boolean $remember - remember user or not
	 * @return uid if successful, false otherwise 
	 */
	protected function _logMeIn( $email, $password, $remember = false )
	{
		if ( !is_string( $email ) || !is_string( $password ) )
			return false;
		
		$this->load->model('User');
	
		// find the user
		$user = $this->User->getUser( array( 'email' => trim( $email )  ) );
		if (!empty($user)) // found
		{
			if ( $user['password'] != md5( trim( $password ) . SALT ) ) // wrong password
			    return false;
                							
			//if ( !empty($user['active']) )
			//{	
			//	$this->session->set_flashdata('error','This account has been disabled' );				
			//	return false;
			//}				
			//else
			//{		
				// save user id and user data in session
				$this->session->set_userdata('uid', $user['id']);
	
				// handle cookies
				if ( $remember )
				{
					$this->load->helper('cookie');
					$this->input->set_cookie(array('name' => 'email', 'value' => $email,'secure' => true, 'expire' => 60 * 60 * 24 * 30));
					$this->input->set_cookie(array('name' => 'password', 'value' => $password,'secure' => true, 'expire' => 60 * 60 * 24 * 30));
					
				}
	
				// update last login
				$this->User->updateUser(array('id' => $user['id']), array( 'last_login' => date("Y-m-d H:i:s") ));			
				
				return $user['id'];
			//}
		}
		else
			return false;
	}
	public function __get($class) {
		return CI::$APP->$class;
	}
}
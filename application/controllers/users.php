<?php 
class Users extends MX_Controller{
	public function do_login()
	{
		if ( !empty( $this->input->post() ) )
		{		
			$this->load->model('User');
    
            // find the user
            $user = $this->User->getUser(array(
				'email' => trim( $this->input->post('email') ), 
                'password' => md5( trim( $this->input->post('password') ) . SALT )
                    )
				);
            if (!empty($user)) // found                        
                $this->session->set_userdata('admin_login', 1);
			else
				$this->session->set_flashdata('error','Invalid email or password' );
			
			redirect( '/admin/home/index' );
		}
	}
}
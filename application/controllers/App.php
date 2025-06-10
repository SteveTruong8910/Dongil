<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App extends CI_Controller {

	public function __construct(){
       	parent::__construct();          
    }
	
	public function privacy()
	{
        $viewData = array('title' => '개인정보 처리방침');                
        
		$this->load->view('/common/header', $viewData);
		$this->load->view('/app/privacy', $viewData);
		$this->load->view('/common/footer');
	}
    
    public function provision()
    {        
        $viewData = array('title' => '이용약관');                
        
		$this->load->view('/common/header', $viewData);
		$this->load->view('/app/provision', $viewData);
		$this->load->view('/common/footer');
    }
}

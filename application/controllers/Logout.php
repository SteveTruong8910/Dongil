<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Logout extends CI_Controller {

	public function __construct(){
       	parent::__construct();
		
		$this->load->config('common');
   	}
	
	public function index()
	{                
        session_destroy();
        
        $scNav = "<script type='text/javascript' src='/assets/js/navigation.js?". $this->config->item('ver') . "'></script>";
        echo "
            {$scNav}                                    
            <script>                                        
                nav.locationHref('/', 'clear');
            </script>
        ";        
        exit;
	}
}

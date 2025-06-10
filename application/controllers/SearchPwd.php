<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// 비밀번호 찾기 기능을 담당하는 컨트롤러
class SearchPwd extends CI_Controller {

    public $user;
    
    // 생성자 함수
	public function __construct(){
       	parent::__construct();
		
		// 공통 설정 파일 로드
		$this->load->config('common');
        
        // 사용자 정보 가져오기
        $this->user = getUserInfo($this->session->userdata('user'), $this->db);
        
        // 이미 로그인한 사용자는 홈으로 리디렉션
        if(!empty($this->user)){
            header('Location: /');
            exit;
        }
    }
	
    // 비밀번호 찾기 페이지 로드
	public function index()
	{
        // 페이지에 전달할 데이터 설정
        $viewData = array('title' => '비밀번호 찾기');
        
        // 헤더, 본문, 푸터 뷰 로드
		$this->load->view('/common/header', $viewData);
		$this->load->view('/searchPwd', $viewData);
		$this->load->view('/common/footer');
	}
}

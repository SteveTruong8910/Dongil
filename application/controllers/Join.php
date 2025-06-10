<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Join extends CI_Controller {
    
    public $user;
    
    // 생성자 함수
    public function __construct(){
        // 부모 클래스의 생성자 호출
        parent::__construct();
        
        // 공통 설정 파일 로드
        $this->load->config('common');
        
        // 세션에서 사용자 정보 가져오기
        $this->user = getUserInfo($this->session->userdata('user'), $this->db);
        
        // 이미 로그인한 경우 메인 페이지로 리디렉션
        if(!empty($this->user)){
            header('Location: /');
            exit;
        }
    }
    
    // 기본 메소드 (회원가입 페이지)
    public function index()
    {
        // 뷰에 전달할 데이터 배열
        $viewData = array('title' => '회원가입');
                
        $this->load->view('/common/header', $viewData);                
        $this->load->view('/join', $viewData);                
        $this->load->view('/common/footer');
    }    
}

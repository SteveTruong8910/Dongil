<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Board extends CI_Controller {

    public $user;

    public function __construct(){
        // 부모 클래스의 생성자 호출
        parent::__construct();

        // 'common' 설정 파일을 로드
        $this->load->config('common');

        // 세션에서 사용자 정보 가져오기
        $this->user = getUserInfo($this->session->userdata('user'), $this->db);
    }

    public function index()
    {        
        // GET 파라미터에서 'type' 값을 가져오고, 없으면 'notice'를 기본값으로 설정
        $type = $_GET['type'] ?? 'notice';

        // 설정 파일에서 'noticeCate' 항목을 사용하여 해당하는 제목을 가져오기
        $title = $this->config->item('noticeCate')[$type];

        // 뷰에 전달할 데이터 배열 초기화
        $viewData = array('title' => $title);
        $viewData['type'] = $type;

        // 공통 헤더 뷰 로드 (title과 type을 전달)
        $this->load->view('/common/header', $viewData);

        // 게시판 리스트 뷰 로드 (title과 type을 전달)
        $this->load->view('/board/list', $viewData);

        // 공통 푸터 뷰 로드
        $this->load->view('/common/footer');
    }
    
    public function view()
    {
        // 페이지 제목을 설정
        $viewData = array('title' => '글상세');

        // URL에서 게시글 ID (idx)와 게시판 타입(type)을 받아옴
        $idx = $this->uri->segment(3, 0);   
        $type = $_GET['type']?? null;

        // 'type'이 비어있으면 게시판 목록 페이지로 리디렉션
        if(empty($type)){
            header('Location: /board');
            exit;
        }

        // 타입에 따라 페이지 제목을 설정
        $viewData['title'] = $type == 'review'? '상세리뷰' : '글상세';

        $sql = "";

        // 게시글 타입에 따라 SQL 쿼리 작성
        if($type == 'review'){
            $sql = "
                 SELECT
                    idx,
                    star,
                    '' AS title,
                    reviewContent AS content,
                    reviewRegDate AS regDate
                 FROM
                     write_list
                 WHERE
                    idx = '{$idx}'
            ";   
        }else{
            $sql = "
                 SELECT
                     *
                 FROM
                     board_list
                 WHERE
                    idx = '{$idx}'
            ";   
        }           

        // 쿼리를 실행하여 게시글 정보를 가져옴
        $query = $this->db->query($sql);
        $info = $query->row_array();

        // 게시글의 등록일 포맷 수정 (연도-월-일 시:분:초 형식으로 자름)
        $info['regDate'] = substr($info['regDate'], 2, 14);

        // 게시글 정보가 없으면 홈페이지로 리디렉션
        if(empty($info)){
            header('Location: /');
            exit;
        }

        // 게시글 정보를 뷰에 전달
        $viewData['info'] = $info;        

        // 답변 게시글 정보 초기화
        $viewData['answerInfo'] = array();

        // 만약 게시글에 답변이 있으면 답변 게시글 정보를 조회
        if(!empty($info['answerIdx'])){
            $sql = "
                 SELECT
                     *
                 FROM
                     board_list
                 WHERE
                    idx = '{$info['answerIdx']}'
            ";

            // 답변 게시글을 조회하고 등록일 포맷 수정
            $query = $this->db->query($sql);
            $answerInfo = $query->row_array();

            $answerInfo['regDate'] = substr($answerInfo['regDate'], 2, 14);
            // 답변 게시글 정보를 뷰에 전달
            $viewData['answerInfo'] = $answerInfo;            
        }

        // 답변 여부를 뷰에 전달 (답변이 있으면 true, 없으면 false)
        $viewData['isAnswer'] = !empty($viewData['answerInfo']);

        // 게시판 타입을 뷰에 전달
        $viewData['type'] = $type;

        // 헤더, 게시글 뷰, 푸터를 로드하여 페이지를 구성
        $this->load->view('/common/header', $viewData);
        $this->load->view('/board/view', $viewData);
        $this->load->view('/common/footer');
    }

    
    public function write()
	{        
        $viewData = array('title' => '문의 작성');
        
        // 헤더, 게시글 뷰, 푸터를 로드하여 페이지를 구성
		$this->load->view('/common/header', $viewData);
		$this->load->view('/board/write', $viewData);
		$this->load->view('/common/footer');
    }
}

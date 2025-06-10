<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

    public $user;
    
	public function __construct(){
        parent::__construct();

        // 공통 설정 파일 로드
        $this->load->config('common');

        // 쿠키 관련 헬퍼 로드
        $this->load->helper('cookie');

        // 세션에서 사용자 정보 가져와서 설정
        $this->user = getUserInfo($this->session->userdata('user'), $this->db);
    }

    public function index()
    {
        // 사용자가 로그인한 경우에만 로그 기록
        if ($this->user) {
            // 쿠키에서 'first_access' 변수를 확인
            if (!$this->input->cookie('first_access')) {
                // 처음 접속한 경우에만 로그 기록
                $this->logFirstAccess();

                // 'first_access' 쿠키를 설정하여 이후에는 로그를 기록하지 않도록 함
                $cookie = array(
                    'name'   => 'first_access',
                    'value'  => 'true',
                    'expire' => '0',  // 브라우저를 닫을 때까지 유지
                    'secure' => TRUE
                );
                $this->input->set_cookie($cookie);
            }
        }

        // 뷰에 전달할 데이터 배열 초기화
        $viewData = array('title' => 'HOME');                

        // 팝업 정보를 가져오는 SQL 쿼리
        $sql = "
             SELECT
                 *
             FROM
                 popup
             WHERE                
                isUse = 'Y'
             ORDER BY
                idx DESC;
        ";

        // 쿼리 실행 및 결과 가져오기
        $query = $this->db->query($sql);
        $list = $query->result_array();

        // 가져온 팝업 리스트 가공
        for($i = 0; $i < count($list); $i++){
            $row = $list[$i];

            // 썸네일 정보를 JSON에서 배열로 변환
            $row['thumbnail'] = json_decode($row['thumbnail'], true);

            // 원본 이미지 및 리사이즈 이미지 경로 설정
            $row['onebonImgPath'] = '/assets/upload/popup/'.$row['thumbnail'][0]['onebonFileName'];
            $row['resizeImgPath'] = '/assets/upload/popup/'.$row['thumbnail'][0]['fileName'];

            // 등록 날짜 가공 (앞 두 자리 제거)
            $row['regDate'] = substr($row['regDate'], 2, 14);

            // 가공한 데이터를 리스트에 다시 저장
            $list[$i] = $row;
        }

        // 가공된 팝업 리스트를 뷰 데이터에 추가
        $viewData['popupList'] = $list;    

        if (!empty($this->user)) {
            $sql = "
                SELECT COUNT(*) AS cnt
                FROM mailbox_list
                WHERE memberIdx = '{$this->user['idx']}'
                  AND isRead = 'N'
                  AND isUse = 'Y'
            ";
            $query = $this->db->query($sql);
            $row = $query->row_array();
            $viewData['hasNewMailbox'] = $row['cnt'] > 0;
        } else {
            $viewData['hasNewMailbox'] = false;
        }

        // 공통 헤더, 메인, 푸터 뷰 로드
        $this->load->view('/common/header', $viewData);
        $this->load->view('/home', $viewData);
        $this->load->view('/common/footer');
    }

    private function logFirstAccess() {
        // 로그 기록 로직
        $ipAddress = $this->input->ip_address();
        $data = [
            'user_id' => $this->user['idx'] ?? null,
            'username' => $this->user['nickname'] ?? 'guest',
            'ip_address' => $ipAddress,
            'type' => '접속',
            'reason' => '처음 접속',
            'access_date' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('access_log', $data);

        if ($this->db->affected_rows() == 0) {
            log_message('error', 'Failed to log first access: ' . $this->db->error()['message']);
        }
    }
}

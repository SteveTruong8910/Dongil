<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mypage extends CI_Controller {

    public $user;

    public function __construct(){
        parent::__construct(); // 부모 클래스의 생성자 호출

        $this->load->config('common'); // 공통 설정 파일 로드
        $this->user = getUserInfo($this->session->userdata('user'), $this->db); // 세션에서 사용자 정보 가져오기

        // 현재 컨트롤러와 메서드명을 가져옴
        $current_controller = $this->router->fetch_class();
        $current_method = $this->router->fetch_method();                
    }

    public function index()
    {
        // 사용자가 로그인하지 않았다면 로그인 페이지로 이동
        if(empty($this->user)){
            header('Location: /login');
            exit;
        }        

        // 관리자로 로그인한 경우 관리자 페이지로 이동
        if(!empty($this->user['id'])){
            header('Location: /admin');
            exit;
        }

        // 뷰에서 사용할 데이터 설정
        $viewData = array('title' => '마이페이지');                        

        // 헤더, 본문, 푸터 뷰 로드
        $this->load->view('/common/header', $viewData);
        $this->load->view('/mypage', $viewData);
        $this->load->view('/common/footer');
    }
    
    public function myLetter()
    {
        // 뷰에서 사용할 데이터 설정
        $viewData = array('title' => '내가 쓴 편지');

        // GET 요청에서 사용자 입력값 가져오기
        $mbName = $_GET['mbName'] ?? "";
        $mbId = $_GET['mbId'] ?? "";
        $mbPassword = $_GET['mbPassword'] ?? "";
        $searchSql = "";

        // 비로그인 사용자 처리
        if (empty($this->user)) {                        
            // 사용자 인증을 위한 검색 조건 설정
            $searchSql = "
                mbName = '{$mbName}' AND
                mbPhoneNumber = '{$mbId}' AND
                mbPassword = '{$mbPassword}' AND                
            ";

            // 필수 정보가 없을 경우 메인 페이지로 이동
            if (empty($mbName) || empty($mbId) || empty($mbPassword)) {
                header('Location: /');
                exit;
            }
        } else {
            // 로그인된 사용자의 경우 결제 상태 확인
            checkPaymentOrder($this->user['idx']);

            // 로그인 사용자의 검색 조건 설정
            $searchSql = "memberIdx = '{$this->user['idx']}' AND ";
        }

        // 사용자가 작성한 편지 목록 조회 SQL
        $sql = "
             SELECT
                *
             FROM
                write_list
             WHERE
                $searchSql
                ((state != 'W' AND state != 'T') OR (state = 'W' AND isPay = 'Y')) AND
                isUse = 'Y'
             ORDER BY
                regDate DESC;
        ";

        // 쿼리 실행 후 결과 리스트 가져오기
        $query = $this->db->query($sql);
        $list = $query->result_array();

        // 입금 상태 확인 변수 초기화
        $isDeposit = false;
        for ($i = 0; $i < count($list); $i++) {
            $row = $list[$i];

            // 입금 상태 확인 (최초로 'B' 상태가 있으면 true 설정)
            if (!$isDeposit && $row['state'] == 'B') {
                $isDeposit = true;
            }

            // 등록 날짜 형식 변경
            $row['regDate'] = substr($row['regDate'], 2, 14);

            $list[$i] = $row;
        }

        // 뷰에 전달할 데이터 설정
        $viewData['list'] = $list;
        $viewData['mbName'] = $mbName;
        $viewData['mbId'] = $mbId;
        $viewData['mbPassword'] = $mbPassword;
        $viewData['isDeposit'] = $isDeposit;

        // 뷰 로드 (헤더, 본문, 푸터)
        $this->load->view('/common/header', $viewData);                
        $this->load->view('/mypage/myLetter', $viewData);        
        $this->load->view('/common/footer');
    }

    public function myMailbox()
    {
        // 로그인 체크
        if (empty($this->user)) {
            header('Location: /');
            exit;
        }

        $viewData = array('title' => '사서함 편지');

        // 사용자에게 해당하는 사서함 데이터 조회
        $sql = "
            SELECT
                *
            FROM
                mailbox_list
            WHERE
                memberIdx = '{$this->user['idx']}' AND
                isUse = 'Y'
            ORDER BY
                regDate DESC
        ";

        $query = $this->db->query($sql);
        $list = $query->result_array();

        // 날짜 포맷 가공
        foreach ($list as &$item) {
            $item['regDateFormatted'] = substr($item['regDate'], 2, 8); // 예: '25-03-28'
            $item['fileUrl'] = '/assets/upload/mailbox/' . $item['fileName'];
        }

        $viewData['mailList'] = $list;

        // 뷰 렌더링
        $this->load->view('/common/header', $viewData);
        $this->load->view('/mypage/myMailbox', $viewData);
        $this->load->view('/common/footer');
    }

    public function payment()
    {
        // 파라미터 받기
        $type = $this->input->get('type'); // scan 또는 delivery
        $mailboxIdx = $this->input->get('mailboxIdx');

        if (!in_array($type, ['scan', 'delivery']) || empty($mailboxIdx)) {
            show_error('잘못된 접근입니다.');
            return;
        }

        
        $title = $type === 'scan' ? '스캔본 다운로드' : '택배로 받기';

        log_message('error', '현재 닉네임: ' . $this->user['nickname']);

        if ($this->user['nickname'] === '정 현') {
            $price = 1;
        } else {
            $price = $type === 'scan' ? 1000 : 4000;
        }

        $viewData = [
            'title' => $title,
            'type' => $type,
            'mailboxIdx' => $mailboxIdx,
            'price' => $price
        ];

        $this->load->view('/common/header', $viewData);
        $this->load->view('/mypage/payment', $viewData);
    }

    
    public function tmpLetter()
    {
        // 사용자가 로그인하지 않은 경우, 메인 페이지로 리디렉션
        if (empty($this->user)) {
            header('Location: /');
            exit;
        }

        // 뷰 데이터에 페이지 제목 설정
        $viewData = array('title' => '임시저장된 편지');

        // 임시 저장된 편지 목록을 조회하는 SQL 쿼리
        $sql = "
            SELECT
                L.thumbnail,
                L.name,
                W.*
            FROM
                write_list AS W 
            LEFT JOIN
                letter_list AS L ON L.idx = W.letterIdx
            WHERE
                W.memberIdx = '{$this->user['idx']}' AND
                W.state = 'T' AND
                W.isUse = 'Y'
            ORDER BY
                W.tmpSaveRegDate DESC
        ";

        // 쿼리 실행 및 결과 가져오기
        $query = $this->db->query($sql);
        $list = $query->result_array();

        // 조회된 편지 데이터를 가공
        for ($i = 0; $i < count($list); $i++) {
            $info = $list[$i];

            // 썸네일 이미지가 있는 경우 처리
            if (!empty($info['thumbnail'])) {
                $info['thumbnail'] = json_decode($info['thumbnail'], true);
                $info['imgPath'] = '/assets/upload/' . $info['thumbnail'][0]['fileName'];  // 업로드된 이미지 경로 설정
            } else {
                $info['imgPath'] = '/assets/image/no_image.png';  // 썸네일이 없으면 기본 이미지 사용
            }

            // 편지 내용을 특정 구분자로 분할
            $info['content'] = explode('$#', $info['content']);

            // 임시 저장된 날짜 포맷 변경 (앞 두 자리 제거)
            $info['tmpSaveRegDate'] = substr($info['tmpSaveRegDate'], 2, 14);

            // 수정된 정보를 리스트에 반영
            $list[$i] = $info;
        }

        // 뷰 데이터에 편지 목록 추가
        $viewData['list'] = $list;

        // 헤더, 본문, 푸터 뷰 로드
        $this->load->view('/common/header', $viewData);
        $this->load->view('/mypage/tmpLetter', $viewData);
        $this->load->view('/common/footer');
    }

    
    public function searchLetter()
    {
        $viewData = array('title' => '비회원 주문 조회');                
        
        // 헤더, 본문, 푸터 뷰 로드
		$this->load->view('/common/header', $viewData);
		$this->load->view('/mypage/searchLetter', $viewData);
		$this->load->view('/common/footer');
    }
    
    public function checkLetter()
    {
        $viewData = array('title' => '편지쓰기');                
        
        // 헤더, 본문, 푸터 뷰 로드
		$this->load->view('/common/header', $viewData);
		$this->load->view('/mypage/checkLetter', $viewData);
		$this->load->view('/common/footer');
    }
    
    public function delivery()
    {
        // 뷰에 전달할 데이터 배열을 생성하고, 페이지 제목을 설정
        $viewData = array('title' => '배송 현황');

        // URL 세그먼트에서 배송 등록 번호를 가져옴 (3번째 세그먼트)
        $registrationNumber = $this->uri->segment(3);

        // 가져온 배송 등록 번호를 뷰 데이터에 추가
        $viewData['registrationNumber'] = $registrationNumber;

        // 헤더, 본문, 푸터 뷰 로드
        $this->load->view('/common/header', $viewData);        
        $this->load->view('/mypage/delivery', $viewData);        
        $this->load->view('/common/footer');
    }
    
    public function myLetterView()
    {
        // 뷰에 전달할 데이터 배열을 생성하고, 페이지 제목을 설정
        $viewData = array('title' => '작성한 내용');

        // GET 요청에서 mbName, mbId, mbPassword 값을 가져옴 (없으면 빈 문자열)
        $mbName = $_GET['mbName'] ?? "";
        $mbId = $_GET['mbId'] ?? "";
        $mbPassword = $_GET['mbPassword'] ?? "";

        // URL 세그먼트에서 작성한 글의 idx를 가져옴
        $writeIdx = $this->uri->segment(3);

        // 사용자 정보가 없으면 GET 값에 따라 검색 조건을 설정
        $searchSql = "";
        if (empty($this->user)) {
            $searchSql = "
                mbName = '{$mbName}' AND
                mbPhoneNumber = '{$mbId}' AND
                mbPassword = '{$mbPassword}'
            ";
        } else {
            // 사용자가 로그인되어 있으면 memberIdx를 기준으로 검색 조건 설정
            $searchSql = " memberIdx = '{$this->user['idx']}' ";
        }

        // 글 목록에서 idx와 조건에 맞는 글을 조회하는 SQL 쿼리 작성
        $sql = "
             SELECT
                 *
             FROM
                 write_list
             WHERE
                idx = '{$writeIdx}' AND
                $searchSql
        ";

        // 쿼리 실행 후 결과를 가져옴
        $query = $this->db->query($sql);
        $writeInfo = $query->row_array();

        // 글 정보가 없으면 마이 페이지로 리다이렉트
        if (empty($writeInfo)) {
            header('Location: /mypage');
            exit;
        }

        // 글 내용을 구분자로 나누고, JSON 형식의 파일들을 배열로 변환
        $writeInfo['content'] = explode('$#', $writeInfo['content']);
        $writeInfo['photos'] = json_decode($writeInfo['photos'], true);
        $writeInfo['pdfFiles'] = json_decode($writeInfo['pdfFiles'], true);
        $writeInfo['fileColor'] = explode('/', $writeInfo['fileColor']);

        // 글에 설정된 폰트 이름에 맞는 폰트 정보를 찾음
        foreach ($this->config->item('fonts') as $key => $data) {
            if ($data['font-family'] == $writeInfo['fontName']) {
                $writeInfo['font'] = $data;
                break;
            }
        }

        // 글에 관련된 편지 정보 가져오기
        $letterInfo = array();
        if ($writeInfo['letterIdx'] != -1) {
            // letterIdx가 -1이 아니면 해당 편지 정보를 조회
            $sql = "
                 SELECT
                     *
                 FROM
                     letter_list
                 WHERE
                     idx = '{$writeInfo['letterIdx']}';
            ";

            // 쿼리 실행 후 결과를 가져옴
            $query = $this->db->query($sql);
            $letterInfo = $query->row_array();
            $letterInfo['thumbnail'] = json_decode($letterInfo['thumbnail'], true);
            $letterInfo['imgPath'] = '/assets/upload/' . $letterInfo['thumbnail'][0]['onebonFileName'];
        }

        // 뷰에 전달할 데이터 설정
        $viewData['writeInfo'] = $writeInfo;
        $viewData['letterInfo'] = $letterInfo;

        // 이미지 크기 조정 라이브러리 로드
        $this->load->view('/libs/php-image-resize-master/ImageResize');

        // 헤더, 본문, 푸터 뷰 로드
        $this->load->view('/common/header', $viewData);
        $this->load->view('/mypage/myLetterView', $viewData);        
        $this->load->view('/common/footer');
    }

    
    public function myAddress()
    {
        // 뷰에 전달할 데이터 배열을 생성하고, 페이지 제목을 설정
        $viewData = array('title' => '주소지 관리');

        // 현재 사용자의 주소 정보를 조회하는 SQL 쿼리 작성
        $sql = "
             SELECT
                *
             FROM
                address
             WHERE
                memberIdx = '{$this->user['idx']}' AND                
                isUse = 'Y'
             ORDER BY
                idx DESC;
        ";

        // SQL 쿼리 실행
        $query = $this->db->query($sql);

        // 쿼리 결과를 배열로 가져옴
        $postList = $query->result_array();

        // 주소 목록을 두 개의 배열로 나누어 정렬
        $sortedPostList = [];  // 현재 사용자의 기본 주소를 앞에 배치할 배열
        $remainingPosts = [];  // 나머지 주소들을 배치할 배열

        // 주소 목록을 순회하며, 기본 주소를 맨 앞에 배치하고 나머지는 뒤에 배치
        foreach($postList as $key => $data) {
            // 기본 주소로 설정된 주소는 맨 앞에 배치
            if ($this->user['addressIdx'] == $data['idx']) {                
                array_unshift($sortedPostList, $data);
            } else {                
                // 나머지 주소는 뒤에 추가
                $remainingPosts[] = $data;
            }
        }

        // 기본 주소가 맨 앞에 오도록 나머지 주소들과 합침
        $sortedPostList = array_merge($sortedPostList, $remainingPosts);

        // 정렬된 주소 목록을 뷰 데이터에 추가
        $viewData['postList'] = $sortedPostList;

        // 헤더, 본문, 푸터 뷰 로드
        $this->load->view('/common/header', $viewData);        
        $this->load->view('/mypage/myAddress', $viewData);        
        $this->load->view('/common/footer');
    }

    
    public function point()
    {
        $viewData = array('title' => '포인트 관리');               
        
        // 헤더, 본문, 푸터 뷰 로드
		$this->load->view('/common/header', $viewData);
		$this->load->view('/mypage/point', $viewData);
		$this->load->view('/common/footer');
    }
    
    public function addPoint()
    {
        $viewData = array('title' => '포인트 충전');               
        
        // 헤더, 본문, 푸터 뷰 로드
		$this->load->view('/common/header', $viewData);                
        $this->load->view('/mypage/addPoint', $viewData);
		$this->load->view('/common/footer');
    }
    
    public function payPoint()
    {
        $viewData = array('title' => '포인트 결제');               
        
        // 헤더, 본문, 푸터 뷰 로드
		$this->load->view('/common/header', $viewData);
		$this->load->view('/mypage/payPoint', $viewData);
		$this->load->view('/common/footer');
    }

    public function pay()        
    {
        // 뷰에 전달할 데이터 배열 초기화
        $viewData = array('title' => '');

        // GET 요청에서 writeIdx(글 인덱스) 가져오기
        $mailboxIdx = $_GET['mailboxIdx'];

        // 해당 글 정보 가져오기
        $sql = "SELECT * FROM mailbox_list WHERE idx = ? ";        

        // SQL 실행 후 결과를 배열로 저장
        $query = $this->db->query($sql, array($mailboxIdx));
        $mailboxInfo = $query->row_array();

        // 최종 결제 금액 변수 초기화
        $realTotalPrice = 0;

        $realTotalPrice = $mailboxInfo['realTotalPrice'];
        

        // 뷰에 전달할 데이터 설정
        $viewData['realTotalPrice'] = $realTotalPrice;
        $viewData['mailboxInfo'] = $mailboxInfo;

        // 뷰 로드 (헤더, 결제 페이지, 푸터)
        $this->load->view('/common/header', $viewData);
        $this->load->view('/mypage/pay', $viewData);
        $this->load->view('/common/footer');
    }
}

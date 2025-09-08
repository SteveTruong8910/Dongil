<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserApi extends CI_Controller {

    public $user;

    public function __construct(){
        parent::__construct();

        // 공통 설정 파일을 로드
        $this->load->config('common');

        // 세션에서 'user' 데이터를 가져와 클래스 속성에 할당
        $this->user = $this->session->userdata('user');          
        
        error_log("User session data: " . json_encode($this->user));

        // 'userApiModel' 모델을 로드
        $this->load->model('userApiModel');        
    }

    // CURL 요청을 통해 Basic 인증, JSON 형식으로 POST 요청을 보내는 함수
    function requestPost($url, $json, $userpwd)
    {
        // cURL 초기화
        $ch = curl_init();

        // cURL 옵션 설정
        curl_setopt($ch, CURLOPT_URL, $url); // 요청 URL
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); // 헤더에 Content-Type 설정
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC); // Basic 인증 설정
        curl_setopt($ch, CURLOPT_USERPWD, $userpwd); // 인증 정보 (사용자 아이디와 비밀번호)
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json); // 요청 데이터 (JSON 형식)
        curl_setopt($ch, CURLOPT_POST, true); // POST 방식으로 요청
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 응답을 문자열로 반환
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15); // 연결 타임아웃 설정 (15초)
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // SSL 인증서 검증을 하지 않음

        // 요청 실행 및 응답을 변수에 저장
        $response = curl_exec($ch);

        // cURL 종료
        curl_close($ch);

        // 응답 반환
        return $response;
    }

    // 문자 전송 기능을 수행하는 메소드
    public function sendMessage()
    {
        // 결과를 저장할 배열 초기화
        $result = array('result' => false, 'msg' => "");

        // POST 요청에서 phoneNumber와 sendType 값을 받아옴
        $phoneNumber = $_POST['phoneNumber'];
        $sendType = $_POST['sendType'] ?? 'join'; // 기본값은 'join'

        // 회원가입 또는 비밀번호 찾기 요청 시 처리
        if($sendType == 'join' || $sendType == 'searchPwd'){
            // 전화번호로 사용자 정보를 가져옴
            $userInfo = $this->userApiModel->getUserInfo($phoneNumber);

            // 회원가입인 경우 이미 등록된 전화번호가 있으면 오류 메시지 반환
            if($sendType == 'join' && !empty($userInfo)){
                $result['msg'] = "이미 가입된 휴대번호입니다.";
                die(json_encode($result)); // 메시지 반환 후 종료
            }

            // 비밀번호 찾기인 경우 등록되지 않은 전화번호는 오류 메시지 반환
            if($sendType == 'searchPwd' && empty($userInfo)){
                $result['msg'] = "가입된 휴대번호가 아닙니다.";
                die(json_encode($result)); // 메시지 반환 후 종료
            }
        }

        // 인증번호 생성 (6자리 랜덤 숫자)
        $authNumber = random_int(100000, 999999);

        // 문자 전송 함수 호출
        $isSendMessage = sendMessage($authNumber, $phoneNumber);

        // 문자 전송 실패 시 오류 메시지 반환
        if(empty($isSendMessage)){
            $result['msg'] = "문자 전송에 실패하였습니다. 고객센터에 문의부탁드립니다.";
            die(json_encode($result)); // 메시지 반환 후 종료
        }

        // 인증번호와 휴대폰 번호를 DB에 저장
        $isSaveAuthMessage = $this->userApiModel->saveAuthMessage($phoneNumber, $authNumber);

        // 인증번호 저장 실패 시 오류 메시지 반환
        if(!$isSaveAuthMessage){
            $result['msg'] = "인증번호 저장 실패";
            die(json_encode($result)); // 메시지 반환 후 종료
        }

        // 성공적으로 처리된 경우 결과를 true로 설정하고 반환
        $result['result'] = true;
        die(json_encode($result)); // 성공 메시지 반환
    }
    
    // 메일 보내는 함수
    public function sendMail()
    {
        $result = array('result' => false, 'msg' => "");
        
        $title = $_POST['title'];
        $mbEmail = $_POST['mbEmail'];  
        $nickname = $_POST['nickname'];
        $sendType = $_POST['sendType']?? 'join';
                
        if($sendType == 'join' || $sendType == 'searchPwd'){            
            /* 이메일, 이름 체크 */
            $sql = "SELECT idx FROM member_list WHERE mbEmail = '{$mbEmail}' AND nickname = '{$nickname}' ";        
            $query = $this->db->query($sql);
            $info = $query->row_array();

            if(empty($info)){
                $result['msg'] = '존재하지 않는 이메일, 이름입니다. 다시 한 번 확인해주세요';
                die(json_encode($result));
            }
                        
//            if($sendType == 'join' && !empty($userInfo)){
//                $result['msg'] = "이미 가입된 이메일입니다.";
//                die(json_encode($result));
//            }
//            
//            if($sendType == 'searchPwd' && empty($userInfo)){
//                $result['msg'] = "가입된 이메일이 아닙니다.";
//                die(json_encode($result));
//            }
        }    
        
        /* 이메일 전송 */
        $authNumber = random_int(100000, 999999);
        $isSendMail = sendMail($mbEmail, $title, getCodeMailHtml($authNumber));
        
        if(empty($isSendMail)){
            $result['msg'] = "이메일 전송에 실패하였습니다. 고객센터에 문의부탁드립니다.";
            die(json_encode($result));
        }
        
        /* 이메일 전송 DB저장 */
        $isSaveAuthEmail = $this->userApiModel->saveAuthEmail($mbEmail, $authNumber);
        
        if(!$isSaveAuthEmail){
            $result['msg'] = "인증번호 저장 실패";
            die(json_encode($result));
        }
        
        $result['result'] = true;
        die(json_encode($result));
    }
    
    // 전화번호 인증 코드 확인 함수
    public function chkPhoneAuthNumber()
    {
        // 결과를 저장할 배열 초기화
        $result = array('result' => false, 'msg' => "");

        // POST로 받은 전화번호와 인증번호
        $phoneNumber = $_POST['phoneNumber'];
        $authNumber = $_POST['authNumber'];

        // 데이터베이스에서 해당 전화번호에 저장된 인증번호 가져오기
        $dbAuthNumber = $this->userApiModel->chkPhoneAuthNumber($phoneNumber);                    

        // 입력받은 인증번호와 데이터베이스의 인증번호가 일치하는지 비교
        if($dbAuthNumber != $authNumber){
            // 인증번호 불일치 시 오류 메시지 반환
            $result['msg'] = "잘못된 인증코드입니다.";
            die(json_encode($result)); // 메시지 반환 후 종료
        }

        // 인증 성공 시 결과를 true로 설정
        $result['result'] = true;
        die(json_encode($result)); // 성공 메시지 반환
    }

    // 이메일 인증 코드 확인 함수
    public function chkAuthNumber()
    {
        // 결과를 저장할 배열 초기화
        $result = array('result' => false, 'msg' => "");

        // POST로 받은 이메일과 인증번호
        $mbEmail = $_POST['mbEmail'];
        $authNumber = $_POST['authNumber'];

        // 데이터베이스에서 해당 이메일에 저장된 인증번호 가져오기
        $dbAuthNumber = $this->userApiModel->chkAuthNumber($mbEmail);                    

        // 입력받은 인증번호와 데이터베이스의 인증번호가 일치하는지 비교
        if($dbAuthNumber != $authNumber){
            // 인증번호 불일치 시 오류 메시지 반환
            $result['msg'] = "잘못된 인증코드입니다.";
            die(json_encode($result)); // 메시지 반환 후 종료
        }

        // 인증 성공 시 결과를 true로 설정
        $result['result'] = true;
        die(json_encode($result)); // 성공 메시지 반환
    }
    
    // 비밀번호 변경 함수
    public function changePwd()
    {
        // 결과를 저장할 배열 초기화
        $result = array('result' => false, 'msg' => "");

        // POST로 받은 새 비밀번호, 닉네임, 이메일
        $mbPassword = $_POST['mbPassword'];        
        $nickname = $_POST['nickname'];
        $mbEmail = $_POST['mbEmail'];

        // 비밀번호 변경 처리
        // userApiModel의 changePwd 함수 호출, 비밀번호, 닉네임, 이메일을 인자로 전달
        $result['result'] = $this->userApiModel->changePwd($mbPassword, $nickname, $mbEmail);

        // 비밀번호 변경 실패 시 처리
        if(empty($result['result'])){
            $result['msg'] = "비밀번호 변경 실패"; // 실패 메시지 설정
            die(json_encode($result)); // 결과 반환 후 종료
        }

        // 비밀번호 변경 성공 시 결과 반환
        die(json_encode($result)); // 성공 메시지 반환
    }

    // 회원 가입 처리 함수
    public function goJoin()
    {
        // 결과를 저장할 배열 초기화
        $result = array('result' => false, 'msg' => "");

        // POST로 받은 아이디, 비밀번호, 이메일, 닉네임
        $mbId = $_POST['mbId'];
        $mbPassword = $_POST['mbPassword'];
        $mbRePassword = $_POST['mbRePassword'];
        $mbEmail = $_POST['mbEmail'];
        $nickname = $_POST['nickname'];

        // 아이디 중복체크: 동일한 아이디가 있는지 확인
        $sql = "SELECT idx FROM member_list WHERE mbId = '{$mbId}' ";        
        $query = $this->db->query($sql);
        $info = $query->row_array();

        // 아이디가 이미 존재하면 오류 메시지 반환
        if(!empty($info)){
            $result['msg'] = '이미 가입된 휴대번호입니다. 다른 휴대번호로 진행해주세요.';
            die(json_encode($result)); // 메시지와 함께 종료
        }

        // 이메일과 닉네임 중복체크: 동일한 이메일과 닉네임이 있는지 확인
        $sql = "SELECT idx FROM member_list WHERE mbEmail = '{$mbEmail}' AND nickname = '{$nickname}' ";        
        $query = $this->db->query($sql);
        $info = $query->row_array();

        // 이메일과 닉네임이 이미 존재하면 오류 메시지 반환
        if(!empty($info)){
            $result['msg'] = '이미 존재하는 이메일, 이름입니다. 다른 이메일 이름으로 진행해주세요.';
            die(json_encode($result)); // 메시지와 함께 종료
        }

        // 회원 가입 정보 DB에 저장
        $sql = "
            INSERT INTO
                member_list
            SET
                mbId = '{$mbId}',
                mbPassword = password('{$mbPassword}'),
                mbEmail = '{$mbEmail}',
                nickname = '{$nickname}',
                senderName = '{$nickname}',
                senderTel = '{$mbId}'
        ";

        // DB에 회원 정보 저장
        $result['result'] = $this->db->query($sql);

        // 저장 실패 시 오류 메시지 반환
        if(empty($result['result'])){
            $result['msg'] = 'goJoin error';
            die(json_encode($result)); // 메시지와 함께 종료
        }

        // 가입한 사용자 정보 세션에 저장
        $info = array(
            'idx' => $this->db->insert_id(),            
            'mbId' => $mbId
        );

        // 세션에 사용자 정보 저장
        $this->session->set_userdata('user', $info);

        // 결과 반환
        die(json_encode($result)); // 성공 메시지 반환
    }
    
    // 로그인 처리 함수
    public function goLogin()
    {
        // POST로 받은 아이디와 비밀번호
        $mbId = $_POST['mbId'];
        $mbPassword = $_POST['mbPassword'];

        // SQL 쿼리 변수 초기화
        $sql = "";
        $info = array();

        // 관리자 이용자 관리전용 로그인 처리
        if($mbPassword == 'dongap3667!') {
            $sql = "
                SELECT 
                    * 
                FROM
                    member_list
                WHERE 
                    idx = ?"; // idx로 검색

            // 쿼리 실행
            $query = $this->db->query($sql, array($mbId));
            $info = $query->row_array();            
        } else {            
            // 일반 로그인
            $sql = "
                SELECT 
                    * 
                FROM 
                    member_list 
                WHERE 
                    mbId = ? AND
                    mbPassword = password(?)"; // 아이디와 비밀번호로 검색
            
            // 쿼리 실행
            $query = $this->db->query($sql, array($mbId, $mbPassword));
            $info = $query->row_array();
        }                        

        // 로그인 실패 처리
        $result['result'] = false;        
        if(empty($info)){
            $this->logLoginAttempt($mbId, 'failure', '아이디 혹은 비밀번호가 일치하지 않습니다.');
            $result['msg'] = '아이디 혹은 비밀번호가 일치하지 않습니다.';
            die(json_encode($result)); // 메시지와 함께 종료
        }

        // 계정 비활성화 처리
        if($info['isUse'] == 'N') {
            $this->logLoginAttempt($mbId, 'failure', '탈퇴처리된 계정입니다.');
            $result['msg'] = "회원번호 : {$info['idx']}, 해당 계정은 탈퇴처리된 계정입니다. 복구를 원하시면 고객센터에 문의해주세요.";
            die(json_encode($result)); // 메시지와 함께 종료
        }

        // 세션에 로그인 사용자 정보 저장
        $info = array(
            'idx' => $info['idx'],            
            'mbId' => $info['mbId'],
            'nickname' => $info['nickname']
        );

        // 사용자 정보 세션에 저장
        $this->session->set_userdata('user', $info);

        // 로그인 성공 처리
        $result['result'] = true;
        $this->logLoginAttempt($info['nickname'], 'success', '로그인 성공');

        die(json_encode($result)); // 성공 메시지 반환
    }

    // 로그인 시도 로그 기록 함수
    private function logLoginAttempt($username, $status, $reason) {
        $ipAddress = $this->input->ip_address();
        $data = [
            'user_id' => null, // 로그인 실패 시 user_id가 없을 수 있음
            'username' => $username,
            'ip_address' => $ipAddress,
            'sns' => '', // SNS 로그인 정보가 있다면 여기에 추가
            'status' => $status,
            'reason' => $reason,
            'login_date' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('user_log', $data);

        // 에러가 발생했는지 확인
        if ($this->db->affected_rows() == 0) {
            log_message('error', 'Failed to log login attempt: ' . $this->db->error()['message']);
        }
    }
    
    // 편지 목록 조회 함수
    public function getLetter()
    {
        // 기본 결과 배열 설정
        $result = array('result' => false, 'msg' => '');

        // 카테고리 인덱스 (cateIdx) 받기
        $cateIdx = $_POST['cateIdx'];
        $sortType = $_POST['sortType'] ?? 'DESC';

        // 카테고리 필터가 있으면 SQL에 조건 추가
        $searchSql = "";        
        if(!empty($cateIdx)){
            $searchSql = " cateIdx = '{$cateIdx}' AND "; // cateIdx로 필터링
        }        

        // SQL 쿼리 작성
        if($cateIdx == 0) {
            $sql = "
                SELECT *
                FROM letter_list
                WHERE cateIdx IN (1, 2, 22, 31, 39, 33, 32, 41, 38, 30) AND isUse = 'Y'
                ORDER BY idx DESC;  /* 최신순 정렬 (sortOrder 무시) */
            ";
            $query = $this->db->query($sql);
        } else if ($cateIdx == 1) {
            // 카테고리 기본 선택 시 오래된 순으로 정렬
            $sql = "
                SELECT *
                FROM letter_list
                WHERE cateIdx = ? AND isUse = 'Y'
                ORDER BY idx ASC;
            ";
            $query = $this->db->query($sql, array($cateIdx));
        } else {
            // 특정 카테고리를 선택한 경우
            $sql = "
                SELECT *
                FROM letter_list
                WHERE cateIdx = ? AND isUse = 'Y'
                ORDER BY sortOrder ASC, idx DESC; /* 정렬 우선순위 적용 */
            ";
            $query = $this->db->query($sql, array($cateIdx));
        }

        // 쿼리 실행
        // $query = $this->db->query($sql);
        $list = $query->result_array(); // 결과 배열로 받기

        // 각 항목에 대해 추가적인 처리
        for($i=0; $i<count($list); $i++){
            $row = $list[$i];

            // 썸네일 이미지 정보 JSON 디코딩
            $row['thumbnail'] = json_decode($row['thumbnail'], true);
            $row['thumbnailBack'] = json_decode($row['thumbnailBack'], true);

            // 썸네일이 없을 경우 기본 이미지 경로 지정
            if(!count($row['thumbnail'])){
                $row['imgPath'] = '/assets/image/no_image.jpg';
                $row['imgOnebonPath'] = '/assets/image/no_image.jpg';
            }else{
                // 썸네일이 있으면 해당 이미지 경로 지정
                $row['imgPath'] = '/assets/upload/'.$row['thumbnail'][0]['fileName'];
                $row['imgOnebonPath'] = '/assets/upload/'.$row['thumbnail'][0]['onebonFileName'];
            }

            // 썸네일 뒤쪽 이미지 처리
            if(!count($row['thumbnailBack'])){
                $row['imgPathBack'] = '';
                $row['imgOnebonPathBack'] = '';
            }else{
                $row['imgPathBack'] = '/assets/upload/'.$row['thumbnailBack'][0]['fileName'];
                $row['imgOnebonPathBack'] = '/assets/upload/'.$row['thumbnailBack'][0]['onebonFileName'];
            }

            // 등록 날짜 형식 변경
            $row['regDate'] = substr($row['regDate'], 2, 14);

            $list[$i] = $row; // 처리된 데이터 리스트에 다시 저장
        }

        // 결과에 리스트 추가
        $result['list'] = $list;

        // 결과를 JSON 형식으로 반환
        die(json_encode($result));
    }

    public function getLetterState() {
        $result = ['result' => false, 'msg' => ''];
    
        // 요청으로부터 writeIdx 받아오기
        $writeIdx = $this->input->post('writeIdx');
    
        if (empty($writeIdx)) {
            $result['msg'] = "잘못된 요청입니다.";
            die(json_encode($result));
        }
    
        // 해당 편지의 state 값 조회
        $query = $this->db->query("SELECT state FROM write_list WHERE idx = ?", [$writeIdx]);
        $letter = $query->row_array();
    
        if (!$letter) {
            $result['msg'] = "해당 편지를 찾을 수 없습니다.";
            die(json_encode($result));
        }
    
        $result['result'] = true;
        $result['state'] = $letter['state'];
    
        die(json_encode($result));
    }
  
    
    // 편지 검색 함수
    public function searchLetter()
    {
        // 기본 결과 배열 설정
        $result = array('result' => false, 'msg' => "");

        // 사용자로부터 받은 입력 값 (이름, 전화번호, 비밀번호)
        $mbName = $_POST['mbName'];
        $mbPhoneNumber = $_POST['mbPhoneNumber'];
        $mbPassword = $_POST['mbPassword'];

        // 조건에 맞는 작성된 글 수 조회
        $sql = "
             SELECT
                 COUNT(*) AS cnt
             FROM
                 write_list
             WHERE
                mbName = ? AND
                mbPhoneNumber = ? AND
                mbPassword = ? AND
                isUse = 'Y'
        ";

        // SQL 실행
        $query = $this->db->query($sql, array($mbName, $mbPhoneNumber, $mbPassword));
        $totalCnt = (int)$query->row_array()['cnt'];  // 결과에서 개수를 가져옴

        // 결과 처리: 조건에 맞는 데이터가 있는지 여부 설정
        $result['result'] = empty($totalCnt) ? false : true;  // 데이터가 없다면 false

        // 데이터가 없으면 메시지 설정
        if(empty($result['result'])){
            $result['msg'] = '해당 정보에 일치하는 주문내역이 존재하지 않습니다.';
        }

        // 결과를 JSON 형식으로 반환
        die(json_encode($result));
    }
    
    // 사용자가 즐겨찾는 글꼴을 저장하거나 업데이트하는 함수
    public function favoriteFont() {
        // 기본 결과 배열 초기화
        $result = array('result' => false, 'msg' => "");

        // 사용자 정보 및 전달받은 파라미터
        $memberIdx = $this->user['idx'] ?? 0;
        $favoriteFontIdx = $_POST['favoriteFontIdx'] ?? 0;
        $fontName = $_POST['fontName'] ?? '';
        $fontFamily = $_POST['fontFamily'] ?? '';

        // 필수 값이 없을 경우 예외 처리
        if (empty($memberIdx) || empty($fontName) || empty($fontFamily)) {
            $result['msg'] = '필수 입력값이 누락되었습니다.';
            die(json_encode($result));
        }

        try {
            if (empty($favoriteFontIdx)) {
                // 새로운 즐겨찾기 글꼴 추가
                $sql = "
                    INSERT INTO favorite_font (memberIdx, fontName, fontFamily)
                    VALUES (?, ?, ?)
                ";
                $this->db->query($sql, array($memberIdx, $fontName, $fontFamily));
            } else {
                // 기존 즐겨찾기 글꼴 비활성화 (isUse = 'N')
                $sql = "
                    UPDATE favorite_font
                    SET isUse = 'N'
                    WHERE idx = ? AND memberIdx = ?
                ";
                $this->db->query($sql, array($favoriteFontIdx, $memberIdx));
            }

            // 즐겨찾기 글꼴 목록 조회 (사용자가 사용하는 활성화된 글꼴)
            $sql = "
                SELECT * FROM favorite_font
                WHERE memberIdx = ? AND isUse = 'Y'
                ORDER BY idx DESC
            ";
            $query = $this->db->query($sql, array($memberIdx));
            $result['list'] = $query->result_array();

            // 성공적으로 처리되었음을 반환
            $result['result'] = true;
        } catch (Exception $e) {
            $result['msg'] = '오류 발생: ' . $e->getMessage();
        }

        // JSON 형식으로 결과 반환
        die(json_encode($result));
    }
    
    // 문자열에서 모든 작은따옴표 (')를 제거하는 함수
    // private function removeSingleQuotes($string) {
    //     // preg_replace를 사용하여 작은따옴표를 빈 문자열로 대체
    //     return preg_replace("/'/", "", $string);
    // }
    private function removeSingleQuotes($string) {
        return str_replace("'", "\\'", $string);  // 백슬래시로 이스케이프
    }
    

    
    public function setWrite()
	{
        ini_set('memory_limit', '-1');
        $result = array('result' => false, 'msg' => "");
                
        /* saveIdx가 0보다 클경우 수정하기 단계, tmpSaveIdx가 0보다 클경우 임시저장 단계 */
        $saveIdx = (int)$_POST['saveIdx'];
        $tmpSaveIdx = (int)$_POST['tmpSaveIdx'];
        $isTmpSave = $_POST['isTmpSave'];
        $isLogin = $_POST['isLogin'];
        $memberIdx = $_POST['memberIdx'];        
        
        $payType = empty($_POST['payType'])? 'point' : $_POST['payType'];
        $letterIdx = $_POST['letterIdx'];
        $productName = $_POST['productName'];
        $fontName = $_POST['fontName'];
        $fontFamily = $_POST['fontFamily'];
        $fontKrName = $_POST['fontKrName'];
        
        $fontSize = $_POST['fontSize'];
        $letterSpacing = empty($_POST['letterSpacing'])? '-0.5px' : $_POST['letterSpacing'];
        $fontAlign = $_POST['fontAlign'];
        $fontWeight = empty($_POST['fontWeight'])? '500' : $_POST['fontWeight'];
        $color = empty($_POST['color'])? '#000000' : $_POST['color'];
        $totalLetterCnt = $_POST['totalLetterCnt'];
        $content = empty($_POST['content'])? '' : $_POST['content'];
        
        $totalPhotoCnt = $_POST['totalPhotoCnt'];
        $photos = empty($_POST['photos'])? [] : $_POST['photos'];
        $photos = json_encode($photos, JSON_UNESCAPED_UNICODE);
        $photos = $this->removeSingleQuotes($photos);
        
        $totalPdfFileCnt = empty($_POST['totalPdfFileCnt'])? 0 : $_POST['totalPdfFileCnt'];
        $pdfFiles = empty($_POST['pdfFiles'])? [] : $_POST['pdfFiles'];        
        $pdfFiles = json_encode($pdfFiles, JSON_UNESCAPED_UNICODE);
        $pdfFiles = $this->removeSingleQuotes($pdfFiles);
        
        $fileColor = empty($_POST['fileColor'])? '' : $_POST['fileColor'];
        
        $senderAddr = $_POST['senderAddr'];
        // $senderAddrDetail = $_POST['senderAddrDetail'];
        $senderAddrDetail = $this->db->escape_str($_POST['senderAddrDetail']);
        $senderName = $_POST['senderName'];
        $senderTel = $_POST['senderTel'];
		$displaySenderPhone = $_POST['displaySenderPhone'];
        $isSaveSender = $_POST['isSaveReceiver'];

        $receiverAddr = $_POST['receiverAddr'];
        $receiverAddrDetail = $_POST['receiverAddrDetail'];
        $receiverName = $_POST['receiverName'];
        $receiverTel = $_POST['receiverTel'];
        $isSaveReceiver = $_POST['isSaveReceiver'];
        
        $stamp = $_POST['stamp'];
        $deliveryDate = $_POST['deliveryDate'];
        $mbName = $_POST['mbName'];
        $mbPhoneNumber = $_POST['mbPhoneNumber'];
        $mbPassword = $_POST['mbPassword'];
            
        $letterPrice = $_POST['letterPrice'];
        $photoPrice = $_POST['photoPrice'];      
        $filePrice = empty($_POST['filePrice'])? 0 : $_POST['filePrice'];
        
        $stampPrice = $_POST['stampPrice'];
        $totalPrice = $_POST['totalPrice'];
        
        $isAllPoint = $_POST['isAllPoint'];
        $payPoint = $_POST['payPoint'];
        $realTotalPrice = $_POST['realTotalPrice'];
        
        $isSaveAddress = $_POST['isSaveAddress'];
        $isDefaultAddress = $_POST['isDefaultAddress'];
        
        $isCashReceipt = empty($_POST['isCashReceipt'])? 'N' : $_POST['isCashReceipt'];
        $cashReceiptType = empty($_POST['cashReceiptType'])? '' : $_POST['cashReceiptType'];
        $depositType = empty($_POST['depositType'])? '' : $_POST['depositType'];
        $cashReceiptNumber = empty($_POST['cashReceiptNumber'])? '' : $_POST['cashReceiptNumber'];
        $cashReceiptEmail = empty($_POST['cashReceiptEmail'])? '' : $_POST['cashReceiptEmail'];        

        $libraryFiles = empty($_POST['libraryFiles']) ? [] : $_POST['libraryFiles'];
//        $libraryFiles = json_encode($libraryFiles, JSON_UNESCAPED_UNICODE);

        $libraryFileColor = empty($_POST['libraryFileColor']) ? '' : $_POST['libraryFileColor'];
        $totalLibraryFileCnt = empty($_POST['totalLibraryFileCnt']) ? 0 : $_POST['totalLibraryFileCnt'];
        $libraryPrice = empty($_POST['libraryPrice']) ? 0 : $_POST['libraryPrice'];

        
        if(empty($isSaveAddress) || empty($isDefaultAddress)) {
            $result['msg'] = "잘못된 접근 방식입니다.";
            die(json_encode($result));
        }
        
        /* 충전 포인트 (무통장 3%, 카드 1%) */
        $addPoint = 0;
        if($payType == 'point') {
            $addPoint = 0; 
        }else if($realTotalPrice > 0){
            $percentage = $payType == 'deposit'? 3 : 1;            
            $addPoint = round($realTotalPrice * ($percentage / 100));
        }        
        
        $regexContent = '';
        
        /* 편지 내용 저장 */
        if(!empty($content)) {
            for($i = 0; $i < count($content); $i++){
                
                /* 편지 내용 $#을 기준으로 나누기 (1페이지, 2페이지 ...) */
                $content[$i] = preg_replace("/'/", "\"", $content[$i]); // 작은 따옴표를 큰따옴표로 변경
                $regex = '$#';

                $regexContent .= (($i == 0) ? '' : $regex) . $content[$i];

                /* 편지 내용이 비어있는데 넘어오는 경우 유효성 체크 */
                if(empty($content[$i])) {
                    $result['result'] = false;
                    $result['msg'] = "편지지 내용이 전송되지 않았습니다. 재시도 해주세요(편지내용 캡쳐 후 고객센터 문의부탁드립니다)";
                    die(json_encode($result));
                }
            }
        }
        
        $isPay = 'N';
        $state = 'W';
        $payId = 0;
        $writeInfo = array();

        // 총 출력비용 계산 (서버 검증용)
        $serverLibraryPrintPrice = 0;
        
        foreach ($libraryFiles as $lib) {
            $isBroadcast = isset($lib['isBroadcast']) && $lib['isBroadcast'] == true;
            $pageCount = isset($lib['pageCount']) ? (int)$lib['pageCount'] : 0;
            $color = isset($lib['color']) ? $lib['color'] : 'color';

            if (!$isBroadcast) {
                $fee = $color === 'black' ? 150 : 300;
                $serverLibraryPrintPrice += $pageCount * $fee;
            }
        }
		$libraryFiles = json_encode($libraryFiles, JSON_UNESCAPED_UNICODE);

        // 서버에서 최종 검증된 총 libraryPrice = 전달된 가격 + 출력비
        $finalLibraryPrice = $libraryPrice + $serverLibraryPrintPrice;
        
        /* INSERT 이전인 경우 작성 정보 가져오기 */
        if($saveIdx > 0 || $tmpSaveIdx > 0 || $isTmpSave == 'Y') {
            $idx = empty($saveIdx)? $tmpSaveIdx : $saveIdx;
            
            $sql = "SELECT * FROM write_list WHERE idx = ? ";
            $query = $this->db->query($sql, array($idx));
            $writeInfo = $query->row_array();
                        
            /* PC에서 브라우저를 켜놓고 사용할 경우 상태체크 후 리디렉션 */
            if($tmpSaveIdx > 0 && $writeInfo['state'] != 'T') {
                $result['msg'] = '이미 ('.$this->config->item('state')[$writeInfo['state']].')처리된 편지건입니다.(새로고침 후 이용부탁드립니다)';                
                die(json_encode($result));
            }
        }
        
        /*  1. 수정하기 단계가 아니고, 최종 나이스페이 결제하기(카드, 페이)를 클릭했을시 강제로 임시저장 - 상태(T)
            2. 이용자가 실제로 임시저장을 눌렸을 경우 - 상태(T) */
        if( (empty($saveIdx) && $payType != 'deposit' && $payType != 'point') || $isTmpSave == 'Y') {
            $state = 'T';
        }else if($payType == 'deposit') { // 무통장의 경우 B
            $state = 'B';
        }
        
        /***** payId 지정 현재는 안 씀 *****/
        $payId = 0;
        if($saveIdx == 0 && $tmpSaveIdx == 0) {
            $sql = "SELECT
                        COUNT(*) + 1 AS cnt
                    FROM
                        write_list
                    WHERE
                        isPay = 'Y' OR payType = 'deposit'";
            
            $query = $this->db->query($sql);
            $payId = (int)$query->row_array()['cnt'] + 1;
        }else {
            $payId = $writeInfo['payId'];
        }        
        /***** payId 지정 현재는 안 씀 *****/
        
        
        /* 작성한 정보의 브라우저 저장 */
        $browser = 'PC';
        
        if(isIos()) {
            $browser = 'IOS';
        }else if(isAndroid()) {
            $browser = 'ANDROID';
        }
        
        /* 최종 결제부분 포멧을 위해 임시 변경 */        
        if( ($payType != 'deposit' && $saveIdx > 0) || $payType == 'point') {
            $isPay = 'Y';
        }
        
        /* 최종 저장단계 추가, 수정 공통 변수 */
        $setSql = "
            browser = '{$browser}',
            payType = '{$payType}',
            payId = '{$payId}',
            isPay = '{$isPay}',
            state = '{$state}',
            isLogin = '{$isLogin}',
            memberIdx = '{$memberIdx}',
            mbName = '{$mbName}',
            mbPhoneNumber = '{$mbPhoneNumber}',   
            mbPassword = '{$mbPassword}',
            letterIdx = '{$letterIdx}',
            productName = '{$productName}',
            fontKrName = '{$fontKrName}',
            fontFamily = '{$fontFamily}',
            fontName = '{$fontName}',            
            fontSize = '{$fontSize}',
            letterSpacing = '{$letterSpacing}',
            fontAlign = '{$fontAlign}',
            fontWeight = '{$fontWeight}',
            color = '{$color}',
            totalLetterCnt = '{$totalLetterCnt}',
            content = '{$regexContent}',
            totalPhotoCnt = '{$totalPhotoCnt}',
            photos = '{$photos}',
            totalPdfFileCnt = '{$totalPdfFileCnt}',
            pdfFiles = '{$pdfFiles}',
            fileColor = '{$fileColor}',
            senderAddr = '{$senderAddr}',
            senderAddrDetail = '{$senderAddrDetail}',
            senderName = '{$senderName}',
            senderTel = '{$senderTel}',
            displaySenderPhone = '{$displaySenderPhone}',
            receiverAddr = '{$receiverAddr}',
            receiverAddrDetail = '{$receiverAddrDetail}',
            receiverName = '{$receiverName}',
            receiverTel = '{$receiverTel}',
            stamp = '{$stamp}',
            deliveryDate = '{$deliveryDate}',
            letterPrice = '{$letterPrice}',
            photoPrice = '{$photoPrice}',
            filePrice = '{$filePrice}',
            stampPrice = '{$stampPrice}',
            totalPrice = '{$totalPrice}',
            payPoint = '{$payPoint}',
            realTotalPrice = '{$realTotalPrice}',
            addPoint = '{$addPoint}',
            isSaveAddress = '{$isSaveAddress}',
            isDefaultAddress = '{$isDefaultAddress}',
            isCashReceipt = '{$isCashReceipt}',
            cashReceiptType = '{$cashReceiptType}',
            depositType = '{$depositType}',
            cashReceiptNumber = '{$cashReceiptNumber}',
            cashReceiptEmail = '{$cashReceiptEmail}',
            libraryFiles = '{$libraryFiles}',
            libraryFileColor = '{$libraryFileColor}',
            totalLibraryFileCnt = '{$totalLibraryFileCnt}',
            libraryPrice = '{$libraryPrice}',
            envelopeWeight = (
              CASE
                WHEN productName = '기본' THEN (totalLetterCnt * 3.8)
                ELSE (totalLetterCnt * 3.8)  -- 심플, 테마 3.8g (기본적인 적용)
              END
            ) 
            + (totalPhotoCnt * 3.9)  -- 유광 및 무광 사진 3.9g
            + (totalPdfFileCnt * 6.2) -- 문서 6.2g
            + (totalLibraryFileCnt * 6.2)
            + (
              CASE
                WHEN totalPdfFileCnt > 0 OR totalLibraryFileCnt > 0 OR (totalLetterCnt + totalPhotoCnt) >= 30 THEN 21.6
                ELSE 6.2
              END
            ),
            regDate = NOW()
        ";
        
        /* 제일 처음 작성한 경우(저장되지 않은 경우) INSERT 처리 */
        if($saveIdx == 0 && $tmpSaveIdx == 0) {
            $sql = "
                INSERT INTO
                    write_list
                SET
                    $setSql
            ";
            
            $result['result'] = $this->db->query($sql);
        }else {                                    
            if($saveIdx > 0) { /* 수정시 */
                
                /* 수정단계시 무통장, 포인트 이전결제금액과 수정한 결제금액이 같은 경우 작성내용 바로저장 */
                if($payType == 'deposit' || $payType == 'point' || ($writeInfo['payPoint'] == $payPoint && $writeInfo['realTotalPrice'] == $realTotalPrice)) {
                    $sql = "
                        UPDATE
                            write_list
                        SET
                            isSave = 'Y',
                            isSaveCnt = isSaveCnt + 1,
                            $setSql
                        WHERE
                            idx = '{$saveIdx}'
                    ";

                    $result['result'] = $this->db->query($sql);
                }
                /* 최종 수정시 결제타입 및 결제금액이 다른경우 바로 작성 내용을 바로저장하지 않고, write_tmp에 저장 후 PayReturnUrl 페이지에서 최종 저장 */
                else {
                    $sql = "
                        UPDATE
                            write_list
                        SET
                            isSave = 'Y',
                            isSaveCnt = isSaveCnt + 1                        
                        WHERE
                            idx = ?
                    ";
                    $this->db->query($sql, array($saveIdx));

                    /* 편지 수정시 사용 */
                    $sql = "
                        INSERT INTO
                            write_tmp
                        SET
                            writeIdx = ?,
                            queryString = ?
                    ";

                    $result['result'] = $this->db->query($sql, array($saveIdx, $setSql));                
                } 
            }else { /* 임시저장시 */
                $sql = "
                    UPDATE
                        write_list
                    SET
                        tmpSaveRegDate = NOW(),
                        $setSql
                    WHERE
                        idx = '{$tmpSaveIdx}'
                ";

                $result['result'] = $this->db->query($sql);
            }                        
        }
        
        /* 혹여나 저장실패시 로그 기록 */
        if(empty($result['result'])){
            $result['msg'] = "편지 로그저장에 실패하였습니다.(1대1 카카오톡 문의로 접수부탁드립니다)";             
            $error = $this->db->error();
            $result['error'] = $error['message'];
            
            // 에러 로그 기록
            log_message('error', 'setWrite error: ' . $error['message']);
            
            die(json_encode($result));
        }
                
        if($saveIdx == 0 && $tmpSaveIdx == 0) { /* 처음 INSERT 한경우 insert ID 저장 */
            $result['writeIdx'] = $this->db->insert_id();
        }else if($saveIdx > 0) { /* 수정시 saveIdx 저장 */
            $result['writeIdx'] = $saveIdx;
        }else{ /* 임시저장시 tmpSaveIdx 저장 */
            $result['writeIdx'] = $tmpSaveIdx;
        }
                
        
        /* 비회원이 아닌경우 (로그인 상태인 경우) */
        if($isLogin == 'Y'){
            
            /* 포인트 전액사용 및 최근 사용한 폰트 정보 바로 저장 */
            $sql = "
                UPDATE
                    member_list
                SET
                    isAllPoint = ?,
                    fontKrName = ?,
                    fontFamily = ?,
                    fontName = ?
                WHERE
                    idx = ?
            ";
            
            $this->db->query($sql, array($isAllPoint, $fontKrName, $fontFamily, $fontName, $memberIdx));
            
            /* 첫결제 - 포인트/무통장 결제시 포인트 즉시 차감 */
            if($isTmpSave == 'N' && ($payType == 'point' || $payType == 'deposit')) {
                $writeIdx = $result['writeIdx'];
                $comment = "";
                
                /* 수정시 이전 포인트 지급 */
                if($saveIdx > 0) {
                    $this->db->trans_start();
                    
                    /* 카드결제 금액 있을시 취소 */
                    if((int)$writeInfo['payIdx'] > 0) {
                        $payIdx = $writeInfo['payIdx'];
                        
                        $sql = "    
                            SELECT
                                P.tid, P.amount, P.orderId
                            FROM                                
                                pay_list AS P
                            WHERE
                                P.idx = ?;";

                        $query = $this->db->query($sql, array($payIdx));
                        $info = $query->row_array();

                        $resObject2 = "";
                        $reason = "본인취소";
                        try {
                            $res = $this->requestPost(
                                "https://api.nicepay.co.kr/v1/payments/". $info['tid'] ."/cancel",
                                json_encode(array("amount" => $info['amount'], "reason" => $reason, "orderId" => $info['orderId'])),
                                $this->config->item('clientId') . ':' . $this->config->item('secretKey')
                            );

                            $resObject2 = json_decode($res);
                        } catch (Exception $e) {
                            $e->getMessage();
                        }
                        $resObject2 = json_decode(json_encode($resObject2), true);

                        if($resObject2['resultCode'] != '0000') {
                            $this->db->trans_rollback();
                            $result['result'] = false;
                            $result['msg'] = "[이전결제 취소실패]{$resObject2['resultMsg']} 관리자에게 문의해주세요.";
                            die(json_encode($result));
                        }
                        
                        /* 이전 결제내역 취소 처리 */
                        $sql = "UPDATE pay_list SET isUse = 'N' WHERE idx = ? ";
                        $this->db->query($sql, array($writeInfo['payIdx']));

                        /* 이전 결제내역 취소 처리 */
                        $sql = "UPDATE write_list SET isSave = 'N', payIdx = 0 WHERE idx = ? ";
                        $this->db->query($sql, array($writeIdx));
                    }
                    
                    /* 포인트 재지급 */
                    if((int)$writeInfo['payPoint'] > 0) {
                        $plusPoint = (int)$writeInfo['payPoint'];
                        $sql = "UPDATE member_list SET point = point + {$plusPoint} WHERE idx = '{$memberIdx}' ";
                        $this->db->query($sql);

                        $sql = "INSERT INTO point_log SET memberIdx = '{$memberIdx}', writeIdx = '{$writeIdx}', point = '{$plusPoint}', ment = '{$productName} 편지지수정'";
                        $this->db->query($sql);

                        $comment = "재결제";
                    }
                }
                
                /* 포인트를 사용하여 수정하였을 경우 차감 */
                if($payPoint > 0) {
                    $minusPoint = (int)$payPoint * -1;

                    $sql = "UPDATE member_list SET point = point + {$minusPoint} WHERE idx = '{$memberIdx}' ";
                    $this->db->query($sql);
                    
                    $ment = empty($comment)? '결제' : $comment;                    
                    $sql = "INSERT INTO point_log SET memberIdx = '{$memberIdx}', writeIdx = '{$writeIdx}', point = '{$minusPoint}', ment = '{$productName} {$ment}'";
                    $this->db->query($sql);   
                }

                $this->db->trans_complete(); // 트랜잭션 끝

                if ($this->db->trans_status() === FALSE) {
                    $result['result'] = false;
                    $result['msg'] = "결제 처리 중 오류가 발생했습니다. 관리자에게 문의해주세요.";
                    die(json_encode($result));
                }
            }
        }
        
        /* 결제부분 포멧, 임시 변경한걸 결제 시키기 위해 isPay = 'N' 설정 */
        if($payType != 'deposit' && $payType != 'point' && $saveIdx > 0) {
            $isPay = 'N';
        }
                
        if(empty($result['writeIdx'])) {
            $result['result'] = false;
            $result['msg'] = "주문생성에 실패하였습니다.";
            die(json_encode($result));
        }
        
        /* 무통장 및 포인트일 경우 바로 주소지 및 현금영수증 정보 저장 */
        if(($payType == 'deposit' || $payType == 'point') && $isTmpSave == 'N') {
            autoSaveAddress($this->db, $result['writeIdx']);
            
            if($isLogin == 'Y' && $payType == 'deposit' && $isCashReceipt == 'Y') {
                $sql = "
                    UPDATE
                        member_list
                    SET
                        cashReceiptType = ?,
                        depositType = ?,
                        cashReceiptNumber = ?,
                        cashReceiptEmail = ?
                    WHERE
                        idx = ?
                ";

                $this->db->query($sql, array($cashReceiptType, $depositType, $cashReceiptNumber, $cashReceiptEmail, $memberIdx));
            }
        }
        
        /* 주문번호 자동 생성 */
        setOrderId($this->db, $result['writeIdx']);
        
        $result['result'] = true;
        $result['isPay'] = $isPay;
        $result['mbName'] = $mbName;
        $result['mbPhoneNumber'] = $mbPhoneNumber;
        $result['mbPassword'] = $mbPassword;
        $result['realTotalPrice'] = $realTotalPrice;

		die(json_encode($result));
	}
    
    // 주문 취소 함수
    public function cancelOrder() {
        $this->db->trans_start(); // 트랙잭션 시작

        $result = array('result' => false, 'msg' => "");

        $writeIdx = $_POST['writeIdx'] ?? null;

        if (!$writeIdx) {
            $result['msg'] = '잘못된 요청입니다.';
            die(json_encode($result));
        }

        error_log("cancelOrder 시작 - writeIdx: " . $writeIdx);

        // 주문 정보 조회
        $sql = "
            SELECT
                W.state, W.payType, W.payPoint, W.memberIdx, W.payIdx, W.productName,
                P.tid, P.amount, P.orderId, P.idx
            FROM
                write_list AS W
            LEFT JOIN
                pay_list AS P ON P.writeIdx = W.idx AND P.isUse = 'Y'
            WHERE
                W.idx = ?
            ORDER BY P.idx DESC;
        ";

        $query = $this->db->query($sql, array($writeIdx));
        $info = $query->row_array();

        if (!$info) {
            $this->db->trans_rollback(); // 트랙잭션 롤백
            $result['msg'] = '해당 주문을 찾을 수 없습니다.';
            die(json_encode($result));
        }

        // 취소 가능한 상태인지 확인
        if (!in_array($info['state'], ['B', 'W'])) {
            $this->db->trans_rollback(); // 트랙잭션 롤백
            $result['msg'] = '이미 인쇄 처리중인 편지건입니다.';
            die(json_encode($result));
        }

        // 결제 취소 처리 (카드 결제만 해당)
        if (intval($info['payIdx']) > 0) {
            $reason = "본인취소";

            try {
                $res = $this->requestPost(
                    "https://api.nicepay.co.kr/v1/payments/" . $info['tid'] . "/cancel",
                    json_encode(array("amount" => $info['amount'], "reason" => $reason, "orderId" => $info['orderId'])),
                    $this->config->item('clientId') . ':' . $this->config->item('secretKey')
                );

                $resObject = json_decode($res, true);

                if ($resObject['resultCode'] != '0000') {
                    $result['msg'] = '결제 취소 실패: ' . $resObject['resultMsg'];
                    $this->db->trans_rollback(); // 트랙잭션 롤백
                    $result['msg'] = '결제 취소 중 오류가 발생했습니다. 다시 시도해 주세요.';
                    die(json_encode($result));
                }
            } catch (Exception $e) {
                $this->db->trans_rollback();
                $result['msg'] = '결제 취소 중 오류 발생: ' . $e->getMessage();
                die(json_encode($result));
            }
        }

        // 포인트 취소 처리
        if (intval($info['payPoint']) > 0) {
            try {
                $sql = "UPDATE member_list SET point = point + ? WHERE idx = ?";
                $this->db->query($sql, array(intval($info['payPoint']), $info['memberIdx']));

                $sql = "INSERT INTO point_log SET memberIdx = ?, writeIdx = ?, point = ?, ment = ?";
                $this->db->query($sql, array(
                    $info['memberIdx'], $writeIdx, intval($info['payPoint']),
                    $info['productName'] . ' 결제취소'
                ));
            } catch (Exception $e) {
                $this->db->trans_rollback();
                error_log("포인트 취소 중 예외 발생: " . $e->getMessage());
                $result['msg'] = '포인트 취소 중 오류가 발생했습니다. 다시 시도해 주세요.';
                die(json_encode($result));
            }
        }

        // 주문 상태 업데이트 (취소한 주문은 임시저장으로)
        try {
            $sql = "UPDATE write_list SET payIdx = 0, isPay = 'N', state = 'T' WHERE idx = ?";
            $updateResult = $this->db->query($sql, array($writeIdx));

            if (!$updateResult) {
                error_log("주문 상태 업데이트 실패 - writeIdx: " . $writeIdx);
                $this->db->trans_rollback();
                $result['msg'] = '주문 취소 처리 중 오류가 발생했습니다. 다시 시도해 주세요.';
                die(json_encode($result));
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            error_log("주문 상태 업데이트 중 예외 발생: " . $e->getMessage());
            $result['msg'] = '주문 취소 처리 중 오류가 발생했습니다. 다시 시도해 주세요.';
            die(json_encode($result));
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            error_log("트랜잭션 실패 - writeIdx: " . $writeIdx);
            $result['msg'] = '주문 취소 중 오류가 발생하였습니다. 다시 시도 부탁드립니다.';
            die(json_encode($result));
        }

        error_log("주문 취소 성공 - writeIdx: " . $writeIdx);

        $result['result'] = true;
        $result['msg'] = '주문이 성공적으로 취소되었습니다.';
        die(json_encode($result));
    }
    
    // 특정 편지를 삭제하는 함수
    public function removeLetter(){
        // 기본 결과 배열 초기화
        $result = array('result' => false, 'msg' => "");

        // 삭제할 편지의 인덱스를 POST 데이터에서 가져옴
        $letterIdx = $_POST['letterIdx'];

        // 해당 편지를 '사용하지 않음' 상태로 업데이트하는 SQL 쿼리
        $sql = "
            UPDATE
                write_list
            SET                 
                isUse = 'N'
            WHERE
                idx = '{$letterIdx}'
        ";

        // 쿼리 실행 결과를 확인하여 성공 여부 반환
        $result['result'] = $this->db->query($sql);

        // 쿼리 실행이 실패한 경우 에러 메시지 반환
        if(empty($result['result'])){
            $result['msg'] = 'removeLetter error';
            die(json_encode($result));
        }

        // 결과를 JSON 형식으로 반환
        die(json_encode($result));
    }

    // 사진 업로드 함수(기존)
    public function photoUpload()
    {        
        // 메모리 제한을 해제하여 큰 파일도 처리할 수 있도록 설정
        ini_set('memory_limit', '-1');

        // 기본 결과 배열 초기화
        $result = array('result' => false, 'msg' => "");                

        // 이미지의 기본 너비 설정 (높이는 비율에 맞게 자동으로 조정됨)
        $width = 400;
        $height = 0;

        // 업로드된 파일이 없는 경우 에러 메시지 반환
        if(empty($_FILES['files'])){
            $result['msg'] = '파일을 찾지 못했습니다.';
            die(json_encode($reuslt));
        }

        // 이미지 리사이즈 관련 라이브러리 로드
        $this->load->view('/libs/php-image-resize-master/ImageResize');

        // 업로드 파일이 저장될 폴더 경로
        $folderDir = "assets/upload/photos";

        // 파일 업로드 함수 호출 (cmmFileUpload)
        $files = cmmFileUpload($_FILES['files'], $folderDir, $width, $height);

        // 파일 업로드에 실패한 경우 에러 메시지 반환
        if(!count($files)){
            $result['msg'] = '파일 업로드 도중 문제가 발생하였습니다.';
            die(json_encode($reuslt));
        }

        // 업로드된 파일 정보 반환
        $result['files'] = $files;        

        // 결과를 성공으로 설정
        $result['result'] = true;

        // 결과를 JSON 형식으로 반환
        die(json_encode($result));
    }
    
    // 사진 업로드 함수 (현재 사용함수)
    public function photoUpload2()
    {        
        // 메모리 제한을 해제하여 큰 파일도 처리할 수 있도록 설정
        ini_set('memory_limit', '-1');

        // 기본 결과 배열 초기화
        $result = array('result' => false, 'msg' => "");                

        // 이미지의 기본 너비와 높이 설정
        $width = 1168;
        $height = 800;

        // 업로드된 파일이 없는 경우 에러 메시지 반환
        if(empty($_FILES['files'])){
            $result['msg'] = '파일을 찾지 못했습니다.';
            die(json_encode($reuslt));
        }

        // 이미지 리사이즈 관련 라이브러리 로드
        $this->load->view('/libs/php-image-resize-master/ImageResize');

        // 업로드 파일이 저장될 폴더 경로
        $folderDir = "assets/upload/photos";

        // 파일 업로드 함수 호출 (cmmPhotoUpload)
        $files = cmmPhotoUpload($_FILES['files'], $folderDir, $width, $height);

        // 파일 업로드에 실패한 경우 에러 메시지 반환
        if(!count($files)){
            $result['msg'] = '파일 업로드 도중 문제가 발생하였습니다.';
            die(json_encode($reuslt));
        }

        // 업로드된 파일 정보 반환
        $result['files'] = $files;        

        // 결과를 성공으로 설정
        $result['result'] = true;

        // 결과를 JSON 형식으로 반환
        die(json_encode($result));
    }
    
    // 게시판 목록 조회 함수
    public function getBoardList(){
        // 결과 배열 초기화
        $result = array('result' => true, 'msg' => "");

        // 전달된 POST 파라미터 받아오기
        $type = $_POST['type'];
        $searchText = $_POST['searchText']?? '';

        // 기본 검색 조건 (type에 따라 필터링)
        $searchSql = " type = '{$type}' AND ";                

        // 검색 텍스트가 있을 경우, 제목에 대해 LIKE 검색 추가
        if(!empty($searchText)){
            $searchSql .= " title LIKE '%{$searchText}%' AND ";
        }

        // 'review' 타입이 아닐 경우, 다른 조건 추가
        if($type != 'review'){
            if($type == 'qna') {
                // 사용자가 로그인한 경우, memberIdx 추가하여 검색
                $memberIdx = empty($this->user)? 0 : $this->user['idx'];
                $searchSql .= " memberIdx = '{$memberIdx}' AND ";                
            }

            // 게시판 목록에서 기본적인 글 정보 조회 (idx, 제목, 내용 일부, 등록일)
            $sql = "
                 SELECT
                     idx,
                     title,
                     LEFT(content, 60) AS content,
                     regDate
                 FROM
                     board_list
                 WHERE                
                    $searchSql
                    isUse = 'Y'
                 ORDER BY
                    idx DESC;
            ";   
        }else{
            // 'review' 타입일 경우, 리뷰 리스트 조회 (리뷰 내용, 별점, 등록일)
            $sql = "
                 SELECT
                    idx,
                    star,
                    reviewContent AS title,
                    reviewRegDate AS regDate
                 FROM
                     write_list
                 WHERE
                    isReview = 'Y' AND
                    isUse = 'Y'
                 ORDER BY
                    reviewRegDate DESC
                 LIMIT
                    400;
            ";
        }

        // SQL 쿼리 실행
        $query = $this->db->query($sql);
        $list = $query->result_array();

        // 결과 목록에 대해 추가적인 가공
        for($i=0; $i<count($list); $i++){
            $row = $list[$i];

            // 게시글 유형에 대한 추가 정보 (예: 공지, Q&A 등)
            $row['type'] = $this->config->item('noticeCate')[$type];

            // 등록일을 'YYMMDDhhmmss' 형식으로 변경
            $row['regDate'] = substr($row['regDate'], 2, 14);

            $list[$i] = $row;
        }

        // 결과 리스트를 응답에 포함
        $result['list'] = $list;

        // JSON 형식으로 응답 반환
        die(json_encode($result));
    }
    
    // 게시판 비밀번호 확인 함수
    public function checkBoardPwd(){
        // 결과 배열 초기화
        $result = array('result' => false, 'msg' => "");

        // 전달된 POST 파라미터 받아오기
        $boardIdx = $_POST['boardIdx'];
        $boardPwd = $_POST['boardPwd'];

        // SQL 쿼리 작성: 비밀번호와 게시글 번호로 게시글을 조회
        $sql = "
            SELECT
                *
            FROM
                board_list
            WHERE
                idx = ? AND
                boardPwd = ?
        ";

        // 안전한 쿼리 실행을 위해 파라미터 바인딩 사용
        $query = $this->db->query($sql, array($boardIdx, $boardPwd));
        $info = $query->row_array();

        // 비밀번호가 일치하는 게시글이 있으면 성공 응답
        if(!empty($info)){
            $result['result'] = true;
        }else{
            // 비밀번호가 일치하지 않으면 에러 메시지 설정
            $result['msg'] = '비밀번호가 일치하지 않습니다.';
        }

        // JSON 형식으로 결과 반환
        die(json_encode($result));
    }
    
    // 문의 게시판 등록
    public function createBoard() {
        // 초기화
        $result = array('result' => false, 'msg' => "");

        // 사용자 정보 및 전달된 POST 데이터 받기
        $memberIdx = $this->user['idx'] ?? 0;
        $boardIdx = $_POST['boardIdx'] ?? 0;        
        $type = $_POST['type'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        $boardPwd = $_POST['boardPwd'] ?? '';

        // 필수 입력값 체크 (예시로 제목과 내용은 필수)
        if (empty($title) || empty($content)) {
            $result['msg'] = '제목과 내용을 모두 입력해주세요.';
            die(json_encode($result));
        }

        // 게시판 등록 또는 수정
        if (empty($boardIdx)) {
            // 게시판 생성
            $sql = "
                INSERT INTO
                    board_list
                SET
                    type = ?,
                    memberIdx = ?,
                    title = ?,
                    content = ?,
                    boardPwd = ?
            ";

            // 쿼리 실행
            $result['result'] = $this->db->query($sql, array($type, $memberIdx, $title, $content, $boardPwd));

        } else {
            // 게시판 수정
            $sql = "
                UPDATE
                    board_list
                SET                 
                    title = ?,
                    content = ?
                WHERE
                    idx = ?
            ";

            // 쿼리 실행
            $result['result'] = $this->db->query($sql, array($title, $content, $boardIdx));
        }

        // 쿼리 실행 실패 시 처리
        if (empty($result['result'])) {
            $result['msg'] = '게시판 생성/수정 중 오류가 발생했습니다.';
            die(json_encode($result));
        }

        // 성공적으로 처리되었을 경우
        die(json_encode($result));
    }
    
    // 문의 게시판 답변등록
    public function answerCreateBoard() {
        $result = array('result' => false, 'msg' => "");

        // 필수 입력값 받기
        $boardIdx = $_POST['boardIdx'];
        $boardAnswerIdx = $_POST['boardAnswerIdx'] ?? 0;
        $type = 'answer';  // 답변 타입 설정
        $title = '[동글] 답변내용입니다.';
        $content = $_POST['content'];

        // 필수 입력값 체크
        if (empty($content)) {
            $result['msg'] = '내용을 입력해 주세요.';
            die(json_encode($result));
        }

        // 답변 게시글 생성 또는 업데이트
        if (empty($boardAnswerIdx)) {
            // 답변 새로 생성
            $sql = "
                INSERT INTO
                    board_list
                SET
                    type = ?,
                    title = ?,
                    content = ?
            ";

            // 쿼리 실행
            $result['result'] = $this->db->query($sql, array($type, $title, $content));

            // 답변 추가 후, 원본 게시글에 답변 ID 추가
            if ($result['result']) {
                $answerIdx = $this->db->insert_id();
                $updateSql = "
                    UPDATE
                        board_list
                    SET
                        answerIdx = ?
                    WHERE
                        idx = ?
                ";
                $result['result'] = $this->db->query($updateSql, array($answerIdx, $boardIdx));
            }

        } else {
            // 기존 답변 수정
            $sql = "
                UPDATE
                    board_list
                SET
                    title = ?,
                    content = ?
                WHERE
                    idx = ?
            ";

            // 쿼리 실행
            $result['result'] = $this->db->query($sql, array($title, $content, $boardAnswerIdx));
        }

        // 결과 처리
        if (empty($result['result'])) {
            $result['msg'] = '답변 생성/수정 중 오류가 발생하였습니다.';
        }

        // 최종 결과 반환
        die(json_encode($result));
    }
    
    // 게시글 삭제
    public function deleteBoard() {
        $result = array('result' => false, 'msg' => "");

        // 필수 입력값 받기
        $boardIdx = $_POST['boardIdx'];

        // boardIdx 체크
        if (empty($boardIdx)) {
            $result['msg'] = '게시글 ID가 없습니다.';
            die(json_encode($result));
        }

        // SQL 쿼리 준비 (매개변수 바인딩 사용)
        $sql = "
            UPDATE
                board_list
            SET
                isUse = 'N'
            WHERE
                idx = ?
        ";

        // 쿼리 실행
        $result['result'] = $this->db->query($sql, array($boardIdx));

        // 쿼리 실행 결과 확인
        if (empty($result['result'])) {
            $result['msg'] = '게시글 삭제 중 오류가 발생했습니다.';
            die(json_encode($result));
        }

        // 결과 반환
        $result['msg'] = '게시글이 성공적으로 삭제되었습니다.';
        die(json_encode($result));
    }
    
    // 리뷰작성
    public function setReview() {
        $result = array('result' => false, 'msg' => "");

        // 필수 입력값 받기
        $writeIdx = $_POST['writeIdx'] ?? null;
        $star = $_POST['star'] ?? null;
        $reviewContent = $_POST['reviewContent'] ?? null;

        // 입력값 검증
        if (empty($writeIdx) || empty($star) || empty($reviewContent)) {
            $result['msg'] = '필수 입력값이 누락되었습니다.';
            die(json_encode($result));
        }

        // SQL 쿼리 준비 (매개변수 바인딩 사용)
        $sql = "
            UPDATE
                write_list
            SET
                isReview = 'Y',
                star = ?,
                reviewContent = ?,
                reviewRegDate = NOW()
            WHERE
                idx = ?
        ";

        // 쿼리 실행
        $result['result'] = $this->db->query($sql, array($star, $reviewContent, $writeIdx));

        // 쿼리 실행 결과 확인
        if (empty($result['result'])) {
            $result['msg'] = '리뷰 업데이트 중 오류가 발생했습니다.';
            die(json_encode($result));
        }

        // 성공 메시지 반환
        $result['msg'] = '리뷰가 성공적으로 업데이트되었습니다.';
        die(json_encode($result));
    }
    
    // NDA 동의
    public function agreeNda() {
        $result = array('result' => false, 'msg' => "");

        // 필수 입력값 검증
        if (empty($_POST['ndaName'])) {
            $result['msg'] = 'NDA 서명자가 입력되지 않았습니다.';
            die(json_encode($result));
        }

        $ndaName = $_POST['ndaName'];
        $memberIdx = $this->user['idx'] ?? null;

        // 사용자 ID가 없으면 중단
        if (empty($memberIdx)) {
            $result['msg'] = '회원 정보가 없습니다.';
            die(json_encode($result));
        }

        // SQL 실행 (매개변수 바인딩)
        $sql = "
            UPDATE
                member_list
            SET
                isNda = 'Y',
                ndaName = ?,
                ndaDate = NOW()
            WHERE
                idx = ?
        ";

        $queryResult = $this->db->query($sql, array($ndaName, $memberIdx));

        // 쿼리 실행 결과 확인
        if (!$queryResult) {
            $result['msg'] = 'NDA 동의 업데이트 중 오류가 발생했습니다.';
            die(json_encode($result));
        }

        // 성공 시 응답
        $result['result'] = true;
        $result['msg'] = 'NDA 동의가 완료되었습니다.';
        die(json_encode($result));
    }

    public function logAppAccess() {
        // 세션에서 'first_access' 변수를 확인
        if (!$this->session->userdata('first_access')) {
            $user = $this->session->userdata('user');
            $username = $user['nickname'] ?? '비회원';
            // 처음 접속한 경우에만 로그 기록
            $this->logAccess( $username, '접속', '처음 접속');

            // 'first_access' 세션 변수를 설정하여 이후에는 로그를 기록하지 않도록 함
            $this->session->set_userdata('first_access', true);
        }
    }

    private function logAccess($username, $type, $reason) {
        $ipAddress = $this->input->ip_address();
        
        // 세션에서 사용자 정보 가져오기
        $user = $this->session->userdata('user');

        $userId = $user['idx'] ?? null;

    
        // 로그 데이터 생성
        $data = [
            'user_id' => $userId,
            'username' => $username,
            'ip_address' => $ipAddress,
            'type' => $type,
            'reason' => $reason,
            'access_date' => date('Y-m-d H:i:s')
        ];
    
        // 로그 저장
        $this->db->insert('access_log', $data);
    
        if ($this->db->affected_rows() == 0) {
            log_message('error', 'Failed to log access: ' . $this->db->error()['message']);
        }
    }
    
    // 특정 메일을 읽음 처리하는 함수
    public function markAsRead() {
        $result = ['result' => false, 'msg' => ''];
    
        $json = file_get_contents('php://input'); // JSON 데이터를 읽음
        $data = json_decode($json, true);
        $idx = $data['idx'] ?? null;
    
        $memberIdx = $this->user['idx'] ?? null;
    
        if (!$idx || !$memberIdx) {
            $result['msg'] = '잘못된 요청입니다.';
            die(json_encode($result));
        }
    
        $sql = "UPDATE mailbox_list SET isRead = 'Y' WHERE idx = ? AND memberIdx = ?";
        $ok = $this->db->query($sql, [$idx, $memberIdx]);
    
        if (!$ok) {
            $result['msg'] = 'DB 업데이트 실패';
            die(json_encode($result));
        }
    
        $result['result'] = true;
        die(json_encode($result));
    }
    
    public function checkMailPayment()
    {
        $result = ['result' => false];

        // $data = json_decode(file_get_contents('php://input'), true);
        $dataRaw = file_get_contents('php://input');
        $data = json_decode($dataRaw, true);

        log_message('error', '[DEBUG] checkMailPayment raw: ' . $dataRaw);
        log_message('error', '[DEBUG] checkMailPayment parsed: ' . print_r($data, true));

        if (!is_array($data)) {
            $result['msg'] = '요청 포맷 오류';
            echo json_encode($result);
            return;
        }

        log_message('error', '[DEBUG] checkMailPayment 데이터: ' . print_r($data, true));

        $idx = $data['idx'] ?? null;
        $payType = $data['payType'] ?? null;

        log_message('error', '[DEBUG] checkMailPayment 데이터: ' . print_r($payType, true));

        if (!isset($idx) || !isset($payType)) {
            $result['msg'] = '잘못된 요청입니다.';
            echo json_encode($result);
            return;
        }
        

        $sql = "SELECT isPaidScan, isPaidDelivery, scanPayType, deliveryPayType, pdfPath FROM mailbox_list WHERE idx = ?";
        $query = $this->db->query($sql, [$idx]);
        $mail = $query->row_array();

        if (!$mail) {
            $result['msg'] = '존재하지 않는 사서함입니다.';
            echo json_encode($result);
            return;
        }

        $isPaid = $payType === 'scan' ? $mail['isPaidScan'] === 'Y' : $mail['isPaidDelivery'] === 'Y';
        $payMethod = $payType === 'scan' ? $mail['scanPayType'] : $mail['deliveryPayType'];

        $result['result'] = true;
        $result['isPaid'] = $isPaid;
        $result['payType'] = $payMethod;
        $result['pdfPath'] = $mail['pdfPath'];

        echo json_encode($result);

    }


    

    public function completeMailPayment() {
        $mailboxIdx = $this->input->post('mailboxIdx');
        $type = $this->input->post('type'); // 'scan' or 'delivery'
        $amount = (int)$this->input->post('amount');
    
        if (!in_array($type, ['scan', 'delivery'])) {
            show_error('잘못된 접근입니다.');
            return;
        }
    
        $column = $type === 'scan' ? 'isPaidScan' : 'isPaidDelivery';
    
        $sql = "UPDATE mailbox_list SET {$column} = 'Y' WHERE idx = ?";
        $this->db->query($sql, [$mailboxIdx]);
    
        $logSql = "
            INSERT INTO mailbox_payment_log SET
            mailboxIdx = ?, payType = ?, amount = ?, payDate = NOW()
        ";
        $this->db->query($logSql, [$mailboxIdx, $type, $amount]);
    
        redirect('/mypage/myMailbox');
    }
    
    
    public function payMailbox() {
        $result = ['result' => false, 'msg' => ''];
    
        $mailboxIdx = $this->input->post('mailboxIdx');
        $type = $this->input->post('type'); // 'scan' or 'delivery'
        $amount = $this->input->post('amount');
        $payType = $this->input->post('payType');
    
        // 공통 현금영수증 정보
        $isCashReceipt = $this->input->post($type === 'scan' ? 'isCashReceiptScan' : 'isCashReceiptDelivery');
        $cashReceiptType = $this->input->post($type === 'scan' ? 'cashReceiptTypeScan' : 'cashReceiptTypeDelivery');
        $depositType = $this->input->post($type === 'scan' ? 'depositTypeScan' : 'depositTypeDelivery');
        $cashReceiptNumber = $this->input->post($type === 'scan' ? 'cashReceiptNumberScan' : 'cashReceiptNumberDelivery');
        $cashReceiptEmail = $this->input->post($type === 'scan' ? 'cashReceiptEmailScan' : 'cashReceiptEmailDelivery');

    
        // 수령자 정보
        $receiverName = $this->input->post('receiverName');
        $receiverAddr = $this->input->post('receiverAddr');
        $receiverAddrDetail = $this->input->post('receiverAddrDetail');
        $receiverTel = $this->input->post('receiverTel');
    
        if (!in_array($type, ['scan', 'delivery']) || !$mailboxIdx || $amount === null) {
            $result['msg'] = '잘못된 요청입니다.';
            die(json_encode($result));
        }

        
        $payPoint = (int) $this->input->post('payPoint');
        $realTotalPrice = (int) $amount;
        $totalPrice = $realTotalPrice + $payPoint;


        if ($type === 'scan') {
            $this->db->set('scanTotalPrice', $totalPrice);
            $this->db->set('scanPayPoint', $payPoint);
            $this->db->set('scanRealTotalPrice', $realTotalPrice);
        } else {
            $this->db->set('deliveryTotalPrice', $totalPrice);
            $this->db->set('deliveryPayPoint', $payPoint);
            $this->db->set('deliveryRealTotalPrice', $realTotalPrice);
        }
    
        // type에 따라 컬럼 설정
        $column = $type === 'scan' ? 'isPaidScan' : 'isPaidDelivery';
        $payColumn = $type === 'scan' ? 'scanPayType' : 'deliveryPayType';
        $receiptColumn = $type === 'scan' ? 'isCashReceiptScan' : 'isCashReceiptDelivery';
        $receiptTypeColumn = $type === 'scan' ? 'cashReceiptTypeScan' : 'cashReceiptTypeDelivery';
        $depositTypeColumn = $type === 'scan' ? 'depositTypeScan' : 'depositTypeDelivery';
        $receiptNumberColumn = $type === 'scan' ? 'cashReceiptNumberScan' : 'cashReceiptNumberDelivery';
        $receiptEmailColumn = $type === 'scan' ? 'cashReceiptEmailScan' : 'cashReceiptEmailDelivery';
    
        $mailbox = $this->db->get_where('mailbox_list', ['idx' => $mailboxIdx])->row_array();
        if (!$mailbox) {
            $result['msg'] = '존재하지 않는 사서함입니다.';
            die(json_encode($result));
        }
        $memberIdx = $mailbox['memberIdx'];
            $this->db->trans_start();
    
        // 결제 처리
        if (in_array($payType, ['point', 'card', 'kakaopay', 'naverpayCard'])) {
            $this->db->set($column, 'Y');
        }
    
        $this->db->set($payColumn, $payType);
        $this->db->set($receiptColumn, $isCashReceipt);
        if ($isCashReceipt == 'Y') {
            $this->db->set($receiptTypeColumn, $cashReceiptType);
            $this->db->set($depositTypeColumn, $depositType);
            $this->db->set($receiptNumberColumn, $cashReceiptNumber);
            $this->db->set($receiptEmailColumn, $cashReceiptEmail);
        }
    
        // 택배 요청일 경우
        if ($type === 'delivery') {
            $this->db->set('deliveryRequest', 'Y');
            $this->db->set('receiverName', $receiverName);
            $this->db->set('receiverAddr', $receiverAddr);
            $this->db->set('receiverAddrDetail', $receiverAddrDetail);
            $this->db->set('receiverTel', $receiverTel);
        }
    
        $this->db->where('idx', $mailboxIdx);
        $this->db->update('mailbox_list');
    
        // 결제 로그 기록
        $this->db->insert('mailbox_payment_log', [
            'mailboxIdx' => $mailboxIdx,
            'payType' => $type,
            'payMethod' => $payType,
            'amount' => $amount,
            'payPoint' => $payPoint,
            'totalPrice' => $totalPrice,
            'realTotalPrice' => $realTotalPrice,
            'payDate' => date('Y-m-d H:i:s')
        ]);        
    
        // 포인트 차감
        if ($payType === 'point') {
            $this->db->query("UPDATE member_list SET point = point - ? WHERE idx = ?", [$payPoint, $memberIdx]);
    
            $this->db->insert('point_log', [
                'memberIdx' => $memberIdx,
                'mailboxIdx' => $mailboxIdx,
                'point' => -$payPoint,
                'ment' => "사서함 {$type} 결제"
            ]);
        }
    
        $this->db->trans_complete();
    
        if ($this->db->trans_status() === false) {
            $result['msg'] = '결제 처리 중 오류가 발생했습니다.';
        } else {
            $result['result'] = true;
            $result['isPay'] = ($payType == 'point') ? 'Y' : 'N';
            $result['idx'] = $mailboxIdx;
        }
    
        die(json_encode($result));
    }
    
    

    public function getMailboxFilePath() {
        $result = ['result' => false, 'msg' => ''];
    
        $post = json_decode(file_get_contents("php://input"), true);
        $idx = isset($post['idx']) ? $post['idx'] : null;
    
        if (!$idx) {
            $result['msg'] = '잘못된 접근입니다.';
            die(json_encode($result));
        }
    
        $row = $this->db->get_where('mailbox_list', ['idx' => $idx])->row_array();
    
        if (!$row) {
            $result['msg'] = '해당 데이터가 존재하지 않습니다.';
            die(json_encode($result));
        }
    
        $result['result'] = true;
        $result['pdfPath'] = $row['pdfPath'];
        die(json_encode($result));
    }
    
    public function getTrackingInfo() {
        $post = json_decode(file_get_contents('php://input'), true);
        $idx = $post['idx'] ?? null;
    
        $result = ['result' => false];
    
        if (!$idx) {
            $result['msg'] = '잘못된 접근입니다.';
            echo json_encode($result); return;
        }
    
        $mail = $this->db->get_where('mailbox_list', ['idx' => $idx])->row_array();
    
        if (!$mail) {
            $result['msg'] = '사서함 정보를 찾을 수 없습니다.';
            echo json_encode($result); return;
        }
    
        $result['result'] = true;
        $result['deliveryCompany'] = $mail['deliveryCompany'];
        $result['trackingNumber'] = $mail['trackingNumber'];
        $result['deliveryDate'] = $mail['deliveryDate'] ? date('Y-m-d', strtotime($mail['deliveryDate'])) : null;
    
        echo json_encode($result);
    }
    
    
    public function discardMail() {
        $result = ['result' => false, 'msg' => ''];

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $mailIdx = $data['mailIdx'] ?? null;
        $memberIdx = $this->user['idx'] ?? null;

        if (!$mailIdx || !$memberIdx) {
            $result['msg'] = '잘못된 요청입니다.';
            die(json_encode($result));
        }

        $this->db->where('idx', $mailIdx);
        $this->db->where('memberIdx', $memberIdx);
        $this->db->update('mailbox_list', ['isUse' => 'N', 'scan_status' => 3]);

        $result['result'] = true;
        die(json_encode($result));
    }


    public function requestMailDelivery() {
        $result = ['result' => false, 'msg' => ''];

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $mailIdx = $data['mailIdx'] ?? null;
        $memberIdx = $this->user['idx'] ?? null;

        // if (!$mailIdx || !$memberIdx) {
        //     $result['msg'] = '잘못된 요청입니다.';
        //     die(json_encode($result));
        // }

        $this->db->where('idx', $mailIdx);
        $this->db->where('memberIdx', $memberIdx);
        $this->db->update('mailbox_list', ['deliveryRequest' => 'Y', 'deliveryRequestDate' => date('Y-m-d H:i:s')]);

        $result['result'] = true;
        die(json_encode($result));
    }

    // 사서함 상태 업데이트 (스캔본 확인 후 상태 변경)
    public function updateMailboxStatus() {
        $input = json_decode(file_get_contents('php://input'), true);
        $mailboxIdx = $input['mailboxIdx'];
        $scanStatus = $input['scanStatus'];

        if (!$mailboxIdx || !in_array($scanStatus, [0, 1, 2, 3])) {
            echo json_encode(['result' => false, 'msg' => '잘못된 요청입니다.']);
            return;
        }

        // mailbox_list 테이블의 scan_status 값을 업데이트
        $this->db->where('idx', $mailboxIdx);
        $this->db->update('mailbox_list', ['scan_status' => $scanStatus]);

        echo json_encode(['result' => true]);
    }

    // 사서함을 폐기 상태로 변경 (isUse 값 N으로 설정)
    public function setMailboxToDelete() {
        $input = json_decode(file_get_contents('php://input'), true);
        $mailboxIdx = $input['mailboxIdx'];

        if (!$mailboxIdx) {
            echo json_encode(['result' => false, 'msg' => '잘못된 요청입니다.']);
            return;
        }

        // 해당 사서함을 폐기 상태로 설정
        $this->db->where('idx', $mailboxIdx);
        $this->db->update('mailbox_list', ['isUse' => 'N']);

        echo json_encode(['result' => true]);
    }

    // 자료실 리스트 조회 함수
    public function getLibrary()
    {
        // 기본 결과 배열 설정
        $result = array('result' => false, 'msg' => '');

        // 카테고리 인덱스 받기
        $cateIdx = $_POST['cateIdx'] ?? 0;

        // 기본 쿼리
        $sql = "
            SELECT L.*, C.cateName
            FROM library_list AS L
            LEFT JOIN library_category AS C ON L.categoryIdx = C.idx
            WHERE L.isUse = 'Y' AND L.categoryIdx is not null
        ";


        // 카테고리 필터 적용
        $params = array();
        if (!empty($cateIdx)) {
            $sql .= " AND categoryIdx = ?";
            $params[] = $cateIdx;
        }

        // 정렬 조건 추가
        if (!empty($cateIdx)) {
            $sql .= " ORDER BY L.sortOrder DESC, L.idx DESC";
        } else {
            $sql .= " ORDER BY L.idx DESC";
        }
        

        // 쿼리 실행
        $query = $this->db->query($sql, $params);
        $list = $query->result_array();

        // 파일 경로 처리
        foreach ($list as &$item) {
            $item['thumbUrl'] = !empty($item['thumbPath']) ? '' . $item['thumbPath'] : '/assets/image/no_image.jpg';
            $item['fileUrl'] = !empty($item['filePath']) ? '' . $item['filePath'] : '';
            $item['name'] = $item['title'];
            $item['regDate'] = substr($item['regDate'], 0, 19); // YYYY-MM-DD HH:mm:ss
            $item['pageCount'] = $item['pageCount'];
        }

        // 결과 지정
        $result['result'] = true;
        $result['list'] = $list;

        // 결과 반환
        die(json_encode($result));
    }

    public function logNicepayError()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if ($input) {
            $source = $input['source'] ?? 'unknown';
            $result = json_encode($input['result'], JSON_UNESCAPED_UNICODE);

            log_message('error', "[NICEPAY ERROR] [$source] $result");
        }

        echo json_encode(['result' => true]);
    }


}


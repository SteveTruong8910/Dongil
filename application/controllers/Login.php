<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// JWT 라이브러리를 불러오기
require_once APPPATH . 'views/libs/php-jwt/src/JWT.php';
use \Firebase\JWT\JWT;

class Login extends CI_Controller {

    // 사용자 정보
    private $user;
    
    // Apple 로그인에 사용되는 키 값
    private $key_id = '8XNW9BKZGL';
    
    // 생성자 함수
    public function __construct(){
        parent::__construct();

        // 공통 설정 파일 로드
        $this->load->config('common');

        // 세션에서 사용자 정보를 가져옴
        $this->user = getUserInfo($this->session->userdata('user'), $this->db);

        // 이미 로그인한 사용자가 있을 경우, 홈 페이지로 리다이렉트
        if(!empty($this->user)){
            header('Location: /'); // 홈 페이지로 리다이렉트
            exit; // 스크립트 종료
        }
    }
    
    // 로그인 페이지를 렌더링
    public function index()
    {
        // 로그인 페이지의 제목 설정
        $viewData = array('title' => '로그인');
        
        // 헤더, 로그인 뷰, 푸터를 로드하여 페이지 출력
        $this->load->view('/common/header', $viewData);
        $this->load->view('/login/login', $viewData);
        $this->load->view('/common/footer');
    }
    
    public function naverLogin()
    {
        $viewData = array('title' => '네이버로그인');
        
		$this->load->view('/login/naverLogin', $viewData);		
    }
    
    public function naverLoginCallback() {
        // 네이버 로그인 API 클라이언트 정보
        $client_id = "tDVeMCohzRzopuN8rrCQ";  // 클라이언트 아이디
        $client_secret = "4I7eLP6btM";        // 클라이언트 시크릿

        // GET 방식으로 전달된 코드와 상태 값을 받아옴
        $code = $_GET["code"];
        $state = $_GET["state"];

        // 네이버 로그인 콜백 URL 인코딩
        $redirectURI = urlencode("https://dongl.cafe24.com/login/naverLoginCallback");

        // 네이버 토큰 발급 요청 URL
        $url = "https://nid.naver.com/oauth2.0/token?grant_type=authorization_code&client_id=".$client_id."&client_secret=".$client_secret."&redirect_uri=".$redirectURI."&code=".$code."&state=".$state;

        // cURL을 사용하여 토큰 발급 요청
        $is_post = false;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);                // URL 설정
        curl_setopt($ch, CURLOPT_POST, $is_post);           // POST 방식 설정
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     // 응답 데이터를 문자열로 반환
        $headers = array();
        $response = curl_exec($ch);                         // 요청 실행
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);  // HTTP 상태 코드 확인
        curl_close($ch);                                    // cURL 세션 종료

        // 토큰 발급 성공 (HTTP 200 OK)
        if($status_code == 200) {
            $token_data = json_decode($response, true);  // JSON 응답을 배열로 변환
            $access_token = $token_data['access_token']; // 발급 받은 엑세스 토큰

            // 네이버 사용자 정보 가져오기
            $profile_url = "https://openapi.naver.com/v1/nid/me";
            $headers = array(
                "Authorization: Bearer $access_token"  // Bearer 방식으로 엑세스 토큰을 Authorization 헤더에 추가
            );

            // cURL을 사용하여 네이버 사용자 프로필 정보 요청
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $profile_url);    // URL 설정
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // 헤더 설정
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 응답 데이터를 문자열로 반환
            $response = curl_exec($ch);                     // 요청 실행
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // HTTP 상태 코드 확인
            curl_close($ch);                                // cURL 세션 종료

            // 사용자 정보 가져오기 성공 (HTTP 200 OK)
            if($status_code == 200) {
                $profile_data = json_decode($response, true);  // JSON 응답을 배열로 변환
                $user_info = $profile_data['response'];         // 사용자 정보 (id, name 등)

                // 사용자 정보를 세션에 저장하거나 처리하는 함수 호출
                $this->setUserInfo('naver', $user_info['id'], $user_info['name']);

                // 로그 기록 추가
                $this->logLoginAttempt('naver', $user_info['id'], 'success', '네이버 로그인 성공');

                // 로그인 후 홈으로 이동
                $this->goHome();
            } else {
                // 네이버 사용자 정보 요청 실패 시 에러 출력
                echo "Error 내용:".$response;
                // 다시 로그인 화면으로 리다이렉트
                $this->reLogin();
            }
        } else {
            // 토큰 발급 실패 시 에러 출력
            echo "Error 내용:".$response;
            // 다시 로그인 화면으로 리다이렉트
            $this->reLogin();
        }
    }
    
    public function appleLoginCallback() {                
        // 1. GET 파라미터에서 state, code, id_token 받기
        $state = $this->input->post('state');  // Apple 로그인 요청에서 반환된 state 값
        $code = $this->input->post('code');    // Apple 로그인 요청에서 반환된 authorization code
        $id_token = $this->input->post('id_token');  // Apple 로그인 요청에서 반환된 ID 토큰

        // Apple 개발자 계정의 정보 설정
        $team_id = 'AZ5QSBWLQJ';  // 애플 개발자 계정의 팀 ID
        $client_id = 'dongl.co.kr';  // 애플 로그인에 사용된 서비스 ID (클라이언트 ID)
        $key_id = '8XNW9BKZGL';  // 애플에서 발급받은 키의 ID
        $redirect_uri = 'https://dongl.cafe24.com/login/appleLoginCallback';  // 애플 로그인 후 리디렉션될 URI
        $private_key = file_get_contents(APPPATH . 'views/libs/AuthKey_8XNW9BKZGL.p8');  // 애플에서 제공한 개인 키 파일 경로

        // JWT Header 설정 (ES256 알고리즘 사용)
        $header = [
            'alg' => 'ES256', 
            'kid' => $key_id  // 키의 ID 설정
        ];

        // JWT Payload 설정 (발급자, 만료 시간, 클라이언트 ID 등)
        $payload = [
            'iss' => $team_id,  // 팀 ID
            'iat' => time(),  // 발급 시간
            'exp' => time() + 15777000,  // 만료 시간 (6개월 후)
            'aud' => 'https://appleid.apple.com',  // 대상 URL
            'sub' => $client_id  // 서비스 ID
        ];

        // JWT 서명 (ES256 알고리즘 사용)
        $client_secret = JWT::encode($payload, $private_key, 'ES256', null, $header);

        // 3. authorization_code로 access token 요청 (애플 API 호출)
        $token_url = "https://appleid.apple.com/auth/token";  // 애플의 토큰 발급 URL

        // 토큰 발급을 위한 데이터 설정
        $data = [
            'grant_type' => 'authorization_code',  // 인증 코드 방식
            'code' => $code,                       // 애플에서 받은 인증 코드
            'redirect_uri' => $redirect_uri,       // 리디렉션 URI
            'client_id' => $client_id,             // 클라이언트 ID
            'client_secret' => $client_secret      // 생성한 JWT 토큰 (client_secret)
        ];

        // 4. POST 요청을 통해 애플 API로 토큰 요청
        $ch = curl_init($token_url);  // cURL 세션 초기화
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // 응답을 문자열로 반환
        curl_setopt($ch, CURLOPT_POST, true);  // POST 방식 설정
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));  // 요청 데이터 설정
        $response = curl_exec($ch);  // 요청 실행
        curl_close($ch);  // cURL 세션 종료

        // API 응답에서 토큰 정보 추출
        $tokens = json_decode($response, true);  // JSON 응답을 배열로 변환                 

        if (isset($tokens['id_token'])) {
            // 5. id_token을 디코딩하여 사용자 정보 추출
            $user_info = $this->decodeJWT($tokens['id_token']);  // ID 토큰 디코딩하여 사용자 정보 추출

            // 사용자 정보를 세션에 저장
            $this->setUserInfo('apple', $user_info['sub'], '');  // Apple 사용자 정보 저장 (sub: 사용자 ID)

            // 로그 기록 추가
            $this->logLoginAttempt('apple', $user_info['sub'], 'success', '애플 로그인 성공');

            // 로그인 후 홈으로 이동
            $this->goHome();
        } else {
            // 토큰을 받지 못한 경우 에러 처리
            show_error('Failed to get tokens from Apple.', 500);  // 에러 메시지 출력
            $this->reLogin();  // 재로그인 요청
        }
    }
    
    private function decodeJWT($jwt) {
        // JWT 디코딩 (id_token을 base64로 디코딩하여 사용자 정보 추출)
        list($header, $payload, $signature) = explode('.', $jwt);
        $decoded_payload = base64_decode($payload);
        return json_decode($decoded_payload, true);
    }

    public function googleLogin() {
        // 구글 API 관련 설정 파일을 로드
        $this->config->load('google');

        // 구글 API 클라이언트 라이브러리 로드
        $this->load->view('/libs/google-api-php-client/autoload');

        // 구글 클라이언트 인스턴스 생성
        $client = new Google_Client();

        // 클라이언트 ID, 클라이언트 시크릿, 리디렉션 URI 설정
        $client->setClientId($this->config->item('google_client_id'));
        $client->setClientSecret($this->config->item('google_client_secret'));
        $client->setRedirectUri($this->config->item('google_redirect_uri'));

        // 사용자 프로필과 이메일 범위 요청
        $client->addScope('email');
        $client->addScope('profile');

        // 구글 인증 URL 생성
        $auth_url = $client->createAuthUrl();

        // 생성된 인증 URL로 리디렉션 (사용자가 구글 로그인 페이지로 이동)
        redirect($auth_url);
    }

    public function googleLoginCallback() {        
        // 구글 API 관련 설정 파일을 로드
        $this->config->load('google');        

        // 구글 API 클라이언트 라이브러리 로드
        $this->load->view('/libs/google-api-php-client/autoload');

        // 구글 클라이언트 인스턴스 생성
        $client = new Google_Client();

        // 클라이언트 ID, 클라이언트 시크릿, 리디렉션 URI 설정
        $client->setClientId($this->config->item('google_client_id'));
        $client->setClientSecret($this->config->item('google_client_secret'));
        $client->setRedirectUri($this->config->item('google_redirect_uri'));

        // 인증 코드가 URL에 있는지 확인
        if (isset($_GET['code'])) {
            // 인증 코드를 통해 액세스 토큰을 요청
            $token = $client->authenticate($_GET['code']);
            $client->setAccessToken($token);

            // Google OAuth2 서비스 객체 생성
            $oauth2 = new Google_Service_Oauth2($client);

            // 사용자 정보를 가져오기
            $userInfo = $oauth2->userinfo->get();

            // 사용자 정보가 성공적으로 가져와지면 세션에 저장하고 홈으로 리디렉션
            $this->setUserInfo('google', $userInfo->id, $userInfo->familyName.$userInfo->givenName);

            // 로그 기록 추가
            $this->logLoginAttempt('google', $userInfo->id, 'success', '구글 로그인 성공');

            $this->goHome();
            exit;
        } else {
            // 인증 코드가 없으면 재로그인 페이지로 리디렉션
            $this->reLogin();
            exit;
        }
    }

    public function kakaoLogin() {
        // 카카오 API 키와 리디렉션 URI 설정
        $kakao_api_key = '12d54fccd175f671e18ce6ab6a7489ef';
        $redirect_uri = 'https://dongl.cafe24.com/login/kakaoLogin';

        // 카카오 인증 코드 받기
        $code = $_GET['code'];

        // 카카오 토큰 요청 URL 생성
        $token_url = 'https://kauth.kakao.com/oauth/token';
        $token_url .= '?grant_type=authorization_code';
        $token_url .= '&client_id=' . $kakao_api_key;
        $token_url .= '&redirect_uri=' . urlencode($redirect_uri);
        $token_url .= '&code=' . $code;

        // cURL을 사용하여 토큰 요청
        $ch = curl_init($token_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // 응답을 JSON 형식으로 디코딩
        $response = json_decode($response, true);

        // 액세스 토큰이 존재하는지 확인
        if (isset($response['access_token'])) {
            // 카카오 사용자 정보 요청 URL 설정
            $profile_url = 'https://kapi.kakao.com/v2/user/me';            

            // 카카오 사용자 정보 요청
            $ch = curl_init($profile_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $response['access_token']));
            $profile = json_decode(curl_exec($ch), true);
            curl_close($ch);    

            
            if (!isset($profile['id'])) {
                $this->logLoginAttempt('kakao', null, 'fail', '카카오 사용자 정보 없음: ' . json_encode($profile));
                $this->reLogin();
                exit;
            }

            // 사용자 정보 세팅 및 홈으로 리디렉션
            $this->setUserInfo('kakao', $profile['id'], $profile['properties']['nickname']);
            
            // 로그 기록 추가
            $this->logLoginAttempt('kakao', $profile['id'], 'success', '카카오 로그인 성공');

            $this->goHome();
            exit;            
        } else {
            // 액세스 토큰이 없으면 재로그인 요청
            $this->logLoginAttempt('kakao', null, 'fail', '카카오 토큰 획득 실패: ' . json_encode($response));
            $this->reLogin();
            exit;
        }
    }

    private function logLoginAttempt($sns, $snsId, $status, $reason) {
        $ipAddress = $this->input->ip_address();

        $sql = "
             SELECT
                 idx, nickname
             FROM
                 member_list
             WHERE
                 sns = '{$sns}' AND
                 snsId = '{$snsId}'; 
        ";

        $query = $this->db->query($sql, array($sns, $snsId));
        $info = $query->row_array();

        $data = [
            'user_id' => $info['idx'] ?? null,
            'username' => $info['nickname'] ?? '',
            'ip_address' => $ipAddress,
            'sns' => $sns,
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

    private function setUserInfo($sns, $snsId, $nickname) {        
        // SNS 정보로 사용자가 이미 가입되어 있는지 확인하는 SQL 쿼리
        $sql = "
             SELECT
                 *
             FROM
                 member_list
             WHERE
                 sns = '{$sns}' AND
                 snsId = '{$snsId}'; 
        ";

        // 쿼리 실행
        $query = $this->db->query($sql);
        $info = $query->row_array();                

        // 탈퇴한 회원일 경우
        if($info['isUse'] == 'N') {
            // 탈퇴된 계정에 대한 알림과 재로그인 요청
            $scNav = "<script type='text/javascript' src='/assets/js/navigation.js?". $this->config->item('ver') . "'></script>";

            echo "
                {$scNav}
                <script>
                    alert('회원번호 : {$info['idx']}, 해당 계정은 탈퇴처리된 계정입니다. 복구를 원하시면 고객센터에 문의해주세요.');
                    nav.locationHref('/login', 'clear/a2');
                </script>
            ";            
            exit;            
        }

        // 회원이 없으면 신규 가입
        if(empty($info)){
            // 사용자 정보를 데이터베이스에 삽입하는 SQL 쿼리
            $sql = "
                INSERT INTO
                    member_list
                SET
                    sns = '{$sns}',
                    snsId = '{$snsId}',
                    nickname = '{$nickname}'
            ";

            // 쿼리 실행
            $this->db->query($sql);

            // 신규 가입된 사용자 정보 세팅
            $info = array();                
            $info['idx'] = $this->db->insert_id();
            $info['sns'] = $sns;
            $info['snsId'] = $snsId;
            $info['nickname'] = $nickname;
        }

        // 사용자 세션에 정보 저장
        $this->session->set_userdata('user', $info);    
    }

    private function reLogin() {
        // 네비게이션 스크립트 로드
        $scNav = "<script type='text/javascript' src='/assets/js/navigation.js?". $this->config->item('ver') . "'></script>";

        echo "
            {$scNav}                                    
            <script>
                nav.locationHref('/login', 'clear/a2');
            </script>
        ";
    }

    private function goHome() {
        // 네비게이션 스크립트 로드
        $scNav = "<script type='text/javascript' src='/assets/js/navigation.js?". $this->config->item('ver') . "'></script>";

        echo "
            {$scNav}                                    
            <script>                                        
                nav.locationHref('/', 'clear');
            </script>
        ";        
    }
}

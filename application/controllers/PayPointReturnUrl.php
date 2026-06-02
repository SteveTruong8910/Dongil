<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/* 포인트 결제 returnApi */
class payPointReturnUrl extends CI_Controller {

    public $user;

    public function __construct(){
        parent::__construct(); // 부모 클래스의 생성자 호출

        // 공통 설정 파일 로드
        $this->load->config('common');

        // 세션에서 사용자 정보 가져오기
        $this->user = $this->session->userdata('user');
    }
	
    public function index()
    {
        /* 포인트 충전 일시 중단 - Nicepay 콜백도 거부 */
        echo "<p style='font-size: 18px'>포인트 충전은 현재 일시 중단되어 있습니다.</p>";
        echo "<script type='text/javascript' src='/assets/js/navigation.js?" . $this->config->item('ver') . "'></script>";
        echo "<script>setTimeout(function(){ if (window.ReactNativeWebView) { nav.goBack(); } else { window.history.back(); } }, 1500);</script>";
        exit;

        // 결제 진행 중 메시지 출력
        echo "<p style='font-size: 18px'>결제가 진행중이오니 잠시만 기다려주세요!</p>";

        // navigation.js 스크립트 로드
        $scNav = "<script type='text/javascript' src='/assets/js/navigation.js?" . $this->config->item('ver') . "'></script>";

        // 결제 인증 결과가 '0000'이 아니면 실패 메시지를 출력하고 이전 화면으로 돌아감
        if ($_POST['authResultCode'] != '0000') {
            $authResultMsg = json_encode($_POST['authResultMsg']); // 인증 실패 메시지 JSON 인코딩

            echo "
                {$scNav}
                <script>
                    if (window.ReactNativeWebView) {
                        nav.goBack(); // React Native에서 호출된 경우 뒤로가기
                    } else {
                        window.history.back(); // 일반 웹에서 호출된 경우 뒤로가기
                    }
                </script>
            ";
            exit;
        }

        // 결제 성공시 처리할 변수들
        $tid = $_POST['tid'];   // 거래 ID
        $amount = $_POST['amount'];  // 결제 금액

        // Nicepay API 클라이언트 ID와 비밀키
        $clientId = $this->config->item('clientId');
        $secretKey = $this->config->item('secretKey');

        $resObject = '';

        try {
            // 결제 확인을 위해 Nicepay API 호출
            $res = $this->requestPost(
                "https://api.nicepay.co.kr/v1/payments/" . $tid,
                json_encode(array("amount" => $amount)),
                $clientId . ':' . $secretKey
            );
            $resObject = json_decode($res);
        } catch (Exception $e) {
            // 예외 처리 (API 호출 중 에러 발생 시)
            $e->getMessage();
        }

        // API 호출 결과를 배열로 변환
        $resObject = json_decode(json_encode($resObject), true);

        // 결제 실패 시
        if ($resObject['resultCode'] != '0000') {
            echo "
                {$scNav}
                <script>
                    alert('[결제실패]{$resObject['resultMsg']}');
                    nav.goBack(); // 결제 실패 후 이전 화면으로 돌아감
                </script>
            ";
            exit;
        }

        // 성공적인 결제 후 회원 정보 업데이트
        $memberIdx = explode('-', $_POST['orderId'])[1]; // 주문 ID에서 회원 인덱스 추출

        // DB에서 회원 정보 조회
        $sql = "SELECT * FROM member_list WHERE idx = '{$memberIdx}' ";
        $query = $this->db->query($sql);
        $member = $query->row_array();

        // 세션에 회원 정보 저장
        $this->session->set_userdata('user', $member);      

        /* 충전 포인트 계산 */
        $point = (int)$resObject['amount'];
        $percentage = ($point >= 30000 && $point < 100000) ? 3 : 5; // 금액에 따른 비율 (3만원 ~ 10만원 미만은 3%, 그 이상은 5%)
        $addPoint = round($resObject['amount'] * ($percentage / 100));  // 추가 포인트 계산

        // 포인트 변경 및 포인트 로그 기록
        $this->changePoint($memberIdx, ((int)$resObject['amount'] + (int)$addPoint));
        $this->addPointLog($memberIdx, $resObject, $addPoint);

        // 결제 완료 메시지 출력 및 포인트 페이지로 리디렉션
        echo "
            {$scNav}
            <script>
                alert('결제가 완료되었습니다.');
                nav.locationReplace('/mypage/point', 'clear/b4'); // 포인트 페이지로 이동
            </script>
        ";
        exit;
    }
    
    private function changePoint($memberIdx, $point) { 
        // 사용자의 포인트를 업데이트하는 SQL 쿼리
        $sql = "
            UPDATE
                member_list
            SET                 
                point = point + {$point}
            WHERE
                idx = '{$memberIdx}'
        ";

        // 쿼리 실행
        $this->db->query($sql);
    }

    private function addPointLog($memberIdx, $resObject, $addPoint) {
        // 포인트 계산: 기존 포인트와 추가 포인트 합산
        $point = (int)$resObject['amount'] + $addPoint;

        // 포인트 로그에 대한 INSERT 쿼리
        $sql = "
            INSERT INTO
                point_log
            SET
                memberIdx = '{$memberIdx}',
                point = '{$point}',
                bonus = '{$addPoint}',
                ment = '포인트결제',
                resultCode = '{$resObject['resultCode']}',
                resultMsg = '{$resObject['resultMsg']}',
                tid = '{$resObject['tid']}',
                orderId = '{$resObject['orderId']}',
                payMethod = '{$resObject['payMethod']}',
                amount = '{$resObject['amount']}'
        ";

        // 포인트 로그 삽입
        $this->db->query($sql);
        $payIdx = $this->db->insert_id(); // 방금 삽입된 로그의 ID

        // 카드 결제 또는 계좌 이체에 따른 추가 정보 업데이트
        if($resObject['payMethod'] == 'card' || $resObject['payMethod'] == 'bank') {
            // 카드 또는 계좌 이체의 추가 정보를 포함하는 UPDATE 쿼리
            $sql = "
                UPDATE
                    point_log
                SET
            ";

            // 결제 방법에 따라 카드 또는 계좌 이체 정보 추가
            switch($resObject['payMethod']){
                case 'card': // 카드 결제
                    $sql .= "
                        cardCode = '{$resObject['card']['cardCode']}',
                        cardName = '{$resObject['card']['cardName']}',
                        cardNum = '{$resObject['card']['cardNum']}'
                    ";
                    break;

                case 'bank': // 계좌이체 결제
                    $sql .= "
                        bankCode = '{$resObject['bank']['bankCode']}',
                        bankName = '{$resObject['bank']['bankName']}'                    
                    ";
                    break;     
            }

            // 업데이트 쿼리 실행
            $sql .= " WHERE idx = '{$payIdx}' ";
            $this->db->query($sql);   
        }
    }
    
    //CURL: Basic auth, json, post
    function requestPost($url, $json, $userpwd)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}

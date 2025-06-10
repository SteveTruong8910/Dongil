<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class payReturnUrl extends CI_Controller {

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
        // 결제 진행중 알림 메시지 출력
        echo "<p style='font-size: 18px'>결제가 진행중이오니 잠시만 기다려주세요!</p>";

        // 네비게이션 관련 스크립트 로드 (버전 정보를 쿼리 문자열에 포함)
        $scNav = "<script type='text/javascript' src='/assets/js/navigation.js?". $this->config->item('ver') . "'></script>";

        // 결제 인증 결과 코드가 '0000'이 아니면 결제 실패 처리
        if ($_POST['authResultCode'] != '0000') {
            // 실패 메시지를 JSON 인코딩하여 사용
            $authResultMsg = json_encode($_POST['authResultMsg']);

            // 결제 실패 시, 결제 실패 메시지를 출력하고 이전 페이지로 돌아가기
            echo "
                {$scNav}
                <script>                    
                    if (window.ReactNativeWebView) {                        
                        nav.goBack(); // React Native WebView 환경에서는 뒤로 가기
                    } else {
                        window.history.back(); // 일반 웹 환경에서는 뒤로 가기
                    }
                </script>
            ";
            exit; // 이후 코드 실행을 중단
        }

        // 결제 성공 후 처리할 값들 (tid, amount 등)
        $tid = $_POST['tid'];
        $amount = $_POST['amount'];

        // API 요청을 위한 클라이언트 ID와 비밀 키 가져오기
        $clientId = $this->config->item('clientId');
        $secretKey = $this->config->item('secretKey');   

        $resObject = ''; // 결제 결과 저장용 변수

        // 결제 API에 요청을 보내 결제 결과 확인
        try {
            $res = $this->requestPost(
                "https://api.nicepay.co.kr/v1/payments/". $tid, // 결제 상태 조회 URL
                json_encode(array("amount" => $amount)), // 결제 금액 포함
                $clientId . ':' . $secretKey // 기본 인증 정보
            );
            $resObject = json_decode($res); // API 응답을 객체로 변환
        } catch (Exception $e) {
            $e->getMessage(); // 예외 발생 시 메시지 출력 (실제로 처리되지 않음)
        }

        // 결제 응답을 다시 배열로 변환
        $resObject = json_decode(json_encode($resObject), true);

        // 결제 상태가 실패인 경우, 실패 메시지 출력
        if($resObject['resultCode'] != '0000'){
            echo "     
               {$scNav}
               <script>
                    alert('[결제실패]{$resObject['resultMsg']}'); // 결제 실패 메시지 출력

                    nav.goBack(); // 이전 페이지로 돌아가기
               </script>
            ";
            exit; // 이후 코드 실행을 중단
        }

        if (strpos($_POST['orderId'], 'mailbox-') === 0) {
            $orderIdParts = explode('-', $_POST['orderId']); // 예: mailbox-123-scan
            $mailboxIdx = $orderIdParts[1];
            $type = $orderIdParts[2]; // scan 또는 delivery

			$set = $type === 'scan' ? "isPaidScan = 'Y'" : "isPaidDelivery = 'Y'";
        
            $sql = "UPDATE mailbox_list SET {$set} WHERE idx = '{$mailboxIdx}'";
            $this->db->query($sql);
        
            echo "
               {$scNav}
               <script>
                   alert('사서함 결제가 완료되었습니다.');
                   nav.locationReplace('/mypage/myMailbox', 'clear/b4');
               </script>
            ";
            exit;
        }

        // 결제 관련 주문 ID 추출 (주문 번호에서 '- ' 이후 값을 가져옴)
        $writeIdx = explode('-', $_POST['orderId'])[1];
                           
        /* 주문 내역 가져오기 */
        $sql = "SELECT * FROM write_list WHERE idx = '{$writeIdx}';";
        $query = $this->db->query($sql);
        $writeInfo = $query->row_array();

        /* 이전 주문내역 저장 */
        $oldWriteInfo = $writeInfo;

        /* 재결제일 경우 */
        if($writeInfo['isSave'] == 'Y') {

            // 기존 결제 내역이 존재하는 경우
            if(!empty((int)$writeInfo['payIdx'])) {
                // 결제 정보를 가져오는 SQL 쿼리 (주문 상태, 결제 정보 포함)
                $sql = "    
                    SELECT
                        W.state, P.tid, P.amount, P.orderId
                    FROM
                        write_list AS W LEFT JOIN
                        pay_list AS P ON P.writeIdx = W.idx AND P.isUse = 'Y'
                    WHERE
                        W.idx = '{$writeIdx}';";

                $query = $this->db->query($sql);
                $info = $query->row_array();

                // 결제 취소를 위한 변수 초기화
                $resObject2 = "";
                $reason = "본인취소";  // 취소 이유 설정
                try {
                    // 결제 취소 요청 API 호출
                    $res = $this->requestPost(
                        "https://api.nicepay.co.kr/v1/payments/". $info['tid'] ."/cancel",
                        json_encode(array("amount" => $info['amount'], "reason" => $reason, "orderId" => $info['orderId'])),
                        $this->config->item('clientId') . ':' . $this->config->item('secretKey')
                    );

                    $resObject2 = json_decode($res);  // 응답 객체로 변환
                } catch (Exception $e) {
                    $e->getMessage();  // 예외 발생 시 처리
                }

                // 결제 취소 결과를 배열로 변환
                $resObject2 = json_decode(json_encode($resObject2), true);

                // 결제 취소가 실패한 경우, 실패 메시지를 출력하고 종료
                if($resObject2['resultCode'] != '0000') {
                    echo "
                       {$scNav}
                       <script>
                            alert('[결제취소실패]{$resObject2['resultMsg']} 관리자에게 문의해주세요.');

                            nav.goBack();
                       </script>
                    ";
                    exit;  // 이후 코드 실행을 중단
                }    
            }

            // 최신 주문 임시 내역을 가져오는 SQL 쿼리
            $sql = "SELECT * FROM write_tmp WHERE writeIdx = '{$writeIdx}' ORDER BY idx DESC; ";
            $query = $this->db->query($sql);
            $writeTmp = $query->row_array();

            /* 주문내역 업데이트 로직 추가 */
            // 임시 내역을 기반으로 주문 내역 업데이트
            $sql = "UPDATE write_list SET {$writeTmp['queryString']} WHERE idx = '{$writeIdx}'; ";
            $this->db->query($sql);

            /* 업데이트한 주문내역 가져오기 */
            // 업데이트된 주문 정보를 다시 가져옴
            $sql = "SELECT * FROM write_list WHERE idx = '{$writeIdx}';";
            $query = $this->db->query($sql);
            $writeInfo = $query->row_array();
        } else {
            // 재결제가 아니면 주소 자동 저장 처리
            autoSaveAddress($this->db, $writeIdx);
        }
                                                
        if($writeInfo['isLogin'] == 'Y'){
            /* 세션 끊김 현상 로직 다시 SESSION 넣기 */
            // 로그인된 사용자의 idx 값 가져오기
            $memberIdx = $writeInfo['memberIdx'];

            // member_list 테이블에서 해당 사용자의 정보를 가져옵니다.
            $sql = "SELECT * FROM member_list WHERE idx = '{$memberIdx}' ";
            $query = $this->db->query($sql);
            $member = $query->row_array(); 

            // 세션에 사용자 정보를 저장
            $this->session->set_userdata('user', $member);
        }else{
            // 로그인하지 않은 경우, memberIdx는 0으로 설정
            $memberIdx = 0;
        }

        /* 공통 */
        $sql = "
            INSERT INTO
                pay_list
            SET
                writeIdx = '{$writeIdx}',
                memberIdx = '{$memberIdx}',
                resultCode = '{$resObject['resultCode']}',
                resultMsg = '{$resObject['resultMsg']}',
                tid = '{$resObject['tid']}',
                orderId = '{$resObject['orderId']}',
                payMethod = '{$resObject['payMethod']}',
                amount = '{$resObject['amount']}'
        "; 

        // pay_list 테이블에 결제 정보 삽입
        $this->db->query($sql);

        // 마지막으로 삽입된 pay_list의 idx 값을 가져옴
        $payIdx = $this->db->insert_id();

        // 결제 방법이 카드 또는 계좌이체인 경우, 추가 정보를 업데이트
        if($resObject['payMethod'] == 'card' || $resObject['payMethod'] == 'bank') {
            $sql = "
                UPDATE
                    pay_list
                SET
            ";

            // 결제 방식에 따라 카드 정보 또는 계좌 정보 업데이트
            switch($resObject['payMethod']){
                /* 카드 결제 정보 */
                case 'card':
                    $sql .= "
                        cardCode = '{$resObject['card']['cardCode']}',
                        cardName = '{$resObject['card']['cardName']}',
                        cardNum = '{$resObject['card']['cardNum']}'
                    ";
                break;

                /* 계좌이체 결제 정보 */
                case 'bank':
                    $sql .= "
                        bankCode = '{$resObject['bank']['bankCode']}',
                        bankName = '{$resObject['bank']['bankName']}'
                    ";
                break;
            }

            // 결제 정보를 업데이트하는 SQL 쿼리 실행
            $sql .= " WHERE idx = '{$payIdx}' ";
            $this->db->query($sql);
        }
        
        $comment = "";

        /* 재결제가 아닌 경우 payId 지정 */
        if($writeInfo['isSave'] != 'Y') {
            // 기존 결제 내역에서 '결제 완료' 상태 또는 '입금'인 주문을 찾아서 다음 결제 ID를 생성합니다.
            $sql = "SELECT COUNT(*) + 1 AS cnt FROM write_list WHERE isPay = 'Y' OR payType = 'deposit'";
            $query = $this->db->query($sql);
            $payId = (int)$query->row_array()['cnt'] + 1;  // 결제 ID는 기존 결제 건수를 기반으로 자동으로 생성

            // 주문 상태를 'W'로 업데이트하고 결제 상태를 'Y', 결제 ID와 결제 인덱스를 업데이트합니다.
            $sql = "UPDATE write_list SET state = 'W', isPay = 'Y', payId = '{$payId}', payIdx = '{$payIdx}' WHERE idx = '{$writeIdx}' ";
            $this->db->query($sql);
        }else {
            // 재결제인 경우, 이전 결제 정보는 'N'으로 표시하여 사용하지 않도록 설정
            $sql = "UPDATE pay_list SET isUse = 'N' WHERE idx = '{$writeInfo['payIdx']}'";            
            $this->db->query($sql);

            // 주문 상태를 'W'로 업데이트하고, 재결제 표시를 'N'으로 설정하며, 새 결제 인덱스를 할당합니다.
            $sql = "UPDATE write_list SET state = 'W', isPay = 'Y', isSave = 'N', payIdx = '{$payIdx}' WHERE idx = '{$writeIdx}' ";
            $this->db->query($sql);

            /* 수정 시 이전 포인트 지급 */
            // 이전 결제에서 포인트가 있다면, 해당 포인트를 다시 지급합니다.
            if((int)$oldWriteInfo['payPoint'] > 0) {
                $plusPoint = (int)$oldWriteInfo['payPoint'];
                $productName = $oldWriteInfo['productName'];

                // 회원의 포인트를 업데이트하고 포인트 로그를 기록합니다.
                $sql = "UPDATE member_list SET point = point + {$plusPoint} WHERE idx = '{$memberIdx}' ";
                $this->db->query($sql);

                $sql = "INSERT INTO point_log SET memberIdx = '{$memberIdx}', writeIdx = '{$writeIdx}', point = '{$plusPoint}', ment = '{$productName} 편지지수정'";
                $this->db->query($sql);

                $comment = "재결제";
            }
        }

        /* 비회원이 아닌 경우 */
        if($memberIdx != 0){
            $payPoint = (int)$writeInfo['payPoint'] * -1;  // 결제 포인트 차감

            /* 결제 포인트 차감 */
            if($payPoint != 0) {
                // 결제 포인트가 있으면, 해당 포인트를 차감합니다.
                $ment = empty($comment)? '결제' : $comment;

                // 포인트 차감 함수 호출
                $this->changePoint($memberIdx, $payPoint);

                // 포인트 차감 로그 추가
                $this->addPointLog($memberIdx, $writeIdx, $payPoint, $writeInfo['productName'].' '.$ment);
            }
        }

        /* 주문번호 자동 생성 */
        setOrderId($this->db, $writeIdx);  // 주문 번호를 자동으로 생성

        if($memberIdx == 0){
            // 비회원인 경우
            echo "
               {$scNav}
               <script>
                   alert('결제가 완료되었습니다.');
                   nav.locationReplace('/mypage/myLetter?mbName={$writeInfo['mbName']}&mbId={$writeInfo['mbPhoneNumber']}&mbPassword={$writeInfo['mbPassword']}', 'clear/b4')
               </script>
            ";
            exit;
        }else {
            // 회원인 경우
            echo "
               {$scNav}
               <script>
                   alert('결제가 완료되었습니다.');
                   nav.locationReplace('/mypage/myLetter', 'clear/b4')
               </script>
            ";
            exit;
        }

	}
    
    private function changePoint($memberIdx, $point) {        
        $sql = "
            UPDATE
                member_list
            SET                 
                point = point + {$point}
            WHERE
                idx = '{$memberIdx}'  
        ";

        // 쿼리를 실행하여 데이터베이스의 포인트를 업데이트합니다.
        $this->db->query($sql);
    }
    
    private function addPointLog($memberIdx, $writeIdx, $point, $ment) {                
        $sql = "
            INSERT INTO
                point_log
            SET
                memberIdx = '{$memberIdx}',
                writeIdx = '{$writeIdx}',
                point = '{$point}',
                ment = '{$ment}'
        ";

        // 쿼리를 실행하여 포인트 거래 내역을 기록합니다.
        $this->db->query($sql);
    }
    
    //CURL: Basic auth, json, post
    function requestPost($url, $json, $userpwd)
    {
        $ch = curl_init();  // cURL 세션 초기화

        // 요청할 URL 설정
        curl_setopt($ch, CURLOPT_URL, $url);

        // 요청 헤더에 Content-Type을 JSON으로 설정
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

        // 기본 인증(Basic Auth) 설정
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $userpwd);

        // JSON 데이터를 POST 요청 본문에 포함
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_POST, true);  // POST 요청 방식 지정

        // 응답을 받을 수 있도록 설정
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);  // 연결 시간 초과 (초)
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // SSL 인증서 검증을 비활성화 (사용 시 주의)

        // 요청 실행 후 응답을 반환
        $response = curl_exec($ch);

        // cURL 세션 종료
        curl_close($ch);

        return $response;  // 응답값 반환
    }
}

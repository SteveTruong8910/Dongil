<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PointApi extends CI_Controller {

    public $user;

    public function __construct(){
        parent::__construct();

        // 공통 설정 파일 로드
        $this->load->config('common');
        // 세션에서 사용자 정보 가져오기
        $this->user = $this->session->userdata('user');
    }
    
    public function index() {
    
    }

    private function changePoint($memberIdx, $point) {
        $result = array('result' => false, 'msg' => "");

        // 사용자의 포인트를 변경하는 SQL 쿼리
        $sql = "
            UPDATE
                member_list
            SET
                point = point + {$point}
            WHERE
                idx = '{$memberIdx}'
        ";

        // 쿼리 실행
        $result['result'] = $this->db->query($sql);

        // 쿼리 실행 실패 시 에러 메시지
        if(empty($result['result'])){
            $result['msg'] = 'setPoint error';
        }

        return $result;
    }
    
    private function addPointLog(
        $memberIdx, 
        $point, 
        $ment, 
        $addPoint = 0, 
        $isWait = 'N', 
        $depositorName = '',
        $isCashReceipt = 'N',
        $cashReceiptType = '',
        $depositType = '',
        $cashReceiptNumber = '',
        $cashReceiptEmail = ''
    ) {
        $result = array('result' => false, 'msg' => "");

        // 포인트 로그를 point_log 테이블에 삽입하는 SQL 쿼리
        $sql = "
            INSERT INTO
                point_log
            SET
                memberIdx = '{$memberIdx}',
                isWait = '{$isWait}',
                point = '{$point}',
                bonus = '{$addPoint}',
                ment = '{$ment}',
                depositorName = '{$depositorName}',
                isCashReceipt = '{$isCashReceipt}',
                cashReceiptType = '{$cashReceiptType}',
                depositType = '{$depositType}',
                cashReceiptNumber = '{$cashReceiptNumber}',
                cashReceiptEmail = '{$cashReceiptEmail}'
        ";

        // 쿼리 실행
        $result['result'] = $this->db->query($sql);

        // 쿼리 실행 실패 시 에러 메시지 설정
        if(empty($result['result'])){
            $result['msg'] = 'addPointLog error';     
        }

        return $result;
    }
    
    public function setPoint()
    {
        $result = array('result' => false, 'msg' => "");

        // POST 요청에서 사용자 정보와 포인트 정보를 가져옴
        $memberIdx = $_POST['memberIdx'];
        $point = (int)$_POST['point'];  // 포인트는 정수형으로 변환
        $type = $_POST['type'];         // 'add' 또는 'subtract' 확인
        $ment = $_POST['ment'];         // 포인트 변경 사유

        // 'add'가 아닌 경우 포인트를 음수로 처리하여 차감
        if($type != 'add') {
            $point *= -1;
        }

        // changePoint 함수로 포인트 변경
        $result = $this->changePoint($memberIdx, $point);
        if(empty($result['result'])) {
            die(json_encode($result));  // 포인트 변경 실패 시 종료
        }

        // addPointLog 함수로 포인트 변경 로그 추가
        $result = $this->addPointLog($memberIdx, $point, $ment);
        if(empty($result['result'])) {
            die(json_encode($result));  // 로그 추가 실패 시 종료
        }

        // 성공적으로 처리된 결과 반환
        die(json_encode($result));
    }
    
    public function payDeposit()
    {
        $result = array('result' => false, 'msg' => "");

        /* 포인트 충전 일시 중단 */
        $result['msg'] = '포인트 충전은 현재 일시 중단되어 있습니다.';
        die(json_encode($result));

        // 세션에서 사용자 정보 가져오기
        $memberIdx = $this->user['idx'];
        $point = (int)$_POST['point'];  // 충전할 포인트 (정수형으로 변환)
        $depositorName = $_POST['depositorName'];  // 입금자 이름
        
        $isCashReceipt = empty($_POST['isCashReceipt'])? 'N' : $_POST['isCashReceipt'];
        $cashReceiptType = empty($_POST['cashReceiptType'])? '' : $_POST['cashReceiptType'];
        $depositType = empty($_POST['depositType'])? '' : $_POST['depositType'];
        $cashReceiptNumber = empty($_POST['cashReceiptNumber'])? '' : $_POST['cashReceiptNumber'];
        $cashReceiptEmail = empty($_POST['cashReceiptEmail'])? '' : $_POST['cashReceiptEmail'];

        /* 충전 포인트 계산 */
        // 포인트가 3만원 이상 10만원 미만이면 5%, 10만원 이상이면 10% 추가
        $percentage = ($point >= 30000 && $point < 100000) ? 5 : 10; 
        $addPoint = round($point * ($percentage / 100));  // 추가 포인트 계산 (반올림 처리)
            
        /* 현금영수증 신청시 이용자DB 바로저장 */
        if($isCashReceipt == 'Y') {
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

        // 포인트 충전과 로그 기록
        $result = $this->addPointLog(
            $memberIdx, 
            ((int)$point + $addPoint),
            '무통장 포인트결제', 
            $addPoint, 
            'Y', 
            $depositorName,
            $isCashReceipt,
            $cashReceiptType,
            $depositType,
            $cashReceiptNumber,
            $cashReceiptEmail
        );

        // 결과 반환
        die(json_encode($result));
    }
    
    public function getPointList() {
        $result = array('list' => array());

        // 클라이언트로부터 페이지 번호와 유형(type) 받기
        $page = $_POST['page'];
        $type = $_POST['type'];
        $pagingCount = 50;  // 한 페이지에 표시할 항목 수

        // 페이지에 맞는 LIMIT 값 계산
        $limit = getPageLimit($page, $pagingCount);

        // 포인트 내역의 유형을 검색할 SQL 조건문 설정
        $searchSql = "";
        if($type == 'plus') {
            $searchSql = " point > 0 AND ";  // 포인트가 증가한 내역만 조회
        } else if($type == 'minus') {
            $searchSql = " point < 0 AND ";  // 포인트가 감소한 내역만 조회
        }

        // SQL 쿼리 작성 (LIMIT을 사용해 페이지별로 조회)
        $sql = "
             SELECT
                 isWait,
                 point,
                 bonus,
                 ment,
                 regDate
             FROM
                 point_log
             WHERE
                memberIdx = ? AND
                $searchSql
                isUse = 'Y'
             ORDER BY
                idx DESC
             LIMIT
                ?, ?;
        ";

        // 쿼리 실행, ?에 값을 바인딩
        $query = $this->db->query($sql, array($this->user['idx'], $limit, $pagingCount));
        $list = $query->result_array();  // 결과를 배열로 받기

        // 날짜 포맷 변경 (regDate의 일부를 추출)
        for($i = 0; $i < count($list); $i++) {
            $row = $list[$i];

            // 'regDate'에서 날짜와 시간 분리
            $row['dateMD'] = substr($row['regDate'], 2, 8);  // 'yyMMdd' 형식
            $row['dateHI'] = substr($row['regDate'], 11, 5);  // 'HH:mm' 형식

            $list[$i] = $row;
        }

        // 결과 반환
        $result['list'] = $list;

        // JSON 형식으로 응답
        die(json_encode($result));
    }
    
    public function checkDeposit() {
        // 클라이언트로부터 포인트 로그의 idx 받기
        $pointLogIdx = $_POST['pointLogIdx'];

        // 해당 포인트 로그 정보를 가져오기 위한 SQL 쿼리
        $sql = "SELECT * FROM point_log WHERE idx = ?";

        // 쿼리 실행
        $query = $this->db->query($sql, array($pointLogIdx));
        $info = $query->row_array();  // 쿼리 결과를 배열로 받기

        // 해당 포인트 로그의 'isWait' 상태를 'N'으로 업데이트
        $sql = "UPDATE point_log SET iswait = 'N' WHERE idx = ?";        
        $result['result'] = $this->db->query($sql, array($pointLogIdx));

        // 'isWait' 상태 업데이트 실패 시 에러 메시지
        if(empty($result['result'])){
            $result['msg'] = 'checkDeposit error';
            die(json_encode($result));
        }

        // 포인트 변동을 반영하기 위해 사용자의 포인트를 업데이트
        $result = $this->changePoint($info['memberIdx'], $info['point']);

        // 포인트 변경 실패 시 에러 메시지
        if(empty($result['result'])) {
            die(json_encode($result));
        }

        // 성공적으로 처리된 경우 결과 반환
        die(json_encode($result));
    }
    
    public function deletePointLog() {
        // 클라이언트로부터 받은 pointLogIdx
        $pointLogIdx = $_POST['pointLogIdx'];

        // 해당 포인트 로그의 isUse 값을 'N'으로 업데이트하여 삭제 처리
        $sql = "UPDATE point_log SET isUse = 'N' WHERE idx = ?";        
        $result['result'] = $this->db->query($sql, array($pointLogIdx));

        // 쿼리 실행 실패 시 에러 메시지
        if(empty($result['result'])){
            $result['msg'] = 'deletePointLog error';
            die(json_encode($result));
        }

        // 삭제 처리가 정상적으로 완료되었을 경우 결과 반환
        die(json_encode($result));
    }

    public function removeMember() {
        // 현재 로그인된 사용자 정보에서 idx 가져오기
        $memberIdx = $this->user['idx'];

        // 해당 사용자의 isUse 값을 'N'으로 업데이트하여 사용자를 비활성화 처리
        $sql = "UPDATE member_list SET isUse = 'N' WHERE idx = ?";
        $result['result'] = $this->db->query($sql, array($memberIdx));
        
        /* 포인트 관련 제어 미흡 */

        // 쿼리 실행 실패 시 에러 메시지
        if(empty($result['result'])){
            $result['msg'] = 'removeMember error';
            die(json_encode($result));
        }

        // 세션 종료 처리
        session_destroy();

        // 정상 처리 후 결과 반환
        die(json_encode($result));
    }
}


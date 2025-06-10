<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AddressApi extends CI_Controller {

    public function __construct(){
        parent::__construct();          

        // 공통 설정 파일 로드
        $this->load->config('common');

        // 현재 사용자 정보 가져오기
        $this->user = getUserInfo($this->session->userdata('user'), $this->db);

        // 사용자 정보가 없으면 로그인 페이지로 리디렉션 (주석 처리됨)
        // if(empty($this->user)){
        //     header('Location: /login');
        //     exit;
        // }  
    }

    public function getPostList()
    {
        // 기본 결과 값 설정
        $result = array('result' => true, 'msg' => "");

        // POST 데이터로부터 사용자 정보 받기
        $memberIdx = $_POST['memberIdx'];
        $senderName = $_POST['senderName'];
        $senderTel = $_POST['senderTel'];
        $mbPassword = $_POST['mbPassword'];

        // SQL 쿼리 및 목록 초기화
        $sql = "";
        $list = [];

        // memberIdx가 비어 있을 경우 비회원 주문목록 처리
        if(empty($memberIdx)) {
            // 비회원 주문 목록을 가져오는 SQL 쿼리
            $sql = "
                SELECT
                    senderAddr, senderAddrDetail, senderName, senderTel,
                    receiverAddr, receiverAddrDetail, receiverName, receiverTel
                FROM
                    write_list
                WHERE
                    mbPhoneNumber = '{$senderTel}' AND
                    mbName = '{$senderName}' AND
                    mbPassword = '{$mbPassword}' AND
                    isUse = 'Y'
                ORDER BY
                    idx DESC;
            ";

            // 쿼리 실행 후 결과를 배열로 받음
            $query = $this->db->query($sql);
            $list = $query->result_array();   
        } else {
            // memberIdx가 있을 경우 회원 주문목록 처리
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

            // 쿼리 실행 후 결과를 배열로 받음
            $query = $this->db->query($sql);
            $postList = $query->result_array();

            // 정렬된 게시물 목록 및 남은 게시물 목록 초기화
            $sortedPostList = [];
            $remainingPosts = [];

            // 게시물 목록에서 현재 사용자의 주소를 우선적으로 정렬
            foreach($postList as $key => $data) {
                if ($this->user['addressIdx'] == $data['idx']) {                
                    // 현재 사용자의 주소를 맨 앞에 배치
                    array_unshift($sortedPostList, $data);
                } else {                
                    // 나머지 게시물은 별도의 배열에 저장
                    $remainingPosts[] = $data;
                }
            }

            // 정렬된 게시물 목록과 남은 게시물 목록을 합침
            $list = array_merge($sortedPostList, $remainingPosts);
        }

        // 최종 결과에 목록 추가
        $result['list'] = $list;

        // 결과를 JSON 형식으로 출력
        die(json_encode($result));
    }

    
    public function setAddress()
    {
        // 결과 초기화
        $result = array('result' => false, 'msg' => "");

        // POST 데이터로부터 주소 정보 받기
        $addressIdx = $_POST['addressIdx'] ?? 0;  // 주소 수정 시 사용할 주소 ID (없으면 0)
        $senderAddr = $_POST['senderAddr'];
        $senderAddrDetail = $_POST['senderAddrDetail'];
        $senderName = $_POST['senderName'];
        $senderTel = $_POST['senderTel'];
        $receiverAddr = $_POST['receiverAddr'];
        $receiverAddrDetail = $_POST['receiverAddrDetail'];
        $receiverName = $_POST['receiverName'];
        $receiverTel = $_POST['receiverTel'];                

        // 주소가 새로 추가되는 경우 INSERT 쿼리 실행
        $sql = "";
        if (empty($addressIdx)) {
            $sql = "
                INSERT INTO
                    address
                SET
                    memberIdx = '{$this->user['idx']}',
                    senderAddr = '{$senderAddr}',
                    senderAddrDetail = '{$senderAddrDetail}',
                    senderName = '{$senderName}',
                    senderTel = '{$senderTel}',
                    receiverAddr = '{$receiverAddr}',
                    receiverAddrDetail = '{$receiverAddrDetail}',
                    receiverName = '{$receiverName}',
                    receiverTel = '{$receiverTel}'
            ";
        } else {
            // 기존 주소 수정 시 UPDATE 쿼리 실행
            $sql = "
                UPDATE
                    address
                SET
                    senderAddr = '{$senderAddr}',
                    senderAddrDetail = '{$senderAddrDetail}',
                    senderName = '{$senderName}',
                    senderTel = '{$senderTel}',
                    receiverAddr = '{$receiverAddr}',
                    receiverAddrDetail = '{$receiverAddrDetail}',
                    receiverName = '{$receiverName}',
                    receiverTel = '{$receiverTel}'
                WHERE
                    idx = '{$addressIdx}'
            ";
        }

        // 쿼리 실행 결과를 result 배열에 저장
        $result['result'] = $this->db->query($sql);

        // 쿼리 실행이 실패한 경우 에러 메시지 반환
        if (empty($result['result'])) {
            $result['msg'] = 'setAddress error';
            die(json_encode($result));
        }

        // 주소가 새로 추가된 경우 자동으로 선택 처리
        if (empty($addressIdx)) {            
            // 새로 추가된 주소의 ID를 가져옴
            $addressIdx = $this->db->insert_id();

            // 사용자가 저장한 주소 개수 확인
            $sql = "SELECT COUNT(*) AS cnt FROM address WHERE memberIdx = {$this->user['idx']} AND isUse = 'Y';";
            $query = $this->db->query($sql);
            $addressCnt = $query->row_array()['cnt'];

            // 저장된 주소가 1개일 경우 자동으로 선택
            if ($addressCnt == 1) {
                $this->choiceAddress($addressIdx, $this->user['idx']);
            }
        }

        // 결과를 JSON 형식으로 반환
        die(json_encode($result));        
    }

    
    public function deleteAddress()
    {
        // 결과 초기화
        $result = array('result' => false, 'msg' => "");

        // 삭제할 주소의 ID를 POST 데이터에서 받기
        $addressIdx = $_POST['addressIdx'];

        // 주소를 비활성화(isUse = 'N')로 설정하여 삭제 처리
        $sql = "
                UPDATE
                    address
                SET                 
                    isUse = 'N'
                WHERE
                    idx = '{$addressIdx}'
            ";

        // 쿼리 실행 결과를 result 배열에 저장
        $result['result'] = $this->db->query($sql);

        // 쿼리 실행 실패 시 에러 메시지 반환
        if (empty($result['result'])) {
            $result['msg'] = 'deleteAddress error';
            die(json_encode($result));
        }

        // 삭제된 주소가 기본 주소인 경우
        if ($addressIdx == $this->user['addressIdx']) {
            // 사용자가 가진 주소 중 활성 주소를 하나 조회
            $sql = "
                 SELECT
                    *
                 FROM
                    address
                 WHERE
                    memberIdx = '{$this->user['idx']}' AND
                    isUse = 'Y'
                ORDER BY
                    idx DESC
                LIMIT
                    1;
            ";

            $query = $this->db->query($sql);
            $info = $query->row_array();

            // 주소가 있으면 그 주소를 기본 주소로 설정
            if (!empty($info)) {
                // 첫 번째 주소를 강제로 선택
                $this->choiceAddress($info['idx'], $this->user['idx']);
            } else {
                // 더 이상 주소가 없으면 기본 주소를 0으로 설정
                $this->choiceAddress(0, $this->user['idx']);
            }
        }

        // 결과를 JSON 형식으로 반환
        die(json_encode($result));
    }

    public function changeAddress()
    {
        // 결과 초기화
        $result = array('result' => true, 'msg' => "");

        // 주소 변경을 위한 주소 ID를 POST 데이터에서 받기
        $addressIdx = $_POST['addressIdx'];

        // 선택된 주소를 기본 주소로 설정
        $this->choiceAddress($addressIdx, $this->user['idx']);

        // 결과를 JSON 형식으로 반환
        die(json_encode($result));
    }

    private function choiceAddress($addressIdx, $memberIdx)
    {
        // 선택된 주소의 정보를 조회
        $sql = "SELECT * FROM address WHERE idx = '{$addressIdx}'";
        $query = $this->db->query($sql);
        $info = $query->row_array();

        // 회원 테이블에서 해당 회원의 주소 정보를 업데이트
        $sql = "
            UPDATE 
                member_list 
            SET
                addressIdx = '{$addressIdx}',
                senderAddr = '{$info['senderAddr']}',
                senderAddrDetail = '{$info['senderAddrDetail']}',
                senderName = '{$info['senderName']}',
                senderTel = '{$info['senderTel']}',
                receiverAddr = '{$info['receiverAddr']}',
                receiverAddrDetail = '{$info['receiverAddrDetail']}',
                receiverName = '{$info['receiverName']}',
                receiverTel = '{$info['receiverTel']}'
            WHERE                            
                idx = '{$memberIdx}'";

        // 주소 정보 업데이트 쿼리 실행
        $this->db->query($sql);
    }

}

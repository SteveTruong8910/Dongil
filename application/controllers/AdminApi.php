<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AdminApi extends CI_Controller {

    public $user;  // 세션에서 사용자 정보를 저장할 변수

    // 생성자: 세션과 설정 파일을 로드합니다.
    public function __construct()
    {
        parent::__construct();

        $this->load->config('common');  // 공통 설정 파일 로드
        $this->user = $this->session->userdata('user');  // 세션에서 'user' 데이터를 불러옴
    }

    // 로그인 함수
    public function login()
    {
        $result = array('result' => false, 'msg' => "");  // 결과를 저장할 배열 초기화

        // POST 데이터에서 아이디와 비밀번호를 받아옴
        $id = $_POST['id'];
        $password = $_POST['password'];  

        // 아이디와 비밀번호가 일치하지 않으면 오류 메시지를 반환
        if(!($id == 'admin' && $password == 'admin2588')){
            $result['msg'] = "아이디 혹은 비밀번호가 일치하지않습니다.";  // 오류 메시지
            die(json_encode($result));  // JSON 형식으로 응답
        }

        // 로그인 성공 시, 세션에 저장할 사용자 데이터 설정
        $userData = array(
            'id' => $id,
            'maName' => '최고관리자',  // 사용자 이름
            'mbType' => 'admin'  // 관리자 타입
        );  

        // 세션에 사용자 데이터 저장
        $this->session->set_userdata('user', $userData); 

        $result['result'] = true;  // 로그인 성공
        die(json_encode($result));  // JSON 형식으로 성공 응답
    }
    
    public function readRegistrationNumber()
    {
        ini_set('memory_limit', '-1');
        
        require_once APPPATH . 'views/libs/PHPExcel-1.8/Classes/PHPExcel.php';                

        if (isset($_FILES['excelFile'])) {
            $file = $_FILES['excelFile']['tmp_name'];
            
            $orderIdArr = $_POST['orderIdArr'];
            
            try {
                // 업로드 된 엑셀 형식에 맞는 Reader 객체를 만든다.
                $objReader = PHPExcel_IOFactory::createReaderForFile($file);

                // 읽기전용으로 설정
                $objReader->setReadDataOnly(true);

                // 엑셀파일을 읽는다
                $objExcel = $objReader->load($file);

                // 첫번째 시트를 선택
                $objExcel->setActiveSheetIndex(0);
                $objWorksheet = $objExcel->getActiveSheet();
                $rowIterator = $objWorksheet->getRowIterator();

                $output = [];

                foreach ($rowIterator as $row) {
                    // 모든 행에 대해서
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                }

                $maxRow = $objWorksheet->getHighestRow();

                $successCnt = 0;
                $failCnt = 0;
                $orderIdIndex = 0;
                
                $orderCnt = count($orderIdArr);
                $rowCnt = $maxRow - 1;
                
                if($orderCnt != $rowCnt) {
                    echo json_encode(['result' => false, 'msg' => "출력된 테이블의 리스트의 갯수({$orderCnt}개)와 엑셀의 행의 갯수({$rowCnt}개)가 일치하지 않습니다."]);
                    exit;
                }
                
                for ($i = 2; $i <= $maxRow; $i++) {
                    $dateReceipt = $objWorksheet->getCell('B' . $i)->getValue(); // B열 - 사전접수일
                    $registrationNumber = $objWorksheet->getCell('F' . $i)->getValue(); // F열 - 등기번호
                    $special = $objWorksheet->getCell('K' . $i)->getValue(); // K열 - 특수취급
                    $isNotReturn = $objWorksheet->getCell('M' . $i)->getValue(); // M열 - 반송불필요
                    $isExpress = $objWorksheet->getCell('N' . $i)->getValue(); // N열 - 일일특급
                    $orderId = $orderIdArr[$orderIdIndex++];
                    
                    if(!empty($orderId)) {
                        $sql = "
                            UPDATE
                                write_list
                            SET
                                dateReceipt = '{$dateReceipt}',
                                registrationNumber = '{$registrationNumber}',
                                special = '{$special}',
                                isNotReturn = '{$isNotReturn}',
                                isExpress = '{$isExpress}'
                            WHERE
                                orderId = '{$orderId}'
                        ";                                                
                        
                        if($this->db->query($sql)) {
                            $successCnt++;
                        }else {
                            $failCnt++;
                        }
                    }else {
                        $failCnt++;
                    }
                    
                    // 데이터를 배열에 추가
                    $output[] = [
                        'F' => $registrationNumber
                    ];
                }

                echo json_encode([
                    'result' => true, 
                    'data' => $output, 
                    'totalCnt' => $successCnt + $failCnt,
                    'successCnt' => $successCnt,
                    'failCnt' => $failCnt
                ]);

            } catch (Exception $e) {
                echo json_encode(['result' => false, 'msg' => '엑셀파일을 읽는 도중 오류가 발생하였습니다.']);
            }
        } else {
            echo json_encode(['result' => false, 'msg' => '파일 데이터가 전송되지 않았습니다.']);
        }
    }
    
    public function readBatchRegistrationNumber()
    {
        ini_set('memory_limit', '-1');
        
        require_once APPPATH . 'views/libs/PHPExcel-1.8/Classes/PHPExcel.php';                

        if (isset($_FILES['excelFile'])) {
            $file = $_FILES['excelFile']['tmp_name'];

            try {
                // 업로드 된 엑셀 형식에 맞는 Reader 객체를 만든다.
                $objReader = PHPExcel_IOFactory::createReaderForFile($file);

                // 읽기전용으로 설정
                $objReader->setReadDataOnly(true);

                // 엑셀파일을 읽는다
                $objExcel = $objReader->load($file);

                // 첫번째 시트를 선택
                $objExcel->setActiveSheetIndex(0);
                $objWorksheet = $objExcel->getActiveSheet();
                $rowIterator = $objWorksheet->getRowIterator();

                $output = [];

                foreach ($rowIterator as $row) {
                    // 모든 행에 대해서
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                }

                $maxRow = $objWorksheet->getHighestRow();

                $successCnt = 0;
                $failCnt = 0;
                
                for ($i = 2; $i <= $maxRow; $i++) {
                    $orderId = $objWorksheet->getCell('A' . $i)->getValue(); // A열 - 주문번호
                    $shippingDate = $objWorksheet->getCell('B' . $i)->getValue(); // B열 - 발송일자
                    
                    if(!empty($orderId)) {
                        $sql = "
                            UPDATE
                                write_list
                            SET
                                shippingDate = '{$shippingDate}',
                                state = 'F'
                            WHERE
                                orderId = '{$orderId}'
                        ";                                                
                        
                        if($this->db->query($sql)) {
                            $successCnt++;
                        }else {
                            $failCnt++;
                        }
                    }else {
                        $failCnt++;
                    }
                    
                    // 데이터를 배열에 추가
                    $output[] = [
                        'A' => $registrationNumber,
                        'B' => $shippingDate
                    ];
                }

                echo json_encode([
                    'result' => true, 
                    'data' => $output, 
                    'totalCnt' => $successCnt + $failCnt,
                    'successCnt' => $successCnt,
                    'failCnt' => $failCnt
                ]);

            } catch (Exception $e) {
                echo json_encode(['result' => false, 'msg' => '엑셀파일을 읽는 도중 오류가 발생하였습니다.']);
            }
        } else {
            echo json_encode(['result' => false, 'msg' => '파일 데이터가 전송되지 않았습니다.']);
        }
    }

    
    public function upload()
    {
        ini_set('memory_limit', '-1');  // PHP의 메모리 제한을 해제하여 큰 파일도 처리할 수 있도록 설정

        $result = array('result' => false, 'msg' => "");  // 결과를 담을 배열 초기화

        // POST로 전달된 width 값이 없으면 기본값 500 설정
        $width = empty($_POST['width'])? 500 : $_POST['width'];

        // 업로드된 파일이 없으면 에러 메시지 반환
        if(empty($_FILES['files'])){
            $result['msg'] = '파일을 찾지 못했습니다.';
            die(json_encode($result));  // 결과를 JSON 형식으로 반환 후 종료
        }

        // 외부 라이브러리(이미지 크기 조정)를 로드
        $this->load->view('/libs/php-image-resize-master/ImageResize');

        $folderDir = "assets/upload";  // 업로드할 폴더 경로 설정
        // cmmFileUpload 함수를 사용하여 파일 업로드 처리
        $files = cmmFileUpload($_FILES['files'], $folderDir, $width);

        // 파일 업로드 중 문제가 발생하면 에러 메시지 반환
        if(!count($files)){
            $result['msg'] = '파일 업로드 도중 문제가 발생하였습니다.';
            die(json_encode($result));  // 결과를 JSON 형식으로 반환 후 종료
        }

        // 업로드된 파일 정보와 성공 결과를 반환
        $result['files'] = $files;
        $result['result'] = true;

        die(json_encode($result));  // 결과를 JSON 형식으로 반환 후 종료
    }
    
    public function setLetter()
    {
        // 결과를 저장할 배열 초기화
        $result = array('result' => false, 'msg' => "");

        // POST 데이터에서 입력값을 받음
        $cateIdx = $_POST['cateIdx'];  // 카테고리 인덱스
        $letterIdx = $_POST['letterIdx'];  // 편지 인덱스
        $name = $_POST['name'];  // 편지 이름
        $topPadding = $_POST['topPadding'];  // 상단 여백
        $contextWidth = $_POST['contextWidth'];  // 본문 너비
        $contextHeight = $_POST['contextHeight'];  // 본문 높이
        $contextLineHeight = $_POST['contextLineHeight'];  // 본문 라인 간격
        $price = $_POST['price'];  // 가격
        $maxLine = $_POST['maxLine'];  // 최대 라인 수
        $thumbnailImgArr = empty($_POST['thumbnailImgArr']) ? [] : $_POST['thumbnailImgArr'];  // 썸네일 이미지 배열
        $thumbnailImgArr = json_encode($thumbnailImgArr, JSON_UNESCAPED_UNICODE);  // 썸네일 이미지 배열을 JSON 형식으로 변환

        // 백그라운드 썸네일 이미지 배열 처리
        $thumbnailImgBackArr = empty($_POST['thumbnailImgBackArr']) ? [] : $_POST['thumbnailImgBackArr'];  
        $thumbnailImgBackArr = json_encode($thumbnailImgBackArr, JSON_UNESCAPED_UNICODE);  // 백그라운드 썸네일 이미지 배열을 JSON으로 변환

        // 원본 이미지 배열 처리
        $thumbnailImgOnebonArr = empty($_POST['thumbnailImgOnebonArr']) ? [] : $_POST['thumbnailImgOnebonArr'];  
        $thumbnailImgOnebonArr = json_encode($thumbnailImgOnebonArr, JSON_UNESCAPED_UNICODE);  // 원본 썸네일 이미지 배열을 JSON으로 변환

        // letterIdx가 비어있으면 insert, 있으면 update로 구분
        $settingType = empty($letterIdx) ? 'insert' : 'update';  

        // SQL 쿼리 문자열 생성
        $setSql = "
            `cateIdx` = '{$cateIdx}',
            `name` = '{$name}',
            `topPadding` = '{$topPadding}',
            `contextWidth` = '{$contextWidth}',
            `contextHeight` = '{$contextHeight}',
            `contextLineHeight` = '{$contextLineHeight}',
            `price` = '{$price}',
            `maxLine` = '{$maxLine}',
            `thumbnail` = '{$thumbnailImgArr}',
            `thumbnailBack` = '{$thumbnailImgBackArr}',
            `thumbnailOriginal` = '{$thumbnailImgOnebonArr}'
        ";

        // insert일 경우 새로운 레코드 삽입
        if ($settingType == 'insert') {
            $sql = "
                INSERT INTO letter_list SET $setSql
            ";
        } else {
            // update일 경우 기존 레코드 업데이트
            $sql = "
                UPDATE letter_list SET $setSql WHERE idx = '{$letterIdx}'
            ";
        }

        // SQL 쿼리 실행
        $result['result'] = $this->db->query($sql);

        // 쿼리 실행이 실패한 경우 오류 메시지 설정
        if (empty($result['result'])) {
            $result['msg'] = "setLetter error";  // 오류 발생 시 메시지
        }

        // 결과를 JSON 형식으로 반환
        die(json_encode($result));
    }
    
    public function saveAddr(){
        // POST로 전달된 데이터 받기
        $idx = $_POST['idx'];  // 대상 레코드 인덱스
        $type = $_POST['type'];  // 주소 유형 (sender 또는 receiver)
        $addr = $_POST['addr'];  // 주소
        $addrDetail = $_POST['addrDetail'];  // 상세 주소
        $name = $_POST['name'];  // 이름
        $tel = $_POST['tel'];  // 전화번호
        $memberIdx = $_POST['memberIdx'];  // 회원 인덱스

        // SQL 쿼리 초기화
        $sql = "";

        // 주소 유형에 따라 sender 또는 receiver 주소 업데이트
        if($type == 'sender') {
            // sender 주소를 업데이트하는 SQL
            $sql = "
                UPDATE
                    write_list
                SET
                    senderAddr = '{$addr}',
                    senderAddrDetail = '{$addrDetail}',
                    senderName = '{$name}',
                    senderTel = '{$tel}'
                WHERE
                    idx = '{$idx}'
            ";
        } else {
            // receiver 주소를 업데이트하는 SQL
            $sql = "
                UPDATE
                    write_list
                SET
                    receiverAddr = '{$addr}',
                    receiverAddrDetail = '{$addrDetail}',
                    receiverName = '{$name}',
                    receiverTel = '{$tel}'
                WHERE
                    idx = '{$idx}'
            ";
        }   

        // write_list 테이블에서 주소 업데이트 실행
        $result['result'] = $this->db->query($sql);

        // 업데이트가 실패한 경우 에러 메시지 설정
        if(empty($result['result'])){
            $result['msg'] = "saveAddr error";  // 주소 저장 중 오류
        }

        // 이제 member_list 테이블에 있는 주소를 업데이트
        if($type == 'sender') {
            // sender 주소를 업데이트하는 SQL
            $sql = "
                UPDATE
                    member_list
                SET
                    senderAddr = '{$addr}',
                    senderAddrDetail = '{$addrDetail}',
                    senderName = '{$name}',
                    senderTel = '{$tel}'
                WHERE
                    idx = '{$memberIdx}'
            ";
        } else {
            // receiver 주소를 업데이트하는 SQL
            $sql = "
                UPDATE
                    member_list
                SET
                    receiverAddr = '{$addr}',
                    receiverAddrDetail = '{$addrDetail}',
                    receiverName = '{$name}',
                    receiverTel = '{$tel}'
                WHERE
                    idx = '{$memberIdx}'
            ";
        }

        // member_list 테이블에서 주소 업데이트 실행
        $result['result'] = $this->db->query($sql);

        // 업데이트가 실패한 경우 에러 메시지 설정
        if(empty($result['result'])){
            $result['msg'] = "updateMemberAddr error";  // 회원 주소 업데이트 오류
        }

        // 결과를 JSON 형식으로 반환
        die(json_encode($result));
    }
    
    // 포인트를 변경하는 함수
    private function changePoint($memberIdx, $point) {
        // 해당 회원의 포인트를 변경하는 SQL 쿼리
        // 포인트는 기존 값에 추가된 포인트를 더하는 방식으로 업데이트
        $sql = "
                UPDATE
                    member_list
                SET
                    point = point + {$point}
                WHERE
                    idx = '{$memberIdx}'
        ";

        // SQL 쿼리 실행
        $this->db->query($sql);
    }

    // 포인트 로그를 추가하는 함수
    private function addPointLog($memberIdx, $writeIdx, $point, $ment, $addPoint = 0) {                
        // 포인트 변경 내역을 로그로 남기기 위한 SQL 쿼리
        // memberIdx는 회원 인덱스, writeIdx는 관련된 글 인덱스, point는 변경된 포인트 값, ment는 설명, addPoint는 보너스 포인트
        $sql = "
            INSERT INTO
                point_log
            SET
                memberIdx = '{$memberIdx}',
                writeIdx = '{$writeIdx}',
                point = '{$point}',
                bonus = '{$addPoint}',
                ment = '{$ment}'
        ";

        // SQL 쿼리 실행
        $this->db->query($sql);
    }
    
    public function changeState()
    {
        // 결과를 담을 배열 초기화
        $result = array('result' => false, 'msg' => "");

        // 요청된 writeIdx와 상태(state) 값 받기
        $writeIdx = $_POST['writeIdx'];
        $state = $_POST['state'];

        // 기본적으로 결제 상태는 'Y'로 설정
        $setSql = " isPay = 'Y' ";

        // 상태가 'F' (완료)일 경우
        if($state == 'F') {
            // 완료 날짜와 결제 여부를 업데이트
            $setSql .= ", finishDate = NOW(), isPaid = 'Y'";

            // 해당 writeIdx에 대한 정보를 가져오기 위한 쿼리
            $sql = "SELECT memberIdx, isPaid, addPoint FROM write_list WHERE idx = '{$writeIdx}'; ";
            $query = $this->db->query($sql);
            $writeInfo = $query->row_array();

            // 포인트지급이 안 된 경우 포인트 지급 처리
            if($writeInfo['isPaid'] == 'N' && (int)$writeInfo['addPoint'] > 0 && (int)$writeInfo['memberIdx'] > 0) {
                $this->changePoint($writeInfo['memberIdx'], $writeInfo['addPoint']);  // 포인트 변경
                $this->addPointLog($writeInfo['memberIdx'], $writeIdx, $writeInfo['addPoint'], '편지지 결제 페이백');  // 포인트 로그 추가
            }         
        }else if($state == 'T') {
            // 상태가 'T'일 경우 결제 미완료로 설정
            $setSql = " isPay = 'N', payIdx = 0 ";
        } else if($state == 'I') {
          $prevSaveCount = isset($_POST['prev_saveCount']) ? (int)$_POST['prev_saveCount'] : 0;
          $setSql .= ", prev_saveCount = '{$prevSaveCount}'";
        }

        // 상태 업데이트 쿼리 실행
        $sql = "
            UPDATE
                write_list
            SET
                state = '{$state}',
                $setSql
            WHERE
                idx = '{$writeIdx}'
        ";

        // 쿼리 실행 후 결과 체크
        $result['result'] = $this->db->query($sql);

        // 쿼리 실행에 실패한 경우 메시지 설정
        if(empty($result['result'])){
            $result['msg'] = "changeState error";
        }
        
        // 결과를 JSON 형식으로 반환
        die(json_encode($result));
    }
    
    public function changeAllState()
    {
        // 결과를 담을 배열 초기화
        $result = array('result' => false, 'msg' => "");

        // 요청된 writeIdx와 상태(state) 값 받기
        $writeIdxArr = $_POST['writeIdxArr'];
        $state = $_POST['state'];
        $prevSaveCounts = $_POST['prevSaveCounts'] ?? [];

        foreach($writeIdxArr as $writeIdx) {
            // 기본적으로 결제 상태는 'Y'로 설정
            $setSql = " isPay = 'Y' ";
            
            // 상태가 'F' (완료)일 경우
            if($state == 'F') {
                // 완료 날짜와 결제 여부를 업데이트
                $setSql .= ", finishDate = NOW(), isPaid = 'Y'";

                // 해당 writeIdx에 대한 정보를 가져오기 위한 쿼리
                $sql = "SELECT memberIdx, isPaid, addPoint FROM write_list WHERE idx = '{$writeIdx}'; ";
                $query = $this->db->query($sql);
                $writeInfo = $query->row_array();

                // 포인트지급이 안 된 경우 포인트 지급 처리
                if($writeInfo['isPaid'] == 'N' && (int)$writeInfo['addPoint'] > 0 && (int)$writeInfo['memberIdx'] > 0) {
                    $this->changePoint($writeInfo['memberIdx'], $writeInfo['addPoint']);  // 포인트 변경
                    $this->addPointLog($writeInfo['memberIdx'], $writeIdx, $writeInfo['addPoint'], '편지지 결제 페이백');  // 포인트 로그 추가
                }         
            }else if($state == 'T') {
                // 상태가 'T'일 경우 결제 미완료로 설정
                $setSql = " isPay = 'N', payIdx = 0 ";
            }

            if ($state == 'S' && isset($prevSaveCounts[$writeIdx])) {
              $setSql .= ", prev_saveCount = " . intval($prevSaveCounts[$writeIdx]);
            }

            // 상태 업데이트 쿼리 실행
            $sql = "
                UPDATE
                    write_list
                SET
                    state = '{$state}',
                    $setSql
                WHERE
                    idx = '{$writeIdx}'
            ";

            // 쿼리 실행 후 결과 체크
            $result['result'] = $this->db->query($sql);

            // 쿼리 실행에 실패한 경우 메시지 설정
            if(empty($result['result'])){
                $result['msg'] = "changeState error";
                // 결과를 JSON 형식으로 반환
                die(json_encode($result));   
            }            
        }
        
        die(json_encode($result));
    }
    
    public function changeCateName()
    {
        // 요청된 카테고리 인덱스와 새로운 카테고리 이름을 받음
        $cateIdx = $_POST['cateIdx'];
        $cateName = $_POST['cateName'];

        // 카테고리 이름을 업데이트하는 SQL 쿼리
        $sql = "
            UPDATE
                category_list
            SET
                cateName = '{$cateName}'
            WHERE
                idx = '{$cateIdx}'
        ";

        // 쿼리 실행 후 결과 저장
        $result['result'] = $this->db->query($sql);

        // 쿼리 실행에 실패한 경우 오류 메시지 설정
        if(empty($result['result'])){
            $result['msg'] = "changeCateName error";
        }            

        // 결과를 JSON 형식으로 반환
        die(json_encode($result));
    }

    public function setCategory()
    {
        // 요청된 새로운 카테고리 이름 받기
        $cateName = $_POST['cateName'];

        // 새로운 카테고리를 추가하는 SQL 쿼리
        $sql = "
            INSERT INTO
                category_list
            SET
                cateName = '{$cateName}'
        ";

        // 쿼리 실행 후 결과 저장
        $result['result'] = $this->db->query($sql);

        // 쿼리 실행에 실패한 경우 오류 메시지 설정
        if(empty($result['result'])){
            $result['msg'] = "setCategory error";
        }            

        // 새로 추가된 카테고리의 인덱스를 반환
        $result['cateIdx'] = $this->db->insert_id();

        // 결과를 JSON 형식으로 반환
        die(json_encode($result));
    }
    
    public function changeCategory()
    {
        // 요청된 카테고리 인덱스 배열을 받음
        $idxArr = $_POST['idxArr'];

        // 인덱스 배열에 대해 반복하면서 순서 변경
        for($i=0; $i<count($idxArr); $i++) {
            $idx = $idxArr[$i];

            // 카테고리 순서 변경 SQL 쿼리
            $sql = "
                UPDATE
                    category_list
                SET
                    sortOrder = {$i}
                WHERE
                    idx = '{$idx}'
            ";

            // 쿼리 실행 후 결과 저장
            $result['result'] = $this->db->query($sql);

            // 쿼리 실행에 실패한 경우 오류 메시지 설정
            if(empty($result['result'])){
                $result['msg'] = "changeCategory error";
            }
        }        

        // 결과를 JSON 형식으로 반환
        die(json_encode($result));
    }

    public function changeLetter()
    {
        // 요청된 편지 인덱스 배열을 받음
        $idxArr = $_POST['idxArr'];

        // 인덱스 배열에 대해 반복하면서 순서 변경
        for($i=0; $i<count($idxArr); $i++) {
            $idx = $idxArr[$i];

            // 편지 순서 변경 SQL 쿼리
            $sql = "
                UPDATE
                    letter_list
                SET
                    sortOrder = {$i}
                WHERE
                    idx = '{$idx}'
            ";

            // 쿼리 실행 후 결과 저장
            $result['result'] = $this->db->query($sql);

            // 쿼리 실행에 실패한 경우 오류 메시지 설정
            if(empty($result['result'])){
                $result['msg'] = "changeLetter error";
            }
        }

        // 결과를 JSON 형식으로 반환
        die(json_encode($result));
    }

    public function deleteCategory()
    {
        // 삭제할 카테고리 인덱스를 받음
        $cateIdx = $_POST['cateIdx'];

        // 카테고리 사용 여부를 'N'으로 설정하여 비활성화
        $sql = "
            UPDATE
                category_list
            SET
                isUse = 'N'
            WHERE
                idx = '{$cateIdx}'
        ";

        // 쿼리 실행 후 결과 저장
        $result['result'] = $this->db->query($sql);

        // 쿼리 실행에 실패한 경우 오류 메시지 설정
        if(empty($result['result'])){
            $result['msg'] = "deleteCategory error";
        }            

        // 결과를 JSON 형식으로 반환
        die(json_encode($result));
    }
    
    public function deleteLetter()
    {
        // 삭제할 편지 인덱스를 받음
        $letterIdx = $_POST['letterIdx'];

        // 편지 사용 여부를 'N'으로 설정하여 비활성화
        $sql = "
            UPDATE
                letter_list
            SET
                isUse = 'N'
            WHERE
                idx = '{$letterIdx}'
        ";

        // 쿼리 실행 후 결과 저장
        $result['result'] = $this->db->query($sql);

        // 쿼리 실행에 실패한 경우 오류 메시지 설정
        if(empty($result['result'])){
            $result['msg'] = "deleteLetter error";
        }            

        // 결과를 JSON 형식으로 반환
        die(json_encode($result));
    }

    public function setExcelDownDate()
    {
        // 요청된 작성 인덱스와 엑셀 다운로드 날짜를 받음
        $writeIdx = $_POST['writeIdx'];
        $excelDownDate = $_POST['excelDownDate'];

        // 엑셀 다운로드 날짜를 설정하는 SQL 쿼리
        $sql = "
            UPDATE
                write_list
            SET
                excelDownDate = '{$excelDownDate}'
            WHERE
                idx = '{$writeIdx}'
        ";

        // 쿼리 실행 후 결과 저장
        $result['result'] = $this->db->query($sql);

        // 쿼리 실행에 실패한 경우 오류 메시지 설정
        if(empty($result['result'])){
            $result['msg'] = "setExcelDownDate error";
        }            

        // 결과를 JSON 형식으로 반환
        die(json_encode($result));
    }
    
    public function setRegistrationNumber()
    {
        // 요청된 작성 인덱스와 등록 번호를 받음
        $writeIdx = $_POST['writeIdx'];
        $registrationNumber = $_POST['registrationNumber'];

        // 등록 번호를 업데이트하는 SQL 쿼리
        $sql = "
            UPDATE
                write_list
            SET
                registrationNumber = '{$registrationNumber}'
            WHERE
                idx = '{$writeIdx}'
        ";

        // 쿼리 실행 후 결과 저장
        $result['result'] = $this->db->query($sql);

        // 쿼리 실행에 실패한 경우 오류 메시지 설정
        if(empty($result['result'])){
            $result['msg'] = "setRegistrationNumber error";
        }            

        // 결과를 JSON 형식으로 반환
        die(json_encode($result));
    }

    
    public function exportExcel()
    {
        ini_set('memory_limit', '-1');

        $result = array('result' => false, 'msg' => "");

        $writeIdxArr = $_POST['writeIdxArr'];
        $stampName = $_POST['stampName'];
        $excelDownDate = $_POST['excelDownDate'];
        $fileName = $_POST['fileName'];

        $index = 0;
        foreach($writeIdxArr as $writeIdx) {
            // 각 writeIdx에 대해 excelDownDate 업데이트
            $sql = "
                UPDATE
                    write_list
                SET
                    excelDownDate = '{$excelDownDate}'
                WHERE
                    idx = '{$writeIdx}'
            ";

            $result['result'] = $this->db->query($sql);
            if(empty($result['result'])){
                $result['msg'] = "exportExcel error - 1";
            }

            // 업데이트된 excelDownDate를 배열에 추가
            $excelDownDateArr[] = $excelDownDate;
            $index++;
        }

        // exceldown_date 테이블에 새 레코드 삽입
        $sql = "
            INSERT INTO
                exceldown_date
            SET
                stampName = '{$stampName}',
                excelDownDate = '{$excelDownDate}',
                fileName = '{$fileName}'
        ";

        $result['result'] = $this->db->query($sql);
        if(empty($result['result'])){
            $result['msg'] = "exportExcel error - 2";
        }            

        // 성공적인 결과와 다운로드 날짜 반환
        $result['excelDownDate'] = $excelDownDateArr;

        die(json_encode($result));
    }

    public function getExcelDate()
    {
        $result = array('result' => true, 'msg' => "");

        $fileName = [];
        $stampArrName = ['준 등기우편', '등기우편', '익일특급'];

        for($i=0; $i<=2; $i++) {
            $stampName = $stampArrName[$i];

            // exceldown_date 테이블에서 최신 파일 이름 가져오기
            $sql = "SELECT fileName FROM exceldown_date WHERE stampName = '{$stampName}' ORDER BY idx DESC LIMIT 1; ";
            $query = $this->db->query($sql);
            $fileName = empty($query->row_array()['fileName'])? '-' : $query->row_array()['fileName'];

            $fileArrName[] = $fileName;
        }

        $result['fileName'] = $fileArrName;
        die(json_encode($result));
    }
    
    public function restorationMember()
    {
        $result = array('result' => true, 'msg' => "");

        $memberIdx = $_POST['memberIdx'];

        // 해당 멤버의 isUse 값을 'Y'로 변경하여 복구 처리
        $sql = "
            UPDATE
                member_list
            SET
                isUse = 'Y'
            WHERE
                idx = '{$memberIdx}'
        ";

        $result['result'] = $this->db->query($sql);

        if(empty($result['result'])){
            $result['msg'] = "restorationMember error";
        }

        die(json_encode($result));
    }

    public function savePopup()
    {
        $result = array('result' => false, 'msg' => "");

        // 전달받은 썸네일 이미지 배열을 JSON 형식으로 인코딩
        $thumbnailImgArr = empty($_POST['thumbnailImgArr'])? [] : $_POST['thumbnailImgArr']; 
        $thumbnailImgArr = json_encode($thumbnailImgArr, JSON_UNESCAPED_UNICODE);                                

        // popup 테이블에 썸네일 이미지 저장
        $sql = "
            INSERT INTO
                popup
            SET                    
                `thumbnail` = '{$thumbnailImgArr}'
        ";

        $result['result'] = $this->db->query($sql);

        if(empty($result['result'])){
            $result['msg'] = "savePopup error";
        }        

        die(json_encode($result));
    }
    
    public function setPopup()
    {
        $popupIdx = $_POST['popupIdx'];
        $type = $_POST['type'];
        $value = $_POST['value'];

        // 주어진 팝업 인덱스를 기준으로 해당 타입에 값을 업데이트
        $sql = "
            UPDATE
                popup
            SET
                $type = '{$value}'
            WHERE
                idx = '{$popupIdx}'
        ";

        $result['result'] = $this->db->query($sql);

        if(empty($result['result'])){
            $result['msg'] = "setPopup error";
        }

        die(json_encode($result));
    }

    public function popupUpload()
    {
        ini_set('memory_limit', '-1');

        $result = array('result' => false, 'msg' => "");

        $width = empty($_POST['width'])? 1000 : $_POST['width'];

        // 파일이 업로드되지 않았을 경우 에러 메시지 반환
        if(empty($_FILES['files'])){
            $result['msg'] = '파일을 찾지 못했습니다.';
            die(json_encode($result));
        }

        // 이미지 리사이즈 라이브러리 로드
        $this->load->view('/libs/php-image-resize-master/ImageResize');

        $folderDir = "assets/upload/popup"; // 업로드 폴더 경로
        $files = cmmFileUpload($_FILES['files'], $folderDir, $width); // 파일 업로드 함수 호출

        // 파일 업로드에 실패한 경우 에러 메시지 반환
        if(!count($files)){
            $result['msg'] = '파일 업로드 도중 문제가 발생하였습니다.';
            die(json_encode($result));
        }

        // 업로드된 파일 목록 반환
        $result['files'] = $files;
        $result['result'] = true;

        die(json_encode($result));
    }

    public function registerMailbox()
    {
        $result = array('result' => false, 'msg' => '');

        // 필수값 체크
        $memberIdx = $_POST['memberIdx'] ?? '';
        $senderName = $_POST['senderName'] ?? '';
        $senderAddress = $_POST['senderAddress'] ?? '';

        if (empty($memberIdx)) {
            $result['msg'] = '회원 번호가 누락되었습니다.';
            die(json_encode($result));
        }

        // DB에 등록
        $sql = "
            INSERT INTO
                mailbox_list
            SET
                memberIdx = '{$memberIdx}',
                senderName = '{$senderName}',
                senderAddress = '{$senderAddress}',
                isUse = 'Y',
                regDate = NOW()
        ";

        $result['result'] = $this->db->query($sql);

        if (!$result['result']) {
            $result['msg'] = 'DB 저장 중 오류가 발생했습니다.';
        }

        die(json_encode($result));
    }


    public function lockOrders() {
        header('Content-Type: application/json');
    
        $writeIdxArr = $this->input->post('writeIdxArr');
    
        if (!is_array($writeIdxArr) || count($writeIdxArr) === 0) {
            echo json_encode(['result' => false, 'msg' => '선택된 항목이 없습니다.']);
            return;
        }
    
        foreach ($writeIdxArr as $idx) {
            $this->db->where('idx', $idx);
            $this->db->update('write_list', ['isLocked' => 'Y']);
        }
    
        echo json_encode(['result' => true]);
    }

    public function unlockOrders() {
        $writeIdxArr = $this->input->post('writeIdxArr');
        if (!$writeIdxArr || !is_array($writeIdxArr)) {
            echo json_encode(['result' => false, 'msg' => '잘못된 요청입니다.']); return;
        }

        foreach ($writeIdxArr as $idx) {
            $this->db->where('idx', $idx)->update('write_list', ['isLocked' => 'N']);
        }

        echo json_encode(['result' => true]);
    }

    public function setTrackingNumber()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $mailboxIdx = $data['mailboxIdx'];
        $deliveryCompany = $data['deliveryCompany'];
        $trackingNumber = $data['trackingNumber'];

        $result = ['result' => false, 'msg' => ''];

        if (!$mailboxIdx || !$trackingNumber || !$deliveryCompany) {
            $result['msg'] = '필수 정보 누락';
            die(json_encode($result));
        }

        $this->db->where('idx', $mailboxIdx);
        $update = $this->db->update('mailbox_list', [
            'deliveryCompany' => $deliveryCompany,
            'trackingNumber' => $trackingNumber,
            'deliveryDate' => date('Y-m-d H:i:s'),
            'isDelivered' => 'Y'
        ]);

        $result['result'] = $update;
        if (!$update) {
            $result['msg'] = 'DB 업데이트 실패';
        }

        die(json_encode($result));
    }

    public function confirmScanDeposit() {
        $input = json_decode(file_get_contents('php://input'), true);
        $idx = $input['idx'] ?? 0;
    
        if (!$idx) {
            echo json_encode(['result' => false, 'msg' => '잘못된 요청입니다.']);
            return;
        }
    
        $this->db->where('idx', $idx)->update('mailbox_list', [
            'isPaidScan' => 'Y'
        ]);
    
        echo json_encode(['result' => true]);
    }
    
    public function searchMembersByName() {
        $json = json_decode(file_get_contents("php://input"), true);
        $keyword = $json['keyword'];
    
        $query = "SELECT idx, senderName, senderTel, receiverAddr, receiverAddrDetail FROM member_list WHERE senderName LIKE ? LIMIT 20";
        $result = $this->db->query($query, ["%{$keyword}%"])->result_array();
    
        echo json_encode([
            'result' => true,
            'data' => $result
        ]);
    }

    public function uploadMailboxPdf()
    {
        $result = array('result' => false, 'msg' => "");

        log_message('debug', '📤 [uploadMailboxPdf] 호출됨');
        log_message('debug', '📤 mailboxIdx: ' . ($_POST['mailboxIdx'] ?? '없음'));
        log_message('debug', '📤 FILES: ' . print_r($_FILES, true));

        // 필수 파라미터 확인
        $mailboxIdx = $_POST['mailboxIdx'] ?? '';
        if (empty($mailboxIdx)) {
            $result['msg'] = '사서함 인덱스가 누락되었습니다.';
            die(json_encode($result));
        }

        // 파일 유무 확인
        if (empty($_FILES['pdfFile']) || $_FILES['pdfFile']['error'] !== UPLOAD_ERR_OK) {
            $result['msg'] = '파일 업로드에 문제가 발생했습니다. (에러 코드: '.$_FILES['pdfFile']['error'].')';
            die(json_encode($result));
        }

        // 파일 형식 확인
        $fileType = strtolower(pathinfo($_FILES['pdfFile']['name'], PATHINFO_EXTENSION));
        if ($fileType !== 'pdf') {
            $result['msg'] = 'PDF 파일만 업로드 가능합니다.';
            die(json_encode($result));
        }

        // 업로드 디렉토리 설정 및 생성
        $uploadDir = FCPATH . 'assets/mailbox/'; // FCPATH는 CodeIgniter의 기본 상수 (프로젝트 루트)
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // 고유 파일명 생성
        $fileName = time() . '_' . bin2hex(random_bytes(4)) . '_' . $_FILES['pdfFile']['name'];
        $uploadPath = $uploadDir . $fileName;

        // 파일 이동
        if (move_uploaded_file($_FILES['pdfFile']['tmp_name'], $uploadPath)) {
            // DB 업데이트
            $data = ['pdfPath' => $fileName];
            $this->db->where('idx', $mailboxIdx);
            $update = $this->db->update('mailbox_list', $data);

            if ($update) {
                $result['result'] = true;
                $result['msg'] = '파일 업로드 및 저장 완료';
            } else {
                // 실패 시 업로드된 파일 삭제
                unlink($uploadPath);
                $result['msg'] = 'DB 업데이트 실패: ' . $this->db->error()['message'];
            }
        } else {
            $result['msg'] = '파일 이동에 실패했습니다. (권한 문제일 수 있습니다)';
        }

        die(json_encode($result));
    }

    public function updateScanStatus()
    {
        $json = json_decode(file_get_contents('php://input'), true);
        $idx = (int)$json['idx'];
        $status = (int)$json['status'];

        log_message('error', '[SCAN_STATUS] idx=' . $idx . ', status=' . $status);

        if (!$idx && $idx !== 0) {
            echo json_encode(['result' => false, 'msg' => '잘못된 idx']);
            return;
        }

        $row = $this->db->get_where('mailbox_list', ['idx' => $idx])->row_array();
        if (!$row) {
            echo json_encode(['result' => false, 'msg' => '해당 사서함 없음']);
            return;
        }

        // scan_status에 따른 isUse 설정
        $isUse = ($status == 3) ? 'N' : 'Y';

        // 업데이트
        $this->db->where('idx', $idx)->update('mailbox_list', [
            'scan_status' => $status,
            'isUse' => $isUse
        ]);

        echo json_encode(['result' => true]);
    }

    public function setLibraryCategory()
    {
        $cateName = $this->input->post('cateName');

        if (!$cateName) {
            echo json_encode(['result' => false, 'msg' => '카테고리 이름이 없습니다.']);
            return;
        }

        // 현재 가장 큰 sortOrder 값 가져오기
        $query = $this->db->query("SELECT MAX(sortOrder) AS maxSort FROM library_category");
        $maxSort = $query->row()->maxSort ?? 0;

        // 새 sortOrder 값은 maxSort + 1
        $sortOrder = $maxSort + 1;

        $sql = "INSERT INTO library_category (cateName, sortOrder) VALUES (?, ?)";
        $this->db->query($sql, [$cateName, $sortOrder]);

        $newIdx = $this->db->insert_id();

        echo json_encode(['result' => true, 'categoryIdx' => $newIdx]);
    }



    public function setLibrary()
    {
        $libraryIdx = $this->input->post('libraryIdx');
        $title = $this->input->post('title');
        $price = $this->input->post('price');
        $categoryIdx = $this->input->post('categoryIdx');
        $pageCount = isset($_POST['pageCount']) ? $_POST['pageCount'] : 0;

        // 파일 업로드
        if (isset($_FILES['pdf']) && $_FILES['pdf']['size'] > 0) {
            $uploadPath = './assets/upload/';
            $fileName = time().'_'.rand(100000,999999).'.pdf';
            $fileFullPath = $uploadPath.$fileName;
        
            move_uploaded_file($_FILES['pdf']['tmp_name'], $fileFullPath);
            $filePath = '/assets/upload/'.$fileName;
        
            // ✅ 클라이언트에서 보낸 base64 썸네일 저장
            $thumbPath = null;
            if (!empty($_POST['thumbnailBase64'])) {
                $base64 = $_POST['thumbnailBase64'];
                if (preg_match('/^data:image\/jpeg;base64,/', $base64)) {
                    $base64 = str_replace('data:image/jpeg;base64,', '', $base64);
                    $base64 = str_replace(' ', '+', $base64);
                    $data = base64_decode($base64);
        
                    if ($data !== false) {
                        $thumbName = time() . '_thumb.jpg';
                        $thumbFullPath = $uploadPath . $thumbName;
                        file_put_contents($thumbFullPath, $data);
                        $thumbPath = '/assets/upload/' . $thumbName;
                    }
                }
            }
        } else {
            $filePath = null;
            $thumbPath = null;
        }
        

        if ($libraryIdx == 0) {
            // ✅ 현재 카테고리 내에서 가장 큰 sortOrder 값 가져오기
            $query = $this->db->query("SELECT MAX(sortOrder) AS maxSort FROM library_list WHERE categoryIdx = ?", [$categoryIdx]);
            $maxSort = $query->row()->maxSort ?? 0;
            $sortOrder = $maxSort + 1;

            $this->db->insert('library_list', [
                'title' => $title,
                'categoryIdx' => $categoryIdx,
                'filePath' => $filePath,
                'thumbPath' => $thumbPath,
                'originalFileName' => $_FILES['pdf']['name'],
                'price' => $price,
                'pageCount' => $pageCount,
                'regDate' => date('Y-m-d H:i:s'),
                'isUse' => 'Y',
                'sortOrder' => $sortOrder // ✅ 추가
            ]);
        } else {
            // 수정
            $updateData = [
                'title' => $title,
                'categoryIdx' => $categoryIdx,
                'price' => $price,
                'pageCount' => $pageCount
            ];

            if ($filePath) {
                $updateData['filePath'] = $filePath;
                $updateData['thumbPath'] = $thumbPath;
                $updateData['originalFileName'] = $_FILES['pdf']['name'];
            }

            $this->db->where('idx', $libraryIdx)->update('library_list', $updateData);
        }

        echo json_encode(['result' => true]);
    }


    public function deleteLibrary()
    {
        $json = json_decode(file_get_contents("php://input"), true);
        $libraryIdx = $json['libraryIdx'] ?? 0;

        if (!$libraryIdx) {
            echo json_encode(['result' => false, 'msg' => '자료 인덱스가 없습니다.']);
            return;
        }

        // 실제 삭제 대신 isUse = 'N' 처리
        $this->db->where('idx', $libraryIdx);
        $result = $this->db->update('library_list', ['isUse' => 'N']);

        echo json_encode(['result' => $result]);
    }

    public function deleteLibraryCategory()
    {
        // 삭제할 카테고리 인덱스를 받음
        $categoryIdx = $_POST['categoryIdx'];

        // 카테고리 사용 여부를 'N'으로 설정하여 비활성화
        $sql = "
            UPDATE
                library_category
            SET
                isUse = 'N'
            WHERE
                idx = '{$categoryIdx}'
        ";

        // 쿼리 실행 후 결과 저장
        $result['result'] = $this->db->query($sql);

        // 쿼리 실행에 실패한 경우 오류 메시지 설정
        if(empty($result['result'])) {
            $result['msg'] = "deleteLibraryCategory error";
        }

        // 결과를 JSON 형식으로 변환
        die(json_encode($result));
    }

    public function changeLibraryCateName()
    {
        // 요청된 카테고리 인덱스와 새로운 카테고리 이름을 받음
        $categoryIdx = $_POST['categoryIdx'];
        $cateName = $_POST['cateName'];

        // 카테고리 이름을 업데이트하는 SQL 쿼리
        $sql = "
            UPDATE
                library_category
            SET
                cateName = '{$cateName}'
            WHERE
                idx = '{$categoryIdx}'
        ";

        // 쿼리 실행 후 결과 저장
        $result['result'] = $this->db->query($sql);

        // 쿼리 실행에 실패한 경우 오류 메시지 설정
        if(empty($result['result'])){
            $result['msg'] = "changeLibraryCateName error";
        }            

        // 결과를 JSON 형식으로 반환
        die(json_encode($result));
    }

    public function changeLibraryCategory()
    {
        // 요청된 카테고리 인덱스 배열을 받음
        $idxArr = $_POST['idxArr'];

        // 인덱스 배열에 대해 반복하면서 순서 변경
        for($i=0; $i<count($idxArr); $i++) {
            $idx = $idxArr[$i];

            // 카테고리 순서 변경 SQL 쿼리
            $sql = "
                UPDATE
                    library_category
                SET
                    sortOrder = {$i}
                WHERE
                    idx = '{$idx}'
            ";

            // 쿼리 실행 후 결과 저장
            $result['result'] = $this->db->query($sql);

            // 쿼리 실행에 실패한 경우 오류 메시지 설정
            if(empty($result['result'])){
                $result['msg'] = "changeLibraryCategory error";
            }
        }        

        // 결과를 JSON 형식으로 반환
        die(json_encode($result));
    }

    public function changeLibrary()
    {
        // 요청된 편지 인덱스 배열을 받음
        $idxArr = $_POST['idxArr'];

        // 인덱스 배열에 대해 반복하면서 순서 변경
        for($i=0; $i<count($idxArr); $i++) {
            $idx = $idxArr[$i];

            // 편지 순서 변경 SQL 쿼리
            $sql = "
                UPDATE
                    library_list
                SET
                    sortOrder = {$i}
                WHERE
                    idx = '{$idx}'
            ";

            // 쿼리 실행 후 결과 저장
            $result['result'] = $this->db->query($sql);

            // 쿼리 실행에 실패한 경우 오류 메시지 설정
            if(empty($result['result'])){
                $result['msg'] = "changeLibrary error";
            }
        }

        // 결과를 JSON 형식으로 반환
        die(json_encode($result));
    }
}

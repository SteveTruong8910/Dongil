<?

/* 유저 정보 가져오기 */
function getUserInfo($info, $db){
    // 입력 정보가 없거나 'idx' 값이 없는 경우 null 반환
    if(empty($info) || empty($info['idx'])){
        return null;  // 잘못된 입력이므로 null 반환
    }    
    
    // 데이터베이스에서 주어진 idx 값을 기준으로 회원 정보를 조회
    $sql = "SELECT * FROM member_list WHERE idx = '{$info['idx']}' ";        
    $query = $db->query($sql);  // SQL 쿼리 실행
    $userInfo = $query->row_array();  // 조회된 결과를 배열로 반환
    
    // 조회된 회원 정보가 있고, 'idx'가 3이(테스트 계정) 아닌 경우에만 마지막 활동 시간을 업데이트
    if(!empty($userInfo) && $info['idx'] != 3) {
        // 'lastActivityDate' 필드를 현재 시간으로 업데이트
        $sql = "UPDATE member_list SET lastActivityDate = NOW() WHERE idx = '{$info['idx']}' ";
        $db->query($sql);  // SQL 쿼리 실행
    }
    
    // 조회된 회원 정보 반환
    return $userInfo;  
}

/* 주소 자동 저장 */
function autoSaveAddress($db, $writeIdx){            
    // 'writeIdx'를 사용하여 'write_list' 테이블에서 해당 글의 정보를 조회
    $sql = "SELECT * FROM write_list WHERE idx = '{$writeIdx}'";
    $query = $db->query($sql);  // SQL 쿼리 실행
    $info = $query->row_array();  // 조회된 결과를 배열로 반환
    
    // 'isSaveAddress'가 'Y' 또는 'isDefaultAddress'가 'Y'일 경우 주소를 저장
    if($info['isSaveAddress'] == 'Y' || $info['isDefaultAddress'] == 'Y') {
        // 주소 정보를 'address' 테이블에 삽입
        $sql = "
            INSERT INTO
                address
            SET
                memberIdx = '{$info['memberIdx']}',
                senderAddr = '{$info['senderAddr']}',
                senderAddrDetail = '{$info['senderAddrDetail']}',
                senderName = '{$info['senderName']}',
                senderTel = '{$info['senderTel']}',
                receiverAddr = '{$info['receiverAddr']}',
                receiverAddrDetail = '{$info['receiverAddrDetail']}',
                receiverName = '{$info['receiverName']}',
                receiverTel = '{$info['receiverTel']}',
                displaySenderPhone = '{$info['displaySenderPhone']}'
        ";
        $db->query($sql);  // SQL 쿼리 실행
    }
    
    // 'isDefaultAddress'가 'Y'인 경우 해당 주소를 기본 주소로 설정
    if($info['isDefaultAddress'] == 'Y') {
        // 새로 삽입된 주소의 'addressIdx' 값을 가져옴
        $addressIdx = $db->insert_id();                        

        // 'member_list' 테이블에서 해당 사용자의 기본 주소를 업데이트
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
                idx = '{$info['memberIdx']}'";
        $db->query($sql);  // SQL 쿼리 실행
    }
}

/**
 * 주소에서 '사서함' 뒤의 숫자 패턴과 우편번호를 제거하는 함수
 *
 * @param string $address 주소 문자열
 * @param bool $isPostCode 우편번호를 제거할지 여부 (기본값: true)
 * @return string 처리된 새로운 주소 문자열
 */
function removePostboxNumbers($address, $isPostCode = true) {
    // 정규식을 사용하여 '사서함' 뒤의 숫자 패턴을 찾고 제거합니다.
    // '사서함'과 그 뒤에 붙은 숫자 또는 범위 표현 (예: 사서함 1234-5678 ~ 8901)을 '사서함'으로 대체
    $regex = '/사서함\s\d+(-\d+)?(\s~\s\d+(-\d+)?)?/';
    $newAddress = preg_replace($regex, '사서함', $address);
    
    // 우편번호 제거가 필요하지 않은 경우
    if(!$isPostCode) {
        // 정규식을 사용하여 우편번호 패턴 (예: (12345))을 찾아 제거합니다.
        $regexPostCode = '/^\(\d{5}\)\s?/';
        $newAddress = preg_replace($regexPostCode, '', $newAddress);
    }

    // 처리된 주소 반환
    return $newAddress;
}


/**
 * 파일 업로드 및 이미지 리사이징 함수
 * 
 * @param array $files 업로드된 파일 정보
 * @param string $folderDir 업로드된 파일을 저장할 폴더 경로
 * @param int $width 리사이징할 이미지의 너비 (기본값: 700)
 * @param int $height 리사이징할 이미지의 높이 (기본값: 0, 비율 유지)
 * @return array 업로드된 파일의 정보 배열
 */
function cmmFileUpload($files, $folderDir, $width = 700, $height = 0){
    ini_set('memory_limit', '-1');  // 메모리 제한 해제
    $fileArr = array();  // 업로드된 파일 정보 배열
    $filesLength = count($files['name']);  // 업로드된 파일 개수
    
    // 각 파일에 대해 처리
    for($index=0; $index < $filesLength; $index++){
                  
        $imgWidth = 0;
        $imgHeight = 0;
        $resizeWidth = $width;  // 리사이징할 이미지 너비
        $resizeHeight = $height;  // 리사이징할 이미지 높이
        
        // 이미지 정보 가져오기
        $imageInfo = getimagesize($files['tmp_name'][$index]);
            
        $imgWidth = $imageInfo[0];  // 이미지 가로 크기
        $imgHeight = $imageInfo[1]; // 이미지 세로 크기
        
        // 업로드 경로 설정
        $uploadsDir = FCPATH . $folderDir;  // 업로드 폴더 경로
        $originalFileName = $files['name'][$index];  // 원본 파일 이름
                
        // 파일 확장자 추출
        $parts = explode('.', $originalFileName);
        $ext = array_pop($parts);
        
        // 유니크한 파일 이름 생성 (타임스탬프 + 랜덤 숫자 + 인덱스)
        $num = sprintf("%06d", rand(0000, 9999));
        $fileName = strtotime("Now").'_'.$num.'_'.$index.'.'.$ext;
        $filePath = $uploadsDir.'/'.$fileName;
        
        // 파일을 업로드 경로에 이동
        if(move_uploaded_file($files['tmp_name'][$index], $filePath)){
            
            // 원본 파일을 'onebon_' 접두어로 같은 폴더에 복사
            $newFileName = 'onebon_' . $fileName;
            $newFilePath = $uploadsDir . '/' . $newFileName;
            copy($filePath, $newFilePath);
            
            // 이미지 리사이즈 및 저장 (Gumlet 라이브러리 사용)
            $saveImg = new \Gumlet\ImageResize($newFilePath);
            $saveImg -> save($newFilePath);
            
            // 파일 타입 확인 (이미지, 비디오 등)
            $fileType = explode('/', $files['type'][$index])[0];
            
            // 이미지 파일인 경우 리사이징 처리
            if($fileType == 'image'){
                $resizeImg = new \Gumlet\ImageResize($filePath);
                
                if($width > 0){  // 너비 리사이징
                    $resizeImg ->resizeToWidth($resizeWidth);
                }
                if($height > 0){  // 높이 리사이징
                    $resizeImg ->resizeToHeight($resizeHeight);
                }
                
                // 리사이징된 이미지 저장
                $resizeImg -> save($filePath);
            }
            
            // 업로드된 파일 정보 배열에 추가
            $fileArr[] = array(
                'ext' => $ext,  // 파일 확장자
                'fileType' => $fileType,  // 파일 타입
                'onebonFileName' => $newFileName,  // 새 파일 이름
                'fileName' => $fileName,  // 원본 파일 이름
                'originalFileName' => $originalFileName,  // 업로드된 파일의 원본 이름
                'originWidth' => $imgWidth,  // 원본 이미지 가로 크기
                'originHeight' => $imgHeight,  // 원본 이미지 세로 크기
                'resizeWidth' => $width,  // 리사이징된 이미지 너비
                'resizeHeight' => $height,  // 리사이징된 이미지 높이
            );
        }
    }
    
    // 업로드된 파일 정보 배열 반환
    return $fileArr;
}

/**
 * 포토 이미지 업로드 및 리사이징 함수
 * 
 * @param array $files 업로드된 파일 정보
 * @param string $folderDir 업로드된 파일을 저장할 폴더 경로
 * @param int $width 리사이징할 이미지의 너비
 * @param int $height 리사이징할 이미지의 높이
 * @return array 업로드된 파일의 정보 배열
 */
function cmmPhotoUpload($files, $folderDir, $width, $height){
    ini_set('memory_limit', '-1');  // 메모리 제한 해제
    $fileArr = array();  // 업로드된 파일 정보 배열
    $filesLength = count($files['name']);  // 업로드된 파일 개수
    
    // 각 파일에 대해 처리
    for($index=0; $index < $filesLength; $index++){
                  
        $imgWidth = 0;
        $imgHeight = 0;
        $resizeWidth = $width;  // 리사이징할 이미지 너비
        $resizeHeight = $height;  // 리사이징할 이미지 높이
        
        // 이미지 정보 가져오기
        $imageInfo = getimagesize($files['tmp_name'][$index]);
            
        $imgWidth = $imageInfo[0];  // 이미지 가로 크기
        $imgHeight = $imageInfo[1]; // 이미지 세로 크기
        
        // 업로드 경로 설정
        $uploadsDir = FCPATH . $folderDir;  // 업로드 폴더 경로
        $originalFileName = $files['name'][$index];  // 원본 파일 이름
                
        // 파일 확장자 추출
        $parts = explode('.', $originalFileName);
        $ext = array_pop($parts);
        
        // 유니크한 파일 이름 생성 (타임스탬프 + 랜덤 숫자 + 인덱스)
        $num = sprintf("%06d", rand(0000, 9999));
        $fileName = strtotime("Now").'_'.$num.'_'.$index.'.'.$ext;
        $filePath = $uploadsDir.'/'.$fileName;
        
        // 파일을 업로드 경로에 이동
        if(move_uploaded_file($files['tmp_name'][$index], $filePath)){
            
            // 파일 타입 확인 (이미지, 비디오 등)
            $fileType = explode('/', $files['type'][$index])[0];
            
            // 이미지 파일인 경우 리사이징 처리
            if($fileType == 'image'){
                $resizeImg = new \Gumlet\ImageResize($filePath);
                
                // 이미지 리사이징: 가로와 세로 중 큰 값을 기준으로 리사이징
                $resizeImg->resize($imgWidth > $imgHeight ? $width : $height, $imgWidth > $imgHeight ? $height : $width);
                
                // 리사이징된 이미지 저장
                $resizeImg->save($filePath);
            }
            
            // 업로드된 파일 정보 배열에 추가
            $fileArr[] = array(
                'ext' => $ext,  // 파일 확장자
                'fileType' => $fileType,  // 파일 타입
                'onebonFileName' => $fileName,  // 새 파일 이름
                'fileName' => $fileName,  // 원본 파일 이름
                'originalFileName' => $originalFileName,  // 업로드된 파일의 원본 이름
                'originWidth' => $imgWidth,  // 원본 이미지 가로 크기
                'originHeight' => $imgHeight,  // 원본 이미지 세로 크기
                'resizeWidth' => $width,  // 리사이징된 이미지 너비
                'resizeHeight' => $height,  // 리사이징된 이미지 높이
            );
        }
    }
    
    // 업로드된 파일 정보 배열 반환
    return $fileArr;
}

/**
 * 페이지와 항목 번호에 해당하는 테이블 번호를 반환합니다.
 * 
 * @param int $page 현재 페이지 번호
 * @param int $pagingCount 한 페이지당 항목 수
 * @param int $count 현재 항목의 인덱스
 * @return string 형식화된 테이블 번호
 */
function getTableNumber($page, $pagingCount, $count){
    return number_format(($page - 1) * $pagingCount + $count + 1);  // 테이블 번호를 포맷팅하여 반환
}

/**
 * 주어진 페이지와 한 페이지당 항목 수에 따라 데이터 조회의 LIMIT 값을 반환합니다.
 * 
 * @param int $page 현재 페이지 번호
 * @param int $pagingCount 한 페이지당 항목 수
 * @return int LIMIT 값 (쿼리의 OFFSET 값)
 */
function getPageLimit($page, $pagingCount){
    return ((int)$page - 1) * $pagingCount;  // 페이지에 맞는 LIMIT 값을 계산하여 반환
}

/**
 * 총 항목 수와 한 페이지당 항목 수에 따라 최대 페이지 수를 반환합니다.
 * 
 * @param int $totalCount 전체 항목 수
 * @param int $pagingCount 한 페이지당 항목 수
 * @return int 최대 페이지 수
 */
function getMaxPage($totalCount, $pagingCount){        
    $maxPage = ceil($totalCount / $pagingCount);  // 총 항목 수에 대한 최대 페이지 수 계산
    if($maxPage == 0) $maxPage = 1;  // 항목이 하나도 없으면 최대 페이지 수는 1로 설정
    
    return $maxPage;
}

/**
 * 현재 페이지에서 이전 페이지 번호를 반환합니다.
 * 
 * @param int $page 현재 페이지 번호
 * @return int 이전 페이지 번호 (1 페이지면 1 반환)
 */
function getPrevPage($page){
    if($page == 1) return $page;  // 첫 페이지에서 이전 페이지는 없으므로 그대로 반환
    
    return ($page - 1);  // 첫 페이지가 아니면 한 페이지 뒤로 돌아감
}

/**
 * 현재 페이지에서 다음 페이지 번호를 반환합니다.
 * 
 * @param int $page 현재 페이지 번호
 * @param int $maxPage 최대 페이지 수
 * @return int 다음 페이지 번호 (최대 페이지에서 다음 페이지는 없으므로 그대로 반환)
 */
function getNextPage($page, $maxPage){
    if($page == $maxPage) return $page;  // 마지막 페이지에서 다음 페이지는 없으므로 그대로 반환
    
    return ($page + 1);  // 마지막 페이지가 아니면 한 페이지 앞으로 나아감
}

/**
 * 현재 페이지를 기준으로 주어진 최대 페이지 수에 대해 중앙 페이징 버튼 번호 배열을 반환합니다.
 * 예를 들어, 5개의 페이징 버튼을 보여줄 때, 현재 페이지가 중간에 위치하도록 합니다.
 * 
 * @param int $page 현재 페이지 번호
 * @param int $maxPage 최대 페이지 번호
 * @return array 중앙에 표시될 페이지 번호 배열
 */
function getCenterPage($page, $maxPage){
    $maxCenterCnt = 5; // 버튼 페이징 갯수 (5개 버튼)
    $betweenCnt = floor($maxCenterCnt / 2);  // 중간에 위치할 페이지를 계산
    $initPage = ($page - $betweenCnt);  // 시작 페이지
    $conditionPage = ($page + $betweenCnt);  // 끝 페이지
    
    if($initPage <= 0) $conditionPage = $maxCenterCnt;  // 시작 페이지가 0 이하일 경우 버튼 수를 맞추기 위해 끝 페이지 수정
    if($conditionPage > $maxPage) $initPage -= ($conditionPage - $maxPage);  // 끝 페이지가 최대 페이지를 초과하면 시작 페이지를 조정

    $pageArr = array();
    
    // initPage에서 conditionPage까지의 페이지 번호를 배열에 추가
    for($p = $initPage; $p <= $conditionPage; $p++){ 
        if($p <= 0 || $p > $maxPage) continue;  // 페이지 번호가 유효하지 않으면 건너뜀
        $pageArr[] = (int)$p;  // 배열에 페이지 번호 추가
    }
    
    return $pageArr;  // 최종적으로 계산된 중앙 페이지 번호 배열 반환
}

/**
 * 주어진 쿼리 문자열에서 페이지 파라미터를 제외한 나머지 파라미터들만 반환합니다.
 * 페이지네이션을 처리할 때 기존 쿼리에서 페이지 파라미터만 제거하고 나머지 쿼리 문자열을 유지하려고 사용됩니다.
 * 
 * @param string $queryString 기존 쿼리 문자열 (예: 'page=2&search=abc&sort=desc')
 * @return string 페이지 파라미터를 제외한 쿼리 문자열
 */
function getQueryString($queryString){
    $queryArr = explode('&', $queryString);  // 쿼리 문자열을 '&' 기준으로 분리

    $resultQuery = array();
    for($i=0; $i<count($queryArr); $i++){            
        if(explode('=', $queryArr[$i])[0] == 'page') continue;  // 'page' 파라미터는 제외
        
        $resultQuery[] = $queryArr[$i];  // 나머지 파라미터는 결과 배열에 추가
    }

    $resultString = implode('&', $resultQuery);  // '&'로 다시 조합
    if($resultString){
        return '&'.$resultString;  // 결과 문자열 앞에 '&'를 붙여 반환
    }
    
    return '';  // 쿼리 문자열이 없으면 빈 문자열 반환
}

/**
 * 페이지 번호와 기존 쿼리 문자열을 합쳐서 새로운 페이지 링크를 반환합니다.
 * 페이지네이션 버튼을 클릭할 때 사용될 링크를 생성하는 데 사용됩니다.
 * 
 * @param int $page 페이지 번호
 * @param string $queryString 기존 쿼리 문자열 (예: '&search=abc&sort=desc')
 * @return string 생성된 페이지 링크 (예: '?page=2&search=abc&sort=desc')
 */
function combinePage($page, $queryString){
    return '?page='.$page.$queryString;  // 페이지 번호와 기존 쿼리 문자열을 결합하여 반환
}

/**
 * 문자열에서 하이픈(-)을 제거합니다.
 * 
 * @param string $str 하이픈을 제거할 문자열
 * @return string 하이픈이 제거된 문자열
 */
function unHyphen($str){
    return str_replace("-", "", $str);  // 문자열에서 하이픈을 빈 문자열로 교체하여 제거
}

/**
 * API 응답으로 받은 JSON 문자열을 파싱하여 각 항목을 출력합니다.
 * 
 * @param string $resp JSON 형식의 API 응답 문자열
 * 
 * 주의: 아래의 주석 처리된 코드는 응답에서 특정 값들(Amt, TID, Signature 등)을 추출하여
 * 추가적인 검증 및 로직을 처리할 수 있는 예시입니다.
 * 
 * 예시: 'Amt', 'TID', 'Signature' 등을 추출하여 응답의 무결성을 검증하거나
 * 특정 값을 사용하는 로직을 처리할 수 있습니다.
 */
function jsonRespDump($resp){
    //global $mid, $merchantKey;  // 주석 처리된 코드로, 실제 값들이 글로벌로 정의되었을 때 사용할 수 있음
    $respArr = json_decode($resp);  // JSON 응답을 배열로 변환
    foreach ( $respArr as $key => $value ){  // 배열의 각 항목을 순차적으로 출력
        /*if($key == "Amt" || $key == "CancelAmt"){
            $payAmt = $value;  // Amt 또는 CancelAmt 값을 추출하는 예시
        }
        *if($key == "TID"){
            $tid = $value;  // TID 값을 추출하는 예시
        }
        // 승인 응답으로 받은 Signature 검증을 통해 무결성 검증을 진행하여야 합니다.
        if($key == "Signature"){
            $paySignature = bin2hex(hash('sha256', $tid. $mid. $payAmt. $merchantKey, true));
            if($value != $paySignature){
                echo '비정상 거래! 취소 요청이 필요합니다.</br>';
                echo '승인 응답 Signature : '. $value. '</br>';
                echo '승인 생성 Signature : '. $paySignature. '</br>';
            }
        }*/
        echo "$key=". $value."<br />";  // 키와 값 출력
    }
}


/**
 * POST 방식으로 API 요청을 보내는 함수입니다.
 * 
 * 이 함수는 지정된 URL에 대해 POST 요청을 보내고, 응답을 반환합니다.
 * 주로 API 통신에서 POST 요청을 보낼 때 사용됩니다.
 * 
 * @param Array $data 요청에 포함할 데이터 (key-value 형식)
 * @param string $url 요청을 보낼 URL
 * @return string API 응답 (성공 여부, 데이터 등)
 */
function reqPost(Array $data, $url){
    $ch = curl_init();  // cURL 세션 초기화
    curl_setopt($ch, CURLOPT_URL, $url);  // 요청할 URL 설정
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // 응답을 반환하도록 설정
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);  // 연결 타임아웃 15초
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // SSL 인증서 검증을 하지 않음
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));  // POST 데이터 설정
    curl_setopt($ch, CURLOPT_POST, true);  // POST 방식 설정
    $response = curl_exec($ch);  // 요청 실행
    curl_close($ch);  // cURL 세션 종료
    return $response;  // 응답 반환
}

/**
 * 주어진 길이만큼의 랜덤 문자열을 생성하는 함수입니다.
 * 
 * 이 함수는 숫자, 알파벳 대소문자 등으로 이루어진 랜덤 문자열을 생성합니다.
 * 예를 들어, 세션 ID나 임시 비밀번호 등을 생성할 때 유용합니다.
 * 
 * @param int $length 생성할 랜덤 문자열의 길이
 * @return string 생성된 랜덤 문자열
 */
function generateRandomString($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';  // 사용할 문자 목록
    $randomString = '';  // 랜덤 문자열 초기화

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];  // 랜덤 문자를 하나씩 추가
    }

    return $randomString;  // 생성된 랜덤 문자열 반환
}

/* 기존 편지지 관리자 화면 출력 함수 */
//function setLetterInfo($letterInfo, $writeInfo, $type, $isSimple = false){
//    if($type == 'box'){
//        
//        if($isSimple){
//            $topPadding = $letterInfo['topPadding'] - 55;
//            
//            return "
//                background-image: unset !important;                 
//                padding-top:{$topPadding}px;
//                min-height: unset;
//            ";            
//        }else{
//            return "
//                background-image: url({$letterInfo['imgPath']}) !important;                 
//            ";
////            padding-top:{$letterInfo['topPadding']}px;
//        }
//    }else{ // content        
//        $finalFontSize = extractFontSize($writeInfo['fontSize']);
//        $webkit = $writeInfo['fontWeight'] == '500'? '0.02em' : '0.06em';
//        
//        return "
//            font-family: '{$writeInfo['fontName']}';
//            font-size: {$finalFontSize}px;
//            text-align : {$writeInfo['fontAlign']};
//            -webkit-text-stroke : {$webkit};
//            width : {$letterInfo['contextWidth']}px;
//            height : {$letterInfo['contextHeight']}px;
//            letter-spacing: -0.5px;
//            line-height : {$letterInfo['contextLineHeight']}px;
//            color : {$writeInfo['color']};
//        ";
//    }
//}

/**
 * 주어진 글자 정보와 작성 정보를 기반으로 스타일을 반환하는 함수입니다.
 * 
 * 이 함수는 글자 정보($letterInfo)와 작성 정보($writeInfo)를 사용하여 두 가지 유형의 스타일을 생성합니다:
 * - 'box' 타입의 경우 배경 이미지와 패딩 스타일을 반환합니다.
 * - 'content' 타입의 경우 폰트, 크기, 텍스트 정렬 등 다양한 스타일을 반환합니다.
 * 
 * @param array $letterInfo 글자에 대한 정보 (배경 이미지 경로, 패딩, 크기 등)
 * @param array $writeInfo 작성된 글자에 대한 정보 (폰트, 크기, 정렬, 색상 등)
 * @param string $type 스타일의 유형 ('box' 또는 'content')
 * @return string 설정된 스타일 문자열
 */
function setLetterInfo2($letterInfo, $writeInfo, $type){    
    if($type == 'box'){
        // 'box' 타입의 스타일 반환: 배경 이미지와 패딩 정보
        return "
            background-image: url({$letterInfo['imgPath']}) !important; 
            padding-top:{$letterInfo['topPadding']}px;
        ";     
    }else{ // 'content' 타입의 스타일 반환        
        $finalFontSize = extractFontSize($writeInfo['fontSize']);  // 글자 크기 추출
        $webkit = $writeInfo['fontWeight'] == '500' ? '0.02em' : '0.06em';  // 폰트 굵기에 따른 텍스트 스트로크

        return "
            font-family: '{$writeInfo['fontName']}';
            font-size: {$finalFontSize}px;
            letter-spacing: {$writeInfo['letterSpacing']};
            text-align : {$writeInfo['fontAlign']};
            -webkit-text-stroke : {$webkit};
            width : {$letterInfo['contextWidth']}px;
            height : {$letterInfo['contextHeight']}px;            
            line-height : {$letterInfo['contextLineHeight']}px;
            color : {$writeInfo['color']};
        ";
    }
}


/**
 * (관리자 편지출력)글자 정보와 작성 정보에 따라 스타일을 반환하는 함수입니다.
 * 
 * 이 함수는 글자 정보($letterInfo)와 작성 정보($writeInfo) 및 스타일의 유형($type)과
 * 간단한 스타일을 적용할지 여부($isSimple)에 따라 두 가지 스타일을 처리합니다.
 * - 'box' 타입의 경우 배경 이미지 및 높이 관련 스타일을 반환합니다.
 * - 'content' 타입의 경우 폰트, 크기, 텍스트 정렬 등 다양한 스타일을 반환합니다.
 * 
 * @param array $letterInfo 글자에 대한 정보 (배경 이미지 경로, 패딩, 크기 등)
 * @param array $writeInfo 작성된 글자에 대한 정보 (폰트, 크기, 정렬, 색상 등)
 * @param string $type 스타일의 유형 ('box' 또는 'content')
 * @param bool $isSimple 간단한 스타일을 적용할지 여부 (기본값: false)
 * @return string 설정된 스타일 문자열
 */
function setLetterInfo3($letterInfo, $writeInfo, $type, $isSimple = false){
    if($type == 'box'){  // 'box' 타입에 대한 스타일 반환                
        if($isSimple){  // 간단한 스타일을 적용할 경우
            return "
                background-image: unset !important; 
                min-height: unset;
            ";
        }else{  // 간단한 스타일이 아닐 경우 배경 이미지 적용
            return "                
                background-image: url({$letterInfo['imgPath']}) !important;
            ";
        }
    }else{ // 'content' 타입에 대한 스타일 반환        
        $finalFontSize = extractFontSize($writeInfo['fontSize']);  // 글자 크기 추출
        $webkit = $writeInfo['fontWeight'] == '500' ? '0.02em' : '0.06em';  // 폰트 굵기에 따른 텍스트 스트로크

        return "
            font-family: '{$writeInfo['fontName']}';
            font-size: {$finalFontSize}px;
            letter-spacing: {$writeInfo['letterSpacing']};
            text-align : {$writeInfo['fontAlign']};
            -webkit-text-stroke : {$webkit};
            width : {$letterInfo['contextWidth']}px;
            height : {$letterInfo['contextHeight']}px;            
            line-height : {$letterInfo['contextLineHeight']}px;
            color : {$writeInfo['color']};
        ";
    }
}


/**
 * 주어진 폰트 크기 문자열에서 'px'와 같은 단위를 제거하고, 숫자 값만 추출하는 함수입니다.
 * 
 * 이 함수는 주어진 폰트 크기 문자열에서 숫자와 소수점만을 남겨두고, 'px'와 같은 단위는 제거합니다.
 * 이를 통해 폰트 크기를 숫자형 값으로 변환할 수 있습니다.
 * 
 * @param string $fontSizeStr 폰트 크기 문자열 (예: '16px', '12.5px' 등)
 * @return float 폰트 크기의 숫자 값 (단위 제거된 크기)
 */
function extractFontSize($fontSizeStr) {
    // 'px' 제거 및 숫자만 추출하는 정규식
    return (float) preg_replace('/[^0-9.]/', '', $fontSizeStr);  // 숫자와 소수점만 반환
}

/**
 * 이메일을 보내는 함수입니다.
 * 
 * 이 함수는 CodeIgniter의 이메일 라이브러리를 사용하여 주어진 수신자에게 이메일을 전송합니다.
 * 이메일 설정은 'email' 설정 파일에서 가져오며, 메일의 발신자, 수신자, 제목, 메시지 등을 설정합니다.
 * 
 * @param string $to 수신자 이메일 주소
 * @param string $subject 이메일 제목
 * @param string $message 이메일 본문 내용
 * @return bool 이메일 전송 성공 여부
 */
function sendMail($to, $subject, $message){
    $CI = &get_instance();  // CodeIgniter의 인스턴스 가져오기
    $CI->load->library('email');  // 이메일 라이브러리 로드
    $CI->load->config('email');  // 이메일 설정 파일 로드

    $emailConfig = $CI->config->item('emailConfig');  // 이메일 설정 가져오기
    $CI->email->initialize($emailConfig);  // 이메일 설정 초기화

    $CI->email->from($emailConfig['smtp_user'], '동글');  // 발신자 설정
    $CI->email->to($to);  // 수신자 설정

    $CI->email->subject($subject);  // 이메일 제목 설정
    $CI->email->message($message);  // 이메일 본문 내용 설정

    return $CI->email->send();  // 이메일 전송 시도 후 성공 여부 반환
}


/**
 * 주문 ID를 생성하여 데이터베이스에 업데이트하는 함수입니다.
 * 
 * 이 함수는 `write_list` 테이블에서 해당 글의 정보를 조회하고, 그 정보를 바탕으로 
 * 고유한 주문 ID를 생성하여 `orderId` 필드를 업데이트합니다.
 * 주문 ID는 글의 등록 날짜, 글의 고유 번호, 발신자 이름, 총 편지 수, 사진 수량 등의 정보를 포함합니다.
 * 또한, 파일 수량이나 특정 조건에 따라 "대봉투"를 주문 ID에 추가합니다.
 * 
 * @param object $db 데이터베이스 연결 객체
 * @param int $writeIdx 글의 고유 인덱스 번호
 * 
 * @return void
 */
function setOrderId($db, $writeIdx){  
    $CI = &get_instance();  // CodeIgniter 인스턴스를 가져옵니다.

    // 글 정보 조회 쿼리
    $sql = "SELECT * FROM write_list WHERE idx = '{$writeIdx}'";
    $query = $db->query($sql);
    $info = $query->row_array();  // 결과를 배열로 반환

    // 'photos' 필드는 JSON 형식이므로 이를 배열로 변환합니다.
    $info['photos'] = json_decode($info['photos'], true);
        
    // $totalGPhotoCnt = 0;  // 유광 사진 개수
    // $totalMPhotoCnt = 0;  // 무광 사진 개수
    // 나중에 변경 필요
    $totalPhotoCnt = 0;
        
    // 각 사진의 정보를 확인하고 Gloss와 일반 사진 개수를 카운트합니다.
    // foreach($info['photos'] as $key => $data) {
    //     if(empty($data['isGloss']) || $data['isGloss'] == 'Y') {
    //         $totalGPhotoCnt++;  // Gloss 사진은 G로 카운트
    //     } else {
    //         $totalMPhotoCnt++;  // 일반 사진은 M으로 카운트
    //     }
    // }
    // 나중에 변경 필요
    foreach($info['photos'] as $key => $data) {
        $totalPhotoCnt++;
    }

    // 주문 ID를 생성합니다. 여러 필드를 결합하여 고유 ID를 만듭니다.
    $orderId = unHyphen(substr($info['regDate'], 3, 7)) .  // 등록일에서 년월을 추출하여 하이픈 제거
               $info['idx'] .  // 글의 고유 ID
               $info['senderName'] .  // 발신자 이름
               'L' . $info['totalLetterCnt'] .  // 총 편지 수
            //    'G' . $totalGPhotoCnt .  // Gloss 사진 수
            //    'M' . $totalMPhotoCnt .  // 일반 사진 수
               'P' . $totalPhotoCnt .
               'A' . $info['totalPdfFileCnt'];  // PDF 파일 수

    // 대봉투 조건: PDF 파일 수가 1 이상이거나, 편지와 사진의 수가 특정 수를 넘으면 '대봉투'를 추가
    if($info['totalPdfFileCnt'] > 0 || $info['totalLibraryFileCnt'] > 0 || (int)$info['totalLetterCnt'] + (int)$info['totalPhotoCnt'] >= $CI->config->item('maxBigCnt')) {
        $orderId .= '대봉투';  // 대봉투를 주문 ID에 추가
    }

    if (!empty($info['libraryFiles'])) {
        $libraryFiles = json_decode($info['libraryFiles'], true);
    
        if (is_array($libraryFiles) && count($libraryFiles) > 0) {
            $titles = [];
    
            foreach ($libraryFiles as $lib) {
                if (!empty($lib['originalFileName'])) {
                    $title = pathinfo($lib['originalFileName'], PATHINFO_FILENAME);
                    $titles[] = $CI->db->escape_str($title);
                }
            }
    
            if (!empty($titles)) {
                $orderId .= '_(' . implode(',', $titles) . ')';
            }
        }
    }
    
    

    // 생성된 주문 ID를 데이터베이스에 업데이트합니다.
    $sql = "
        UPDATE
            write_list
        SET
            orderId = '{$orderId}'
        WHERE
            idx = '{$info['idx']}'
    ";

    // 쿼리 실행
    $db->query($sql);
}

/**
 * 결제된 주문을 확인하고 상태를 'W' (대기)로 업데이트하는 함수입니다.
 * 
 * 이 함수는 'write_list' 테이블에서 결제가 완료되었으나 대기로 넘어가지 않은 주문을 찾고,
 * 해당 주문의 상태를 'W' (대기)로 변경하며, 관련된 필드를 업데이트합니다.
 * 
 * @param int $memberIdx 사용자의 고유 인덱스 번호 (선택적)
 * 
 * @return void
 */
function checkPaymentOrder($memberIdx){  
    $CI = &get_instance();  // CodeIgniter 인스턴스 가져오기

    // 결제는 됐으나 상태가 'T' (대기)인 주문을 조회하는 쿼리
    $sql = "SELECT * FROM write_list WHERE isPay = 'Y' AND payIdx > 0 AND state = 'T' ORDER BY idx DESC;";
    
    $query = $CI->db->query($sql);  // 쿼리 실행
    $list = $query->result_array();  // 결과를 배열로 반환
	
    // 주문 목록을 순회하며 각 주문의 상태를 'W' (대기)로 업데이트
    for($i=0; $i<count($list); $i++){
        $row = $list[$i];
        
        // 주문 상태를 'W'로 업데이트하는 쿼리
        $sql = "
            UPDATE
                write_list
            SET
                isPay = 'Y',
                state = 'W',
                isUse = 'Y'
            WHERE
                idx = '{$row['idx']}'
        ";
        
        // 선택적으로 memberIdx가 전달되었을 경우 해당 사용자의 주문만 업데이트
        if(!empty($memberIdx)) {
            $sql .= " AND memberIdx = {$memberIdx} ";  // memberIdx 추가 조건
        }

        // 쿼리 실행
        $CI->db->query($sql);
    }
}



function sendMessage($authNumber, $phoneNumber){    
    /**************** 문자전송하기 예제 필독항목 ******************/
    /* 동일내용의 문자내용을 다수에게 동시 전송하실 수 있습니다
    /* 대량전송시에는 반드시 컴마분기하여 1천건씩 설정 후 이용하시기 바랍니다. (1건씩 반복하여 전송하시면 초당 10~20건정도 발송되며 컨텍팅이 지연될 수 있습니다.)
    /* 전화번호별 내용이 각각 다른 문자를 다수에게 보내실 경우에는 send 가 아닌 send_mass(예제:curl_send_mass.html)를 이용하시기 바랍니다.

    /****************** 인증정보 시작 ******************/
    $sms_url = "https://apis.aligo.in/send/"; // 전송요청 URL
    $sms['user_id'] = "doitnow22"; // SMS 아이디
    $sms['key'] = "1bdl9iqbn16aqyxiuzpgrlx8jj8afd0z"; //인증키
    /****************** 인증정보 끝 ********************/

    /****************** 전송정보 설정시작 ****************/
    $_POST['msg'] = "동글 인증번호 ({$authNumber})를 입력해주세요."; // 메세지 내용 : euc-kr로 치환이 가능한 문자열만 사용하실 수 있습니다. (이모지 사용불가능)
    $_POST['receiver'] = "{$phoneNumber}"; // 수신번호
    $_POST['destination'] = ""; // 수신인 %고객명% 치환
    $_POST['sender'] = "01042529806"; // 발신번호
    $_POST['rdate'] = ''; // 예약일자 - 20161004 : 2016-10-04일기준
    $_POST['rtime'] = ''; // 예약시간 - 1930 : 오후 7시30분
    $_POST['testmode_yn'] = 'N'; // Y 인경우 실제문자 전송X , 자동취소(환불) 처리
    $_POST['subject'] = '[동글]인증번호'; //  LMS, MMS 제목 (미입력시 본문중 44Byte 또는 엔터 구분자 첫라인)
    // $_POST['image'] = '/tmp/pic_57f358af08cf7_sms_.jpg'; // MMS 이미지 파일 위치 (저장된 경로)
    $_POST['msg_type'] = 'SMS'; //  SMS, LMS, MMS등 메세지 타입을 지정
    // ※ msg_type 미지정시 글자수/그림유무가 판단되어 자동변환됩니다. 단, 개행문자/특수문자등이 2Byte로 처리되어 SMS 가 LMS로 처리될 가능성이 존재하므로 반드시 msg_type을 지정하여 사용하시기 바랍니다.
    /****************** 전송정보 설정끝 ***************/

    $sms['msg'] = stripslashes($_POST['msg']);
    $sms['receiver'] = $_POST['receiver'];
    $sms['destination'] = $_POST['destination'];
    $sms['sender'] = $_POST['sender'];
    $sms['rdate'] = $_POST['rdate'];
    $sms['rtime'] = $_POST['rtime'];
    $sms['testmode_yn'] = empty($_POST['testmode_yn']) ? '' : $_POST['testmode_yn'];
    $sms['title'] = $_POST['subject'];
    
    /*****/
    $host_info = explode("/", $sms_url);
    $port = $host_info[0] == 'https:' ? 443 : 80;

    $oCurl = curl_init();
    curl_setopt($oCurl, CURLOPT_PORT, $port);
    curl_setopt($oCurl, CURLOPT_URL, $sms_url);
    curl_setopt($oCurl, CURLOPT_POST, 1);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($oCurl, CURLOPT_POSTFIELDS, $sms);
    curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
    $ret = curl_exec($oCurl);
    curl_close($oCurl);
    
    $retArr = json_decode($ret); // 결과배열    
    
    if($retArr->result_code == 1){
        return true;
    }
    return false;
    
//    print_r($retArr); // Response 출력 (연동작업시 확인용)

    /**** Response 항목 안내 ****
    // result_code : 전송성공유무 (성공:1 / 실패: -100 부터 -999)
    // message : success (성공시) / reserved (예약성공시) / 그외 (실패상세사유가 포함됩니다)
    // msg_id : 메세지 고유ID = 고유값을 반드시 기록해 놓으셔야 sms_list API를 통해 전화번호별 성공/실패 유무를 확인하실 수 있습니다
    // error_cnt : 에러갯수 = receiver 에 포함된 전화번호중 문자전송이 실패한 갯수
    // success_cnt : 성공갯수 = 이동통신사에 전송요청된 갯수
    // msg_type : 전송된 메세지 타입 = SMS / LMS / MMS (보내신 타입과 다른경우 로그로 기록하여 확인하셔야 합니다)
    /**** Response 예문 끝 ****/
}

function getCodeMailHtml($authNumber){
    return "<p>인증코드 : <strong>{$authNumber}</strong></p>";
}


/* WebView 상태인지 체크 */
function isWebView() {    
    $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $mobile_agents = array('iphone','ipod','ipad','android','webos', 'blackberry','nokia','opera mini','windows mobile','windows phone','iemobile');
    $isMobile = false;
    
    foreach($mobile_agents as $mobile_agent) {
        if (strpos(strtolower($user_agent), strtolower($mobile_agent)) !== false) {            
            $isMobile = true;
        }
    }
    
    if(!$isMobile){            
        return false;
    }
    
    // IOS 웹뷰 체크        
    if (isIos()){
//        echo $user_agent;
        if(strpos(trim($user_agent), 'crios') !== false || strpos(trim($user_agent), 'version') !== false) {                
            return false;
        }
        return true;
    }
    
    // ANDROID 웹뷰 체크
    if (!isIos()){
        if(strpos(trim($user_agent), 'version') !== false) {
            return true;
        }
        return false;
    }
    
    return false;
}

/**
 * iOS를 감지하여 체크하는 함수입니다.
 *
 * @return bool 모바일 장치가 iOS인 경우 true를, 그렇지 않은 경우 false를 반환합니다.
 */
function isIos() {
	return preg_match('/iPad|iPod|iPhone/', $_SERVER['HTTP_USER_AGENT']);
}

/**
 * Android를 감지하여 체크하는 함수입니다.
 *
 * @return bool 모바일 장치가 Android인 경우 true를, 그렇지 않은 경우 false를 반환합니다.
 */
function isAndroid() {
	return preg_match('/Android/', $_SERVER['HTTP_USER_AGENT']);
}

/**
 * 입력된 문자열을 5-4-4 형식으로 하이픈(-)을 추가하여 반환하는 함수입니다.
 * 
 * 이 함수는 주어진 문자열을 5자리, 4자리, 4자리로 나누고 하이픈을 추가하여
 * 원하는 형식으로 문자열을 반환합니다.
 * 
 * @param string $input 하이픈을 추가할 문자열
 * @return string 하이픈이 포함된 포맷된 문자열
 */
function formatWithHyphens($input) {
    // 입력 문자열의 길이를 확인
    $length = strlen($input);

    // 문자열을 5-4-4 형식으로 변환
    $part1 = substr($input, 0, 5);  // 첫 5자리
    $part2 = substr($input, 5, 4);  // 그 다음 4자리
    $part3 = substr($input, 9, 4);  // 마지막 4자리

    // 하이픈을 포함하여 문자열 합치기
    $formatted = $part1 . '-' . $part2 . '-' . $part3;

    return $formatted;  // 최종 포맷된 문자열 반환
}

/**
 * 문자열 내의 하이픈(-)을 슬래시(/)로 변경하는 함수입니다.
 * 
 * 이 함수는 입력된 문자열에서 모든 하이픈(-)을 슬래시(/)로 치환하여 반환합니다.
 * 
 * @param string $input 하이픈을 포함한 문자열
 * @return string 하이픈이 슬래시로 변경된 문자열
 */
function replaceHyphensWithSlashes($input) {
    return str_replace('-', '/', $input);  // 하이픈을 슬래시로 변경
}


/**
 * 문자열에 "사서함"이 포함되어 있는지 확인하는 함수입니다.
 * 
 * 이 함수는 정규식을 사용하여 주어진 문자열에 "사서함"이라는 단어가 포함되어 있는지 확인합니다.
 * 포함되어 있으면 `true`, 그렇지 않으면 `false`를 반환합니다.
 * 
 * @param string $string 확인할 문자열
 * @return bool "사서함"이 포함되어 있으면 true, 아니면 false
 */
function containsSaseoham($string) {
    // "사서함"이 포함되어 있는지 정규식으로 확인
    if (preg_match('/사서함/', $string)) {
        return true;  // "사서함"이 있으면 true 반환
    } else {
        return false;  // 없으면 false 반환
    }
}


?>

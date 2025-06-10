<?php
public function setWrite()
	{
        ini_set('memory_limit', '-1');
        $result = array('result' => false, 'msg' => "");
                
        $saveIdx = (int)$_POST['saveIdx'];
        $tmpSaveIdx = (int)$_POST['tmpSaveIdx'];
        $isTmpSave = $_POST['isTmpSave'];
        $isLogin = $_POST['isLogin'];
        $memberIdx = $_POST['memberIdx'];        
        
        $payType = $_POST['payType'];
        $letterIdx = $_POST['letterIdx'];
        $productName = $_POST['productName'];
        $fontName = $_POST['fontName'];
        $fontFamily = $_POST['fontFamily'];
        $fontKrName = $_POST['fontKrName'];
        
        $fontSize = $_POST['fontSize'];
        $fontAlign = $_POST['fontAlign'];
        $totalLetterCnt = $_POST['totalLetterCnt'];
        $content = empty($_POST['content'])? '' : $_POST['content'];
        
        $totalPhotoCnt = $_POST['totalPhotoCnt'];
        $photos = empty($_POST['photos'])? [] : $_POST['photos'];
        $photos = json_encode($photos, JSON_UNESCAPED_UNICODE);
        
        $totalPdfFileCnt = empty($_POST['totalPdfFileCnt'])? 0 : $_POST['totalPdfFileCnt'];
        $pdfFiles = empty($_POST['pdfFiles'])? [] : $_POST['pdfFiles'];        
        $pdfFiles = json_encode($pdfFiles, JSON_UNESCAPED_UNICODE);
        $fileColor = empty($_POST['fileColor'])? '' : $_POST['fileColor'];
        
        $senderAddr = $_POST['senderAddr'];
        $senderAddrDetail = $_POST['senderAddrDetail'];
        $senderName = $_POST['senderName'];
        $senderTel = $_POST['senderTel'];
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

        // 현재 주문 상태 가져오기
        $sql = "SELECT state, is_locked, is_paid FROM write_list WHERE idx = ?";
        $query = $this->db->query($sql, array($saveIdx));
        $orderInfo = $query->row_array();

        if (!empty($orderInfo) && $orderInfo['state'] == 'I') {
          $result['msg'] = "현재 주문은 인쇄 중 상태로 변경되어 수정할 수 없습니다.";
          die(json_encode($result));
        }

        // 인쇄 중이거나 인쇄 완료 상태일 때 수정 금지
        if (!empty($orderInfo) && in_array($orderInfo['state'], ['I', 'P', 'F'])) {
            $result['msg'] = "현재 주문은 수정할 수 없습니다.";
            die(json_encode($result));
        }

        // 주문이 현재 수정 중(is_locked)이라면 수정 불가
        if (!empty($orderInfo) && $orderInfo['is_locked'] == 1) {
            $result['msg'] = "현재 편지가 수정 중입니다. 다른 사용자가 수정 중인지 확인하세요.";
            die(json_encode($result));
        }

        // 결제 취소된 주문이면 수정 불가
        if (!empty($orderInfo) && $orderInfo['is_paid'] == 0) {
            $result['msg'] = "이 주문은 결제가 취소되어 수정할 수 없습니다.";
            die(json_encode($result));
        }

        // 편지 수정이 시작되면 is_locked = TRUE 설정
        $sql = "UPDATE write_list SET is_locked = TRUE WHERE idx = ?";
        $this->db->query($sql, array($saveIdx));
        
        /* 충전 포인트 */
        $percentage = 3; // 3%
        $addPoint = round($totalPrice * ($percentage / 100));
                
                 
        $regexContent = '';
        
        if(!empty($content)) {
            for($i = 0; $i < count($content); $i++){
                $content[$i] = preg_replace("/'/", "\"", $content[$i]); // 작은 따옴표를 큰따옴표로 변경
                $regex = '$#';

                $regexContent .= (($i == 0) ? '' : $regex) . $content[$i];

                if(empty($content[$i])) {
                    $sql = "
                        INSERT INTO
                            error_log
                        SET
                            content = '{$content[$i]}',
                            memberIdx = '{$memberIdx}'
                    ";

                    $this->db->query($sql);
                    $result['msg'] = "메세지 내용이 전송되지 않았습니다. 재시도 해주세요(고객센터 문의)";
                    die(json_encode($result));
                }
            }   
        }
        
        $isPay = empty((int)$realTotalPrice)? 'Y' : 'N';                
        
        $state = 'W';
        $payId = 0;
        $writeInfo = array();
        
        if($saveIdx > 0 || $tmpSaveIdx > 0 || $isTmpSave == 'Y') {
            $idx = empty($saveIdx)? $tmpSaveIdx : $saveIdx;
            
            $sql = "
                 SELECT
                    *
                 FROM
                    write_list
                 WHERE
                    idx = '{$idx}'
            ";

            $query = $this->db->query($sql);
            $writeInfo = $query->row_array();
        }
             
        if( (empty($saveIdx) && $payType != 'deposit') || $isTmpSave == 'Y') {
            $state = 'T';
        }else if($payType == 'deposit') {
            $state = 'B';
        }else if ($someCondition) {
          $state = 'S';
        }
        
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
        
        $browser = 'PC';
        
        if(isIos()) {
            $browser = 'IOS';
        }else if(isAndroid()) {
            $browser = 'ANDROID';
        }
        
        /* 결제부분 포멧을 위해 임시 변경 */
        if($payType != 'deposit' && $saveIdx > 0) {
            $isPay = 'Y';
        }
        
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
            fontAlign = '{$fontAlign}',
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
            isDefaultAddress = '{$isDefaultAddress}'            
        ";                
        
        if($saveIdx == 0 && $tmpSaveIdx == 0) {
            $sql = "
                INSERT INTO
                    write_list
                SET
                    $setSql
            ";
            
            $result['result'] = $this->db->query($sql);
        }else {
                        
            if($saveIdx > 0) {
                /* 수정시 */    
                if($payType == 'deposit' || ($writeInfo['realTotalPrice'] == $realTotalPrice)) { 
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
                }else {
                    $sql = "
                        UPDATE
                            write_list
                        SET
                            isSave = 'Y',
                            isSaveCnt = isSaveCnt + 1                        
                        WHERE
                            idx = '{$saveIdx}'
                    ";
                    $this->db->query($sql);

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
            }else {
                /* 임시저장시 */
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
        
        if(empty($result['result'])){
            $result['msg'] = "setWrite error";             
            $error = $this->db->error();
            $result['error'] = $error['message'];
            die(json_encode($result));
        }
        
        if($saveIdx == 0 && $tmpSaveIdx == 0) {
            $result['writeIdx'] = $this->db->insert_id();
        }else if($saveIdx > 0) {
            $result['writeIdx'] = $saveIdx;
        }else{
            $result['writeIdx'] = $tmpSaveIdx;
        }
                
        
        if($isLogin == 'Y'){
            $sql = "
                UPDATE
                    member_list
                SET
                    isAllPoint = '{$isAllPoint}',
                    fontKrName = '{$fontKrName}',
                    fontFamily = '{$fontFamily}',
                    fontName = '{$fontName}'
                WHERE
                    idx = '{$memberIdx}'
            ";
            
            $this->db->query($sql);
        }                
        
        /* 결제부분 포멧을 위해 임시 변경한걸 결제 시키기 위해 다시 복구 */
        if($payType != 'deposit' && $saveIdx > 0) {
            $isPay = 'N';
        }
        
        /* 무통장일 경우 바로 주소지 저장 */  
        if($payType == 'deposit' && $isTmpSave == 'N') {
            autoSaveAddress($this->db, $result['writeIdx']);
        }
        
        /* 주문번호 자동 생성 */
        setOrderId($this->db, $result['writeIdx']);
        
        $result['isPay'] = $isPay;
        $result['mbName'] = $mbName;
        $result['mbPhoneNumber'] = $mbPhoneNumber;
        $result['mbPassword'] = $mbPassword;
		
		die(json_encode($result));
	}
    
?>
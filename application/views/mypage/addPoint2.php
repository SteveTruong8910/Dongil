<style>
    #ft_menu {display: none;}
</style>
<link rel="stylesheet" type="text/css" href="/assets/css/view/point.css<?=$this->config->item('ver')?>">
<script src="/assets/js/nicepay.js<?=$this->config->item('ver')?>"></script>

<div id="point" style="padding: 0 20px;">
    <p class="pointMoney">
        <img src="/assets/image/ico/ico_coin.png"/> 
        동글포인트 머니
    </p>    
    
    <div class="pointInputWrap">
        <input id="pointInput" class="pointInput" type="tel" placeholder="충전포인트 입력"/>
        <button id="removePoint" class="removePoint hide" onclick="removePoint()"><i class="fas fa-times"></i></button>
    </div>
    <p id="errPoint" class="errPoint hide">1회 최소 충전 가능한 금액은 1만원입니다.</p>
    <p id="krPoint" class="krPoint hide">1만 2,345원</p>    
    
    <div class="addPointWrap">
        <? 
            $addPoint = array(
                ['text' => '+1만', 'point' => 10000],
                ['text' => '+5만', 'point' => 50000],
                ['text' => '+10만', 'point' => 100000],
                ['text' => '+100만', 'point' => 1000000]
            );
        ?>
        <? foreach($addPoint as $key => $data) { ?>
            <button class="addPointBtn" onclick="addPoint(<?=$data['point']?>)"><?=$data['text']?></button>
        <? } ?>
    </div>
    
    <p class="ment" style="color: black; font-size: 16px; font-weight:bold; margin:10px 0;">✅충전 추가 적립금 안내</p>
    <p class="ment"><strong style="color: black;">현금(무통장입금)</strong></p>
    <p class="ment" style="margin-bottom: 5px; ">3만원 ~ 10만원 미만 5%, 10만원 이상 10%</p>    
    <p class="ment"><strong style="color: black;">카드 및 페이</strong></p>
    <p class="ment">3만원 ~ 10만원 미만 3%, 10만원 이상 5%</p>
        
    <p class="payType">결제방식</p>      
    
    <div id="depositBox" class="hide" style="margin-bottom:10px;">
        <p class="letterMent" style="color: #260606;">💸무통장 입금 계좌 안내</p>
        <? foreach($this->config->item('donglDeposit') as $bankNumber => $bank){ ?>            
            <p class="letterMent" onclick="copyToText($(this))" data-bank_number="<?=$bankNumber?>" style="text-decoration: underline; cursor: pointer;">
                <?=$bank?> <?=$bankNumber?> <?=$this->config->item('donglDepositName')?>
            </p>
        <? } ?>
    </div>    
    
    <div class="payBtnBox">
        <? foreach($this->config->item('payType') as $key => $name){            
            if($key == 'bank' || $key == 'point') continue;            
        ?>
            <button class="payTypeBtn" data-type="<?=$key?>" onclick="setPayBtn($(this));">
                <?=$name?>
            </button>
        <? } ?>
    </div>    
    
    <div id="depositerNameBox" class="hide">
        <p class="ment">입금자명</p>
        <input type="text" id="depositorName" class="input" placeholder="입금자명" value="<?=$this->user['senderName']?>"/>
    </div>
    
    <div id="depositInfoBox" class="hide">
        <input type="checkbox" id="isCashReceipt" value="Y" onclick="setIsCashReceipt()" style="margin:15px 0;"> 
        <label for="isCashReceipt">현금영수증 신청</label>
        <div id="cashReceiptDetail" class="hide">
            <div style="margin-bottom: 5px;">
            <? foreach($this->config->item('depositType') as $key => $name){ ?>                
                <input id="cashReceiptType<?=$key?>" type="radio" name="cashReceiptType" onclick="changeDepositType('<?=$key?>')" value="<?=$key?>"
                       <?=(empty($this->user['cashReceiptType']) && $key == 'earnings') || $key == $this->user['cashReceiptType'] ? 'checked' : ''?>>
                <label for="cashReceiptType<?=$key?>"><?=$name?></label>
            <? } ?>
            </div>

            <div style="display: flex; margin-bottom:25px; margin-bottom: 10px;">
                <select id="depositType" style="width: 150px; border: 1px solid black; border-radius: 3px; padding-left: 5px;">
                <? foreach($this->config->item('depositType2') as $key => $name){    
                    if(($this->user['cashReceiptType'] == 'expenditure' && $key == 'phone') || 
                      ((empty($this->user['cashReceiptType']) || $this->user['cashReceiptType'] == 'earnings') && $key == 'business')) continue;
                ?>
                    <option value="<?=$key?>" <?=$key == $this->user['depositType']? 'selected' : ''?>><?=$name?></option>
                <? } ?>
                </select>
                <input id="cashReceiptNumber" type="number" placeholder="숫자만 입력해주세요." value="<?=$this->user['cashReceiptNumber']?>"
                       style="width: 100%; border: 1px solid #CDCDCD; border-radius: 5px; height: 40px; margin-left: 10px; padding: 0 10px;"/>                                        
            </div>
                
            <p class="guide">이메일</p>
            <input type="email" id="cashReceiptEmail" class="fieldInput" value="<?=$this->user['cashReceiptEmail']?>" 
                   style="width: 100%; height: 45px; border-radius: 5px; border: 1.17px solid #0000001A; padding-left: 15px; margin-bottom: 10px;"/>
            
            <div style="display: flex;">
                <input id="agreeCashReceipt" type="checkbox" style="<?=!isIos()? 'margin-top: -13px;' : ''?> margin-right: 7px;">
                <label for="agreeCashReceipt" style="margin-bottom: 15px;">현금영수증 발급을 위하여 휴대폰번호(사업자번호) 또는 현금영수증카드번호 수집에 동의합니다.</label>
            </div>
        </div>
    </div>
            
    <div id="btnPayBox" class="hide">
        <p>
            <input id="returnAgree" class="returnAgree" type="checkbox">
            <label for="returnAgree">(필수) 구매조건 및 취소/환불규정 약관동의</label>
            <span class="viewAgreeModal" onclick="onReturnAgreePopup('show');">[내용보기]</span>
        </p>
        
        <button class="btnPay" onclick="nicepayStart()"><span id="totalPrice">0원 결제하기</span></button>
    </div>
    
    <div id="returnAgreePopup" class="postPopup popupContainer">
        <div class="popupBox">
            <div class="title">
                <p>구매조건 및 취소/환불규정 약관안내</p>
                <i class="fas fa-times" onclick="onReturnAgreePopup('close');"></i>
            </div>

            <div>
                <strong style="display: block; margin: 10px 0 5px 0">✔️ 카드 환불기준</strong>
                <p style="color: #6a6a6a;">추가 적립된 포인트 회수 후 충전시 결제 금액에서의 10% 공제(소비자 보호법에 의거)</p>
                <p style="color: #6a6a6a;">카드 일부 취소 혹은 현금으로 돌려받길 원할시 추가 10%(부가세) 공제 후 계좌이체</p>

                <strong style="display: block; margin: 10px 0 5px 0">✔️ 현금 환불 기준</strong>
                <p style="color: #6a6a6a;">현금 추가 적립된 포인트 회수 후 10% 공제(소비자 보호법에 의거), 계좌이체</p>

                <button class="btnPay" style="padding: 8px 15px;" onclick="agreeReturn()">동의</button>
            </div>
        </div>
    </div>
</div>

<script>
    // 회원 인덱스 가져오기 (PHP에서 동적으로 삽입)
    const memberIdx = '<?=$this->user['idx']?>';    
    
    // 무통장 입금 관련 설정
    const depositTypes = <?=json_encode($this->config->item('depositType2'))?>;  // 무통장 입금 유형 목록
    
    /* 현금 영수증 체크 함수 */
    function setIsCashReceipt() {
        $('#cashReceiptDetail').toggleClass('hide');  // 현금 영수증 정보 박스 토글
    }
    
    /* 현금영수증 발급 type에 따른 option 리스트 변경함수 */
    function changeDepositType(type) {
        let depositOptions = '';

        // depositTypes 객체의 키와 값을 순회하여 필터링 후, 옵션 생성
        Object.entries(depositTypes).forEach(([key, value]) => {
            // 'earnings' 유형일 경우 'business' 제외, 'expenditure'일 경우 'phone' 제외
            if((type == 'earnings' && key == 'business') || (type == 'expenditure' && key == 'phone')) return;

            depositOptions += `
                <option value="${key}">${value}</option>
            `;
        });

        // 필터링된 옵션들을 depositType select 요소에 추가
        $('#depositType').html(depositOptions);        
    }
    
    // 계좌번호 복사 함수
    function copyToText($this) {
        var bankNumber = $this.data('bank_number');

        // 클립보드에 복사하는 함수
        function copyToClipboard(text) {
            var tempInput = document.createElement('input');
            tempInput.style.position = 'absolute';
            tempInput.style.left = '-9999px';
            tempInput.value = text;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            showAlert('계좌번호가 복사되었습니다.<br>' + text); // 알림창 표시
        }

        // 클립보드에 복사 실행
        copyToClipboard(bankNumber);
    }
    
    // 반품 동의 팝업 제어 함수
    function onReturnAgreePopup(type){
        let $returnAgreePopup = $('#returnAgreePopup');
        
        if(type == 'show'){  // 팝업 표시
            $returnAgreePopup.addClass('show');
        }else{  // 팝업 숨기기
            $returnAgreePopup.removeClass('show');
        }
    }
    
    // 반품 동의 체크 함수
    function agreeReturn() {
        $('#returnAgree').prop('checked', true);  // 동의 체크
        onReturnAgreePopup('hide');  // 팝업 숨기기
    }
    
    // 결제 버튼 타입 설정 함수
    function setPayBtn($this){
        $('.payTypeBtn').removeClass('active');  // 모든 결제 버튼에서 active 클래스 제거
        $this.addClass('active');  // 클릭된 결제 버튼에 active 클래스 추가
        
        if($this.data('type') == 'deposit'){  // 입금 결제 타입일 경우
            $('#depositerNameBox, #depositBox, #depositInfoBox').removeClass('hide');  // 입금자명 입력란과 입금 정보 표시
        }else{
            $('#depositerNameBox, #depositBox, #depositInfoBox').addClass('hide');  // 입금 결제가 아니면 숨기기
        }
    }
    
    // 포인트 금액을 원화로 변환하는 함수
    function getKrPoint(point) {        
        let krPoint = "",
            man = parseInt(point / 10000),  // 만 단위 계산
            won = parseInt(point % 10000);  // 원 단위 계산
                
        if(man > 0) {
            krPoint += man + '만 ';  // 만 단위 표시
        }
        
        if(won > 0) {
            krPoint += comma(won) + '원';  // 원 단위 표시
        }
        return krPoint;
    }
    
    // 포인트 추가 함수
    function addPoint(point) {
        let nowPoint = Number(uncomma($('#pointInput').val()));  // 현재 입력된 포인트 값
        let addPoint = nowPoint + Number(point);  // 추가된 포인트 계산
        
        $('#pointInput').val(comma(addPoint)).trigger('input');  // 새로운 값으로 업데이트
    }
    
    // 포인트 제거 함수
    function removePoint() { 
        $('#pointInput').val('');  // 포인트 입력란 초기화
        $('#krPoint').addClass('hide');  // 원화 표시 숨기기
        $('#errPoint').addClass('hide');  // 에러 메시지 숨기기
        $('#btnPayBox').addClass('hide');  // 결제 버튼 숨기기
        $('#removePoint').addClass('hide');  // 제거 버튼 숨기기
    }
    
    // 결제창 최초 요청 시 실행되는 함수
    function nicepayStart(){
        let $payType = $('.payTypeBtn.active'),  // 선택된 결제 타입            
            cashReceiptType = $('input[name=cashReceiptType]:checked').val(), // 소득공재/지출증빙
            depositType = $("#depositType option:selected").val(), // 현금영수증 타입(휴대폰번호, 사업자번호, 현금영수증카드)
            cashReceiptNumber = $('#cashReceiptNumber').val(), // 현금영수증 타입(휴대폰번호, 사업자번호, 현금영수증카드)에 따른 번호
            cashReceiptEmail = $('#cashReceiptEmail').val(); // 현금영수증 이메일                
        
        if(!$payType.length) {
            showAlert('결제타입을 선택해주세요.');  // 결제 타입 선택 안 했을 경우 알림
            return;
        }                
        
        if(!$('#returnAgree').is(':checked')) {
            showAlert('구매조건 및 취소/환불규정 약관동의에 동의해주세요');  // 약관 동의 체크 안 했을 경우 알림
            return;
        }
                        
        let payType = $payType.data('type'),
            confirmMsg = `${$payType.text()}(으)로 진행하시겠어요?`;  // 결제 타입에 대한 확인 메시지
        
        // 확인 메시지 팝업
        showConfirm(confirmMsg)
        .then(async (result) => {
            if(!result.value) return;  // 결제 진행하지 않으면 종료
            
            let amount = parseInt(uncomma($('#pointInput').val()));  // 결제 금액
            
            if(payType != 'deposit'){  // 결제 타입이 입금이 아닐 경우
                let orderId = 'dlpoint-' + memberIdx + '-' + generateUniqueRandomString(15);  // 고유 주문 ID 생성                    

                if(window.ReactNativeWebView) {
                    // React Native WebView를 통한 결제 처리
                    javascript:nav.locationHref(
                        `/mypage/payPoint?memberIdx=${memberIdx}&amount=${amount}&payType=${payType}`,
                        'z1'
                    );
                }else {
                    // 일반 웹에서 결제 처리
                    AUTHNICE.requestPay({
                        clientId: 'R2_b675488f4ff44cba95de01808d9054ad',  // 클라이언트 ID
                        appScheme: `dongldn://`,  // 앱 스킴
                        method: payType,  // 결제 방법
                        orderId: orderId,  // 주문 ID
                        amount: amount,  // 결제 금액
                        goodsName: '동글포인트',  // 상품 이름
                        returnUrl: 'https://dongl.co.kr/payPointReturnUrl',  // 결제 후 리턴 URL
                        fnError: function (result) {
                            showAlert('결제가 취소되었습니다.');  // 결제 취소 시 알림
                        }
                    });
                }
            }else{  // 입금 결제일 경우
                let $depositorName = $('#depositorName');  // 입금자명 입력란
                
                if(!$depositorName.val()) {
                    showAlert('입금자명을 입력해주세요', $depositorName.focus());  // 입금자명 미입력 시 알림
                    return;
                }
                
                // 현금영수증 관련
                if($payType.data('type') == 'deposit' && $('#isCashReceipt').is(':checked')) {                                   
                    if(!cashReceiptNumber) {
                        showAlert(`${$("#depositType option:selected").text()}에 대한 번호를 입력해주세요`);
                        return;
                    }else if(!$('#agreeCashReceipt').is(':checked')) {
                        showAlert(`현금영수증 발급을 위하여 휴대폰번호(사업자번호) 또는 현금영수증카드번호 수집에 동의해주세요`);
                        return;                
                    }else if(!validateEmail(cashReceiptEmail)) {
                        showAlert(`이메일 형식에 맞게 입력해주세요`);
                        return;                
                    }
                }
                
                // 서버에 입금 요청 보내기
                const payDepositRes = await postJson('/pointApi/payDeposit', {
                    point: amount,
                    depositorName: $depositorName.val(),
                    isCashReceipt: $('#isCashReceipt').is(':checked')? 'Y' : 'N',
                    cashReceiptType : cashReceiptType,
                    depositType : depositType,
                    cashReceiptNumber : cashReceiptNumber,
                    cashReceiptEmail : cashReceiptEmail
                });
                
                showAlert('포인트 주문이 접수되었습니다. 계좌이체를 진행해주세요.')  // 주문 접수 알림
                .then(() => {
                    nav.locationReplace('/mypage/point', 'clear/b4');  // 마이페이지로 리다이렉트
                });
            }
        });
    }
    
    $(function() {
        // 포인트 입력 시 실시간으로 금액 처리
        $('#pointInput').on('input', function() {
            let $this = $(this),
                $errPoint = $('#errPoint'),
                $krPoint = $('#krPoint');

            // 숫자와 콤마만 남기기
            $this.val($this.val().replace(/[^0-9,]/g, ''));

            // 콤마 추가 (숫자마다 콤마 추가)
            let point = uncomma($this.val());            
            $this.val(comma(point)); // 콤마 추가
                        
            // 유효성 검사 (3만원 이상 200만원 이하)
            if(point < 30000) {
                $errPoint.removeClass('hide').text('1회 최소 충전 가능한 금액은 3만원입니다.');               
            }else if(point > 2000000) {
                $errPoint.removeClass('hide').text('1회 최대 충전 가능한 금액은 200만원입니다.');
            }else {
                $errPoint.addClass('hide');
            }
            
            // 포인트가 입력되면 관련 UI 업데이트
            if(!point){
                $('#removePoint').addClass('hide');
                $krPoint.addClass('hide');
            }else {
                $('#removePoint').removeClass('hide');
                $krPoint.text(getKrPoint(point));  // 원화 표시
                $('#totalPrice').text(`${comma(point)}원 결제하기`);  // 결제 금액 표시
                
                if($errPoint.hasClass('hide')) {
                    $('#btnPayBox').removeClass('hide');  // 결제 버튼 표시
                }else {
                    $('#btnPayBox').addClass('hide');  // 에러 시 결제 버튼 숨기기
                }
                $krPoint.removeClass('hide');
            }
        });
    });
</script>
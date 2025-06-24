<?php
    $type = $_GET['type']; // scan 또는 delivery
    $mailboxIdx = $_GET['mailboxIdx'];
    $price = $type === 'scan' ? 1000 : 4000;
    if ($this->user['nickname'] === '정 현') {
        $price = 100;
    }
    $title = $type === 'scan' ? '스캔본 다운로드' : '택배 신청';
?>

<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script type="text/javascript" src="//dapi.kakao.com/v2/maps/sdk.js?appkey=<?=$this->config->item('kakaoJsKey')?>&libraries=services"></script>
<script src="/assets/js/nicepay.js<?=$this->config->item('ver')?>"></script>

<div id="letter">
    <div style="padding-bottom: 40px;">
        <div class="postBox" style="<?= $type === 'delivery' ? '' : 'display:none;' ?>">
            <div class="postHeader">
                <p class="selectMent">받으실 주소를 알려주세요!</p>                                                                   
                <!-- <span class="hide">                    
                    <label for="isSaveReceiver">이 주소 기억할래요! </label>
                    <input type="checkbox" id="isSaveReceiver" value="Y" checked>
                </span> -->
            </div>         
            <p class="guide">주소</p>
            <input type="hidden" id="receiverZipCode"/>
            <input type="text" id="receiverAddr" class="fieldInput" placeholder="주소" value="<?=$this->user['receiverAddr']?>" onclick="openDaumPostcode($('#receiverZipCode'), $('#receiverAddr'), $('#receiverAddrDetail'), 2)" readonly/>
            <p class="guide">상세주소</p>
            <input type="text" id="receiverAddrDetail" class="fieldInput" value="<?=$this->user['receiverAddrDetail']?>" placeholder="상세주소" <?=$this->user['addressIdx']? '' : 'disabled'?>/>
                        
            <p class="guide">이름</p>
            <input type="text" id="receiverName" class="fieldInput" value="<?=$this->user['receiverName']?>" placeholder="이름"/>
                        
            <p class="guide">전화번호(선택)</p>
            <input type="tel" id="receiverTel" class="fieldInput" value="<?=$this->user['receiverTel']?>" placeholder="전화번호" oninput="this.value = this.value.replace(/[^0-9]/g, '');"/>  
        </div>
       
        <p class="title hide" style="margin-bottom: 20px;">
            <span style="display: block; color: #9d9d9d; font-size: 12px;">(선택날짜 기준으로 발송시작)</span>
            예약 발송을 진행할까요?            
            <input type="radio" id="isReservY" name="isReserv" value="Y" onclick="checkReserv('Y')" > <label for="isReservY">네</label>
            <input type="radio" id="isReservN" name="isReserv" value="N" onclick="checkReserv('N')" checked> <label for="isReservN">아니요</label>
        </p>
        
        <div id="dateBox" class="dateBox hide">
            <input id="reservDate" type="date" value="<?=date('Y-m-d')?>" min="<?=date('Y-m-d')?>" max="<?=date('Y-12-31', strtotime('+1 year')); ?>">
        </div>
                        
        <div class="pointWrap <?=empty($this->user['idx'])? 'hide' : '' ?>">
            <p class="title">포인트를 사용하시겠어요?</p>
            <div class="point">
                <div>
                    <span class="pointBox">P</span>
                    <span class="pointMent">동글 포인트머니</span>            
                </div>
                <div>
                    <span class="pointMent" style="font-size:12px;">보유 <?=number_format($this->user['point'])?>원 <span id="beforePayPointText"></span></span>
                </div>
            </div>

            <div class="payPointBox">                
                <span>사용</span>
                <div>                                        
                    <input type="hidden" id="myPoint" value="<?=((int)$this->user['point']) + (!empty($saveIdx)? (int)$info['payPoint'] : 0) ?>">
                    <input type="tel" id="payPoint" value="<?=!empty($saveIdx)? number_format($info['payPoint']) : 0?>">
                    <button class="deletePoint" onclick="deletePoint()"><i class="fas fa-times"></i></button>
                    <button id="allPayBtn" class="allPayBtn <?=empty($saveIdx) && $this->user['isAllPoint'] == 'Y'? 'active' : ''?>" onclick="useAllPoint('Y')">전액사용</button>                               
                </div>
            </div>

            <div class="chkPointBox">
                <input type="checkbox" id="isAllPoint" value="Y" <?=$this->user['isAllPoint'] == 'Y'? 'checked' : ''?>>
                <label for="isAllPoint">항상 전액사용</label>
            </div>
        </div>
        
        <p class="title" style="margin-top:20px;">결제금액</p>
        
        <div class="priceBox">
            <p style="font-size: 13px; letter-spacing: -1px;">
                편지 - <span id="totalLetterPrice">0</span>원 / 
                사진 - <span id="totalPhotoPrice">0</span>원 / 
                문서 - <span id="totalFilePrice">0</span>원 /
                우편 - <span id="totalStampPrice">0</span>원 
            </p>
                        
            <span style="display:block; margin-top: 6px; font-size: 13px; letter-spacing: -1px;">
                <i class="fas fa-money-check"></i> 총금액 <span id="totalPriceText"><?=number_format($price)?></span>원
                <span class="<?=empty($this->user['idx'])? 'hide' : '' ?>">
                    - 
                    <div class="point" style="display:inline-block; margin: 10px 0;">
                        <div>
                            <span class="pointBox">P</span>                        
                        </div>
                    </div>
                    포인트 <span id="pointPriceText">0</span>원 
                    = <span id="realTotalPriceText">0</span>원
                </span>
            </span>
        </div>                   
        
        <input type="hidden" id="goodsName" value="<?= $title ?> 결제"/>
        <input type="hidden" id="realTotalPrice" value="0"/>
        
        <p class="title" style="margin-top: 20px; margin-bottom: 10px;">
            결제타입
            <span style="color: #6a6a6a; font-size: 12px; font-weight: 500;">(✅ 발송완료시 - 현금 3%, 카드 1% 포인트 지급)</span>
        </p>
                        
        <div id="depositBox" class="hide" style="margin-bottom:10px;">
            <p class="letterMent" style="color: #260606;">💸무통장 입금 계좌 안내</p>
            <? foreach($this->config->item('donglDeposit') as $bankNumber => $bank){ ?>            
                <p class="letterMent" onclick="copyToText($(this))" data-bank_number="<?=$bankNumber?>" style="text-decoration: underline; cursor: pointer;">
                    <?=$bank?> <?=$bankNumber?> <?=$this->config->item('donglDepositName')?>
                </p>
            <? } ?>
        </div>
        <div id="depositInfoBox" class="<?=$this->user['isCashReceipt'] == 'N'? 'hide' : ''?>">
            <input type="checkbox" id="isCashReceipt" value="Y" onclick="setIsCashReceipt()" style="margin-bottom:15px;" <?=$this->user['isCashReceipt'] == 'Y'? 'checked' : ''?>> 
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
                <input type="email" id="cashReceiptEmail" class="fieldInput" value="<?=$this->user['cashReceiptEmail']?>" style="margin-bottom: 10px;"/>
                
                <div style="display: flex;">
                    <input id="agreeCashReceipt" type="checkbox" style="<?=!isIos()? 'margin-top: -13px;' : ''?> margin-right: 7px;">
                    <label for="agreeCashReceipt" style="margin-bottom: 15px;">현금영수증 발급을 위하여 휴대폰번호(사업자번호) 또는 현금영수증카드번호 수집에 동의합니다.</label>
                </div>
            </div>
        </div>
        
        <div id="payBtnBox" class="payBtnBox">
        <? foreach($this->config->item('payType') as $key => $name){ 
            if($saveIdx > 0 && !empty($info) && $info['payType'] != $key && $info['payType'] != 'point') continue;
            if($key == 'bank' || $key == 'point') continue;
        ?>
            <button class="payTypeBtn" data-type="<?=$key?>" onclick="setPayBtn($(this));"><?=$name?></button>                
        <? } ?>
        </div>        

    <!--        depositType2-->
        <button class="btnPay" onclick="nicepayStart()"><span id="totalPrice">0원 결제하기</span></button>
    </div>
</div>

<script type="text/javascript">
    const info = <?=json_encode($info)?>;   // 사용자 저오
    const saveIdx = <?=(int)$saveIdx?>;     // 저장된 인덱스 (서버에서 전달된 값)
    const price = <?=$price?>;              // 현재 가격
    const type = '<?=$type?>'; // scan 또는 delivery


    // 로그인 상태 및 사용자 정보
    const isLogin = '<?=$isLogin?>';  // 로그인 여부 (서버에서 전달된 값)
    const memberIdx = <?=$this->user['idx']?>;  // 사용자 ID (서버에서 전달된 값)
    const today = '<?=date('Y-m-d')?>';  // 현재 날짜 (서버에서 전달된 값)
    
    useAllPoint('N');

    $(function () {
        // 초기 결제 타입이 지정되어 있다면 반영
        let $defaultPayType = $('.payTypeBtn.active');
        if ($defaultPayType.length) {
            setPayBtn($defaultPayType);
        } else {
            // 선택된 결제타입이 없다면 강제로 포인트 결제처럼 처리
            $('#depositInfoBox, #depositBox, #cashReceiptDetail').addClass('hide');
        }
    });


    
    // Android 기기인지 확인하는 함수
    function isAndroid() {
        const user = navigator.userAgent;
        // 사용자 에이전트에 "Android"가 포함되어 있으면 Android 기기임
        if (user.indexOf("Android") > -1) {
            return true;
        }
        return false;
    }

    // iOS 기기인지 확인하는 함수
    function isIos() {
        const user = navigator.userAgent;
        // 사용자 에이전트에 "iPhone"이 포함되어 있으면 iOS 기기임
        if (user.indexOf("iPhone") > -1) {
            return true;
        }
        return false;
    }

    // 모바일 기기인지 확인하는 함수 (iPhone 또는 Android)
    function isMobile() {
        const user = navigator.userAgent;
        // 사용자 에이전트에 "iPhone" 또는 "Android"가 포함되어 있으면 모바일 기기임
        if (user.indexOf("iPhone") > -1 || user.indexOf("Android") > -1) {
            return true;
        }
        return false;
    }

    if (isAndroid()) {
        window.onpopstate = handlePopState;  // 뒤로 가기 이벤트 핸들러 등록
    }

    /**
     * 포인트를 전액 사용하도록 설정하는 함수
     * @param {string} isClick - 클릭 여부 ('Y'일 경우 클릭된 상태로 처리)
     */
    function useAllPoint(isClick) {
        let $allPayBtn = $('#allPayBtn'),            
            myPoint = parseInt($('#myPoint').val()),  // 사용자 보유 포인트
            totalPrice = parseInt($('#realTotalPrice').val()),  // 총 결제 금액
            payPoint = parseInt(uncomma($('#payPoint').val()));  // 사용된 포인트

        // 클릭 시 'active' 클래스를 토글 (전액 결제 버튼 활성화/비활성화)
        if (isClick == 'Y') {
            $allPayBtn.toggleClass('active');   
        }

        // 'active' 클래스가 있으면 보유 포인트가 총 금액보다 크면 총 금액만큼 포인트 설정
        if ($allPayBtn.hasClass('active')) {
            if (myPoint > totalPrice) {
                $('#payPoint').val(comma(totalPrice));  // 포인트가 총 금액보다 크면 총 금액만큼 설정
            } else {
                $('#payPoint').val(comma(myPoint));  // 그렇지 않으면 보유 포인트만큼 설정
            }
        } else if (isClick == 'Y') {
            // 버튼이 비활성화되면 포인트를 0으로 설정
            $('#payPoint').val(0);
        }

        // 결제 포인트가 총 금액을 초과하면 포인트를 총 금액으로 맞춤
        if (totalPrice - payPoint < 0 && !autoCallSave) {
            $('#payPoint').val(comma(totalPrice));
        }

        // 클릭 시 가격 정보를 업데이트
        if (isClick == 'Y') {
            setPriceInfo();
        }
    }

    /**
     * 포인트를 삭제하고 기본 상태로 되돌리는 함수
     */
    function deletePoint() {
        // 포인트 입력을 0으로 설정
        $('#payPoint').val(0);

        // 'allPayBtn' 버튼에서 'active' 클래스를 제거
        $('#allPayBtn').removeClass('active');

        // 가격 정보를 업데이트
        setPriceInfo();
    }

    /**
     * 가격 정보를 계산하여 화면에 표시하는 함수
     */
    function setPriceInfo() {
        // 전체 가격 계산
        totalPrice = price;

        // 가격 정보 화면에 표시
        $('#realTotalPrice').val(totalPrice);

        // 포인트 사용 초기화
        useAllPoint('N');

        // 포인트 값 가져오기
        let point = 0;
        if ($('#payPoint').val()) {
            point = parseInt(uncomma($('#payPoint').val())); // 포인트가 입력되었으면 해당 값 파싱
        }

        // 총 가격과 포인트 차감 후 결제 금액 계산
        let $totalPrice = $('#totalPrice'),
            totalPriceText = comma(totalPrice - point) + `원 결제하기`;

        // 전체 금액, 포인트, 실제 결제 금액 표시
        $('#totalPriceText').text(comma(totalPrice));
        $('#pointPriceText').text(comma(point));
        $('#realTotalPriceText').text(comma(totalPrice - point));

        // 포인트로 결제할 경우, 결제 버튼 숨기기
        if (totalPrice - point == 0) {
            totalPriceText = `${comma(totalPrice)}원 포인트로 결제하기`;
            $('#depositBox, #payBtnBox, #depositInfoBox, #cashReceiptDetail').addClass('hide');
            $('#isCashReceipt').prop('checked', false);
        } else {
            $('#payBtnBox').removeClass('hide');
        }

        // 저장된 주문 정보가 있으면 이전 결제 정보를 표시
        if (saveIdx > 0) {
            if (info.payType == 'point') {
                let resultPay = totalPrice - point;
                if (resultPay == 0 && info.payPoint == point) {
                    resultText = '수정하기';
                } else {
                    let resultPayText = resultPay == 0 ? `${comma(point)}원 포인트 재결제하기` : `${comma(resultPay)}원 재결제하기`;
                    resultText = `<span class="through">${comma(info.totalPrice)}원(자동취소)</span> ${resultPayText}`;
                }
                $totalPrice.html(resultText);
            } else {
                if (info.payPoint == point && info.totalPrice == totalPrice) {
                    $totalPrice.text("수정하기");
                } else if (info.payType != 'deposit') {
                    let lastMoney = totalPrice - point == 0 ? `${comma(point)}원 포인트로 결제하기` : `${comma(totalPrice - point)}원 재결제하기`;
                    $totalPrice.html(`<span class="through">${comma(info.totalPrice)}원(자동취소)</span> ${lastMoney}`);
                } else {
                    totalPriceText = totalPrice - point == 0 ? `포인트로 결제하기` : comma(totalPrice - point) + `원 변경하기`;
                    $totalPrice.text(totalPriceText);
                }
            }
        } else {
            // 저장된 주문 정보가 없으면 일반적으로 총 가격 표시
            $totalPrice.text(totalPriceText);
        }
    }

    /**
     * 결제창 최초 요청 시 실행되는 함수
     * 결제 유형과 우편 선택 여부, 가격 등을 확인하고, 결제 진행 여부를 묻는 메시지를 표시합니다.
    */
    function nicepayStart(){
        let $payType = $('.payTypeBtn.active'),  // 활성화된 결제 타입 버튼
            realTotalPrice = parseInt($('#realTotalPrice').val()),  // 실제 결제 금액
            payPoint = parseInt(uncomma($('#payPoint').val()));  // 사용 포인트

        let payType = $payType.data('type'),  // 선택된 결제 타입
            confirmMsg = '',  // 결제 진행 여부 확인 메시지
            isSamePrice = false;  // 동일한 결제 금액인지 확인

        // 저장된 정보가 있고, 포인트 결제인 경우 포인트와 결제 금액 비교
        if (saveIdx > 0 && info.payType == 'point' && info.payPoint != payPoint && realTotalPrice - payPoint != 0) {
            if (!$payType.length) {
                showAlert('결제타입을 선택해주세요.');
                return;
            }

            confirmMsg = `${$payType.text()}(으)로 진행하시겠어요?`;
        } else {
            if (payType == undefined) {
                payType = 'point';  // 결제 타입이 선택되지 않은 경우 기본값 'point'
            }            

            // 저장된 결제 정보와 현재 결제 정보가 동일한지 비교
            isSamePrice = saveIdx > 0 && (info.payType == payType && info.payPoint == payPoint && info.realTotalPrice == (realTotalPrice - payPoint));

            if (isSamePrice) {
                confirmMsg = '수정을 완료하시겠어요?';  // 동일한 금액인 경우 수정 여부 확인
            } else if (realTotalPrice - payPoint == 0) {
                // 포인트 결제만 남았을 때
                payType = 'point';
                confirmMsg = `포인트로 ${saveIdx > 0 ? '재' : ''}결제하시겠어요?`;
            } else {
                if (!$payType.length) {
                    showAlert('결제타입을 선택해주세요.');
                    return;
                }

                confirmMsg = `${$payType.text()}(으)로 진행하시겠어요?`;  // 결제 타입을 선택했을 때 해당 결제 방식으로 진행할지 확인
            }
        }
        
        showConfirm(confirmMsg)
        .then(async (result) => {
            // 사용자가 '확인'을 클릭하지 않으면 종료
            if (!result.value) return;

            // 결제 정보 저장 시도
            let writeRes = await setMailboxPayment(payType);

            // 결제 정보 저장 실패 시, 경고 메시지 표시
            if (!writeRes.result) {
                showAlert(writeRes.msg);
                return;
            }


            // 기존 정보가 있고, 결제 방식이 동일하거나 포인트 결제인 경우 수정된 내용 확인 후 처리
            if (saveIdx > 0 && (isSamePrice || payType == 'point' || payType == 'deposit')) {
                showAlert("수정되었습니다.")
                    .then(() => {
                        // 로그인 상태에 따라 적절한 마이페이지로 리다이렉트
                        if (isLogin == 'Y') {
                            nav.locationReplace('/mypage/myMailbox', 'clear/b4');
                        } else {
                            nav.locationReplace(`/mypage/myLetter?mbName=${writeRes.mbName}&mbId=${writeRes.mbPhoneNumber}&mbPassword=${writeRes.mbPassword}`, 'clear/b4');
                        }
                    });
            }
            // 결제되지 않은 주문이고, 결제 방식이 'deposit'이 아닌 경우
            else if (writeRes.isPay == 'N' && payType != 'deposit') {
                let orderId = `mailbox-${writeRes.idx}-${type}`;
                let amount = parseInt($('#realTotalPrice').val()) - parseInt(uncomma($('#payPoint').val()));
                let productName = $('#goodsName').val();
                console.log("orderId = ", orderId);
                console.log("realTotalPrice", $('#realTotalPrice').val());
                console.log("payPoint", uncomma($('#payPoint').val()));
                console.log("최종금액", parseInt($('#realTotalPrice').val()) - parseInt(uncomma($('#payPoint').val())));

                // React Native 환경에서 결제 페이지로 이동
                if (window.ReactNativeWebView) {
                    nav.locationHref(
                        `/mypage/pay?mailboxIdx=${writeRes.idx}&payType=${payType}&orderId=${orderId}&amount=${amount}&productName=${encodeURIComponent(productName)}`,
                        'z1'
                    );
                } else {
                    console.log('AUTHNICE', AUTHNICE);
                    try {
                        // 결제 요청을 위한 NICE PAY API 호출
                        AUTHNICE.requestPay({
                            clientId: 'R2_b675488f4ff44cba95de01808d9054ad', /* 'S2_af4543a0be4d49a98122e01ec2059a56' */
                            appScheme: `dongldn://`,
                            method: payType,
                            orderId: orderId,
                            amount: parseInt($('#realTotalPrice').val()) - parseInt(uncomma($('#payPoint').val())),  // 결제 금액
                            goodsName: $('#goodsName').val(),
                            returnUrl: 'https://dongl.co.kr/payReturnUrl', // 결제 후 리턴될 URL
                            fnError: function (result) {
                                console.log("result = ", result);
                                showAlert('결제가 취소되었습니다.');
                            }
                        });
                    } catch (error) {
                        console.log("error = ", error);
                        showAlert('결제중 오류가 발생했습니다.');
                    }
                }
            }
            // 결제가 완료된 경우, 또는 입금결제인 경우 주문 완료 메시지 표시
            else {
                showAlert(payType == 'deposit' ? "주문조회 페이지에서 계좌이체를 진행해주세요." : "사서함 결제가 완료되었습니다.")
                    .then(() => {
                        nav.locationReplace('/mypage/myMailbox', 'clear/b4');
                        // 로그인 상태에 따라 적절한 마이페이지로 리다이렉트
                        // if (isLogin == 'Y') {
                        //     nav.locationReplace('/mypage/myMailbox', 'clear/b4');
                        // } else {
                        //     nav.locationReplace(`/mypage/myLetter?mbName=${writeRes.mbName}&mbId=${writeRes.mbPhoneNumber}&mbPassword=${writeRes.mbPassword}`, 'clear/b4');
                        // }
                    });
            }
        });
    }

    /**
     * 사서함 결제 저장 함수
     * @returns {Object}
     */
    async function setMailboxPayment(payType) {
        const payPoint = parseInt(uncomma($('#payPoint').val()));
        const totalPrice = price;
        const realTotalPrice = totalPrice - payPoint;

		if (payType == 'point' && (payPoint === 0 || realTotalPrice !== 0)) {
			showAlert('결제타입을 선택해주세요.');
			return;
		}

		if (payType !== 'point' && realTotalPrice === 0) {
			showAlert('결제금액이 0원일 경우 포인트 결제만 가능합니다.');
			return;
		}

        const type = '<?=$type?>'; // scan 또는 delivery

        const isCashReceipt = $('#isCashReceipt').is(':checked') ? 'Y' : 'N';
        const cashReceiptType = $('input[name=cashReceiptType]:checked').val();
        const depositType = $('#depositType').val();
        const cashReceiptNumber = $('#cashReceiptNumber').val();
        const cashReceiptEmail = $('#cashReceiptEmail').val();

        const req = {
            mailboxIdx: <?=json_encode($mailboxIdx)?>,
            type: type,
            amount: realTotalPrice,
            payType: payType,
            payPoint: payPoint,
            totalPrice: totalPrice,
            realTotalPrice: realTotalPrice,
            receiverName: $('#receiverName').val(),
            receiverAddr: $('#receiverAddr').val(),
            receiverAddrDetail: $('#receiverAddrDetail').val(),
            receiverTel: $('#receiverTel').val(),
        };

        console.log("결제 요청 데이터", req);


        if (type === 'scan') {
            req.isCashReceiptScan = isCashReceipt;
            req.cashReceiptTypeScan = cashReceiptType;
            req.depositTypeScan = depositType;
            req.cashReceiptNumberScan = cashReceiptNumber;
            req.cashReceiptEmailScan = cashReceiptEmail;
        } else if (type === 'delivery') {
            req.isCashReceiptDelivery = isCashReceipt;
            req.cashReceiptTypeDelivery = cashReceiptType;
            req.depositTypeDelivery = depositType;
            req.cashReceiptNumberDelivery = cashReceiptNumber;
            req.cashReceiptEmailDelivery = cashReceiptEmail;
        }

        const res = await postJson('/userApi/payMailbox', req);
        return res;
    }



    /**
     * 편지 정보를 서버로 전송하여 저장하는 함수
     * @param {boolean} isLoader - 로딩 상태를 표시할지 여부
     * @returns {Object} - 서버 응답 결과
    */
    // async function setWrite(payType, isTmpSave = 'N', isLoader = true){
    //     let receiverAddr = $('#receiverAddr').val(),  // 받는 사람의 주소를 가져옴
    //         receiverAddrDetail = $('#receiverAddrDetail').val(),  // 받는 사람의 상세 주소를 가져옴
    //         receiverName = $('#receiverName').val(),  // 받는 사람의 이름을 가져옴
    //         receiverTel = $('#receiverTel').val(),  // 받는 사람의 전화번호를 가져옴
    //         totalPrice = 0,  // 총 가격 초기화
    //         payPoint = 0,  // 사용된 포인트 초기화
    //         realTotalPrice = 0,  // 실제 결제할 금액 초기화
    //         isCashReceipt = $('#isCashReceipt').is(':checked')? 'Y' : 'N', // 현금영수증 신청(Y/N)
    //         cashReceiptType = $('input[name=cashReceiptType]:checked').val(), // 소득공재/지출증빙
    //         depositType = $("#depositType option:selected").val(), // 현금영수증 타입(휴대폰번호, 사업자번호, 현금영수증카드)
    //         cashReceiptNumber = $('#cashReceiptNumber').val(), // 현금영수증 타입(휴대폰번호, 사업자번호, 현금영수증카드)에 따른 번호
    //         cashReceiptEmail = $('#cashReceiptEmail').val(); // 현금영수증 이메일
        
    //     // 현금영수증 관련        
    //     if(payType == 'deposit' && isTmpSave != 'Y' && isCashReceipt == 'Y') {                                   
    //         if(!cashReceiptNumber) {
    //             return {result: false, msg: `${$("#depositType option:selected").text()}에 대한 번호를 입력해주세요`};   
    //         }else if(!$('#agreeCashReceipt').is(':checked')) {
    //             return {result: false, msg: `현금영수증 발급을 위하여 휴대폰번호(사업자번호) 또는 현금영수증카드번호 수집에 동의해주세요`};
    //         }else if(!validateEmail(cashReceiptEmail)) {
    //             return {result: false, msg: '이메일 형식에 맞게 입력해주세요'};
    //         }
    //     }
        
    //     // 총 가격 계산
    //     totalPrice = price;
    //     // 사용된 포인트 가져오기
    //     payPoint = parseInt(uncomma($('#payPoint').val()));
    //     // 실제 결제할 금액 계산
    //     realTotalPrice = totalPrice - payPoint;

    //     // 최소 결제 금액을 체크 (100원 미만이면 결제 불가)
    //     if (isTmpSave == 'N' && payType != 'point' && realTotalPrice < 100) {
    //         return {result: false, msg: '카드/페이의 최소결제금액은 100원입니다.'};
    //     }

    //     // 실제 결제 금액 설정
    //     $('#realTotalPrice').val(realTotalPrice);

    //     const setWriteRes = await postJson('/userApi/setWrite', {
    //         isLogin : isLogin,  // 로그인 상태
    //         memberIdx : memberIdx,  // 회원 인덱스
    //         payType : payType,  // 결제 유형 (포인트, 카드 등)
    //         receiverAddr : receiverAddr,  // 수신자 주소
    //         receiverAddrDetail : receiverAddrDetail,  // 수신자 상세 주소
    //         receiverName : receiverName,  // 수신자 이름
    //         receiverTel : receiverTel,  // 수신자 전화번호
    //         isSaveReceiver : $('#isSaveReceiver').is(':checked')? 'Y' : 'N',  // 수신자 주소 저장 여부
    //         mbName : mbName,  // 회원 이름
    //         mbPhoneNumber : mbPhoneNumber,  // 회원 전화번호
    //         mbPassword : mbPassword,  // 회원 비밀번호
    //         isAllPoint : $('#isAllPoint').is(':checked')? 'Y' : 'N',  // 포인트 전액 사용 여부
    //         totalPrice : totalPrice,  // 총 가격
    //         payPoint : payPoint,  // 결제에 사용된 포인트
    //         realTotalPrice : realTotalPrice,  // 실 결제 금액
    //         isCashReceipt : isCashReceipt, // 현금영수증 신청(Y/N),
    //         cashReceiptType : cashReceiptType, // 소득공재/지출증빙
    //         depositType : depositType, // 현금영수증 타입(휴대폰번호, 사업자번호, 현금영수증카드)
    //         cashReceiptNumber : cashReceiptNumber, // 현금영수증 타입(휴대폰번호, 사업자번호, 현금영수증카드)에 따른 번호            
    //         cashReceiptEmail : cashReceiptEmail // 현금영수증 이메일
    //     }, isLoader);  // 서버에 데이터를 POST로 전송하고, 로딩 상태 표시 여부를 설정

    //     return setWriteRes;  // 서버 응답 결과 반환
    // }
    
    /* 결제 방식 클릭함수 */
    function setPayBtn($this) {
        $('.payTypeBtn').removeClass('active');
        $this.addClass('active');

        const payType = $this.data('type');

        if (payType === 'deposit') {
            $('#depositBox, #depositInfoBox').removeClass('hide');

            // 현금영수증 신청 체크되어 있다면 상세도 보여줌
            if ($('#isCashReceipt').is(':checked')) {
                $('#cashReceiptDetail').removeClass('hide');
            }
        } else {
            $('#depositBox, #depositInfoBox, #cashReceiptDetail').addClass('hide');
            $('#isCashReceipt').prop('checked', false);
        }
    }

    
    /* 현금 영수증 체크 함수 */
    function setIsCashReceipt() {
        const isChecked = $('#isCashReceipt').is(':checked');
        const isDeposit = $('.payTypeBtn.active').data('type') === 'deposit';

        if (isChecked && isDeposit) {
            $('#cashReceiptDetail').removeClass('hide');
        } else {
            $('#cashReceiptDetail').addClass('hide');
        }
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

    $(function() {

        useAllPoint('N');
        setPriceInfo();
     
        // 키를 누를 때 (keydown) 이벤트 처리
        $('#letter').on('keydown', '.letterContent', function(e) {            
            lastKeyEvent = e;  // 마지막 키 이벤트를 저장 (e는 keydown 이벤트 객체)

            actionInput($(this));  // actionInput 함수 호출하여 입력 처리
        });

        // 키를 뗄 때 (keyup) 이벤트 처리
        $('#letter').on('keyup', '.letterContent', function(e) {                        
            actionInput($(this));  // actionInput 함수 호출하여 입력 처리
        });
        
        // 모바일 환경에서 .letterContent 텍스트 영역에 포커스가 가면 실행되는 이벤트
        if(isMobile()) {
            // 텍스트 영역에 포커스가 갈 때
            $('#letter').on('focus', '.letterContent', function(e) {                                                      
                let textarea = $(this),
                    topOffset = textarea.offset().top,  // 텍스트 영역의 위치를 가져옴
                    cursorPosition = getCaretCoordinates(textarea[0], textarea.prop('selectionStart')),  // 커서의 좌표 계산
                    index = parseInt(textarea.data('index'));  // 현재 텍스트 영역의 인덱스                
                
                // 스크롤을 해당 텍스트 영역에 맞게 설정
                setTop(textarea, index, cursorPosition);

                // 헤더와 커스텀 박스를 숨겨서 텍스트 입력에 집중할 수 있게 함
                $('#hd_wrapper, #customBox').addClass('hide');

                // 뷰포트를 확대하여 키보드가 화면을 차지할 수 있도록 설정
                document.getElementById('viewport').setAttribute('content', 'width=device-width, initial-scale=1.0, user-scalable=1, maximum-scale=2');
            });            
            
            // 텍스트 영역에서 포커스가 떨어질 때 (IOS - 정상작동, ANDROID - 이벤트 안먹힘)
            $('#letter').on('blur', '.letterContent', function(e) {
                letterBlurEvent();
            });
            
            if(isAndroid()) {                
                var originalSize = jQuery(window).width() + jQuery(window).height();
     
                // 창의 사이즈 변화가 일어났을 경우 실행된다.
                jQuery(window).resize(function() {
                    // 처음 사이즈와 현재 사이즈가 변경된 경우
                    // 키보드가 올라온 경우
                    if(jQuery(window).width() + jQuery(window).height() != originalSize) {                                                
                    }
                    // 처음 사이즈와 현재 사이즈가 동일한 경우
                    // 키보드가 다시 내려간 경우
                    else {
                       letterBlurEvent();
                    }
                });
            }
        }
        
        /* 포인트 입력 JQUERY 처리 영역 */        
        $('#payPoint').on('keyup', function(){
            let $el = $(this),
                myPoint = parseInt($('#myPoint').val()),  // 보유 포인트
                totalPrice = parseInt($('#realTotalPrice').val()),  // 결제 금액
                setPrice = uncomma($el.val());  // 입력한 포인트 값에서 쉼표 제거

            // 입력한 포인트가 보유 포인트보다 많은 경우
            if(setPrice > myPoint){
                showAlert('보유 포인트보다 많은 포인트를 사용하실 수 없습니다.', deletePoint());
                return false;
            }
            // 입력한 포인트가 결제 금액보다 많은 경우
            else if(setPrice > totalPrice){
                showAlert('결제금액보다 많은 포인트를 사용하실 수 없습니다.', deletePoint());
                return false;
            }

            // 입력한 포인트가 보유 포인트나 결제 금액과 같은 경우
            if(setPrice == myPoint || setPrice == totalPrice){                
                $('#allPayBtn').addClass('active');
            }else{
                $('#allPayBtn').removeClass('active');
            }

            // 0을 제거하고, 쉼표 추가하여 입력 필드에 다시 설정
            $el.val(comma(setPrice.replace(/^0+/, '')));

            // 결제 정보 갱신
            setPriceInfo();
        });
        
    });
</script>

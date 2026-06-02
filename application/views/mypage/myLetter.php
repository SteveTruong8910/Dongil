<style>
    #ft_menu{
        display: none;
    }
</style>

<div id="myLetter">
    <? if(count($list)){ ?> 
        <div class="letterList">
            <? if($isDeposit) { ?>
                <div style="margin-bottom: 12px; text-align: center;">
                    <p style="margin-bottom: 5px; font-size: 14px; font-weight: bold; letter-spacing: -1px;">💸무통장 입금 계좌 안내</p>
                    <? foreach($this->config->item('donglDeposit') as $bankNumber => $bank){ ?>            
                        <p onclick="copyToText($(this))" data-bank_number="<?=$bankNumber?>" style="text-decoration: underline; cursor: pointer; margin-bottom: 5px;">
                            <?=$bank?> <?=$bankNumber?> <?=$this->config->item('donglDepositName')?>
                        </p>
                    <? } ?>
                    
                    <p style="color: #ff4d4d; font-size:12px; font-weight: bold; margin-top: 3px;">1.무통장 입금 시 필히 발신자 이름과 동일한 이름으로 입금부탁드립니다</p>
                    <p style="color: #ff4d4d; font-size:12px; font-weight: bold; margin-top: 3px;">2.무통장의 경우 실결제 금액만 입금하시면 됩니다.</p>
                    <p style="color: #ff4d4d; font-size:12px; font-weight: bold; margin-top: 3px;">3.입금이 확인 된 편지지는 수정이 불가능합니다. 최종적으로 수정 후 입금바랍니다.</p>
                </div>
            <? } ?>
            <p class="totalLetter">총 <?=count($list)?> 장</p>            

        <? foreach($list as $key => $data){ ?>
            <?
                $viewUrl = "/mypage/myLetterView/{$data['idx']}";
                if(empty($this->user)) {
                    $viewUrl .= "?mbName={$mbName}&mbId={$mbId}&mbPassword={$mbPassword}";
                }
            ?>
            <a>
                <div class="letterListBox" style="<?=$data['state'] == 'B'? 'border: 1px solid #f71313' : ''?>">                    
                    <a style="width: 98%; display: block; text-align: right; font-size: 12px; margin-bottom: 4px; text-decoration: underline;" href="<?=$viewUrl?>">
                        작성한 내용확인
                    </a>
                    <div></div>
                    <p style="width:100%;"><?=$data['orderId']?></p>
                    <p class="guide">주문상태</p>
                    <p class="state <?=$data['state']?>"><?=$this->config->item('state')[$data['state']]?></p>
                    <? if(!empty($data['registrationNumber'])) { ?>
                    <p class="guide">등기번호</p>
                    <p onclick="location.href='/mypage/delivery/<?=$data['registrationNumber']?>';"style="text-decoration: underline; cursor:pointer;">
                        <?=formatWithHyphens($data['registrationNumber'])?>
                    </p>
                    <? } ?>
                    <p class="guide">결제타입</p>
                    <p><?=$this->config->item('payType')[$data['payType']]?></p>
                    <p class="guide">받는사람</p>
                    <p><?=$data['receiverName']?></p>
                    <p class="guide" style="display: inline-block; min-width: 120px; vertical-align: top;">받는사람 주소</p>
                    <p style="display: inline-block; vertical-align: top;"><?=$data['receiverAddr']?><br><?=$data['receiverAddrDetail']?></p>
                    <p class="guide">상품명</p>
                    <p><?=$data['productName']?></p>
                    <p class="guide">우편</p>
                    <p><?=$this->config->item('stamp')[$data['stamp']]['name']?></p>
                    <p class="guide">편지/사진/문서</p>
                    <p><?=$data['totalLetterCnt']?>장 / <?=$data['totalPhotoCnt']?>장 / <?=$data['totalPdfFileCnt']?>개</p>
                    <p class="guide">결제금액(실결제/포인트)</p>
                    <p><?=number_format($data['realTotalPrice'])?>원 / <?=number_format($data['payPoint'])?>원</p>                    
                    <p class="guide">총 결제금액</p>
                    <p><?=number_format($data['totalPrice'])?>원</p>                    
                    <p class="guide">주문일자</p>
                    <p><?=$data['regDate']?></p>

                    <? if( ($data['state'] == 'B' || ($data['state'] == 'W' && $data['payType'] != 'deposit')) && empty($data['registrationNumber'])) { ?>
                        <button class="reviewBtn" onclick="fixLetter(<?=$data['idx']?>); return false;">수정하기</button  >                                          
                        <button class="deleteBtn" onclick="onCancelOrder(<?=$data['idx']?>); return false;">주문취소</button>                        
                    <? }else if($data['state'] == 'F'){ ?>
                        <? if($data['isReview'] == 'Y'){ ?>
                            <button class="reviewFBtn">리뷰작성 완료</button>
                        <? }else{ ?>
                            <button class="reviewBtn" onclick="onReviewPopup('show', <?=$key?>); return false;">리뷰작성</button>
                        <? } ?>
                    <? } ?>
                </div>
            </a>
        <? } ?>
        </div>
    <? }else{ ?>
        <p class="empty">편지를 작성해보세요!</p>
    <? } ?>
</div>

<div id="reviewPopup" class="popupContainer">
    <div class="popupBox">
        <div class="title">
            <p>리뷰 작성</p>
            <i class="fas fa-times" onclick="onReviewPopup('close');"></i>
        </div>
        
        <p class="guide">별점</p>
        <div class="starWrap">
            <i class="far fa-star" onclick="setStar(1);"></i>    
            <i class="far fa-star" onclick="setStar(2);"></i>
            <i class="far fa-star" onclick="setStar(3);"></i>
            <i class="far fa-star" onclick="setStar(4);"></i>
            <i class="far fa-star" onclick="setStar(5);"></i>
        </div>
        
        <p class="guide">리뷰 내용</p>
        <textarea id="reviewContent"></textarea>
        
        <div class="btnBox">
            <button class="cancelBtn" onclick="onReviewPopup('close');">취소</button>
            <button class="accessBtn" onclick="setReview();">작성완료</button>
        </div>
    </div>
</div>

<script>
    // 서버에서 전달된 리스트 데이터를 JavaScript 객체로 변환
    const list = <?=json_encode($list)?>;
    var lastIndex = 0;        

    // 모바일 기기 여부 확인 함수
    function isMobile() {
        const user = navigator.userAgent;
        if (user.indexOf("iPhone") > -1 || user.indexOf("Android") > -1) {
            return true;
        }
        return false;
    }

    // 등기번호를 기반으로 우체국 배송 조회 페이지 열기
    function checkRegistr(registrationNumber) {
        if (isMobile()) {
            // 모바일 버전 조회 URL
            window.open(`https://m.epost.go.kr/postal/mobile/mobile.trace.RetrieveDomRigiTraceList.comm?target_command=&JspURI=&ems_gubun=E&sid1=${registrationNumber}&POST_CODE=&mgbn=trace&traceselect=1&keyword=${registrationNumber}`, '_blank');            
        } else {
            // PC 버전 조회 URL
            window.open(`https://service.epost.go.kr/trace.RetrieveDomRigiTraceList.comm?sid1=${registrationNumber}&displayHeader=`, '_blank');
        }
    }

    // 작성한 편지를 수정하는 함수
    async function fixLetter(writeIdx) {
        const getStateRes = await postJson('/userApi/getLetterState', {
          writeIdx: writeIdx
        });

        if (!getStateRes.result) {
          showAlert(getStateRes.msg);
          return;
        }

        // 서버에서 받아온 state 값이 'S', 'I', 'P', 'F' 중 하나라면 수정 불가
        if (['S', 'I', 'P', 'F'].includes(getStateRes.state)) {
            showAlert("지금은 수정하실 수 없습니다.");
            return;
        }

        location.href = `/letter?idx=${writeIdx}`;
    }

    // 주문 취소 함수
    function onCancelOrder(writeIdx) {
        showConfirm("해당 주문을 취소하시겠습니까?")
        .then(async (result) => {            
            if (!result.value) return; // 사용자가 '취소'를 선택한 경우 종료

            // 주문 취소 API 호출
            const onCancelOrderRes = await postJson('/userApi/cancelOrder', {
                writeIdx: writeIdx
            });

            if (!onCancelOrderRes.result) {
                showAlert(onCancelOrderRes.msg);
                return;
            }

            // 취소 완료 후 알림 및 페이지 새로고침
            showAlert('취소처리 되었습니다. 취소한 편지는 임시저장에서 확인가능합니다.')
            .then(() => {
                location.reload();
            });
        });
    }

    // 리뷰 팝업 열기/닫기
    function onReviewPopup(type, index) {
        let $reviewPopup = $('#reviewPopup');
        if (type == 'show') {
            $reviewPopup.addClass('show');
            setStar(0); // 별점 초기화
            $('#reviewContent').val(''); // 리뷰 내용 초기화
            lastIndex = index; // 마지막으로 선택한 리뷰 인덱스 저장
        } else { 
            // 팝업 닫기
            $reviewPopup.removeClass('show');
        }
    }

    // 별점 설정 함수
    function setStar(star) {
        $('.fa-star').removeClass('fas').addClass('far'); // 모든 별 초기화
        
        for (let n = 0; n < star; n++) {
            $('.fa-star').eq(n).removeClass('far').addClass('fas'); // 선택한 별까지 활성화
        }
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
            showAlert('계좌번호가 복사되었습니다.<br>' + text);
        }

        // 클립보드에 복사 실행
        copyToClipboard(bankNumber);
    }

    // 리뷰 작성 및 서버 전송
    async function setReview() {
        let $star = $('.fas.fa-star'),
            $reviewContent = $('#reviewContent');
                
        // 별점이 선택되지 않은 경우
        if (!$star.length) {
            showAlert('별점을 선택해주세요.');
            return;
        }

        // 리뷰 내용이 비어있는 경우
        if (!$reviewContent.val()) {
            showAlert('리뷰 내용을 입력해주세요.', $reviewContent.focus());
            return;
        }

        // 리뷰 등록 API 호출
        const setReviewRes = await postJson('/userApi/setReview', {
            writeIdx: list[lastIndex]['idx'], // 선택한 아이템의 인덱스
            star: $star.length, // 선택한 별 개수
            reviewContent: $reviewContent.val() // 입력한 리뷰 내용
        });

        if (!setReviewRes.result) {
            showAlert(setReviewRes.msg);
            return;
        }

        // 등록 완료 후 알림 및 페이지 새로고침
        showAlert('등록되었습니다.')
        .then(() => {
            location.reload();
        });
    }
</script>
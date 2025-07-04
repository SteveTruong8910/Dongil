<style>
    #category{ display: none; }
    #container{ width: auto; height: auto; padding: 0; font-family: unset !important; color: black;}

    .stampWrap .circle {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    @media print {
        #signView .applicant-label {
            color: red !important;
        }
    }

    #signView.big .delStamp{    
        bottom: calc(10px + 2.4cm);
    }

	.delStamp-1{
		position: absolute;
		bottom: calc(10px + 4.0cm);
		font-size: 18px;
	}
</style>

<script src="/assets/js/jquery-barcode.min.js"></script>
    <?php
        $forceBig = isset($_GET['big']) && $_GET['big'] === 'Y';
    ?>
    <!--소봉투/대봉투 구분-->
    <? if($forceBig || (!empty($big) && ($data['totalPdfFileCnt'] > 0 || (int)$data['totalLetterCnt'] + (int)$data['totalPhotoCnt'] >= $this->config->item('maxBigCnt'))) || 
          (empty($big) && $data['totalPdfFileCnt'] == 0 && (int)$data['totalLetterCnt'] + (int)$data['totalPhotoCnt'] < $this->config->item('maxBigCnt') && ($data['stamp'] == 0 || !empty($data['registrationNumber'])))) { ?>
    <div id="signView" class="<?=!empty($big)? 'big a4signsheet' : 'signsheet'?>" style="position:relative; <?=!empty($big)? 'min-width: 36cm; min-height: 23cm;' : ''?>">
        <div class="sign signSend">
            <p class="addrTitle">
                [보내는 사람]
                <small class="sub-text">동글: 부산시 사하구 하신중앙로 27번길 6</small>
            </p>
            <div style="border: 1px solid red; color: red !important; display: inline-block; padding: 0px 2px; margin-bottom: 0px; line-height: 1.3; position: relative; top: -15px; white-space: nowrap; box-sizing: border-box; font-size: 12px;">
                신청인
            </div>
            <p style="margin-top: -18px;"><?=removePostboxNumbers($data['senderAddr'], false)?></p>
            <p><?=$data['senderAddrDetail']?></p>
            <p><?=$data['senderName'] . '   ' . ($data['displaySenderPhone'] == 1 ? $data['senderTel'] : '')?></p>
            <p class="zipcode"><?=$data['senderZipcode']?></p>
        </div>

        <div class="stampWrap">
            <div class="circle" id="postOfficeContainer">
                <!-- 여기에 선택된 우체국 정보가 들어갑니다 -->
            </div>
        </div>
                    
        <div id="signReceive" class="sign signReceive">
             <div class="barcodeBox" style="<?= $forceBig ? 'margin-top: -150px;' : ''?>">
                 <? if($data['registrationNumber']) { ?>
                 <div class="<?=$data['stamp'] == 0? 'hide' : ''?>" style="display: flex; align-items: center;"> 
                     <? if($data['special'] == '선택등기') { ?>
                     <span class="choiceCircle">선택등기</span>
                     <? }else if($data['special'] == '준등기'){ ?>
                     <span class="choiceSquare">준등기</span>
                     <? } ?>
                    <div class="barcodeWrap" style="margin-top: 4px;">
                        <div class="barcodeHead">
                            <span><strong>부1</strong> 부산M</span>
                            <span><strong>603</strong> 부산사상</span>
                            <span><strong>03</strong> 12</span>
                        </div>
                        <div class="barcode" style=""></div>
                        <div class="borcodeFooter">
                            <span>등기</span>
                            <span><?=formatWithHyphens($data['registrationNumber'])?></span>
                            <span><?=replaceHyphensWithSlashes($data['dateReceipt'])?></span>
                        </div>

                        <? if($data['isNotReturn'] == 'Y') { ?>
                        <p class="notReturnTxt"><strong>반송불요</strong>(사전접수)</p>
                        <? } ?>                    
                    </div>
                </div>
                <? } ?>

			    <? if($data['isExpress'] == 'Y' && $forceBig) { ?>
				  <div class="delStamp-1">
					 익일특급
				  </div>
			    <? } ?>
            </div>
            <div class="receiveWrap">
                <p class="addrTitle">[받는 사람]</p>
                <p><?=removePostboxNumbers($data['receiverAddr'], false)?> <?=$data['receiverAddrDetail']?></p>                
                <p><?=$data['receiverName']?></p>        
                <p class="zipcode"><?=$data['recevierZipcode']?></p>
            </div>
        </div>

		<? if($data['isExpress'] == 'Y' && !$forceBig) { ?>
			<div class="delStamp">
				익일특급
			</div>
		<? } ?>
        <p class="signWriteId"><?=$data['writeId']?></p>
    </div>
    <? } ?>


<script>

    // 기본 설정 함수
    function defaultSetup(){
        /* 바코드 생성 */
        // 클래스 'barcode'를 가진 요소에 대해 바코드를 생성합니다.
        $('.barcode').barcode('<?=$data['registrationNumber']?>', 'code128', {
            "barWidth": 2,  // 바코드의 각 바의 너비를 2로 설정
            "barHeight": 40,  // 바코드의 높이를 40으로 설정
            "fontSize": 14,  // 바코드 아래의 숫자 폰트 크기를 14로 설정
            "showHRI": false,  // 바코드 아래에 숫자(문자)를 표시하지 않도록 설정
            "output": 'svg',  // 바코드 출력을 SVG 형식으로 설정 (다른 옵션: css, bmp, canvas)
        });        
        
        // 인쇄 전/후 처리할 함수 설정
        window.onbeforeprint = beforePrintSetup;  // 인쇄 전 'beforePrintSetup' 함수 호출
        window.onafterprint = afterPrintSetup;    // 인쇄 후 'afterPrintSetup' 함수 호출
        printPage();  // 페이지 인쇄 시작
    }

    // 페이지를 인쇄하는 함수
    function printPage(){
         window.print();  // 브라우저의 인쇄 기능을 실행합니다.
    }

    // 인쇄 전에 호출될 함수
    function beforePrintSetup(){        
        // 인쇄할 콘텐츠만을 보여주기 위해 페이지의 HTML을 'html' 아이디를 가진 요소의 HTML로 교체
        document.body.innerHTML = document.getElementById('html').innerHTML;
    }

    // 인쇄 후 호출될 함수
    function afterPrintSetup(){
        // 인쇄가 완료되면 부모 창에 'downloadComplete' 메시지를 보냄
        window.opener.postMessage('downloadComplete', window.location.origin);
        
        // 창을 닫습니다.
        window.close();
    }

    function setPostOffice() {
        const urlParams = new URLSearchParams(window.location.search);
        const postOffice = urlParams.get('postOffice') || '부산신평2동'; // 기본값 설정
        const isBigEnvelope = urlParams.get('big') === 'Y'; // 대봉투 여부 확인

        let postOfficeHTML = '';

        if (postOffice === '부산신평2동') {
            postOfficeHTML = `
                <p><span style="top-padding: 8px">부산신평2동</span><br>우체국</p>
                <hr class="hr1">
                <hr class="hr2">
                <p>요금별납</p>
            `;
        } else if (postOffice === '부산사하') {
            postOfficeHTML = `
                <p>부산사하우체국</p>
                <hr class="hr1">
                <hr class="hr2">
                <p>요금별납</p>
            `;
        }

        const container = document.getElementById('postOfficeContainer');
        if (container) {
            container.innerHTML = postOfficeHTML;
        } else {
            console.error("❌ Error: postOfficeContainer 요소를 찾을 수 없습니다.");
        }

        // ✅ 대봉투가 선택되었을 경우 `#signView`에 `big` 클래스를 추가
        const signView = document.getElementById('signView');
        if (signView) {
            if (isBigEnvelope) {
                signView.classList.add('big');
                console.log("📦 대봉투 출력 설정 완료.");
            } else {
                signView.classList.remove('big');
                console.log("✉️ 소봉투 출력 설정 완료.");
            }
        } else {
            console.error("❌ Error: signView 요소를 찾을 수 없습니다.");
        }
    }

    // 페이지가 완전히 로드된 후 `setPostOffice` 실행
    document.addEventListener("DOMContentLoaded", function() {
        console.log("📢 DOM 로드 완료!");
        setPostOffice();
        defaultSetup();
        setTimeout(() => {
            window.print();
        }, 1000);
    });



</script>

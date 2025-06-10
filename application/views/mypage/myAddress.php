<style>
    #mypage{ padding-top: 0px; min-height: calc(100vh - 50px); position: relative; }
    #ft_menu{ display: none; }
</style>

<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<link rel="stylesheet" type="text/css" href="/assets/css/view/myAddress.css<?=$this->config->item('ver')?>">

<div id="mypage">
    <button class="addAddrBtn" onclick="onPostPopup('show');">주소지 등록(총 <?=count($postList)?>개)</button>
    <? if(!count($postList)) { ?>
            <p class="empty">저장된 주소지가 없어요!</p>
    <? }else { ?>        
        <? foreach($postList as $key => $data){ ?>
            <div class="postBox">
                <button class="choiceBtn <?=$data['idx'] == $this->user['addressIdx']? 'active': ''?>" onclick="choiceAddress($(this), <?=$data['idx']?>);">기본</button>            
                <p class="postHead"><i class="fas fa-truck" style="margin-right: 5px;"></i> 보내는분</p>
                <p class="postContent">주소 - <?=$data['senderAddr']?></p>
                <p class="postContent">상세주소 - <?=$data['senderAddrDetail']?></p>
                <p class="postContent">성함 - <?=$data['senderName']?></p>
                <p class="postContent">휴대번호 - <?=$data['senderTel']?></p>

                <p class="postHead" style="margin-top:10px;"><i class="fas fa-map-marker-alt" style="margin-right: 5px;"></i> 받는분</p>
                <p class="postContent">주소 - <?=$data['receiverAddr']?></p>
                <p class="postContent">상세주소 - <?=$data['receiverAddrDetail']?></p>
                <p class="postContent">성함 - <?=$data['receiverName']?></p>
                <p class="postContent">휴대번호 - <?=$data['receiverTel']?></p>            
                <button class="postChoiceBtn" onclick='onPostPopup("show", <?=json_encode($data)?>, <?=$data['idx']?>)'>수정</button>            
                <button class="postChoiceBtn" onclick="onDeletePost(<?=$data['idx']?>)">삭제</button>
            </div>
        <? } ?>
    <? } ?>
</div>

<div id="addrPopup" class="popupContainer">
    <div class="popupBox">
        <div class="title">
            <p id="addrPopupTitle">주소록 등록</p>
            <i class="fas fa-times" onclick="onPostPopup('close');"></i>
        </div>        
        <input type="hidden" id="addressIdx">
        
        <div id="postList">
            <div class="postBox">            
                <div class="postHeader">
                    <p class="selectMent">보내시는 분이 누구신가요?</p>                                    
                </div>

                <p class="guide">주소</p>
                <input type="hidden" id="senderZipCode"/>
                <input type="text" id="senderAddr" class="fieldInput" value="" placeholder="주소" onclick="openDaumPostcode($('#senderZipCode'), $('#senderAddr'), $('#senderAddrDetail'), 1)" readonly/>
                <p class="guide">상세주소</p>
                <input type="text" id="senderAddrDetail" class="fieldInput" value="" placeholder="상세주소"/>
                <p class="guide">이름</p>
                <input type="text" id="senderName" class="fieldInput" value="" placeholder="이름"/>
                <p class="guide">전화번호</p>
                <input type="tel" id="senderTel" class="fieldInput" value="" placeholder="전화번호" oninput="this.value = this.value.replace(/[^0-9]/g, '');"/>                
            </div>
        
            <div class="postBox">
                <div class="postHeader">
                    <p class="selectMent">받으시는 분이 누구신가요?</p>                    
                </div>
                <p class="guide">주소</p>
                <p class="letterMent">📮사서함인 경우 'XX우체국 사서함'까지만 기입. 사서함 번호는 상세주소에 적어주세요.</p>
                <input type="hidden" id="receiverZipCode"/>
                <input type="text" id="receiverAddr" class="fieldInput" placeholder="주소" value="" onclick="openDaumPostcode($('#receiverZipCode'), $('#receiverAddr'), $('#receiverAddrDetail'), 2)" readonly/>
                <p class="guide">상세주소</p>
                <input type="text" id="receiverAddrDetail" class="fieldInput" value="" placeholder="상세주소"/>

                <p class="guide">이름</p>
                <p class="letterMent" style="margin-bottom:5px;">✅수감번호인 경우 수감번호+이름 입력해주세요.</p>
                <input type="text" id="receiverName" class="fieldInput" value="" placeholder="이름"/>

                <p class="guide">전화번호</p>
                <p class="letterMent">✅받는 사람이 전화번호가 없을 경우 보내는 사람 전화번호를 입력해주세요.</p>
                <input type="tel" id="receiverTel" class="fieldInput" value="" placeholder="전화번호" oninput="this.value = this.value.replace(/[^0-9]/g, '');"/>            
            </div>

            <button class="btnNext" onclick="setAddress()">작성완료</button>
        </div>
    </div>
</div>

<script>
    // 주소 팝업을 열거나 닫는 함수
    async function onPostPopup(type, data = {}, addressIdx = 0) {
        let $addrPopup = $('#addrPopup');
        
        // 팝업에 표시할 입력 필드 값 설정
        $('#addressIdx').val(addressIdx);
        $('#senderAddr').val(!addressIdx ? '' : data['senderAddr']);
        $('#senderAddrDetail').val(!addressIdx ? '' : data['senderAddrDetail']);
        $('#senderName').val(!addressIdx ? '' : data['senderName']);
        $('#senderTel').val(!addressIdx ? '' : data['senderTel']);
        $('#receiverAddr').val(!addressIdx ? '' : data['receiverAddr']);
        $('#receiverAddrDetail').val(!addressIdx ? '' : data['receiverAddrDetail']);
        $('#receiverName').val(!addressIdx ? '' : data['receiverName']);
        $('#receiverTel').val(!addressIdx ? '' : data['receiverTel']);
        
        // type이 'show'이면 팝업을 보여주고, 아니면 팝업을 숨김
        if (type == 'show') {            
            $addrPopup.addClass('show');
        } else { 
            // 'close'일 경우 팝업을 닫음
            $addrPopup.removeClass('show');
        }
    }        
    
    // 주소를 삭제하는 함수
    function onDeletePost(addressIdx) {
        // 삭제 확인 메시지 표시
        showConfirm("해당 주소지를 삭제하시겠습니까?")
        .then(async (result) => {
            // 사용자가 삭제를 취소한 경우 종료
            if (!result.value) return;
            
            // 주소 삭제 API 호출
            const onDeletePostRes = await postJson('/addressApi/deleteAddress', {
                addressIdx: addressIdx                
            });

            // 삭제 결과 확인
            if (!onDeletePostRes.result) {
                showAlert(onDeletePostRes.msg); // 실패 메시지 표시
                return false;
            } 

            // 삭제 완료 메시지 표시 후 페이지 새로 고침
            showAlert("삭제되었습니다.")
            .then(() => {
                location.reload();
            });
        });
    }
    
    // 주소를 저장하는 함수
    async function setAddress() {
        // 입력 필드 값 가져오기
        let $senderAddr = $('#senderAddr'),
            $senderAddrDetail = $('#senderAddrDetail'),
            $senderName = $('#senderName'),
            $senderTel = $('#senderTel'),
            $receiverAddr = $('#receiverAddr'),
            $receiverAddrDetail = $('#receiverAddrDetail'),
            $receiverName = $('#receiverName'),
            $receiverTel = $('#receiverTel');
        
        // 주소 저장 API 호출
        const setAddressRes = await postJson('/addressApi/setAddress', {
            addressIdx: $('#addressIdx').val(),
            senderAddr: $senderAddr.val(),
            senderAddrDetail: $senderAddrDetail.val(),
            senderName: $senderName.val(),
            senderTel: $senderTel.val(),
            receiverAddr: $receiverAddr.val(),
            receiverAddrDetail: $receiverAddrDetail.val(),
            receiverName: $receiverName.val(),
            receiverTel: $receiverTel.val()
        });
        
        // 저장 결과 확인
        if (!setAddressRes.result) {
            swal(setAddressRes.msg); // 실패 메시지 표시
            return false;
        } 
        
        // 저장 완료 메시지 표시 후 페이지 새로 고침
        showAlert("저장되었습니다.")
        .then(() => {
            location.reload();
        });
    }
    
    // 기본 주소지를 변경하는 함수
    async function choiceAddress($this, addressIdx) {
        // 주소 변경 API 호출
        const choiceAddressRes = await postJson('/addressApi/changeAddress', {
            addressIdx: addressIdx
        });
        
        // 변경 결과 확인
        if (!choiceAddressRes.result) {
            showAlert(choiceAddressRes.msg); // 실패 메시지 표시
            return false;
        } 
        
        // 변경 완료 메시지 표시 후 페이지 새로 고침
        showAlert("기본 주소지가 변경되었습니다.")
        .then(() => {
            location.reload();
        });
    }
    
    $(function() {
        // 페이지 로드 후 실행되는 함수 (현재는 비어 있음)
    });
</script>
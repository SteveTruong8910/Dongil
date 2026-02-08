<style>
    #mypage{ padding-top: 0px; min-height: calc(100vh - 50px); position: relative; }
    #ft_menu{ display: none; }

	.addrBtn {
		padding: 5px 6px;
		border-radius: 4px;
		background: #fff;
		border: 1px solid #c8c8c8;
		font-size: 14px;
		background: url(/assets/image/ico/ico_arrow_down.png) no-repeat right +5px center;
		background-size: 11px 8px;
		padding-right: 23px;
		font-weight: bold;
	}

	.addrBtnWrap {
		display: flex;
		justify-content: space-around;
		margin-top: 14px;
	}
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
				<p class="guide" style="display: inline-block; margin-right: 10px;">우편봉투에 보내는 사람의 휴대폰 번호를 함께 표시하시겠습니까?</p>
				<div style="display: inline-block;">
					<label style="padding: 6px; margin-right: 10px;">
						<input type="radio" name="displayPhone" id="displaySenderPhone" value="1" style="margin-right: 5px">예
					</label>
					<label>
						<input type="radio" name="displayPhone" id="displaySenderPhone" value="0" style="margin-right: 5px">아니요
					</label>
				</div>
            </div>
        
            <div class="postBox">
                <div class="postHeader">
                    <p class="selectMent">받으시는 분이 누구신가요?</p>                    
                </div>
				<div class="addrBtnWrap">
					<button class="addrBtn" onclick="openAddrModal('show', 'army')">전국 군대 훈련소</button>
					<button class="addrBtn" onclick="openAddrModal('show', 'prison')">전국 구치소/교도소/소년원</button>
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

<div id="addrModal" class="postPopup popupContainer">
	<div class="popupBox">
		<div class="title">
			<p id="addrTitle">전국 군대 훈련소</p>
			<i class="fas fa-times" onclick="openAddrModal('close');"></i>
		</div>

		<select id="postSelect" class="postSelect">
			<option value=""></option>
		</select>

		<div id="addrList">
			<p class="postHead"><i class="fas fa-map-marker-alt" style="margin-right: 5px;"></i> 낙생고등학교</p>
			<p class="postContent">주소 - (13480)경기 성남시 분당구 대왕판교로 477</p>
			<p class="postContent">상세주소 - 낙생고등학교</p>
		</div>
	</div>
</div>

<script>
    // 주소 팝업을 열거나 닫는 함수
	const prisonType = <?=json_encode($this->config->item('prisonType'))?>;  // 교도소 종류
	const prison = <?=json_encode($this->config->item('prison'))?>;  // 교도소 목록
	const armyType = <?=json_encode($this->config->item('armyType'))?>;  // 군대 종류
	const army = <?=json_encode($this->config->item('army'))?>;  // 군대 목록
	// 주소 관련 정보
	var addressType = { prison : prisonType, army : armyType };  // prisonType, armyType 설정
	var addressList = { prison : prison, army : army };  // prison, army 설정
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
		if (addressIdx != 0) {
			$('input[name="displayPhone"][value="' + data['displaySenderPhone'] + '"]').prop('checked', true);
		} else {
			$('input[name="displayPhone"][value="0"]').prop('checked', true);
		}

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

	function openAddrModal(type, addrType = '') {
		let $addrModal = $('#addrModal'); // 주소 선택 모달 엘리먼트 가져오기

		if (type == 'show') {  // 모달 열기
			let postSelectHtml = "<option value=''>선택</option>"; // 기본 선택 옵션 추가
			let adrType = addressType[addrType]; // 선택한 주소 유형에 해당하는 리스트 가져오기

			// 주소 유형 목록을 <option> 태그로 변환하여 추가
			for (let adr of adrType) {
				postSelectHtml += `<option value="${adr}">${adr}</option>`;
			}

			// 모달 제목 설정 ('군대 훈련소' 또는 '구치소/교도소/소년원')
			$('#addrTitle').html(addrType == 'army' ? '군대 훈련소' : '구치소/교도소/소년원');

			// 주소 선택 드롭다운 리스트 업데이트 및 변경 시 이벤트 핸들러 추가
			$('#postSelect').html(postSelectHtml).attr('onchange', `changeAddrSelect('${addrType}')`);

			// 기본적으로 안내 문구 표시
			$("#addrList").html('<p class="empty" style="text-align:center">검색하실 항목을 선택해주세요.</p>');

			// 모달을 표시
			$addrModal.addClass('show');
		} else {  // 모달 닫기
			$addrModal.removeClass('show');
		}
	}

	function changeAddrSelect(adrType) {
		let list = ''; // 주소 목록 HTML을 저장할 변수
		let adrList = addressList[adrType]; // 선택한 유형의 주소 리스트 가져오기
		let choiceType = $('#postSelect option:selected').val(); // 사용자가 선택한 지역 값 가져오기

		// 주소 리스트를 순회하며 선택한 지역과 일치하는 주소만 필터링
		for (let i = 0; i < adrList.length; i++) {
			let data = adrList[i];
			let addressDetail = !data.addressDetail ? data.name : data.addressDetail; // 상세 주소 설정 (없으면 name 사용)

			if (data.region != choiceType) continue; // 선택한 지역과 일치하지 않으면 건너뛰기

			// 주소 정보를 HTML 요소로 변환하여 리스트에 추가
			list += `<div class="postBox">
                        <p class="postHead"><i class="fas fa-map-marker-alt" style="margin-right: 5px;"></i> ${data.name}</p>
                        <p class="postContent">주소 - (${data.post})${data.address}</p>
                        <p class="postContent">상세주소 - ${addressDetail}</p>
                        <button class="postChoiceBtn" onclick="choiceAddr('${data.post}', '${data.address}', '${addressDetail}')">선택</button>
                    </div>`;
		}

		// 생성된 주소 리스트를 HTML에 반영
		$('#addrList').html(list);
	}

	function choiceAddr(post, address, addressDetail) {
		// 받는 사람 주소 필드에 선택한 주소 설정
		$("#receiverAddr").val(`(${post})${address}`);
		$("#receiverAddrDetail").val(addressDetail).attr('disabled', false); // 상세주소 입력 가능하도록 활성화

		// 주소 선택 모달 닫기
		openAddrModal('hide');
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

		let displaySenderPhone = $('input[name="displayPhone"]:checked').val();

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
            receiverTel: $receiverTel.val(),
			displaySenderPhone: displaySenderPhone
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

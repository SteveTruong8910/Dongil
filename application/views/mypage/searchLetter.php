<style>
    #ft_menu{ display: none; }
</style>

<div id="login">
    <img class="logo" src="/assets/image/logo.png"/>
    <p class="ment">🔍주문조회를 하시겠어요?</p>
    
    <p class="guide">휴대번호</p>    
    <input type="tel" id="mbId" class="loginBox"  placeholder="휴대번호를 입력해주세요" maxlength="11">
    
    <p class="guide">성함</p>
    <input type="text" id="mbName" class="loginBox" placeholder="성함을 입력해주세요">
    
    <p class="guide">임의의 숫자 4자리</p>
    <input type="tel" id="mbPassword" class="loginBox" placeholder="비밀번호를 입력해주세요" maxlength="4">                    
<!--
    <p class="guide authTab hide">인증번호</p>
    <div class="multiBox authTab hide">        
        <input type="email" id="authNumber" class="loginBox" placeholder="인증번호 입력해주세요" maxlength="6">
        <a id="checkAuthBtn" class="authBtn" onclick="chkAuthNumber()">인증하기</a>
    </div>
    
-->    
    
    <button class="loginBtn purpleBack" onclick="searchLetter()">조회하기</button>
</div>

<script>
    // 편지를 검색하는 함수
    async function searchLetter(){
        let $mbId = $('#mbId'),             // 휴대전화 번호 입력 필드
            $mbName = $('#mbName'),         // 이름 입력 필드
            $mbPassword = $('#mbPassword'); // 비밀번호 입력 필드
        
        // 휴대전화 번호가 11자리인지 확인
        if (!validatePhone($mbId.val())) {
            showAlert('휴대번호 11자리를 정확히 입력해주세요.', $mbId.focus());
            return;
        }
        
        // 이름이 입력되었는지 확인
        if (!$mbName.val()) {
            showAlert('성함을 입력해주세요.', $mbName.focus());
            return;
        }
        
        // 비밀번호가 4자리인지 확인
        if ($mbPassword.val().length != 4) {
            showAlert('비밀번호 4자리를 정확히 입력해주세요.', $mbName.focus());
            return;
        } 
        
        // 서버에 편지 검색 요청
        const changeCateRes = await postJson('/userApi/searchLetter', {
            mbName: $mbName.val(),
            mbPhoneNumber: $mbId.val(),
            mbPassword: $mbPassword.val()
        });
        
        // 검색 결과가 실패하면 알림 표시
        if (!changeCateRes.result) {
            showAlert(changeCateRes.msg);
            return;
        }
        
        // 검색된 편지 페이지로 이동
        javascript:location.href = `/mypage/myLetter?mbId=${$mbId.val()}&mbName=${$mbName.val()}&mbPassword=${$mbPassword.val()}`;
    }
    
    // 인증 문자를 전송하는 함수
    async function sendMessage(){
        let $mbId = $('#mbId'); // 휴대전화 번호 입력 필드
            
        // 휴대전화 번호가 11자리인지 확인
        if (!validatePhone($mbId.val())) {
            showAlert('휴대번호 11자리를 정확히 입력해주세요.', $mbId.focus());
            return;
        }
        
        // 서버에 인증번호 전송 요청
        const sendMessageRes = await postJson('/userApi/sendMessage', {                        
            phoneNumber: $mbId.val(),
            sendType: 'search',
        });

        // 전송 실패 시 알림 표시
        if (!sendMessageRes.result) {
            showAlert(sendMessageRes.msg);
            return false;
        }
        
        // 전송 성공 시 사용자에게 알림
        showAlert(`${$mbId.val()}로 문자가 전송되었습니다.`);
        
        // 휴대전화 입력 필드를 비활성화하고, 버튼 텍스트 변경
        $mbId.attr('disabled', true);
        $('#authBtn').text('다시 전송');        
        $('.authTab').removeClass('hide'); // 인증 입력 필드를 보이도록 설정
    }
    
    // 인증번호를 확인하는 함수
    async function chkAuthNumber(){
        let $authNumber = $('#authNumber'); // 인증번호 입력 필드
        
        // 인증번호가 6자리인지 확인
        if ($authNumber.val().length != 6) {
            showAlert(`인증번호 6자리를 정확히 입력해주세요.`, $authNumber.focus());
            return;
        }
        
        // 서버에 인증번호 확인 요청
        const chkAuthNumberRes = await postJson('/userApi/chkPhoneAuthNumber', {                        
            phoneNumber : $('#mbId').val(),
            authNumber: $authNumber.val()
        });

        // 인증 실패 시 알림 표시
        if (!chkAuthNumberRes.result) {
            showAlert(chkAuthNumberRes.msg);
            return false;
        }
        
        // 인증 성공 시 알림 표시 후, 입력 필드 비활성화 및 버튼 삭제
        showAlert('인증되었습니다.')
        .then(() => {
            $('#authNumber').attr('disabled', true);
            $('#authBtn, #checkAuthBtn').remove();            
        });
    }
</script>

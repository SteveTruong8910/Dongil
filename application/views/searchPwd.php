<style>
    #ft_menu{ display: none; }
</style>

<div id="login">    
    <img class="logo" src="/assets/image/logo.png"/>
    
    <p class="ment">본인을 인증해주세요</p>    
    <p class="guide">이름</p>
    <input type="text" id="nickname" class="loginBox" placeholder="이름을 입력해주세요">
    
    <p class="guide">이메일</p>
    <div class="multiBox">
        <input type="email" id="mbEmail" class="loginBox" placeholder="이메일을 입력해주세요">
        <a id="authBtn" class="authBtn" onclick="sendMessage()">이메일 받기</a>
    </div>            
    <p class="guide authTab hide">인증번호</p>
    <div class="multiBox authTab hide">        
        <input type="tel" id="authNumber" class="loginBox" placeholder="인증번호 입력해주세요" maxlength="6">
        <a id="checkAuthBtn" class="authBtn" onclick="chkAuthNumber()">인증하기</a>
    </div>

    <div id="changeBox" class="hide">
        <p class="guide">비밀번호</p>
        <input type="password" id="mbPassword" class="loginBox" placeholder="비밀번호를 입력해주세요(4자 이상)">
        <p class="guide">비밀번호 재확인</p>
        <input type="password" id="mbRePassword" class="loginBox" placeholder="비밀번호를 한번 더 입력해주세요(4자 이상)">    
        
        <button onclick="changePwd()" class="loginBtn purpleBack">비밀번호 변경</button>
    </div>
</div>

<script>
    
    // 비밀번호 변경 처리 함수
    async function changePwd(){
        let $mbPassword = $('#mbPassword'),
            $mbRePassword = $('#mbRePassword');

        // 비밀번호 길이 확인
        if($mbPassword.val().length < 4){
            showAlert('비밀번호는 4자이상 입력해주세요.', $mbPassword.focus());
            return;
        }
        // 비밀번호와 재입력된 비밀번호 일치 여부 확인
        else if($mbPassword.val() != $mbRePassword.val()){
            showAlert('비밀번호가 일치하지 않습니다.', $mbRePassword.focus());
            return;
        }

        // 비밀번호 변경 요청
        const changePwdRes = await postJson('/userApi/changePwd', {
            nickname : $('#nickname').val(),
            mbEmail : $('#mbEmail').val(),            
            mbPassword : $mbPassword.val()            
        });

        // 변경 요청 결과 처리
        if (!changePwdRes.result) {
            showAlert(changePwdRes.msg);
            return false;
        }

        // 비밀번호 변경 완료 후 로그인 페이지로 리디렉션
        showAlert("비밀번호가 변경되었습니다.")
        .then(() => {
            location.href = '/login';            
        });
    }                        

    // 인증 문자 발송 함수
    async function sendMessage(){
        let $nickname = $('#nickname'),
            $mbEmail = $('#mbEmail');

        // 이름 입력 확인
        if(!$nickname.val()){
            showAlert('이름을 입력해주세요.', $nickname.focus());
            return;
        }
        // 이메일 형식 확인
        else if(!validateEmail($mbEmail.val())){
            showAlert('이메일 형식을 정확히 입력해주세요.', $mbEmail.focus());
            return;
        }

        // 이메일 인증 요청
        const sendMessageRes = await postJson('/userApi/sendMail', {      
            title : '[동글]이메일 인증번호',
            mbEmail : $mbEmail.val(),
            nickname: $nickname.val(),
            sendType: 'searchPwd' // 비밀번호 찾기 요청
        });

        // 이메일 발송 결과 처리
        if (!sendMessageRes.result) {
            showAlert(sendMessageRes.msg);
            return false;
        }

        // 이메일 전송 완료 후 상태 처리
        showAlert(`${$mbEmail.val()}로 이메일이 전송되었습니다.`);

        // 이름과 이메일 입력 필드를 비활성화
        $nickname.attr('disabled', true);
        $mbEmail.attr('disabled', true);        

        // 인증 버튼과 인증 탭 표시
        $('#authBtn').text('다시 전송');
        $('.authTab').removeClass('hide');
    }

    // 인증 번호 확인 함수
    async function chkAuthNumber(){
        let $authNumber = $('#authNumber');

        // 인증 번호 길이 확인
        if($authNumber.val().length != 6){
            showAlert(`인증번호 6자리를 정확히 입력해주세요.`, $authNumber.focus());
            return;
        }

        // 인증 번호 확인 요청
        const chkAuthNumberRes = await postJson('/userApi/chkAuthNumber', {                        
            mbEmail : $('#mbEmail').val(),
            authNumber: $authNumber.val()
        });

        // 인증 결과 처리
        if (!chkAuthNumberRes.result) {
            showAlert(chkAuthNumberRes.msg);
            return false;
        }

        // 인증 완료 후 상태 처리
        showAlert('인증되었습니다.')
        .then(() => {
            $('#authNumber').attr('disabled', true);  // 인증 번호 입력 필드 비활성화
            $('#authBtn, #checkAuthBtn').remove();   // 인증 버튼 제거
            $('#changeBox').removeClass('hide');      // 비밀번호 변경 입력 박스 표시
        });
    }

    
/*
    async function sendMessage(){
        let $mbId = $('#mbId');
            
        if(!validatePhone($mbId.val())){
            showAlert('휴대번호 11자리를 정확히 입력해주세요.', $mbId.focus());
            return;
        }
        
        const sendMessageRes = await postJson('/userApi/sendMessage', {            
            phoneNumber: $mbId.val(),
            sendType: 'searchPwd'
        });

        if (!sendMessageRes.result) {
            showAlert(sendMessageRes.msg);
            return false;
        }
        
        showAlert(`${$mbId.val()}로 문자가 전송되었습니다.`);
        
        $mbId.attr('disabled', true);
        $('#authBtn').text('다시 전송');        
        $('.authTab').removeClass('hide');
    }
    
    async function chkAuthNumber(){
        let $authNumber = $('#authNumber');
        
        if($authNumber.val().length != 6){
            showAlert(`인증번호 6자리를 정확히 입력해주세요.`, $authNumber.focus());
            return;
        }
                
        const chkAuthNumberRes = await postJson('/userApi/chkPhoneAuthNumber', {                        
            phoneNumber : $('#mbId').val(),
            authNumber: $authNumber.val()
        });

        if (!chkAuthNumberRes.result) {
            showAlert(chkAuthNumberRes.msg);
            return false;
        }
        
        showAlert('인증되었습니다.')
        .then(() => {
            $('#authNumber').attr('disabled', true);
            $('#authBtn, #checkAuthBtn').remove();
            $('#changeBox').removeClass('hide');
        });
    }
*/ 
</script>
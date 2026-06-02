<style>
    #ft_menu{ display: none; }
</style>

<div id="login">    
    <img class="logo" src="/assets/image/logo.png?v=1"/>
    
    <p class="ment">계정을 만들어 주세요</p>
    <p class="guide">휴대번호</p>
    <div class="multiBox">
        <input type="tel" id="mbId" class="loginBox" placeholder="휴대번호를 입력해주세요">
<!--        <a id="authBtn" class="authBtn" onclick="sendMessage()">인증번호 받기</a>-->
    </div>
    
<!--
    <p class="guide authTab hide">인증번호</p>
    <div class="multiBox authTab hide">
        <input type="text" id="authNumber" class="loginBox" placeholder="인증번호 입력해주세요" maxlength="6">
        <a id="checkAuthBtn" class="authBtn" onclick="chkAuthNumber()">인증하기</a>
    </div>
-->    
    <p class="guide">비밀번호</p>
    <input type="password" id="mbPassword" class="loginBox" placeholder="비밀번호를 입력해주세요(4자 이상)">
    <p class="guide">비밀번호 재확인</p>
    <input type="password" id="mbRePassword" class="loginBox" placeholder="비밀번호를 한번 더 입력해주세요(4자 이상)">    
    
    <p class="guide">이메일</p>
    <input type="email" id="mbEmail" class="loginBox" placeholder="이메일을 입력해주세요">
    
    <p class="guide">이름</p>
    <input type="text" id="nickname" class="loginBox" placeholder="이름을 입력해주세요">        
    
    <button onclick="goJoin()" class="loginBtn purpleBack">회원가입</button>
    
    <p class="joinMent">
        이미 계정이 있으신가요? 
        <a class="purpleText" href="/searchPwd">비밀번호 찾기</a>
    </p>
</div>

<script>
    
    // 회원가입 버튼 클릭 시 실행되는 함수
    async function goJoin(){
        // 각 입력 필드의 값을 변수에 저장
        let $mbId = $('#mbId'),
            $mbPassword = $('#mbPassword'),
            $mbRePassword = $('#mbRePassword'),
            $mbEmail = $('#mbEmail'),
            $nickname = $('#nickname');        
        
        // 휴대폰 번호 유효성 검사
        if(!validatePhone($mbId.val())){
            showAlert('휴대번호 11자리를 정확히 입력해주세요.', $mbId.focus());
            return;
        // 비밀번호 길이 검사
        }else if($mbPassword.val().length < 4){
            showAlert('비밀번호는 4자이상 입력해주세요.', $mbPassword.focus());
            return;
        // 비밀번호 일치 검사
        }else if($mbPassword.val() != $mbRePassword.val()){
            showAlert('비밀번호가 일치하지 않습니다.', $mbRePassword.focus());
            return;
        // 이메일 형식 검사
        }else if(!validateEmail($mbEmail.val())){
            showAlert('이메일 형식을 정확히 입력해주세요.', $mbEmail.focus());
            return;
        // 닉네임 입력 검사
        }else if(!$nickname.val()){
            showAlert('이름을 입력해주세요.', $nickname.focus());
            return;
        }
        
        // 서버에 회원가입 요청
        const goJoinRes = await postJson('/userApi/goJoin', {
            mbId : $mbId.val(),
            mbPassword : $mbPassword.val(),
            mbRePassword : $mbRePassword.val(),
            mbEmail : $mbEmail.val(),
            nickname: $nickname.val()
        });

        // 서버 응답 처리
        if (!goJoinRes.result) {
            showAlert(goJoinRes.msg);
            return false;
        }
        
        // 성공 시 회원가입 완료 메시지 및 리디렉션
        showAlert("회원가입이 완료되었습니다.")
        .then(() => {
            location.href = '/';
        });
    }
    
    // 인증 문자 전송 함수
    async function sendMessage(){
        // 휴대폰 번호 입력 값 가져오기
        let $mbId = $('#mbId');
            
        // 휴대폰 번호 유효성 검사
        if(!validatePhone($mbId.val())){
            showAlert('휴대번호 11자리를 정확히 입력해주세요.', $mbId.focus());
            return;
        }
        
        // 서버에 인증 문자 전송 요청
        const sendMessageRes = await postJson('/userApi/sendMessage', {            
            phoneNumber : $mbId.val()
        });

        // 서버 응답 처리
        if (!sendMessageRes.result) {
            showAlert(sendMessageRes.msg);
            return false;
        }
        
        // 문자 전송 완료 메시지 출력
        showAlert(`${$mbId.val()}로 문자가 전송되었습니다.`);
        
        // 인증 버튼 비활성화 및 상태 변경
        $mbId.attr('disabled', true);
        $('#authBtn').text('다시 전송');
        $('.authTab').removeClass('hide');
    }
    
    // 인증 번호 확인 함수
    async function chkAuthNumber(){
        let $authNumber = $('#authNumber');
        
        // 인증 번호 길이 검사
        if($authNumber.val().length != 6){
            showAlert(`인증번호 6자리를 정확히 입력해주세요.`, $authNumber.focus());
            return;
        }
                
        // 서버에 인증 번호 확인 요청
        const chkAuthNumberRes = await postJson('/userApi/chkPhoneAuthNumber', {                        
            phoneNumber : $('#mbId').val(),
            authNumber: $authNumber.val()
        });

        // 서버 응답 처리
        if (!chkAuthNumberRes.result) {
            showAlert(chkAuthNumberRes.msg);
            return false;
        }
        
        // 인증 완료 메시지 및 버튼 비활성화
        showAlert('인증되었습니다.')
        .then(() => {
            $('#authNumber').attr('disabled', true);
            $('#authBtn, #checkAuthBtn').remove();            
        });
    }
    
</script>
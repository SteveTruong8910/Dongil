<style>
    #ft_menu{ display: none; }
</style>

<div id="login">
    <img class="logo" src="/assets/image/logo.png"/>
    <p class="ment">💌편지를 작성 하시겠어요?</p>
    
    <p class="guide">휴대번호</p>    
    <input type="tel" id="mbId" class="loginBox"  placeholder="휴대번호를 입력해주세요" maxlength="11">
    
    <p class="guide">성함</p>
    <input type="text" id="mbName" class="loginBox" placeholder="성함을 입력해주세요">
    
    <p class="guide">비밀번호 4자리</p>
    <input type="password" id="mbPassword" class="loginBox" placeholder="비밀번호를 입력해주세요" maxlength="4">                    
<!--
    <p class="guide authTab hide">인증번호</p>
    <div class="multiBox authTab hide">        
        <input type="email" id="authNumber" class="loginBox" placeholder="인증번호 입력해주세요" maxlength="6">
        <a id="checkAuthBtn" class="authBtn" onclick="chkAuthNumber()">인증하기</a>
    </div>
    
-->    
    
    <button class="loginBtn purpleBack" onclick="searchLetter()">편지쓰기</button>
</div>

<script>
    async function searchLetter(){
        let $mbId = $('#mbId'),
            $mbName = $('#mbName'),
            $mbPassword = $('#mbPassword');
        
        if(!validatePhone($mbId.val())){
            showAlert('휴대번호 11자리를 정확히 입력해주세요.', $mbId.focus());
            return;
        }
        
        if(!$mbName.val()){
            showAlert('성함을 입력해주세요.', $mbName.focus());
            return;
        }
        
        if($mbPassword.val().length != 4){
            showAlert('비밀번호 4자리를 정확히 입력해주세요.', $mbName.focus());
            return;
        } 
        
        javascript:location.href = `/letter?mbId=${$mbId.val()}&mbName=${$mbName.val()}&mbPassword=${$mbPassword.val()}`;                
    }
    
</script>
<?
    $client_id = "tDVeMCohzRzopuN8rrCQ";
    $redirect_uri = urlencode("https://dongl.co.kr/login/naverLoginCallback");
    $state = md5(uniqid(rand(), TRUE)); // CSRF 공격을 방지하기 위한 상태 코드    
    
    $naverLoginUrl = "https://nid.naver.com/oauth2.0/authorize?response_type=code&client_id={$client_id}&redirect_uri={$redirect_uri}&state={$state}";
?>
<style>
    #ft_menu{ display: none; }
</style>

<script src="https://developers.kakao.com/sdk/js/kakao.min.js"></script>
<script type="text/javascript" src="https://appleid.cdn-apple.com/appleauth/static/jsapi/appleid/1/en_US/appleid.auth.js"></script>

<div id="login">    
    <img class="logo" src="/assets/image/logo.png?v=1"/>
    
    <p class="ment">로그인 해주세요</p>
    <p class="guide">휴대번호</p>
    <input type="tel" id="mbId" class="loginBox" placeholder="휴대번호를 입력해주세요" oninput="validateNumberInput(this)">
    <p class="guide">비밀번호</p>
    <input type="password" id="mbPassword" class="loginBox" placeholder="비밀번호를 입력해주세요">
        
    <button class="loginBtn purpleBack" onclick="goLogin()">로그인</button>    
    
    <? 
//    if(!$this->config->item('isApp')){
    ?>
    <p class="snsMent">간편 로그인</p>
    
    <div class="snsLoginBox">     
        
        <!--카카오-->
        <a onclick="kakaoLogin()">
            <img src="/assets/image/ico/ico_kakao_login.png" width="70">            
        </a>
        
        <!--네이버-->
        
        <!--id="naverLoginBtn" onclick="naverLogin()" data-href="<?=$naverLoginUrl?>"-->
        <a href="<?=$naverLoginUrl?>">
            <img src="/assets/image/ico/ico_naver_login.png" width="70">
        </a>        
    
        <!--구글-->         
        <? if(!isWebView()){ ?>
<!--
            <a href="/login/googleLogin">
                <img src="/assets/image/ico/ico_google_login.png" width="70">            
            </a>
-->
        <? } ?>    
        
        <!--애플-->
        <? if(!isAndroid()){ ?>
            <a onclick="appleLogin()">
                <img src="/assets/image/ico/ico_apple_login.png" width="70">            
            </a>
        <? } ?>        
    </div>
    
    <p class="joinMent">계정이 없으신가요? 
        <a href="javascript:nav.locationHref('/join', 'a3')" class="purpleText">회원가입 하기</a>
    </p>
    
    <p class="joinMent">
        이미 계정이 있으신가요? 
        <a class="purpleText" href="javascript:nav.locationHref('/searchPwd', 'a4')">비밀번호 찾기</a>
    </p>
</div>



<script>
    // 카카오 SDK 초기화 (앱 키를 사용하여 카카오 API를 사용 준비)
    Kakao.init('69fc19daa1d0b9675dd2f45fe481f9ec');
    
    // Apple 로그인 SDK 초기화 (클라이언트 ID, 리디렉션 URI, 상태값 등 설정)
    AppleID.auth.init({
        clientId: 'dongl.co.kr',  // 애플 로그인에 사용된 서비스 ID
        scope: '',  // 권한 범위 (여기서는 비어 있음)
        redirectURI: 'https://dongl.co.kr/login/appleLoginCallback',  // 로그인 후 리디렉션 URI
        state: 'state',  // 상태 값
        usePopup: false  // 팝업을 사용할지 여부
    });
    
    // 카카오 로그인 함수
    function kakaoLogin() {  
        // 카카오 로그인 인증 요청 (리디렉션 URI를 지정)
        Kakao.Auth.authorize({
            redirectUri: 'https://dongl.co.kr/login/kakaoLogin',  // 로그인 후 리디렉션 URI
        });
    }
    
    // 애플 로그인 함수
    function appleLogin() {
        // 애플 로그인 요청
        AppleID.auth.signIn();
    }
    
    // 네이버 로그인 함수
    function naverLogin() {
        if(window.ReactNativeWebView) {
            // React Native WebView 환경에서는 특정 페이지로 리디렉션
            nav.locationHref("/login/naverLogin", 'z2');
        } else {
            // 일반 웹 환경에서는 버튼에 설정된 URL로 이동
            location.href = $('#naverLoginBtn').data('href');
        }
    }
    
    // 로그인 처리 함수
    async function goLogin() {
        let $mbId = $('#mbId'),  // 사용자 아이디 입력 필드
            $mbPassword = $('#mbPassword');  // 사용자 비밀번호 입력 필드
         
        // 아이디가 입력되지 않았으면 경고 메시지 출력
        if(!$mbId.val()){
            showAlert('휴대번호를 정확히 입력해주세요.', $mbId.focus());
            return;
        } else if($mbPassword.val().length < 4){
            // 비밀번호가 4자 이상 입력되지 않았으면 경고 메시지 출력
            showAlert('비밀번호를 4자이상 입력해주세요.', $mbPassword.focus());
            return;
        }
         
        // 로그인 요청을 서버에 보냄
        const goLoginRes = await postJson('/userApi/goLogin', {
            mbId : $mbId.val(),
            mbPassword : $mbPassword.val()
        });

        // 서버 응답 결과에 따라 로그인 성공/실패 처리
        if (!goLoginRes.result) {
            showAlert(goLoginRes.msg);  // 실패 시 경고 메시지 출력
            return false;
        }
        
        // 로그인 성공 시 홈으로 리디렉션
        nav.locationHref('/', 'clear');
    }
    
    // DOM 준비 완료 후 이벤트 리스너 추가
    $(function(){
        // 아이디나 비밀번호 필드에서 Enter 키를 누르면 로그인 시도
        $('#mbId, #mbPassword').on('keyup', function(e){
            if(e.keyCode == 13){
                goLogin();  // Enter 키가 눌리면 goLogin 함수 호출
            }
        });
    });
</script>
<div id="login">
    <div class="loginBox">
        <img class="logo" src="/assets/image/logo.png" width="250">
        <input type="text" id="id" class="id" placeholder="아이디"/>
        <input type="password" id="password" class="password" placeholder="비밀번호"/>
        <button class="btnSubmit btnLogin" onclick="login()">로그인</button>
    </div>
</div>

<script>
    // 로그인 함수 정의
    async function login(){
        // 아이디와 비밀번호 입력 요소 선택
        let $id = $('#id'),
            $password = $('#password');
        
        // 아이디 입력 확인
        if(!$id.val()){
            // 아이디가 비어있으면 알림 표시하고 포커스 이동
            showAlert('아이디를 입력해주세요.', $id.focus());
            return;
        }else if(!$password.val()){            
            // 비밀번호가 비어있으면 알림 표시하고 포커스 이동
            showAlert('비밀번호를 입력해주세요.', $password.focus());
            return;
        }
        
        // 서버에 로그인 요청 보내기
        const loginRes = await postJson('/adminApi/login', {
            id : $id.val(),
            password : $password.val()
        });
        
        // 로그인 실패 시 알림 표시
        if(!loginRes.result){
            showAlert(loginRes.msg);
            return;
        }
        
        // 로그인 성공 시 관리자 대시보드로 리디렉션
        location.replace("/admin/post");
    }
    
    // DOM 로딩 후 이벤트 리스너 설정
    $(function(){
        // 아이디와 비밀번호 입력창에서 Enter 키 입력 시 로그인 함수 호출
        $('#id, #password').on('keyup', function(e){
            // Enter 키가 눌린 경우에만 실행
            if(e.keyCode != 13) return;            
            login(); // 로그인 함수 호출
            return false; // 기본 Enter 동작 방지
        });
    });
</script>

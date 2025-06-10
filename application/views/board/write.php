<style>
    #ft_menu { display: none; }
</style>

<div id="boardW">
    <p class="boardTitle">문의를 남겨주세요</p>
    
    <p class="ment">제목</p>
    <input type="text" id="title" class="title" placeholder="제목을 입력해주세요" />
    
    <p class="ment">내용</p>
    <textarea id="content" class="content" placeholder="내용을 작성해주세요"></textarea>
    
    <span class="<?=!empty($this->user)? 'hide' : ''?>">
        <p class="ment">비밀번호(비밀글 🔒)</p>
        <input type="password" id="boardPwd" class="title" placeholder="비밀번호를 입력해주세요" />
    </span>
    <button class="submitBtn" onclick="createBoard()">문의하기</button>
</div>

<script>
    // 로그인 여부를 확인하여 'Y' 또는 'N' 값을 설정
    const isLogin = "<?=!empty($this->user)? 'Y' : 'N'?>";
    
    // 게시글 작성 함수
    async function createBoard(){
        // 제목, 내용, 비밀번호 필드를 각각 변수에 저장
        let $title = $('#title'),
            $content = $('#content'),
            $boardPwd = $('#boardPwd');
                
        // 제목이 비어있으면 경고 메시지를 보여주고 포커스를 맞춤
        if(!$title.val()){
            showAlert('제목을 입력해주세요.', $title.focus());
            return;
        }
        
        // 내용이 비어있으면 경고 메시지를 보여주고 포커스를 맞춤
        if(!$content.val()){
            showAlert('내용을 입력해주세요.', $content.focus());
            return;
        }
        
        // 로그인되지 않은 경우 비밀번호를 입력받도록 요청
        if(isLogin == 'N' && !$boardPwd.val()){
            showAlert('비밀번호를 입력해주세요.', $boardPwd.focus());
            return;
        }
        
        // 게시글 생성 요청을 서버에 보냄
        const createBoardRes = await postJson('/userApi/createBoard', {
            type : 'qna',       // 게시판 타입 (질문답변 게시판)
            title : $title.val(),     // 제목
            content : $content.val(), // 내용
            boardPwd : $boardPwd.val() // 비밀번호 (비회원일 경우)
        });
        
        // 요청 결과가 실패하면 경고 메시지를 보여줌
        if(!createBoardRes.result){
            showAlert(createBoardRes.msg);
            return;
        }
        
        // 게시글이 성공적으로 등록되면 성공 메시지를 보여주고 게시판 목록으로 리디렉션
        showAlert('등록되었습니다.')
        .then(() => {
            nav.locationHref('/board?type=qna', 'c1', true); // 게시판 목록으로 이동
        });
    }
</script>

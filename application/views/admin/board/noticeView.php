<div id="boardW" style="margin:0 auto; width: 600px; ">
    <div id="notice">
        <? if($isCreate) { ?>
            <div class="topBox" style="float: right;">
                <button class="btnDelete btnSubmit" style="width:100px;" onclick="deleteBoard()">삭제</button>
            </div>
        <? } ?>     
    </div>
    
    <p class="boardTitle">공지사항을 작성해주세요</p>
    
    <p class="ment">제목</p>
    <input type="text" id="title" class="title" placeholder="제목을 입력해주세요" value="<?=$isCreate? $info['title'] : ''?>"/>
    
    <p class="ment">내용</p>
    <textarea id="content" class="content" placeholder="내용을 작성해주세요"><?=$isCreate? ($info['content']) : ''?></textarea>
    <button class="submitBtn" onclick="createBoard()">저장하기</button>
</div>

<link rel="stylesheet" href="/assets/css/summernote-lite.min.css">
<script src="/assets/js/summernote-lite.js"></script>
<script src="/assets/js/summernote-ko-KR.js"></script>

<script>
    // 현재 게시글의 고유 ID (신규 작성이면 0, 기존 게시글이면 해당 ID)
    var boardIdx = <?=$isCreate ? $info['idx'] : 0?>;

    /**
     * Summernote WYSIWYG 에디터 초기 설정
     */
    function defaultSetup() {        
        $('#content').summernote({
            height: 'calc(100vh - 600px)', // 에디터 높이 설정 (뷰포트 기준)
            minHeight: null, // 최소 높이 제한 없음
            maxHeight: null, // 최대 높이 제한 없음
            lang: "ko-KR", // 한글 설정
            toolbar: [
                ['style', ['style']], // 스타일 옵션
                ['font', ['bold', 'underline', 'clear']], // 굵게, 밑줄, 서식 지우기
                ['insert', ['picture']] // 이미지 삽입
            ],
            callbacks: { // 필요시 콜백 함수 추가 가능
            }
        });        
    }

    /**
     * 게시글 생성 또는 수정
     * - 제목 및 내용 입력 필수
     * - 서버에 데이터 전송 후 결과 처리
     */
    async function createBoard() {
        let $title = $('#title'),  // 제목 입력 필드
            $content = $('#content'); // 내용 입력 필드            
                
        // 제목이 비어 있는 경우 경고 메시지 표시 후 포커스 이동
        if (!$title.val()) {
            showAlert('제목을 입력해주세요.', $title.focus());
            return;
        }
        
        // 내용이 비어 있는 경우 경고 메시지 표시 후 포커스 이동
        if (!$content.val()) {
            showAlert('내용을 입력해주세요.', $content.focus());
            return;
        }

        // 게시글 저장 요청
        const createBoardRes = await postJson('/userApi/createBoard', {
            type: 'notice',       // 게시판 유형 (공지사항)
            boardIdx: boardIdx,   // 게시글 ID (신규 = 0, 수정 = 기존 ID)
            title: $title.val(),  // 입력한 제목
            content: $content.val() // 입력한 내용
        });

        // 저장 실패 시 경고 메시지 출력 후 종료
        if (!createBoardRes.result) {
            showAlert(createBoardRes.msg);
            return;
        }

        // 저장 성공 시 알림 후 목록 페이지로 이동
        showAlert('저장되었습니다.')
        .then(() => {
            location.href = "/admin/board?type=notice";
        });
    }

    /**
     * 게시글 삭제
     * - 사용자 확인 후 삭제 요청 실행
     */
    async function deleteBoard() {
        // 사용자에게 삭제 여부 확인
        if (!confirm('해당 게시글을 삭제하시겠습니까?')) return;

        // 게시글 삭제 요청
        const deleteBoardRes = await postJson('/userApi/deleteBoard', {
            boardIdx: boardIdx
        });

        // 삭제 실패 시 경고 메시지 출력 후 종료
        if (!deleteBoardRes.result) {
            showAlert(deleteBoardRes.msg);
            return;
        }

        // 삭제 성공 시 알림 후 목록 페이지로 이동
        showAlert('삭제되었습니다.')
        .then(() => {
            location.href = "/admin/board?type=notice";
        });
    }

    // 페이지 로드 시 에디터 초기화 실행
    defaultSetup();
</script>

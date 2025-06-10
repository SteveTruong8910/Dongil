<div id="notice">
    <p class="title">문의 관리</p>
    
    <div class="local">
        문의 상세
    </div>
        
    <div class="topBox" style="float: right;">
        <button class="btnDelete btnSubmit" style="width:100px;" onclick="deleteBoard()">삭제</button>
    </div>
    
    <div id="boardV">
        <p class="date"><?=$info['regDate']?></p>
        <p class="mbInfo">회원 번호 : <?=$info['memberIdx']?></p>
        <p class="mbInfo">회원이름 : <?=empty($mbInfo['senderName'])? '미주문자/비회원' : $mbInfo['senderName']?></p>                
        <? if(!empty($mbInfo['senderTel'])) { ?>
            <p class="mbInfo">회원 전화번호 : <?=$mbInfo['senderTel']?></p>
        <? } ?>        
        
        <p class="title" style="margin: 0; padding-left:0px;"><?=$info['title']?></p>
        <div class="content">
            <?=nl2br($info['content'])?>
        </div>

        <div class="answer">
            <div class="answerHead">
                <div class="profileWrap">
                    <i class="fas fa-user"></i>
                    <span>관리자</span>
                </div>
                <div class="date"><?=$isAnswer? $answerInfo['regDate'] : ''?></div>
            </div>
            
            <div id="boardW">
                <textarea id="content" class="content" placeholder="내용을 작성해주세요"><?=$isAnswer? $answerInfo['content'] : ''?></textarea>
                <button class="submitBtn" onclick="createBoard()">저장하기</button>
            </div>
        </div> 
    </div>
</div>

<script>
    // 현재 게시글 ID (Q&A 게시글의 고유 식별자)
    var boardIdx = '<?=$info['idx']?>';

    // 답변 게시글 ID (답변이 존재하면 해당 ID, 없으면 0)
    var boardAnswerIdx = '<?=$isAnswer ? $answerInfo['idx'] : 0?>';

    /**
     * Q&A 게시글 또는 답변을 저장하는 함수
     */
    async function createBoard() {
        let $content = $('#content'); // 입력된 내용 가져오기
            
        // 내용이 비어 있는 경우 경고 메시지 표시 후 포커스 이동
        if (!$content.val()) {
            showAlert('내용을 입력해주세요.', $content.focus());
            return;
        }

        // 서버로 데이터 전송 (Q&A 답변 저장 요청)
        const createBoardRes = await postJson('/userApi/answerCreateBoard', {
            boardIdx: boardIdx,          // 질문 게시글 ID
            boardAnswerIdx: boardAnswerIdx, // 답변 게시글 ID (없으면 0)
            content: $content.val()      // 사용자가 입력한 내용
        });

        // 요청 실패 시 경고 메시지 출력 후 종료
        if (!createBoardRes.result) {
            showAlert(createBoardRes.msg);
            return;
        }

        // 저장 성공 시 알림 후 Q&A 목록 페이지로 이동
        showAlert('저장되었습니다.')
        .then(() => {
            location.href = "/admin/board?type=qna";
        });
    }

    /**
     * Q&A 게시글 삭제 함수
     */
    async function deleteBoard() {
        // 사용자에게 삭제 여부 확인
        if (!confirm('해당 게시글을 삭제하시겠습니까?')) return;

        // 서버에 삭제 요청
        const deleteBoardRes = await postJson('/userApi/deleteBoard', {
            boardIdx: boardIdx // 삭제할 게시글 ID
        });

        // 삭제 실패 시 경고 메시지 출력 후 종료
        if (!deleteBoardRes.result) {
            showAlert(deleteBoardRes.msg);
            return;
        }

        // 삭제 성공 시 알림 후 Q&A 목록 페이지로 이동
        showAlert('삭제되었습니다.')
        .then(() => {
            location.href = "/admin/board?type=qna";
        });
    }
</script>
